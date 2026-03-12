<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\App\Models\Company;
use Workbench\Database\Factories\ProductFactory;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Company::each(function (Company $company): void {
            ProductFactory::new()->count(3)->create(['company_id' => $company->getKey()]);
        });
    }
}
