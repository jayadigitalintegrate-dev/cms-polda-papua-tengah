<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::where('status', 'published')
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
                    'published_at' => $item->published_at,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json($news);
    }
}