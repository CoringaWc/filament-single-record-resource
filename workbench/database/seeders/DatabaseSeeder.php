<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            WalletSeeder::class,
            CompanySeeder::class,
            ProductSeeder::class,
            NoteSeeder::class,
        ]);
    }
}
