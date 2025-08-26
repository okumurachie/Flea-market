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
use App\Models\Purchase;

class UserInformationTest extends TestCase
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

    public function test_mypage_shows_user_profile_and_items()
    {
        $user = User::first();
        $profile = $user->profile;


        $this->actingAs($user);

        $listedItems = Item::take(2)->get();
        foreach ($listedItems as $listedItem) {
            $listedItem->update([
                'user_id' => $user->id,
            ]);
        }

        $listedItems = Item::where('user_id', $user->id)->get();

        $purchasedItems = Item::orderBy('id', 'desc')->take(2)->get()->reverse();
        foreach ($purchasedItems as $purchasedItem) {
            Purchase::create([
                'user_id' => $user->id,
                'item_id' => $purchasedItem->id,
                'payment_method' => 'card',
                'post_code' => $profile->post_code,
                'destination' => $profile->address . ($profile->building ?? ''),
            ]);
        }

        $response = $this->get(route('mypage'));
        $response->assertSee($profile->user_name);
        $response->assertSee(asset($profile->image));

        $response = $this->get('/mypage?page=sell');
        foreach ($listedItems as $listedItem) {
            $response->assertSee($listedItem->item_name);
        }

        $response = $this->get('/mypage?page=buy');
        foreach ($purchasedItems as $purchasedItem) {
            $response->assertSee($purchasedItem->item_name);
        }
    }

    public function test_profile_edit_page_displays_current_user_information()
    {
        $user = User::first();
        $profile = $user->profile;

        $this->actingAs($user);

        $response = $this->get('/mypage/profile');

        $response->assertSee(asset($profile->image));

        $response->assertSee($profile->user_name);
        $response->assertSee($profile->post_code);
        $response->assertSee($profile->address);

        if (!empty($profile->building)) {
            $response->assertSee($profile->building);
        }
    }
}
