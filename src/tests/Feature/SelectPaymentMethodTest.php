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

class SelectPaymentMethodTest extends TestCase
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

    public function test_selected_payment_method_is_reflected_correctly()
    {
        $user = User::first();
        $item = Item::first();

        $this->actingAs($user);

        $response = $this->get(route('purchase.show', ['id' => $item->id]));
        $response->assertSee('選択してください');

        $response = $this->followingRedirects()->post(route('purchase.checkout'), [
            'item_id' => $item->id,
            'payment_method' => 'konbini',
            'post_code' => '',
            'destination' => '',
        ]);
        $response->assertSee('郵便番号を入力してください');
        $response->assertSee('配送先を指定してください');
        $response->assertSee('コンビニ支払い');

        $response = $this->followingRedirects()->post(route('purchase.checkout'), [
            'item_id' => $item->id,
            'payment_method' => 'card',
            'post_code' => '',
            'destination' => '',
        ]);
        $response->assertSee('郵便番号を入力してください');
        $response->assertSee('配送先を指定してください');
        $response->assertSee('カード支払い');
    }
}
