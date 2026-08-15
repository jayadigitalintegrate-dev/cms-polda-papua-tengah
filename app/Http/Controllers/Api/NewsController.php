<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    /**
     * API daftar berita publik.
     *
     * Pengumuman popup dan pengumuman resmi
     * sengaja tidak dimasukkan ke endpoint berita.
     */
    public function index()
    {
        $news = News::where('status', 'published')
            ->whereNotIn('category', [
                'pengumuman-popup',
                'pengumuman-resmi',
            ])
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get([
                'id',
                'title',
                'slug',
                'excerpt',
                'content',
                'image',
                'document',
                'document_name',
                'category',
                'featured',
                'sort_order',
                'published_at',
                'created_at',
            ])
            ->map(function (News $item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'excerpt' => $item->excerpt,
                    'content' => $item->content,
                    'image' => $item->image,

                    'image_url' => $item->image
                        ? asset('storage/' . $item->image)
                        : null,

                    'document' => $item->document,
                    'document_name' => $item->document_name,

                    'document_url' => $item->document
                        ? asset('storage/' . $item->document)
                        : null,

                    'category' => $item->category,

                    'featured' => (bool) $item->featured,
                    'sort_order' => (int) $item->sort_order,

                    'published_at' => $item->published_at,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json($news);
    }

    /**
     * API khusus pengumuman popup.
     *
     * Hanya mengambil satu popup aktif terbaru.
     */
    public function popup()
    {
        $announcement = News::where('status', 'published')
            ->where('category', 'pengumuman-popup')
            ->latest('published_at')
            ->first([
                'id',
                'title',
                'slug',
                'excerpt',
                'content',
                'image',
                'document',
                'document_name',
                'category',
                'published_at',
                'created_at',
            ]);

        if (!$announcement) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $announcement->id,
            'title' => $announcement->title,
            'slug' => $announcement->slug,
            'excerpt' => $announcement->excerpt,
            'content' => $announcement->content,
            'image' => $announcement->image,

            'image_url' => $announcement->image
                ? asset('storage/' . $announcement->image)
                : null,

            'document' => $announcement->document,
            'document_name' => $announcement->document_name,

            'document_url' => $announcement->document
                ? asset('storage/' . $announcement->document)
                : null,

            'category' => $announcement->category,

            'published_at' => $announcement->published_at,
            'created_at' => $announcement->created_at,
        ]);
    }
}