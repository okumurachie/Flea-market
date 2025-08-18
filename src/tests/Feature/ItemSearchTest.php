<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\ItemsTableSeeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Purchase;

class ItemSearchTest extends TestCase
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

    public function test_items_are_searched_by_partial_name()
    {
        $items = Item::all();

        $firstItem = $items->first();

        $keyword = '時計';

        $response = $this->get("/?keyword={$keyword}");

        $response->assertSee($firstItem->item_name);

        foreach ($items->skip(1) as $item) {
            $response->assertDontSee($item->item_name);
        }
    }

    public function test_search_keyword_is_retained_on_mylist_tab()
    {
        $user = User::factory()->create();
        $items = Item::all();
        $favoriteItems = $items->take(2);
        $otherItems = $items->skip(2);

        foreach ($favoriteItems as $favoriteItem) {
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $favoriteItem->id,
            ]);
        }

        $searchedItem = $favoriteItems->first();
        $otherFavoriteItem = $favoriteItems->last();
        $keyword = '時計';

        $response = $this->get("/?keyword={$keyword}");

        $response->assertSee($searchedItem->item_name);
        $response->assertDontSee($otherFavoriteItem->item_name);
        foreach ($otherItems as $otherItem) {
            $response->assertDontSee($otherItem->item_name);
        }

        $response = $this->actingAs($user)->get("/?tab=mylist&keyword={$keyword}");

        $response->assertSee($searchedItem->item_name);
        $response->assertDontSee($otherFavoriteItem->item_name);
        foreach ($otherItems as $otherItem) {
            $response->assertDontSee($otherItem->item_name);
        }
    }
}
