<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\App\Models\Product;
use Workbench\Database\Factories\NoteFactory;

class NoteSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Product::each(function (Product $product): void {
            NoteFactory::new()->count(2)->create(['product_id' => $product->getKey()]);
        });
    }
}
