<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\ItemsTableSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Purchase;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsersTableSeeder::class);
        $this->seed(ConditionsTableSeeder::class);
        $this->seed(CategoriesTableSeeder::class);
        $this->seed(ItemsTableSeeder::class);
    }

    public function test_guest_can_view_all_items()
    {
        $response = $this->get('/');

        $items = Item::paginate(8);
        foreach ($items as $item) {
            $response->assertSee($item->item_name);
        }
    }

    public function test_sold_label_is_displayed_for_purchased_items()
    {
        $users = User::all();

        $paginatedItems = Item::paginate(8);
        $paginatedItemIds = $paginatedItems->pluck('id');
        $purchasedItems = Item::whereIn('id', $paginatedItemIds)->take(3)->get();

        foreach ($purchasedItems as $item) {
            $userId = $users->random()->id;
            Purchase::create([
                'user_id' => $userId,
                'item_id' => $item->id,
                'payment_method' => 'card',
                'post_code' => '123_4567',
                'destination' => '東京都渋谷区1-1-1',
            ]);
        }

        $response = $this->get('/');

        foreach ($paginatedItems as $item) {
            $response->assertSee($item->item_name);
            if ($purchasedItems->contains('id', $item->id)) {
                $response->assertSee('<span class="sold-label">', false);
            }
        }
    }
}
