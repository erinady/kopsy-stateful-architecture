<?php

namespace Database\Seeders;

use App\Enums\SavingTypeEnum;
use App\Models\SavingProduct;
use Illuminate\Database\Seeder;

class SavingProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SavingTypeEnum::cases() as $case) {
            SavingProduct::factory()->create([
                'name' => $case->value,
            ]);
        }
    }
}
