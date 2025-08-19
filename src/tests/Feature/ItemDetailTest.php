<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\TestCategoriesTableSeeder;
use Database\Seeders\TestItemsTableSeeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Comment;


class ItemDetailTest extends TestCase
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

    public function test_item_show_all_information()
    {

        $allUsers = User::all();
        $users = User::take(2)->get();
        $showItem = Item::first();
        $loginUser = $allUsers->last();

        foreach ($users as $user) {
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $showItem->id,
            ]);

            Comment::create([
                'user_id' => $user->id,
                'item_id' => $showItem->id,
                'comment' => 'いいね！',
            ]);
        }

        $favoritesCount = $showItem->favorites()->count();
        $comments = $showItem->comments()->get();
        $commentsCount = $comments->count();

        $response = $this->actingAs($loginUser)->get('/item/' . $showItem->id);

        $response->assertSee([
            $showItem->item_name,
            $showItem->brand,
            $showItem->description,
            number_format($showItem->price),
            $showItem->item_image,
            $showItem->condition->condition,
            (string)$favoritesCount,
            (string)$commentsCount,
        ]);

        $categories = $showItem->categories;

        foreach ($categories as $category) {
            $response->assertSee($category->content);
        }

        foreach ($comments as $comment) {
            $response->assertSee($comment->user->profile->user_name);
            $response->assertSee($comment->user->profile->image);
            $response->assertSee($comment->comment);
        }
    }

    public function test_item_detail_displays_all_selected_categories()
    {
        $item = Item::first();
        $item->categories()->sync([1, 4, 6]);
        $item->load('categories');

        $categories = $item->categories;

        $response = $this->get('/item/' . $item->id);

        foreach ($categories as $category) {
            $response->assertSee($category->content);
        }
    }
}
