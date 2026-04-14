<?php

namespace CoringaWc\FilamentSingleRecordResource\Traits;

use App\Models\User;
use CoringaWc\FilamentSingleRecordResource\Contracts\SingleRecordResolvableResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Exceptions\UrlGenerationException;

/**
 * Trait for record pages (ViewRecord/EditRecord) following the single-record pattern.
 *
 * ## Automatic behavior by page type
 *
 * The trait detects whether the page belongs to a **root resource** (no `$parentResource`)
 * or a **nested resource** (with `$parentResource`) and applies the correct behavior.
 *
 * ### Root resource page (e.g. `ViewMyWallet`)
 *
 * The single record is automatically resolved from the authenticated user, without a
 * `{record}` parameter in the URL. The breadcrumb suppresses the record title, displaying
 * only: `<Resource Label> → View`.
 *
 * Provided behavior:
 * - `mount()` accepts `$record = ''`, resolves via `resolveSingleRecord()` and redirects
 *   to login if no record is found.
 * - `getHeading()` displays the model label instead of the record title.
 * - `getBreadcrumbs()` suppresses the record title in the breadcrumb.
 * - `resolveSingleRecord()` resolves via `resolveSingleRecordBuilder()` by default.
 * - `resolveSingleRecordBuilder()` prefers the Resource contract by default.
 * - Page-specific overrides remain supported for backward compatibility.
 *
 * Override `resolveSingleRecord()` on the page when you need page-specific logic:
 *
 * ```php
 * protected function resolveSingleRecord(): ?Model
 * {
 *
 *     /** @var User|null $user * /
 *     $user = Filament::auth()->user();
 *
 *     if ($user === null) {
 *         return null;
 *     }
 *
 *     return $user->wallet()->firstOrCreate([]);
 * }
 * ```
 *
 * Prefer implementing `SingleRecordResolvableResource` on the Resource and overriding
 * `resolveSingleRecordBuilder()` there so page mount and authorization can share the
 * same lookup strategy. For backward compatibility, the page still accepts Resources
 * that expose the same methods without implementing the interface.
 *
 * ```php
 * protected function resolveSingleRecordBuilder(Builder $query): Builder
 * {
 *     return parent::resolveSingleRecordBuilder($query)
 *         ->where('active', true);
 * }
 * ```
 *
 * ### Nested resource page (e.g. `ViewTenant`, `ViewProposal`)
 *
 * Filament generates the first breadcrumb item using `getIndexUrl()` from the resource
 * as the PHP array key. When the nested resource points its `getIndexUrl()` to the root
 * resource URL (via `HasSingleRecordResource`), a key collision occurs in the breadcrumb
 * array. This trait resolves the collision and injects the root resource as a prefix.
 *
 * Provided behavior:
 * - `getBreadcrumbs()` detects the collision, inserts the root resource with a link as
 *   the first item, and converts the colliding item to an integer key (no link).
 * - `getSingleRecordResource()` traverses the parent chain to find the root resource,
 *   delegating to `resolveSingleRecordParent()` when the resource uses
 *   `HasSingleRecordResource`.
 *
 * ## Usage
 *
 * **Root resource page:**
 * ```php
 * class ViewMyWallet extends ViewRecord
 * {
 *     use HasSingleRecord;
 *
 *     protected static string $resource = MyWalletResource::class;
 * }
 * ```
 *
 * **Nested resource page:**
 * ```php
 * class ViewTenant extends BaseViewTenant
 * {
 *     use HasSingleRecord;
 *
 *     protected static string $resource = TenantResource::class;
 * }
 * ```
 *
 * @mixin ViewRecord
 * @mixin EditRecord
 * @mixin HasSingleRecordResource
 */
trait HasSingleRecord
{
    // ── ROOT BEHAVIOR ───────────────────────────────────────────────────────

