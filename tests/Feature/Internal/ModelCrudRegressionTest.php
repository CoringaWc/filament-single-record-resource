<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrudWallet extends Model
{
    protected $table = 'crud_wallets';

    protected $guarded = [];

    public function companies()
    {
        return $this->hasMany(CrudCompany::class, 'wallet_id');
    }
}

class CrudCompany extends Model
{
    protected $table = 'crud_companies';

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(CrudProduct::class, 'company_id');
    }
}

class CrudProduct extends Model
{
    protected $table = 'crud_products';

    protected $guarded = [];

    public function notes()
    {
        return $this->hasMany(CrudNote::class, 'product_id');
    }
}

class CrudNote extends Model
{
    protected $table = 'crud_notes';

    protected $guarded = [];
}

beforeEach(function (): void {
    Schema::create('crud_wallets', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });

    Schema::create('crud_companies', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('wallet_id')->constrained('crud_wallets')->cascadeOnDelete();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('crud_products', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('company_id')->constrained('crud_companies')->cascadeOnDelete();
        $table->string('description');
        $table->timestamps();
    });

    Schema::create('crud_notes', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('product_id')->constrained('crud_products')->cascadeOnDelete();
        $table->text('content');
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('crud_notes');
    Schema::dropIfExists('crud_products');
    Schema::dropIfExists('crud_companies');
    Schema::dropIfExists('crud_wallets');
});

it('runs full CRUD chain for wallet, company, product, and note', function (): void {
    $wallet = CrudWallet::query()->create();
    $company = CrudCompany::query()->create([
        'wallet_id' => $wallet->getKey(),
        'name' => 'Alpha Company',
    ]);

    $product = CrudProduct::query()->create([
        'company_id' => $company->getKey(),
        'description' => 'Original Description',
    ]);

    $note = CrudNote::query()->create([
        'product_id' => $product->getKey(),
        'content' => 'Initial note',
    ]);

    expect($wallet->companies()->count())->toBe(1);
    expect($company->products()->count())->toBe(1);
    expect($product->notes()->count())->toBe(1);

    $company->update(['name' => 'Beta Company']);
    $product->update(['description' => 'Updated Description']);
    $note->update(['content' => 'Updated note']);

    expect($company->fresh()?->name)->toBe('Beta Company');
    expect($product->fresh()?->description)->toBe('Updated Description');
    expect($note->fresh()?->content)->toBe('Updated note');

    $note->delete();
    $product->delete();
    $company->delete();

    expect(CrudCompany::query()->find($company->getKey()))->toBeNull();
    expect(CrudProduct::query()->find($product->getKey()))->toBeNull();
    expect(CrudNote::query()->find($note->getKey()))->toBeNull();

    $wallet->delete();

    expect(CrudWallet::query()->find($wallet->getKey()))->toBeNull();
});
