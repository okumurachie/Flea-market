<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_guest_can_view_all_items()
    {
        $users = User::factory()->count(10)->create();
        $items = Item::factory()
            ->count(10)
            ->for($users->random())
            ->withRandomCategories()
            ->create();
        $response = $this->get('/');

        foreach ($items as $item) {
            $response->assertSee($item->item_name);
        }
    }
}
