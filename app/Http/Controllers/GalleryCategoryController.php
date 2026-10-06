<?php

namespace App\Http\Controllers;

use App\Models\GalleryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = GalleryCategory::query();

        if ($request->filled('search')) {
            $keyword = trim($request->search);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('slug', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $categories = $query
            ->withCount('galleries')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.gallery_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.gallery_categories.create');
    }

    public function store(Request $request)
    {
        $this->normalizeSlug($request);

        $validated = $request->validate($this->rules());

        GalleryCategory::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('gallery-categories.index')
            ->with('success', 'Kategori Galeri berhasil ditambahkan.');
    }

    public function edit(GalleryCategory $galleryCategory)
    {
        return view(
            'admin.gallery_categories.edit',
            compact('galleryCategory')
        );
    }

    public function update(
        Request $request,
        GalleryCategory $galleryCategory
    ) {
        $this->normalizeSlug($request);

        $validated = $request->validate(
            $this->rules($galleryCategory)
        );

        $galleryCategory->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('gallery-categories.index')
            ->with('success', 'Kategori Galeri berhasil diperbarui.');
    }

    public function destroy(GalleryCategory $galleryCategory)
    {
        if ($galleryCategory->galleries()->exists()) {
            return redirect()
                ->route('gallery-categories.index')
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh item Galeri.'
                );
        }

        $galleryCategory->delete();

        return redirect()
            ->route('gallery-categories.index')
            ->with('success', 'Kategori Galeri berhasil dihapus.');
    }

    /**
     * Slug boleh dikosongkan; jika kosong dibuat otomatis dari nama.
     */
    private function normalizeSlug(Request $request): void
    {
        $request->merge([
            'slug' => Str::slug(
                (string) ($request->input('slug') ?: $request->input('name'))
            ),
        ]);
    }

    private function rules(?GalleryCategory $galleryCategory = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gallery_categories', 'slug')
                    ->ignore($galleryCategory?->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
