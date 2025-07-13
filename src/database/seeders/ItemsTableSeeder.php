<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Item;
use App\Models\Category;


class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::pluck('id')->toArray();

        $items = [
            [
                'item_name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'item_image' => 'images/ItemsSeeder/item1.jpg',
                'condition_id' => 1,
                'category_id' => [1, 5],
            ],
            [
                'item_name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'item_image' => 'images/ItemsSeeder/item2.jpg',
                'condition_id' => 2,
                'category_id' => [2],
            ],
            [
                'item_name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => '',
                'description' => '新鮮な玉ねぎ3束のセット',
                'item_image' => 'images/ItemsSeeder/item3.jpg',
                'condition_id' => 3,
                'category_id' => [10],
            ],
            [
                'item_name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'description' => 'クラッシックなデザインの革靴',
                'item_image' => 'images/ItemsSeeder/item4.jpg',
                'condition_id' => 4,
                'category_id' => [1, 5],
            ],
            [
                'item_name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'item_image' => 'images/ItemsSeeder/item5.jpg',
                'condition_id' => 1,
                'category_id' => [2],
            ],
            [
                'item_name' => 'マイク',
                'price' => 8000,
                'brand' => '',
                'description' => '高音質のレコーディング用マイク',
                'item_image' => 'images/ItemsSeeder/item6.jpg',
                'condition_id' => 2,
                'category_id' => [2],
            ],
            [
                'item_name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'item_image' => 'images/ItemsSeeder/item7.jpg',
                'condition_id' => 3,
                'category_id' => [1, 4],
            ],
            [
                'item_name' => 'タンブラー',
                'price' => 500,
                'brand' => '',
                'description' => '使いやすいタンブラー',
                'item_image' => 'images/ItemsSeeder/item8.jpg',
                'condition_id' => 4,
                'category_id' => [10],
            ],
            [
                'item_name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'item_image' => 'images/ItemsSeeder/item9.jpg',
                'condition_id' => 1,
                'category_id' => [3, 10],
            ],
            [
                'item_name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'item_image' => 'images/ItemsSeeder/item10.jpg',
                'condition_id' => 2,
                'category_id' => [1, 4],
            ],
        ];
        foreach ($items as $data) {
            $item = Item::create([
                'user_id' => rand(1, 5),
                'item_name' => $data['item_name'],
                'price' => $data['price'],
                'brand' => $data['brand'],
                'description' => $data['description'],
                'item_image' => 'storage/' . $data['item_image'],
                'condition_id' => $data['condition_id'],
                'shipping_status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $item->categories()->attach($data['category_id']);
        }
    }
}
