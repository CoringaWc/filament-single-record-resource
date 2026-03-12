<?php

use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

class ContractRootResource extends Resource
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

class ContractNestedResource extends Resource
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
