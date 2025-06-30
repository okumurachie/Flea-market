<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Condition;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $conditions = [
            "良好",
            "目立った傷や汚れなし",
            "やや傷や汚れあり",
            "状態が悪い",
        ];

        foreach ($conditions as $condition) {
            Condition::create(['condition' => $condition]);
        }
    }
}
