<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\App\Models\User;
use Workbench\Database\Factories\WalletFactory;

class WalletSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::doesntHave('wallet')->each(function (User $user): void {
            WalletFactory::new()->create(['user_id' => $user->getKey()]);
        });
    }
}
