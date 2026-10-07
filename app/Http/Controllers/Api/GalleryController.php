<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryImage;

class GalleryController extends Controller
{
    /**
     * API Galeri publik.
     *
     * Satu request berisi master kategori aktif dan seluruh item
     * Galeri published, agar website cukup melakukan satu fetch
     * lalu memfilter berdasarkan gallery_category_id secara lokal.
     */
    public function index()
    {
        $categories = GalleryCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'sort_order']);

        $categoryIds = $categories->pluck('id');

        $galleries = Gallery::with(['galleryCategory:id,name,slug', 'images'])
            ->where('status', 'published')
            ->whereIn('gallery_category_id', $categoryIds)
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->orderByDesc('taken_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (Gallery $item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'description' => $item->description,

                    // Isi berita/editorial — hanya terisi untuk Media Center.
                    'content' => $item->galleryCategory->isMediaCenter() ? $item->content : null,
                    'image' => $item->image,

                    'image_url' => $item->image
                        ? asset('storage/' . $item->image)
                        : null,

                    'gallery_category_id' => $item->gallery_category_id,
                    'category_id' => $item->gallery_category_id,

                    'category' => [
                        'id' => $item->galleryCategory->id,
                        'name' => $item->galleryCategory->name,
                        'slug' => $item->galleryCategory->slug,
                    ],

                    'taken_at' => $item->taken_at?->toDateString(),
                    'date' => ($item->taken_at ?? $item->created_at)?->toDateString(),
                    'featured' => (bool) $item->featured,
                    'sort_order' => (int) $item->sort_order,
                    'status' => $item->status,
                    'created_at' => $item->created_at,

                    // Galeri Dokumentasi & Media Center: satu item berisi 1–5 foto child.
                    // Item kategori lain tetap foto tunggal (images kosong).
                    // single | documentation | media_center
                    'type' => $item->galleryCategory->kind(),
                    'is_collection' => $item->galleryCategory->usesCollection(),
                    'images_count' => $item->images->count(),
                    'images' => $item->images->map(fn (GalleryImage $image) => [
                        'id' => $image->id,
                        'image' => $image->image,
                        'image_url' => asset('storage/' . $image->image),
                        'sort_order' => (int) $image->sort_order,
                    ])->values(),
                ];
            });

        return response()->json([
            'categories' => $categories->map(fn (GalleryCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'sort_order' => (int) $category->sort_order,
            ]),
            'data' => $galleries,
        ]);
    }
}
