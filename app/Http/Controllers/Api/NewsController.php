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
                'category',
                'published_at',
                'created_at',
            ]);

        return response()->json($news);
    }
}