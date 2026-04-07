<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get category IDs
        $espressoCatId = Category::where('name', 'Espresso Based')->value('id');
        $coldBrewCatId = Category::where('name', 'Cold Brew & Iced')->value('id');
        $manualBrewCatId = Category::where('name', 'Manual Brew')->value('id');
        $matchaCatId = Category::where('name', 'Matcha & Tea')->value('id');
        $chocolateCatId = Category::where('name', 'Chocolate Series')->value('id');
        $juiceCatId = Category::where('name', 'Fresh Juice')->value('id');
        $pastryCatId = Category::where('name', 'Pastries')->value('id');
        $mainCourseCatId = Category::where('name', 'Main Course')->value('id');
        $coffeeBeansCatId = Category::where('name', 'Coffee Beans')->value('id');

        $products = [
            // Espresso Based
            ['barcode' => 'CF-001', 'name' => 'Double Espresso', 'category_id' => $espressoCatId, 'buy_price' => 10000, 'sell_price' => 20000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/espresso.png'],
            ['barcode' => 'CF-002', 'name' => 'Caffe Americano', 'category_id' => $espressoCatId, 'buy_price' => 12000, 'sell_price' => 25000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/espresso.png'],
            ['barcode' => 'CF-003', 'name' => 'Cafe Latte', 'category_id' => $espressoCatId, 'buy_price' => 15000, 'sell_price' => 32000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/espresso.png'],
            ['barcode' => 'CF-004', 'name' => 'Cappuccino', 'category_id' => $espressoCatId, 'buy_price' => 15000, 'sell_price' => 32000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/espresso.png'],

            // Cold Brew
            ['barcode' => 'CF-005', 'name' => 'Signature Cold Brew', 'category_id' => $coldBrewCatId, 'buy_price' => 18000, 'sell_price' => 35000, 'stock' => 50,  'min_stock' => 5,  'unit' => 'bottle', 'image' => 'products/coldbrew.png'],
            ['barcode' => 'CF-006', 'name' => 'Iced Vanilla Latte', 'category_id' => $coldBrewCatId, 'buy_price' => 18000, 'sell_price' => 38000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/coldbrew.png'],

            // Manual Brew
            ['barcode' => 'CF-007', 'name' => 'V60 Specialty', 'category_id' => $manualBrewCatId, 'buy_price' => 20000, 'sell_price' => 40000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/manualbrew.png'],

            // Non-Coffee - Matcha & Tea
            ['barcode' => 'NC-001', 'name' => 'Kyoto Matcha Latte', 'category_id' => $matchaCatId, 'buy_price' => 15000, 'sell_price' => 35000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/noncoffee.png'],
            ['barcode' => 'NC-002', 'name' => 'Premium Grey Tea', 'category_id' => $matchaCatId, 'buy_price' => 10000, 'sell_price' => 22000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/noncoffee.png'],

            // Non-Coffee - Chocolate Series
            ['barcode' => 'NC-003', 'name' => 'Signature Dark Chocolate', 'category_id' => $chocolateCatId, 'buy_price' => 15000, 'sell_price' => 35000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/noncoffee.png'],
            ['barcode' => 'NC-004', 'name' => 'Creamy Milk Chocolate', 'category_id' => $chocolateCatId, 'buy_price' => 14000, 'sell_price' => 32000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/noncoffee.png'],

            // Non-Coffee - Fresh Juice
            ['barcode' => 'NC-005', 'name' => 'Orange Sunshine', 'category_id' => $juiceCatId, 'buy_price' => 12000, 'sell_price' => 28000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'cup', 'image' => 'products/drinks.png'],

            // Pastry
            ['barcode' => 'PS-001', 'name' => 'Butter Croissant', 'category_id' => $pastryCatId, 'buy_price' => 12000, 'sell_price' => 25000, 'stock' => 20,  'min_stock' => 5,  'unit' => 'pcs', 'image' => 'products/pastry.png'],
            ['barcode' => 'PS-002', 'name' => 'Choco Brownie', 'category_id' => $pastryCatId, 'buy_price' => 10000, 'sell_price' => 22000, 'stock' => 15,  'min_stock' => 5,  'unit' => 'pcs', 'image' => 'products/pastry.png'],

            // Food & Bakery - Main Course
            ['barcode' => 'FD-001', 'name' => 'Truffle Mac n Cheese', 'category_id' => $mainCourseCatId, 'buy_price' => 25000, 'sell_price' => 55000, 'stock' => 999, 'min_stock' => 10, 'unit' => 'pcs', 'image' => 'products/pastry.png'],

            // Merchandise - Coffee Beans
            ['barcode' => 'ME-001', 'name' => 'House Blend 250g', 'category_id' => $coffeeBeansCatId, 'buy_price' => 45000, 'sell_price' => 95000, 'stock' => 999, 'min_stock' => 2, 'unit' => 'pcs', 'image' => 'products/espresso.png'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['barcode' => $product['barcode']],
                array_merge($product, ['is_active' => true])
            );
        }
    }
}
