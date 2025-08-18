<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\TestItemsTableSeeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Purchase;

class MylistIndexTest extends TestCase
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
        $this->seed(TestItemsTableSeeder::class);
    }

    public function test_mylist_shows_only_favorite_items()
    {
        $user = User::factory()->create();
        $items = Item::all();
        $favoriteItems = $items->take(2);

        foreach ($favoriteItems as $favoriteItem) {
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $favoriteItem->id,
            ]);
        }
        $response = $this->actingAs($user)->get('/?tab=mylist');

        foreach ($favoriteItems as $favoriteItem) {
            $response->assertSee($favoriteItem->item_name);
        }

        $notFavoriteItems = $items->skip(2);
        foreach ($notFavoriteItems as $item) {
            $response->assertDontSee($item->item_name);
        }
    }

    public function test_mylist_sold_label_is_displayed_for_purchased_items()
    {
        $user = User::factory()->create();
        $items = Item::all();
        $favoriteItems = $items->take(2);

        foreach ($favoriteItems as $favoriteItem) {
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $favoriteItem->id,
            ]);
        }

        $purchasedItem = $favoriteItems->first();
        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $purchasedItem->id,
            'payment_method' => 'card',
            'post_code' => '123_4567',
            'destination' => '東京都渋谷区1-1-1',
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');
        $html = $response->getContent();

        $crawler = new Crawler($html);

        $crawler->filter('a.content__id')->each(function (Crawler $block) use ($purchasedItem, $favoriteItems) {
            $itemName = $block->filter('div.content__name p')->text();
            $hasSoldLabel = $block->filter('span.sold-label')->count() > 0;

            if ($itemName === $purchasedItem->item_name) {
                $this->assertTrue($hasSoldLabel);
            } elseif ($favoriteItems->pluck('item_name')->contains($itemName)) {
                $this->assertFalse($hasSoldLabel);
            }
        });
    }

    public function test_mylist_not_visible_for_unauthenticated_users()
    {
        $items = Item::all();
        $response = $this->get('/?tab=mylist');

        foreach ($items as $item) {
            $response->assertDontSee($item->item_name);
        }

        $response->assertSee('表示する商品がありません');
    }
}
