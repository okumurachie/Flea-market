<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;


class RegisterItemTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */


    public function test_items_are_registered_required_information()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Condition::create(['id' => 1, 'condition' => '良好']);
        $category1 = Category::create(['id' => 1, 'content' => 'ファッション']);
        $category2 = Category::create(['id' => 2, 'content' => '家電']);

        $path = storage_path('app/public/images/ItemsSeeder/item1.jpg');

        $uploadedFile = new UploadedFile(
            $path,
            'item1.jpg',
            'image/jpeg',
            null,
            true
        );

        $response = $this->post('/sell', [
            'item_name' => 'Test item',
            'condition_id' => 1,
            'brand' => 'abc',
            'description' => 'Good Item',
            'price' => 1500,
            'item_image' => $uploadedFile,
            'category_ids' => [1, 2],
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'item_name' => 'Test item',
            'condition_id' => 1,
            'brand' => 'abc',
            'description' => 'Good Item',
            'price' => 1500,
        ]);

        $item = Item::where('item_name', 'Test item')->first();
        $this->assertTrue($item->categories()->where('category_id', $category1->id)->exists());
        $this->assertTrue($item->categories()->where('category_id', $category2->id)->exists());

        $storedFilePath = str_replace('storage/', 'app/public/', $item->item_image);
        $this->assertFileExists(storage_path($storedFilePath));
    }
}
