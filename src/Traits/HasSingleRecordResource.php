<?php

namespace CoringaWc\FilamentSingleRecordResource\Traits;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function Filament\Support\original_request;

/**
 * Trait for Resources representing a single record per authenticated user.
 *
 * ## Automatic behavior by resource type
 *
 * The trait detects whether the resource is a **root** (no `$parentResource`) or
 * **nested** (with `$parentResource`) and applies the correct behavior in each case.
 *
 * ### Root resource (e.g. `MyWalletResource`)
 *
 * The standard Filament pattern requires an `index` page to register the sidebar item
 * and generate `getIndexUrl()`. In this pattern there is no listing — the resource
 * displays **a single record** bound to the authenticated user. The `view` page is
 * registered at the `'view'` key with a root route `/`, without a `{record}` parameter
 * in the URL.
 *
 * Provided behavior:
 * - `getIndexUrl()` points to `getUrl('view')` — no `.index` route needed
 * - `getNavigationUrl()` points to `getUrl('view')` — correct sidebar link
 * - `getNavigationItems()` bypasses Filament's `hasPage('index')` guard
 * - `canAccess()` falls back to `view` on the resolved record when `viewAny` is denied
 * - `resolveSingleRecord()` / `resolveSingleRecordBuilder()` centralize default record lookup
 *
 * ### Nested resource (e.g. `Tenants\TenantResource` under `MyWalletResource`)
 *
 * When a nested resource overrides `getParentResourceRegistration()` to remove URL
 * parameters (e.g. `wallet_id`), Filament can no longer automatically derive the full
 * slug or the index URL through the parent chain.
 *
 * Provided behavior:
 * - `getSlug()` prefixes the root resource slug to the base slug via parent chain traversal
 * - `getIndexUrl()` points to `rootResource::getUrl('view')` of the root resource
 * - `resolveSingleRecordParent()` traverses the parent chain up to the root resource
 *
 * ## Usage
 *
 * **Root resource:**
 * ```php
 * class MyWalletResource extends Resource implements HasShieldPermissions
 * {
 *     use HasActiveIcon;
 *     use HasSingleRecordResource;
 *
 *     public static function getPages(): array
 *     {
 *         return ['view' => ViewMyWallet::route('/')];
 *     }
 * }
 * ```
 *
 * **Nested resource:**
 * ```php
 * class TenantResource extends BaseTenantResource
 * {
 *     use HasSingleRecordResource;
 *
 *     protected static ?string $parentResource = MyWalletResource::class;
 *
 *     public static function getParentResourceRegistration(): ?ParentResourceRegistration
 *     {
 *         return null; // removes {wallet_id} from the URL
 *     }
 * }
 * ```
 *
 * ## Integration with HasSingleRecord
 *
 * `HasSingleRecord` (used in Pages) automatically detects whether the Resource uses
 * this trait by checking `method_exists($resource, 'resolveSingleRecordParent')` and
 * delegates root resource resolution to this method.
 *
 * ## Note on 'index' vs 'view' route key
 *
 * Filament registers the route name based on the page class slug (`ViewRecord` → `view`),
 * regardless of the key in the `getPages()` array. Therefore the actual route generated
 * is always `filament.admin.resources.{slug}.view`, never `.index`. This trait uses
 * `getUrl('view')` to ensure the URL is resolved correctly.
 *
 * @phpstan-require-extends \Filament\Resources\Resource
 *
 * @mixin \Filament\Resources\Resource
 */
trait HasSingleRecordResource
{
    /**
     * Root single-record resources should remain accessible when Filament denies
     * `viewAny()`, as long as the authenticated user can `view()` the resolved record.
     *
     * This keeps resource registration and sidebar access aligned with the single-record
     * UX, where there is no collection page and the entrypoint is always the `view` page.
     */
    public static function canAccess(): bool
    {
        if (static::isNestedResource()) {
            return parent::canAccess();
        }

        if (parent::canAccess()) {
            return true;
        }

        $record = static::resolveSingleRecord();

        if ($record === null) {
            return false;
        }

        return static::canView($record);
    }

