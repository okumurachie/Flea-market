<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use Illuminate\Support\Carbon;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected static $imageIndex = 1;

    public function definition(): array
    {
        $imageFile = 'storage/images/tests/test' . self::$imageIndex . '.jpg';

        self::$imageIndex++;
        if (self::$imageIndex > 10) {
            self::$imageIndex = 1;
        }
        return [
            'user_id' => rand(1, 5),
            'item_name' => $this->faker->word(),
            'price' => $this->faker->numberBetween(1000, 15000),
            'brand' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'item_image' => $imageFile,
            'condition_id' => rand(1, 4),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
    public function withRandomCategories()
    {
        return $this->afterCreating(function (Item $item) {
            $categoryIds = collect(range(1, 14))
                ->random(rand(1, 3))
                ->toArray();

            $item->categories()->attach($categoryIds);
        });
    }
}
