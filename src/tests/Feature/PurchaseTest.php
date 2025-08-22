<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\TestCategoriesTableSeeder;
use Database\Seeders\TestItemsTableSeeder;
use App\Models\User;
use App\Models\Item;
use App\Services\StripeService;



class PurchaseTest extends TestCase
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

    public function test_card_payment_creates_purchase()
    {
        $user = User::first();
        $item = Item::first();

        $this->actingAs($user)
            ->post(route('purchase.checkout'), [
                'item_id' => $item->id,
                'post_code' => '123-4567',
                'destination' => '東京都新宿区',
                'payment_method' => 'card',
            ])
            ->assertRedirect();

        $response = $this->actingAs($user)
            ->withSession(['item_id' => $item->id,])
            ->get(route('purchase.success', ['session_id' => 'dummy_session']));
        $response->assertRedirect('/')
            ->assertSessionHas('message', '購入しました。ありがとうございます。');

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'card',
        ]);
    }

    public function test_konbini_payment_redirects_to_voucher_url()
    {
        $user = User::first();
        $item = Item::first();

        $this->actingAs($user);

        $stripeMock = \Mockery::mock(StripeService::class);
        $stripeMock->shouldReceive('createKonbiniPayment')
            ->once()
            ->andReturn((object)[
                'next_action' => (object)[
                    'konbini_display_details' => (object)[
                        'hosted_voucher_url' => 'https://stripe.test/voucher/123',
                    ],
                ],
            ]);

        $this->app->instance(StripeService::class, $stripeMock);

        $response = $this->post(route('purchase.checkout'), [
            'item_id' => $item->id,
            'payment_method' => 'konbini',
            'post_code' => '123-4567',
            'destination' => '東京都新宿区',
        ]);

        $response->assertRedirect(route('konbini.confirm'));

        $response = $this->actingAs($user)
            ->withSession(['voucher_url' => 'https://stripe.test/voucher/123'])
            ->get(route('konbini.confirm'));

        $response->assertStatus(200);
        $response->assertSee('https://stripe.test/voucher/123');
    }
}
