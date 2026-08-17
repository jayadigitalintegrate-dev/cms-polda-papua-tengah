<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = config('official_positions', []);

        foreach ($positions as $index => $position) {
            Position::updateOrCreate(
                [
                    'name_id' => $position['value'],
                ],
                [
                    'name_en' => $position['position_en'] ?? null,
                    'sort_order' => $index + 1,
                    'status' => true,
                ]
            );
        }
    }
}
