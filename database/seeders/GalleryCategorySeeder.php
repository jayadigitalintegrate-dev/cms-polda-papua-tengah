<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GalleryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            'Kegiatan Pimpinan',
            'Pelayanan Publik',
            'Operasional',
            'Sosial',
            'Event',
            'Galeri Dokumentasi',
            'Media Center',

        ];

        foreach ($categories as $index => $category) {

            GalleryCategory::updateOrCreate(

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
