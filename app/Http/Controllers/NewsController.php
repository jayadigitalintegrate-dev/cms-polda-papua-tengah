<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'image' => ['nullable', 'image', 'mimes:webp,png', 'max:5120'],
        ]);

        $image = null;

        if ($request->hasFile('image')) {

            $image = $request->file('image')
                ->store('news', 'public');

        }

        $status = $request->input('action') === 'publish'
            ? 'publish'
            : 'draft';

        News::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'category' => $validated['category'],
            'image' => $image,

            'status' => $status,

            'published_at' => $status === 'publish'
                ? now()
                : null,

            'author_id' => auth()->id(),

            'last_modified_by' => auth()->user()->name,
        ]);

        return redirect()
            ->route('news.index')
            ->with(
                'success',
                $status === 'publish'
                    ? 'Berita berhasil dipublikasikan.'
                    : 'Draft berhasil disimpan.'
            );
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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'category' => ['required', 'exists:news_categories,slug'],
            'image' => ['nullable', 'image', 'mimes:webp,png', 'max:5120'],
        ]);

        $image = $news->image;

        if ($request->hasFile('image')) {

            if ($image) {
                Storage::disk('public')->delete($image);
            }

            $image = $request->file('image')
                ->store('news', 'public');
        }

        $status = $request->input('action') === 'publish'
            ? 'publish'
            : 'draft';

        $news->update([

            'title' => $validated['title'],

            'slug' => Str::slug($validated['title']),

            'excerpt' => $validated['excerpt'] ?? null,

            'content' => $validated['content'],

            'category' => $validated['category'],

            'image' => $image,

            'status' => $status,

            'published_at' => $status === 'publish'
                ? now()
                : $news->published_at,

            'last_modified_by' => auth()->user()->name,

        ]);

        return redirect()
            ->route('news.index')
            ->with(
                'success',
                $status === 'publish'
                    ? 'Berita berhasil dipublikasikan.'
                    : 'Draft berhasil diperbarui.'
            );
    }

    public function destroy(News $news)
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()
            ->route('news.index')
            ->with('success', 'Berita berhasil dihapus.');
    }
}