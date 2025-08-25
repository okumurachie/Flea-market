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

class ChangeShippingAddressTest extends TestCase
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

    public function test_changed_address_is_reflected_on_purchase_page()
    {
        $user = User::first();
        $item = Item::first();
        $profile = $user->profile;

        $this->actingAs($user);

        $this->get('/purchase/address/' . $item->id);

        $this->patch(route('address.update', ['id' => $item->id]));

        $response = $this->actingAs($user)->get(route('purchase.show', ['id' => $item->id]));
        $response->assertSee($profile->post_code);
        $response->assertSee($profile->address . ($profile->building ?? ''));
    }

    public function test_shipping_address_is_linked_the_purchased_item_and_registered()
    {
        $user = User::first();
        $item = Item::first();
        $profile = $user->profile;

        $this->actingAs($user);

        $this->get('/purchase/address/' . $item->id);

        $this->patch(route('address.update', ['id' => $item->id]));

        $response = $this->actingAs($user)->get(route('purchase.show', ['id' => $item->id]));
        $response->assertSee($profile->post_code);
        $response->assertSee($profile->address . ($profile->building ?? ''));

        $response = $this->actingAs($user)->post(route('purchase.checkout'));

        $response = $this->actingAs($user)->post(route('purchase.checkout'), [
            'item_id' => $item->id,
            'post_code' => $profile->post_code,
            'destination' => $profile->address . ($profile->building ?? ''),
            'payment_method' => 'card',
        ])->assertRedirect();

        $response = $this->actingAs($user)
            ->withSession(['item_id' => $item->id,])
            ->get(route('purchase.success', ['session_id' => 'dummy_session']));

        $this->assertDatabaseHas('purchases', [
            'post_code' => $profile->post_code,
            'destination' => $profile->address . ($profile->building ?? ''),
        ]);
    }
}
