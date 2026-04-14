<?php

use CoringaWc\FilamentSingleRecordResource\Contracts\SingleRecordResolvableResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Workbench\App\Models\User;

class ContractViewOnlyModel extends Model
{
    protected $guarded = [];
}

class ContractDeniedModel extends Model
{
    protected $guarded = [];
}

class ContractViewOnlyPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return false;
    }

    public function view(Authenticatable $user, ContractViewOnlyModel $record): bool
    {
        return (int) $record->getKey() === 123;
    }
}

class ContractDeniedPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return false;
    }

    public function view(Authenticatable $user, ContractDeniedModel $record): bool
    {
        return false;
    }
}

class ContractRootResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $model = Model::class;

    public static function getPages(): array
    {
        return [
            'view' => ContractRootViewPage::route('/'),
        ];
    }
}

class ContractNestedResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $model = Model::class;

    protected static ?string $parentResource = ContractRootResource::class;

    public static function getPages(): array
    {
        return [
            'view' => ContractNestedViewPage::route('/{record}'),
            'edit' => ContractNestedEditPage::route('/{record}/edit'),
        ];
    }
}

class ContractRootViewPage extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = ContractRootResource::class;
}

class ContractNestedViewPage extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = ContractNestedResource::class;
}

class ContractNestedEditPage extends EditRecord
{
    use HasSingleRecord;

    protected static string $resource = ContractNestedResource::class;
}

class ContractViewOnlyRootResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $model = ContractViewOnlyModel::class;

    public static function getPages(): array
    {
        return [
            'view' => ContractRootViewPage::route('/view-only'),
        ];
    }

    public static function resolveSingleRecord(): ?Model
    {
        return tap(new ContractViewOnlyModel, fn (ContractViewOnlyModel $record) => $record->setAttribute('id', 123));
    }
}

class ContractDeniedRootResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $model = ContractDeniedModel::class;

    public static function getPages(): array
    {
        return [
            'view' => ContractRootViewPage::route('/denied'),
        ];
    }

    public static function resolveSingleRecord(): ?Model
    {
        return tap(new ContractDeniedModel, fn (ContractDeniedModel $record) => $record->setAttribute('id', 999));
    }
}

class ContractLegacyCompatibleRootResource extends Resource
{
    use HasSingleRecordResource;

    protected static ?string $model = ContractViewOnlyModel::class;

    public static function getPages(): array
    {
        return [
            'view' => ContractRootViewPage::route('/legacy-compatible'),
        ];
    }

    public static function resolveSingleRecord(): ?Model
    {
        return tap(new ContractViewOnlyModel, fn (ContractViewOnlyModel $record) => $record->setAttribute('id', 321));
    }
}

class ContractLegacyCompatibleRootViewPage extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = ContractLegacyCompatibleRootResource::class;

    public function resolveSingleRecordForTest(): ?Model
    {
        return $this->resolveSingleRecord();
    }
}

class ContractInvalidRootResource extends Resource
{
    protected static ?string $model = Model::class;

    public static function getPages(): array
    {
        return [
            'view' => ContractRootViewPage::route('/invalid'),
        ];
    }
}

class ContractInvalidRootViewPage extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = ContractInvalidRootResource::class;

    public static function resolveResourceContractForTest(): string
    {
        return static::getSingleRecordResourceContract();
    }
}

it('defines single-record root resource contract', function (): void {
    $pages = ContractRootResource::getPages();

    expect($pages)->toHaveKey('view');
    expect($pages)->not->toHaveKey('index');
    expect(class_uses_recursive(ContractRootResource::class))->toContain(HasSingleRecordResource::class);
    expect(class_uses_recursive(ContractRootViewPage::class))->toContain(HasSingleRecord::class);
});

it('defines nested resources/pages contract for breadcrumb-safe single-record flow', function (): void {
    $companyPages = ContractNestedResource::getPages();

    expect($companyPages)->toHaveKey('edit');
    expect(ContractNestedResource::isNestedResource())->toBeTrue();
    expect(class_uses_recursive(ContractNestedResource::class))->toContain(HasSingleRecordResource::class);
    expect(class_uses_recursive(ContractNestedViewPage::class))->toContain(HasSingleRecord::class);
    expect(class_uses_recursive(ContractNestedEditPage::class))->toContain(HasSingleRecord::class);
    expect(ContractNestedViewPage::getSingleRecordResource())->toBe(ContractRootResource::class);
});

it('allows a root single-record resource with view permission only', function (): void {
    Gate::policy(ContractViewOnlyModel::class, ContractViewOnlyPolicy::class);

    auth()->login(User::factory()->create());

    expect(ContractViewOnlyRootResource::canViewAny())->toBeFalse();
    expect(ContractViewOnlyRootResource::canAccess())->toBeTrue();
});

it('denies a root single-record resource when the resolved record cannot be viewed', function (): void {
    Gate::policy(ContractDeniedModel::class, ContractDeniedPolicy::class);

    auth()->login(User::factory()->create());

    expect(ContractDeniedRootResource::canViewAny())->toBeFalse();
    expect(ContractDeniedRootResource::canAccess())->toBeFalse();
});

it('keeps compatibility with resources that expose the methods without implementing the interface', function (): void {
    $page = new ContractLegacyCompatibleRootViewPage;

    expect($page->resolveSingleRecordForTest())
        ->toBeInstanceOf(ContractViewOnlyModel::class)
        ->and($page->resolveSingleRecordForTest()?->getKey())->toBe(321);
});

it('fails fast when a page uses HasSingleRecord without a compatible resource contract', function (): void {
    expect(fn (): string => ContractInvalidRootViewPage::resolveResourceContractForTest())
        ->toThrow(LogicException::class, SingleRecordResolvableResource::class);
});