    /**
     * Indicates whether the resource is nested (has a parent configured).
     * Considers both the static `$parentResource` property and a custom
     * `getParentResourceRegistration()` override.
     */
    public static function isNestedResource(): bool
    {
        return static::getParentResource() !== null
            || static::getParentResourceRegistration() !== null;
    }

    /**
     * Traverses the parent chain up to the root resource (the one with no parent).
     * Tries first via `getParentResource()` and falls back to `getParentResourceRegistration()`.
     *
     * Throws `\LogicException` if the current resource has no parent configured,
     * as this method requires at least one level of nesting.
     *
     * @return class-string<\Filament\Resources\Resource>
     */
    public static function resolveSingleRecordParent(): string
    {
        $current = static::class;
        $parent = $current::getParentResource()
            ?? $current::getParentResourceRegistration()?->getParentResource();

        if ($parent === null) {
            throw new \LogicException(
                sprintf(
                    'Resource [%s] has no parent resource configured. HasSingleRecordResource::resolveSingleRecordParent() requires at least one level of nesting.',
                    $current,
                )
            );
        }

        while (true) {
            $next = $parent::getParentResource()
                ?? $parent::getParentResourceRegistration()?->getParentResource();

            if ($next === null) {
                break;
            }

            $parent = $next;
        }

        return $parent;
    }

    /**
     * Resolves the authenticated user's single record for root resources.
     *
     * Exposed on the Resource so authorization, navigation, and page mounting all share
     * the same resolution strategy by default.
     */
    public static function resolveSingleRecord(): ?Model
    {
        if (static::isNestedResource()) {
            return null;
        }

        $modelClass = static::getModel();

        /** @var Model|null $record */
        $record = static::resolveSingleRecordBuilder($modelClass::query())->first();

        return $record;
    }

    /**
     * Query builder used to locate the authenticated user's single record.
     *
     * Override this on the Resource when the default `whereBelongsTo($user)` lookup is
     * insufficient and you still want all entrypoints to share the same logic.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public static function resolveSingleRecordBuilder(Builder $query): Builder
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            return $query->whereKey(-1);
        }

        try {
            return $query->whereBelongsTo($user);
        } catch (\RuntimeException) {
            return $query->whereKey(-1);
        }
    }

    /**
     * For nested resources: prefixes the root resource slug to the base slug.
     * For root resources (no parent): returns the default Filament slug via `parent::`.
     */
    public static function getSlug(?Panel $panel = null): string
    {
        if (! static::isNestedResource()) {
            return parent::getSlug($panel);
        }

        $root = static::resolveSingleRecordParent();

        return $root::getSlug($panel) . '/' . parent::getSlug($panel);
    }

    /**
     * For root resources: points to the `view` page (no index route).
     * For nested resources: points to the root resource's `view` page.
     *
     * @param  array<mixed>  $parameters
     */
    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        if (! static::isNestedResource()) {
            return static::getUrl('view');
        }

        $root = static::resolveSingleRecordParent();

        return $root::getUrl('view');
    }

    /**
     * URL used by the sidebar navigation item.
     * Overridden only for root resources: points to `view` instead of `index`.
     */
    public static function getNavigationUrl(): string
    {
        if (static::isNestedResource()) {
            return parent::getNavigationUrl();
        }

        return static::getUrl('view');
    }

    /**
     * Overrides the default implementation that returns `[]` when there is no `index` page.
     * Builds the `NavigationItem` using `getNavigationUrl()` to point to `view`.
     * Applies only to root resources; nested resources delegate to the default behavior.
     *
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        if (static::isNestedResource()) {
            return parent::getNavigationItems();
        }

        $activeRoutePattern = static::getNavigationItemActiveRoutePattern();

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => original_request()->routeIs($activeRoutePattern))
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }
}
