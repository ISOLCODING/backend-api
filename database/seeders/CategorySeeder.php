<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Coffee Shop specialized categories
        $categories = [
            ['name' => 'Coffee', 'children' => ['Espresso Based', 'Cold Brew & Iced', 'Manual Brew']],
            ['name' => 'Non-Coffee', 'children' => ['Matcha & Tea', 'Chocolate Series', 'Fresh Juice']],
            ['name' => 'Food & Bakery', 'children' => ['Pastries', 'Main Course', 'Dessert']],
            ['name' => 'Merchandise', 'children' => ['Coffee Beans', 'Tumbler', 'T-Shirt']],
        ];

        foreach ($categories as $cat) {
            $parent = Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
            ]);

            foreach ($cat['children'] as $child) {
                Category::create([
                    'name'      => $child,
                    'slug'      => Str::slug($child),
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
