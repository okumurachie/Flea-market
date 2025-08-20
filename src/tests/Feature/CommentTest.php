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
use App\Models\Comment;

class CommentTest extends TestCase
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

    public function test_logged_in_user_can_submit_comments()
    {
        $user = User::first();
        $item = Item::first();

        $beforeCount = $item->comments()->count();

        $commentData = [
            'item_id' => $item->id,
            'comment' => 'Test comment',
        ];

        $response = $this->actingAs($user)->post('/comments', $commentData);
        $response->assertStatus(302);

        $item->refresh();
        $afterCount = $item->comments()->count();

        $this->assertEquals($beforeCount + 1, $afterCount);

        $response = $this->actingAs($user)->get('/item/' .  $item->id);
        $response->assertSee('Test comment');
    }

    public function test_user_cannot_submit_comments_before_logging_in()
    {
        $item = Item::first();

        $response = $this->get('/item/' .  $item->id);
        $response->assertStatus(200);

        $commentData = [
            'item_id' => $item->id,
            'comment' => 'Test comment',
        ];

        $response = $this->post('/comments', $commentData);
    }
}
