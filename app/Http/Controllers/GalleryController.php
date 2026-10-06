<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    /**
     * Daftar item Galeri.
     */
    public function index(Request $request)
    {
        $query = Gallery::with('galleryCategory');

        // Pencarian
        if ($request->filled('search')) {
            $keyword = trim($request->search);

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('gallery_category_id', $request->category);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $galleries = $query
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->latest('taken_at')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = $this->activeCategories();

        return view(
            'admin.galleries.index',
            compact('galleries', 'categories')
        );
    }

    /**
     * Form tambah item Galeri.
     */
    public function create()
    {
        $categories = $this->activeCategories();

        return view(
            'admin.galleries.create',
            compact('categories')
        );
    }

    /**
     * Simpan item Galeri.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(imageRequired: true)
        );

        $path = $request->file('image')->store('gallery', 'public');

        Gallery::create([
            'gallery_category_id' => $validated['gallery_category_id'],
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'image' => $path,
            'taken_at' => $validated['taken_at'] ?? null,
            'featured' => $request->boolean('featured'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('galleries.index')
            ->with('success', 'Item Galeri berhasil ditambahkan.');
    }

    /**
     * Detail tidak digunakan; arahkan ke form edit.
     */
    public function show(Gallery $gallery)
    {
        return redirect()->route('galleries.edit', $gallery);
    }

    /**
     * Form edit item Galeri.
     */
    public function edit(Gallery $gallery)
    {
        $categories = $this->activeCategories();

        // Kategori yang sudah nonaktif tetap ditampilkan agar pilihan lama tidak hilang.
        if (
            $gallery->galleryCategory &&
            !$categories->contains('id', $gallery->gallery_category_id)
        ) {
            $categories->push($gallery->galleryCategory);
        }

        return view(
            'admin.galleries.edit',
            compact('gallery', 'categories')
        );
    }

    /**
     * Perbarui item Galeri.
     */
    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate(
            $this->rules(imageRequired: false, gallery: $gallery)
        );

        $data = [
            'gallery_category_id' => $validated['gallery_category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'taken_at' => $validated['taken_at'] ?? null,
            'featured' => $request->boolean('featured'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
            'updated_by' => auth()->id(),
        ];

        if ($gallery->title !== $validated['title']) {
            $data['slug'] = $this->uniqueSlug($validated['title'], $gallery->id);
        }

        $oldImage = null;

        if ($request->hasFile('image')) {
            $oldImage = $gallery->image;
            $data['image'] = $request->file('image')->store('gallery', 'public');
        }

        $gallery->update($data);

        // Hapus foto lama setelah data baru tersimpan.
        if ($oldImage && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('galleries.index')
            ->with('success', 'Item Galeri berhasil diperbarui.');
    }

    /**
     * Hapus item Galeri beserta fotonya.
     */
    public function destroy(Gallery $gallery)
    {
        if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
            Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return redirect()
            ->route('galleries.index')
            ->with('success', 'Item Galeri berhasil dihapus.');
    }

    private function activeCategories(): Collection
    {
        return GalleryCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function rules(bool $imageRequired, ?Gallery $gallery = null): array
    {
        // Kategori harus aktif; saat edit, kategori lama tetap diterima.
        $categoryRule = Rule::exists('gallery_categories', 'id')
            ->where(function ($q) use ($gallery) {
                $q->where('is_active', true);

                if ($gallery) {
                    $q->orWhere('id', $gallery->gallery_category_id);
                }
            });

        return [
            'title' => ['required', 'string', 'max:255'],
            'gallery_category_id' => ['required', 'integer', $categoryRule],
            'image' => [
                $imageRequired ? 'required' : 'nullable',
                'image',
                'mimes:webp,png,jpg,jpeg',
                'max:5120',
            ],
            'description' => ['nullable', 'string'],
            'taken_at' => ['nullable', 'date'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'galeri';
        $slug = $base;
        $counter = 2;

        while (
            Gallery::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
