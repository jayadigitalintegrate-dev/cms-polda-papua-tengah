<?php

namespace App\Http\Controllers;
use App\Models\NewsImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with('newsCategory')
            ->latest()
            ->paginate(10);

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        $categories = NewsCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.news.create', compact('categories'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'excerpt' => ['nullable', 'string'],
        'content' => ['required', 'string'],
        'category' => ['required', 'exists:news_categories,slug'],

        'image' => [
            'nullable',
            'image',
            'mimes:webp,png',
            'max:5120',
        ],

        'gallery' => [
            'nullable',
            'array',
            'max:5',
        ],

        'gallery.*' => [
            'image',
            'mimes:webp,png,jpg,jpeg',
            'max:5120',
        ],
    ]);

    DB::transaction(function () use ($request, $validated) {

        $cover = null;

        if ($request->hasFile('image')) {
            $cover = $request->file('image')->store('news', 'public');
        }

        $status = $request->input('action') === 'publish'
            ? 'publish'
            : 'draft';

        $news = News::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'category' => $validated['category'],
            'image' => $cover,
            'status' => $status,
            'published_at' => $status === 'publish' ? now() : null,
            'author_id' => auth()->id(),
            'last_modified_by' => auth()->user()->name,
        ]);

        if ($request->hasFile('gallery')) {

            foreach ($request->file('gallery') as $index => $photo) {

                $path = $photo->store('news/gallery', 'public');

                NewsImage::create([
                    'news_id' => $news->id,
                    'image' => $path,
                    'caption' => null,
                    'sort_order' => $index + 1,
                ]);
            }
        }

    });

    return redirect()
        ->route('news.index')
        ->with('success', 'Berita berhasil disimpan.');
}

    public function show(News $news)
    {
        return view('admin.news.show', compact('news'));
    }

    public function edit(News $news)
    {
        $categories = NewsCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.news.edit', compact('news', 'categories'));
    }

    public function update(Request $request, News $news)
    {
        return back()->with('success', 'Update sementara berhasil.');
    }

    public function destroy(News $news)
    {
        return back()->with('success', 'Delete sementara berhasil.');
    }
}