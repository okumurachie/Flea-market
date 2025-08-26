<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\TestCategoriesTableSeeder;
use Database\Seeders\TestItemsTableSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
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
        $this->seed(TestCategoriesTableSeeder::class);
        $this->seed(TestItemsTableSeeder::class);
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

    public function test_user_cannot_see_their_own_items()
    {
        $user = User::factory()->create();
        $items = Item::paginate(8);
        $myItems = $items->take(2);

        foreach ($myItems as $item) {
            $item->update(['user_id' => $user->id]);
        }

        $otherItems = $items->skip(2);

        $response = $this->actingAs($user)->get('/');

        foreach ($myItems as $myItem) {
            $response->assertDontSee($myItem->item_name);
        }

        foreach ($otherItems as $otherItem) {
            $response->assertSee($otherItem->item_name);
        }
    }
}
