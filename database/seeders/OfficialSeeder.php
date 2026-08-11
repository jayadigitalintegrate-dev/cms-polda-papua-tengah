<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;

class OfficialSeeder extends Seeder
{
    public function run(): void
    {
        $positions = config('official_positions', []);

        foreach ($positions as $index => $position) {
            Official::updateOrCreate(
                [
                    'position_id' => $position['value'],
                ],
                [
                    'name_id' => '',
                    'name_en' => '',
                    'rank' => '',
                    'position_en' => $position['position_en'] ?? null,

                    'nrp' => null,
                    'birth_place' => null,
                    'birth_date' => null,
                    'religion' => null,
                    'marital_status' => null,
                    'spouse' => null,
                    'children' => 0,
                    'motto' => null,

                    'education' => [],
                    'assignments' => [],
                    'career' => [],
                    'awards' => [],

                    'sort_order' => $index + 1,
                    'status' => 'active',
                ]
            );
        }
    }
}