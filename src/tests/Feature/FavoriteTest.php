<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\TestCategoriesTableSeeder;
use Database\Seeders\TestItemsTableSeeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Comment;

class FavoriteTest extends TestCase
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

    public function test_item_is_favorited_when_icon_clicked()
    {
        $user = User::first();
        $item = Item::latest()->first();

        $beforeCount = $item->favorites()->count();

        $response = $this->actingAs($user)->postJson('/item/favorite/toggle', [
            'item_id' => $item->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'added',
            ]);

        $item->refresh();
        $afterCount = $item->favorites()->count();
        $this->assertEquals($beforeCount + 1, $afterCount);

        $response->assertJson([
            'count' => $afterCount,
        ]);
    }
}
