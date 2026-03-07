<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\App\Models\Wallet;
use Workbench\Database\Factories\CompanyFactory;

class CompanySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Wallet::each(function (Wallet $wallet): void {
            CompanyFactory::new()->count(3)->create(['wallet_id' => $wallet->getKey()]);
        });
    }
}
