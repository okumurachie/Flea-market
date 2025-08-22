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
        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'comment' => 'Test comment',
        ]);
    }

    public function test_submit_comments_fails_when_name_is_missing()
    {
        $user = User::first();
        $item = Item::first();

        $response = $this->actingAs($user)->get('/item/' .  $item->id);
        $response->assertStatus(200);

        $response = $this->from('/item/' .  $item->id)->post('/comments', [
            'item_id' => $item->id,
            'comment' => '',
        ]);

        $response->assertRedirect('/item/' .  $item->id);
        $response->assertSessionHasErrors([
            'comment' => 'コメントを入力してください'
        ]);
        $response = $this->get('/item/' .  $item->id);
        $response->assertSee('コメントを入力してください');
    }

    public function test_submit_comments_fails_when_comment_too_long()
    {
        $user = User::first();
        $item = Item::first();

        $response = $this->actingAs($user)->get('/item/' .  $item->id);
        $response->assertStatus(200);

        $longComment = str_repeat('a', 256);

        $response = $this->from('/item/' .  $item->id)->post('/comments', [
            'item_id' => $item->id,
            'comment' => $longComment,
        ]);

        $response->assertRedirect('/item/' .  $item->id);
        $response->assertSessionHasErrors([
            'comment' => 'コメントは255文字以内で入力してください'
        ]);
        $response = $this->get('/item/' .  $item->id);
        $response->assertSee('コメントは255文字以内で入力してください');
    }
}
