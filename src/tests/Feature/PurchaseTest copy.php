<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Component\DomCrawler\Crawler;
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

    public function test_payment_intent_succeeded_creates_purchase()
    {
        $user = User::first();
        $item = Item::first();
        $profile = $user->profile;

        $payload = [
            'id' => 'evt_test_123',
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'object' => 'payment_intent',
                    'payment_method_types' => ['konbini'],
                    'metadata' => [
                        'item_id' => $item->id,
                        'user_id' => $user->id,
                        'post_code' => $profile->post_code,
                        'destination' => $profile->address . ($profile->building ?? ''),
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/webhook/stripe', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'konbini',
            'post_code' => $profile->post_code,
            'destination' => $profile->address . ($profile->building ?? ''),
        ]);
    }

    public function test_purchased_items_are_displayed_with_sold_label_on_item_index()
    {
        $user = User::first();
        $paginatedItems = Item::paginate(8);

        $purchasedItem = $paginatedItems->first();
        $this->actingAs($user);

        $this->post(route('purchase.checkout'), [
            'item_id' => $purchasedItem->id,
            'post_code' => '123-4567',
            'destination' => '東京都新宿区',
            'payment_method' => 'card',
        ])
            ->assertRedirect();

        $purchasedItem->refresh();

        $response = $this->get('/');
        $html = $response->getContent();

        $crawler = new Crawler($html);

        $crawler->filter('a.content__id')->each(function (Crawler $block) use ($purchasedItem) {
            $itemName = $block->filter('div.content__name p')->text();


            if ($itemName === $purchasedItem->item_name) {
                $hasSoldLabel = $block->filter('span.sold-label')->count() > 0;
                $this->assertTrue($hasSoldLabel);
            }
        });
    }

    public function test_purchased_items_are_displayed_on_mypage()
    {
        $user = User::first();
        $item = Item::first();

        $purchasedItem = $item->first();
        $this->actingAs($user);

        $this->post(route('purchase.checkout'), [
            'item_id' => $purchasedItem->id,
            'post_code' => '123-4567',
            'destination' => '東京都新宿区',
            'payment_method' => 'card',
        ])
            ->assertRedirect();

        $purchasedItem->refresh();

        $response = $this->get('/mypage/?page=buy');
        $response->assertStatus(200);
        $response->assertSee($purchasedItem->item_name);
    }
}