    /**
     * Returns the associated Resource after validating that it exposes the
     * single-record resolution hooks required by this page trait.
     *
     * Resources implementing `SingleRecordResolvableResource` express the contract
     * explicitly for static analysis. Legacy Resources that define the same methods
     * manually are still accepted for backward compatibility.
     *
     * @return class-string<resource>
     */
    protected static function getSingleRecordResourceContract(): string
    {
        /** @var class-string<resource> $resource */
        $resource = static::getResource();

        if (is_a($resource, SingleRecordResolvableResource::class, true)) {
            return $resource;
        }

        if (
            method_exists($resource, 'resolveSingleRecord')
            && method_exists($resource, 'resolveSingleRecordBuilder')
        ) {
            return $resource;
        }

        throw new \LogicException(sprintf(
            'Page [%s] uses HasSingleRecord, but Resource [%s] does not expose the single-record resolution contract. Implement %s on the Resource or define resolveSingleRecord() and resolveSingleRecordBuilder() manually.',
            static::class,
            $resource,
            SingleRecordResolvableResource::class,
        ));
    }

    /**
     * Resolves the record to display.
     *
     * By default, retrieves the authenticated user and runs `resolveSingleRecordBuilder()`
     * against the resource's model. Returns `null` when there is no authenticated user
     * or when the builder returns no results.
     *
     * Override when you need logic that cannot be expressed through the builder alone,
     * such as auto-creating the record.
     */
    protected function resolveSingleRecord(): ?Model
    {
        if ($this->usesCustomPageSingleRecordBuilder()) {
            $modelClass = static::getResource()::getModel();

            /** @var Model|null $record */
            $record = $this->resolveSingleRecordBuilder($modelClass::query())->first();

            return $record;
        }

        $resource = static::getSingleRecordResourceContract();

        if (is_a($resource, SingleRecordResolvableResource::class, true)) {
            /** @var class-string<resource&SingleRecordResolvableResource> $typedResource */
            $typedResource = $resource;

            return $typedResource::resolveSingleRecord();
        }

        /** @var ?Model $record */
        /** @phpstan-ignore-next-line legacy fallback guarded by getSingleRecordResourceContract() */
        $record = $resource::{'resolveSingleRecord'}();

        return $record;
    }

    /**
     * Determines whether the concrete page overrides the default builder behavior
     * provided by this trait.
     */
    protected function usesCustomPageSingleRecordBuilder(): bool
    {
        $method = new \ReflectionMethod(static::class, 'resolveSingleRecordBuilder');

        return $method->getFileName() !== __FILE__;
    }

    /**
     * Query builder used to locate the authenticated user's single record.
     *
     * Prefers the Resource builder contract by default so authorization fallback and
     * page mounting can share a common lookup strategy.
     *
     * Override on the page only when the page intentionally needs a different lookup
     * than the Resource itself. Existing page-level overrides remain supported.
     *
     * If you do override on the page, you can still compose with the Resource logic:
     *
     * ```php
     * protected function resolveSingleRecordBuilder(Builder $query): Builder
     * {
     *     return parent::resolveSingleRecordBuilder($query)
     *         ->where('active', true);
     * }
     * ```
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function resolveSingleRecordBuilder(Builder $query): Builder
    {
        $resource = static::getSingleRecordResourceContract();

        if (is_a($resource, SingleRecordResolvableResource::class, true)) {
            /** @var class-string<resource&SingleRecordResolvableResource> $typedResource */
            $typedResource = $resource;

