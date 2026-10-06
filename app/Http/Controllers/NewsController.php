<?php

namespace App\Http\Controllers;
use App\Models\NewsImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::with('newsCategory')
            ->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $news = $query
            ->paginate(10)
            ->withQueryString();

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
                        ['pengumuman-popup', 'pengumuman', 'ppid', 'video'],
                        true
                    )
                ),
            ],
            'category' => ['required', 'exists:news_categories,slug'],
            'youtube_url' => [
                'nullable',
                'url',
                Rule::requiredIf(fn () => $request->input('category') === 'video'),
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }

                    $host = strtolower(parse_url($value, PHP_URL_HOST) ?? '');

                    $allowed = [
                        'youtube.com',
                        'www.youtube.com',
                        'youtu.be',
                        'www.youtu.be',
                        'm.youtube.com',
                    ];

                    if (!in_array($host, $allowed, true)) {
                        $fail('URL video harus berasal dari YouTube.');
                    }
                },
            ],

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
                // Kolom content NOT NULL; kategori tanpa isi disimpan string kosong.
                'content' => $validated['content'] ?? '',
                'category' => $validated['category'],
                'youtube_url' => $validated['youtube_url'] ?? null,
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
        'title' => [
            'required',
            'string',
            'max:255',
        ],

        'category' => [
            'required',
            'exists:news_categories,slug',
        ],
        'youtube_url' => [
            'nullable',
            'url',
            Rule::requiredIf(fn () => $request->input('category') === 'video'),
            function ($attribute, $value, $fail) {
                if (!$value) {
                    return;
                }

                $host = strtolower(parse_url($value, PHP_URL_HOST) ?? '');

                $allowed = [
                    'youtube.com',
                    'www.youtube.com',
                    'youtu.be',
                    'www.youtu.be',
                    'm.youtube.com',
                ];

                if (!in_array($host, $allowed, true)) {
                    $fail('URL video harus berasal dari YouTube.');
                }
            },
        ],

        'excerpt' => [
            'nullable',
            'string',
        ],

        'content' => [
            'nullable',
            'string',
            Rule::requiredIf(
                fn () => !in_array(
                    $request->input('category'),
                    [
                        'pengumuman-popup',
                        'pengumuman',
                        'ppid',
                        'video',
                    ],
                    true
                )
            ),
        ],

        'status' => [
            'nullable',
            'in:draft,published',
        ],

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

    /*
    |--------------------------------------------------------------------------
    | COVER IMAGE
    |--------------------------------------------------------------------------
    */

    if ($request->hasFile('image')) {

        if (
            $news->image &&
            Storage::disk('public')->exists($news->image)
        ) {
            Storage::disk('public')->delete($news->image);
        }

        $validated['image'] = $request
            ->file('image')
            ->store('news', 'public');
    }

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT PDF
    |--------------------------------------------------------------------------
    */

    if ($request->hasFile('document')) {

        if (
            $news->document &&
            Storage::disk('public')->exists($news->document)
        ) {
            Storage::disk('public')->delete($news->document);
        }

        $validated['document'] = $request
            ->file('document')
            ->store('news/documents', 'public');

        $validated['document_name'] = $request
            ->file('document')
            ->getClientOriginalName();
    }

    /*
    |--------------------------------------------------------------------------
    | SLUG
    |--------------------------------------------------------------------------
    */

    $validated['slug'] = Str::slug(
        $request->input('title')
    );

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    $status = $request->input('action') === 'publish'
        ? 'published'
        : (
            $request->input('action') === 'draft'
                ? 'draft'
                : ($request->status ?? $news->status)
        );

    $validated['status'] = $status;

    $validated['published_at'] =
        $status === 'published'
            ? now()
            : null;

    /*
    |--------------------------------------------------------------------------
    | LAST MODIFIED
    |--------------------------------------------------------------------------
    */

    $validated['last_modified_by'] =
        auth()->user()->name;

    // Kolom content NOT NULL; kategori tanpa isi disimpan string kosong.
    if (
        array_key_exists('content', $validated) &&
        $validated['content'] === null
    ) {
        $validated['content'] = '';
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE NEWS
    |--------------------------------------------------------------------------
    */

    $news->update($validated);

    /*
    |--------------------------------------------------------------------------
    | GALLERY
    |--------------------------------------------------------------------------
    */

    if ($request->hasFile('gallery')) {

        $currentGalleryCount = $news->images()->count();

        foreach (
            $request->file('gallery')
            as $index => $photo
        ) {

            if ($currentGalleryCount >= 5) {
                break;
            }

            $path = $photo->store(
                'news/gallery',
                'public'
            );

            NewsImage::create([
                'news_id' => $news->id,
                'image' => $path,
                'caption' => null,
                'sort_order' =>
                    $currentGalleryCount + $index + 1,
            ]);
        }
    }

    return redirect()
        ->route('news.index')
        ->with(
            'success',
            'Berita berhasil diperbarui.'
        );
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

    /**
 * Bulk Delete Berita
 */
public function bulkDelete(Request $request)
{
    $validated = $request->validate([
        'ids' => [
            'required',
            'array',
            'min:1',
        ],
        'ids.*' => [
            'integer',
            'exists:news,id',
        ],
    ]);

    $items = News::whereIn('id', $validated['ids'])->get();

    foreach ($items as $item) {

        if (
            $item->image &&
            Storage::disk('public')->exists($item->image)
        ) {
            Storage::disk('public')->delete($item->image);
        }

        if (
            $item->document &&
            Storage::disk('public')->exists($item->document)
        ) {
            Storage::disk('public')->delete($item->document);
        }

        foreach ($item->images as $image) {

            if (
                Storage::disk('public')->exists($image->image)
            ) {
                Storage::disk('public')->delete($image->image);
            }

            $image->delete();
        }

        $item->delete();
    }

    return redirect()
        ->route('news.index')
        ->with(
            'success',
            count($validated['ids']) .
            ' berita berhasil dihapus.'
        );
}

/**
 * Bulk Publish
 */
public function bulkPublish(Request $request)
{
    $validated = $request->validate([
        'ids' => [
            'required',
            'array',
            'min:1',
        ],
        'ids.*' => [
            'integer',
            'exists:news,id',
        ],
    ]);

    News::whereIn(
        'id',
        $validated['ids']
    )->update([
        'status' => 'published',
        'published_at' => now(),
        'last_modified_by' => auth()->user()->name,
    ]);

    return redirect()
        ->route('news.index')
        ->with(
            'success',
            count($validated['ids']) .
            ' berita berhasil dipublish.'
        );
}

/**
 * Export PDF
 */
public function exportPdf(Request $request)
{
    $validated = $request->validate([
        'ids' => [
            'required',
            'array',
            'min:1',
        ],
        'ids.*' => [
            'integer',
            'exists:news,id',
        ],
    ]);

    $news = News::whereIn(
        'id',
        $validated['ids']
    )
    ->latest()
    ->get();

    $pdf = Pdf::loadView(
        'admin.news.pdf',
        compact('news')
    );

    $pdf->setPaper(
        'a4',
        'portrait'
    );

    return $pdf->download(
        'laporan-berita-' .
        now()->format('YmdHis') .
        '.pdf'
    );
}

}