# Filament Single Record Plugin

## What This Plugin Solves

This plugin implements the single-record resource pattern for Filament.

It also exposes an explicit Resource contract through `SingleRecordResolvableResource` for codebases that want static-analysis-friendly typing.

Use it when a resource should represent one business record per context, not a list. Typical examples:

- My Wallet
- My Profile
- My Settings
- Current Subscription
- Current Tenant Record

It also supports nested resources where you intentionally remove the parent ID from the URL while keeping navigation, slugs, and breadcrumbs consistent.

## Core Traits

### `HasSingleRecordResource` (Resource side)

Use in Filament `Resource` classes.

Responsibilities:

- Detect root vs nested resource (`isNestedResource()`).
- Redirect `getIndexUrl()` behavior to `view` route.
- Keep sidebar navigation working even without an `index` page.
- Allow root resource access with `view()` on the resolved single record when `viewAny()` is denied.
- Provide the default shared single-record resolution hooks on the Resource.
- Pair naturally with `SingleRecordResolvableResource` when you want an explicit public contract.
- Resolve root parent for nested chains (`resolveSingleRecordParent()`).
- Normalize nested slug/index behavior in single-record hierarchies.

### `HasSingleRecord` (Page side)

Use in record pages (`ViewRecord` and `EditRecord`).

Responsibilities:

- Resolve the single record automatically in root single-record pages.
- Allow custom resolution strategy via:
    - `resolveSingleRecordBuilder(Builder $query)`
    - `resolveSingleRecord()`
- Prefer the Resource contract when available, while remaining compatible with legacy Resources that expose the same methods manually.
- Normalize heading and breadcrumb behavior.
- Fix nested breadcrumb collisions and ensure root breadcrumb prefix exists.

## Installation

```bash
composer require coringawc/filament-single-record-resource
```

If needed:

```bash
php artisan vendor:publish --tag="filament-single-record-resource-config"
php artisan vendor:publish --tag="filament-single-record-resource-migrations"
php artisan migrate
```

## Register Plugin In Panel

```php
use CoringaWc\FilamentSingleRecordResource\FilamentSingleRecordResourcePlugin;

return $panel
    ->plugin(FilamentSingleRecordResourcePlugin::make());
```

## Root Single-Record Usage

### 1) Resource

```php
<?php

namespace App\Filament\Resources\MyWallets;

use App\Filament\Resources\MyWallets\Pages\ViewMyWallet;
use CoringaWc\FilamentSingleRecordResource\Contracts\SingleRecordResolvableResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\Resource;

class MyWalletResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    public static function getPages(): array
    {
        return [
            'view' => ViewMyWallet::route('/'),
        ];
    }
}
```

### 2) View Page

```php
<?php

namespace App\Filament\Resources\MyWallets\Pages;

use App\Filament\Resources\MyWallets\MyWalletResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewMyWallet extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = MyWalletResource::class;
}
```

### 3) Optional Edit Page

Yes, trait usage in `EditRecord` is supported.

```php
<?php

namespace App\Filament\Resources\MyWallets\Pages;

use App\Filament\Resources\MyWallets\MyWalletResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use Filament\Resources\Pages\EditRecord;

class EditMyWallet extends EditRecord
{
    use HasSingleRecord;

    protected static string $resource = MyWalletResource::class;
}
```

And register it:

```php
public static function getPages(): array
{
    return [
        'view' => Pages\ViewMyWallet::route('/'),
        'edit' => Pages\EditMyWallet::route('/edit'),
    ];
}
```

## Nested Resource Usage

### Example: `MyWallet -> Companies`

```php
<?php

namespace App\Filament\Resources\MyWallets\Resources\Companies;

use App\Filament\Resources\MyWallets\MyWalletResource;
use CoringaWc\FilamentSingleRecordResource\Contracts\SingleRecordResolvableResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\ParentResourceRegistration;
use Filament\Resources\Resource;

class CompanyResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $parentResource = MyWalletResource::class;

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        // Optional: remove {parent_id} segment from URL.
        // If you do this, enforce data scoping in model/query logic.
        return null;
    }
}
```

Page side:

```php
class ViewCompany extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = CompanyResource::class;
}
```

## Deep Nested Example

For a deep chain like `MyWallet -> Companies -> Products`, apply `HasSingleRecord` to the deep record page (for example `ViewProduct` and/or `EditProduct`) to preserve breadcrumb behavior.

## Required Rules

- Use both traits together where applicable:
    - Resource: `HasSingleRecordResource`
    - Record page (`ViewRecord` / `EditRecord`): `HasSingleRecord`
- Root single-record resources should expose `view` as the main entry route.
- Prefer implementing `SingleRecordResolvableResource` on Resources using `HasSingleRecordResource` so static analysis can understand the contract explicitly.
- If you remove parent IDs from nested URLs, enforce strict scoping in your data layer.
- Do not use this plugin for collection-first resources where `index` is the main UX.

## Common Problems And Fixes

### Missing parameter for nested `view` route

Symptom:

- `Missing required parameter ... companies/{record}`

Typical causes:

- Route generation called without `record`.
- Transient Livewire state during breadcrumb generation.
- Stale cached routes/views.

Fixes:

1. Ensure nested `view`/`edit` links always pass record.
2. Keep `HasSingleRecord` on nested record pages.
3. Clear app cache:

```bash
./vendor/bin/testbench optimize:clear
```

### Root breadcrumb missing in deep nested page

Fix:

- Use current `HasSingleRecord::getBreadcrumbs()` logic and keep trait on deep nested record pages.

### Root single-record page unexpectedly requires list access

Symptom:

- The user can `view` their own record but still cannot open the root single-record resource because `viewAny` is denied.

Fix:

1. Keep `HasSingleRecordResource` on the root Resource.
2. Make sure the Resource can resolve the user-owned record through the default builder or a custom `resolveSingleRecord()` / `resolveSingleRecordBuilder()` override.
3. Ensure the policy returns `true` for `view($user, $resolvedRecord)` even if `viewAny()` returns `false`.

## Testing

### Contract tests (plugin behavior expected by users)

- `tests/Feature/PluginContract/SingleRecordRouteContractTest.php`

### Internal regression tests (domain CRUD chain)

- `tests/Feature/Internal/ModelCrudRegressionTest.php`

Run:

```bash
XDEBUG_MODE=coverage ./vendor/bin/pest --compact tests/Feature/PluginContract tests/Feature/Internal
```

Full suite:

```bash
XDEBUG_MODE=coverage ./vendor/bin/pest --compact
./vendor/bin/phpstan analyse --memory-limit=512M
./vendor/bin/pint
```

## Namespace Note

Examples in this file use the package namespace:

`CoringaWc\FilamentSingleRecordResource\Traits\...`

If you copy snippets, keep this namespace unless your package namespace changes.
