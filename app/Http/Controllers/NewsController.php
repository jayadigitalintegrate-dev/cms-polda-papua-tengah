<?php

namespace App\Http\Controllers;
use App\Models\NewsImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'content' => [
                'nullable',
                'string',
                Rule::requiredIf(
                    fn () => !in_array(
                        $request->input('category'),
                        ['pengumuman-popup', 'pengumuman', 'ppid'],
                        true
                    )
                ),
            ],
            'category' => ['required', 'exists:news_categories,slug'],

            'image' => [
                'nullable',
                'image',
                'mimes:webp,png,jpg,jpeg',
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

            'document' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ]);

        DB::transaction(function () use ($request, $validated) {

            $cover = null;
            $documentPath = null;
            $documentName = null;

            if ($request->hasFile('image')) {
                $cover = $request->file('image')->store('news', 'public');
            }

            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('news/documents', 'public');
                $documentName = $request->file('document')->getClientOriginalName();
            }
            $status = $request->input('action') === 'publish'
                ? 'published'
                : 'draft';
            $news = News::create([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'],
                'category' => $validated['category'],
                'image' => $cover,
                'document' => $documentPath,
                'document_name' => $documentName,
                'status' => $status,
                'published_at' => $status === 'published' ? now() : null,
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|exists:news_categories,slug',
            'excerpt' => 'nullable|string',
            'content' => [
            'nullable',
            'string',
            Rule::requiredIf(
                fn () => !in_array(
                    $request->input('category'),
                    ['pengumuman-popup', 'pengumuman', 'ppid'],
                    true
                )
            ),
        ],
            'status' => 'nullable|in:draft,published',
            'image' => 'nullable|image|mimes:webp,png,jpg,jpeg|max:5120',
        ]);

        if ($request->hasFile('image')) {

            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }

            $validated['image'] = $request->file('image')
                ->store('news', 'public');
        }

        $validated['slug'] = Str::slug($request->title);

        $status = $request->input('action') === 'publish'
            ? 'published'
            : ($request->status ?? $news->status);

        $validated['status'] = $status;

        $validated['published_at'] =
            $status === 'published'
            ? now()
            : null;
        $validated['last_modified_by'] = auth()->user()->name;

        $news->update($validated);

        return redirect()
            ->route('news.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }


    public function destroy(News $news)
    {
        if ($news->image && Storage::disk('public')->exists($news->image)) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()
            ->route('news.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

}
