<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsCategory;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            'Berita Utama',
            'Berita',
            'Press Release',
            'Pengumuman',
            'Himbauan',
            'Kegiatan',
            'Prestasi',
            'Lalu Lintas',
            'Kriminal',
            'PPID',

        ];

        foreach ($categories as $index => $category) {

            NewsCategory::updateOrCreate(

                [
                    'slug' => Str::slug($category)
                ],

                [
                    'name' => $category,
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]

            );

        }
    }
}