            return $typedResource::resolveSingleRecordBuilder($query);
        }

        /** @var Builder<Model> $builder */
        /** @phpstan-ignore-next-line legacy fallback guarded by getSingleRecordResourceContract() */
        $builder = $resource::{'resolveSingleRecordBuilder'}($query);

        return $builder;
    }

    /**
     * Bypasses the `{record}` route parameter (absent from the root URL) and resolves
     * the record automatically for root resource pages. Nested resource pages delegate
     * to Filament's default behavior.
     */
    public function mount(int | string $record = ''): void
    {
        if (static::isNestedResource()) {
            parent::mount($record);

            return;
        }

        $resolved = $this->resolveSingleRecord();

        if ($resolved === null) {
            $this->redirect(filament()->getLoginUrl());

            return;
        }

        parent::mount($resolved->getKey());
    }

    /**
     * Generic page heading for root resources — uses the model label instead of the
     * record title. Nested resource pages delegate to the default behavior.
     */
    public function getHeading(): string | Htmlable
    {
        if (static::isNestedResource()) {
            return parent::getHeading() ?? '';
        }

        $resource = static::getResource();

        return __('filament-panels::resources/pages/view-record.title', [
            'label' => $resource::getTitleCaseModelLabel(),
        ]);
    }

    // ── BREADCRUMBS (shared, auto-dispatched by context) ──────────────────────────

    /**
     * For root resources: suppresses the record title in the breadcrumb.
     * Result: `<Resource Label> → View` (without the record title in between).
     *
     * For nested resources: injects the root resource as a breadcrumb prefix, fixing the
     * key collision caused by `getIndexUrl()` pointing to the same URL as the root resource.
     *
     * @return array<string|int, string>
     */
    public function getBreadcrumbs(): array
    {
        if (! static::isNestedResource()) {
            return [
                ...$this->getResourceBreadcrumbs(),
                $this->getBreadcrumb(),
            ];
        }

        try {
            $breadcrumbs = parent::getBreadcrumbs();
        } catch (UrlGenerationException) {
            // During transient Livewire states, route parameter guessing may fail.
            // Fall back to a safe breadcrumb instead of breaking the page.
            $breadcrumbs = [
                ...$this->getResourceBreadcrumbs(),
                $this->getBreadcrumb(),
            ];
        }

        $rootResource = static::getSingleRecordResource();
        $rootUrl = $rootResource::getUrl('view');
        $rootLabel = $rootResource::getNavigationLabel();

        if (array_key_first($breadcrumbs) === $rootUrl) {
            $firstLabel = array_shift($breadcrumbs);

            if (! is_string($firstLabel)) {
                return [
                    $rootUrl => $rootLabel,
                    ...$breadcrumbs,
                ];
            }

            return [
                $rootUrl => $rootLabel,
                $firstLabel,
                ...$breadcrumbs,
            ];
        }

        if (array_key_exists($rootUrl, $breadcrumbs)) {
            return $breadcrumbs;
        }

        return [
            $rootUrl => $rootLabel,
            ...$breadcrumbs,
        ];
    }

    // ── NESTED BEHAVIOR ───────────────────────────────────────────────────

    /**
     * Indicates whether the page belongs to a nested resource.
     *
     * Delegates to `isNestedResource()` on the Resource when available (via
     * `HasSingleRecordResource`), ensuring the same detection logic is used on both
     * sides without duplication. Falls back to checking the Resource properties directly.
     */
    protected static function isNestedResource(): bool
    {
        $resource = static::getResource();

        if (method_exists($resource, 'isNestedResource')) {
            return $resource::isNestedResource();
        }

        return $resource::getParentResource() !== null
            || $resource::getParentResourceRegistration() !== null;
    }

    /**
     * Traverses the parent chain of the current resource to find the root resource
     * (the one with no parent configured) — which must be the single-record resource.
     *
     * When the Resource uses `HasSingleRecordResource`, delegates to
     * `resolveSingleRecordParent()` instead of repeating the same traversal logic.
     *
     * Throws `\LogicException` if the current resource has no parent configured,
     * as this functionality requires at least one level of nesting.
     *
     * @return class-string<\Filament\Resources\Resource>
     */
    public static function getSingleRecordResource(): string
    {
        /** @var class-string<\Filament\Resources\Resource> $resource */
        $resource = static::getResource();

        if (method_exists($resource, 'resolveSingleRecordParent')) {
            return $resource::resolveSingleRecordParent();
        }

        $current = $resource;
        $parent = $current::getParentResource()
            ?? $current::getParentResourceRegistration()?->getParentResource();

        if ($parent === null) {
            throw new \LogicException(
                sprintf(
                    'Resource [%s] has no parent resource configured. HasSingleRecord::getSingleRecordResource() requires at least one level of nesting.',
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
}
