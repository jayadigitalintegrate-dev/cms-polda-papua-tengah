<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class GalleryController extends Controller
{
    /** Folder foto child koleksi pada disk public, per jenis item. */
    private const COLLECTION_FOLDERS = [
        GalleryCategory::KIND_DOCUMENTATION => 'gallery/dokumentasi',
        GalleryCategory::KIND_MEDIA_CENTER => 'gallery/media-center',
    ];

    /** Batas panjang isi berita Media Center (karakter). */
    private const MAX_CONTENT_LENGTH = 50000;

    /**
     * Daftar item Galeri.
     */
    public function index(Request $request)
    {
        $query = Gallery::with('galleryCategory')
            ->withCount('images');

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

        $documentationCategoryId = $categories
            ->first(fn (GalleryCategory $category) => $category->isDocumentation())
            ?->id;

        $mediaCenterCategoryId = $categories
            ->first(fn (GalleryCategory $category) => $category->isMediaCenter())
            ?->id;

        $maxImages = Gallery::MAX_COLLECTION_IMAGES;

        return view(
            'admin.galleries.create',
            compact('categories', 'documentationCategoryId', 'mediaCenterCategoryId', 'maxImages')
        );
    }

    /**
     * Simpan item Galeri.
     */
    public function store(Request $request)
    {
        $kind = $this->categoryKind($request->input('gallery_category_id'));

        if ($kind !== GalleryCategory::KIND_SINGLE) {
            return $this->storeCollection($request, $kind);
        }

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
        $kind = $this->kindOf($gallery);
        $isCollection = $kind !== GalleryCategory::KIND_SINGLE;
        $isMediaCenter = $kind === GalleryCategory::KIND_MEDIA_CENTER;

        // Jenis item (foto tunggal / Galeri Dokumentasi / Media Center) tidak dapat
        // diubah lewat edit, sehingga dropdown hanya berisi kategori berjenis sama.
        $categories = $this->activeCategories()
            ->filter(fn (GalleryCategory $category) => $category->kind() === $kind)
            ->values();

        // Kategori yang sudah nonaktif tetap ditampilkan agar pilihan lama tidak hilang.
        if (
            $gallery->galleryCategory &&
            !$categories->contains('id', $gallery->gallery_category_id)
        ) {
            $categories->push($gallery->galleryCategory);
        }

        $gallery->load('images');

        $maxImages = Gallery::MAX_COLLECTION_IMAGES;

        return view(
            'admin.galleries.edit',
            compact('gallery', 'categories', 'isCollection', 'isMediaCenter', 'maxImages')
        );
    }

    /**
     * Perbarui item Galeri.
     */
    public function update(Request $request, Gallery $gallery)
    {
        $kind = $this->kindOf($gallery);

        if ($kind !== $this->categoryKind($request->input('gallery_category_id'))) {
            throw ValidationException::withMessages([
                'gallery_category_id' => 'Jenis item tidak dapat diubah antara foto tunggal, Galeri Dokumentasi, dan Media Center. Buat item baru.',
            ]);
        }

        if ($kind !== GalleryCategory::KIND_SINGLE) {
            return $this->updateCollection($request, $gallery, $kind);
        }

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
     * Hapus item Galeri beserta fotonya (termasuk seluruh foto child koleksi).
     */
    public function destroy(Gallery $gallery)
    {
        $paths = $gallery->images()->pluck('image')
            ->push($gallery->image)
            ->filter()
            ->unique()
            ->values();

        // Baris gallery_images ikut terhapus (FK cascadeOnDelete).
        $gallery->delete();

        foreach ($paths as $path) {
            $this->deleteFileIfUnreferenced($path);
        }

        return redirect()
            ->route('galleries.index')
            ->with('success', 'Item Galeri berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | KOLEKSI 1–5 FOTO: GALERI DOKUMENTASI & MEDIA CENTER
    |--------------------------------------------------------------------------
    */

    private function storeCollection(Request $request, string $kind)
    {
        $validated = $request->validate(
            $this->rules(imageRequired: false)
            + $this->collectionPhotoRules(required: true)
            + $this->contentRules($kind)
        );

        $stored = $this->storePhotos($request->file('photos'), self::COLLECTION_FOLDERS[$kind]);

        try {
            DB::transaction(function () use ($validated, $request, $stored, $kind) {
                $gallery = Gallery::create([
                    'gallery_category_id' => $validated['gallery_category_id'],
                    'title' => $validated['title'],
                    'slug' => $this->uniqueSlug($validated['title']),
                    'description' => $this->cleanText($validated['description'] ?? null, $kind),
                    'content' => $this->contentFor($validated, $kind),
                    // Cover = foto pertama koleksi (file yang sama, bukan salinan).
                    'image' => $stored[0],
                    'taken_at' => $validated['taken_at'] ?? null,
                    'featured' => $request->boolean('featured'),
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'status' => $validated['status'],
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                foreach ($stored as $index => $path) {
                    $gallery->images()->create([
                        'image' => $path,
                        'sort_order' => $index + 1,
                    ]);
                }
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($stored);

            throw $e;
        }

        return redirect()
            ->route('galleries.index')
            ->with('success', $this->kindLabel($kind) . ' berhasil ditambahkan (' . count($stored) . ' foto).');
    }

    private function updateCollection(Request $request, Gallery $gallery, string $kind)
    {
        $validated = $request->validate(
            $this->rules(imageRequired: false, gallery: $gallery)
            + $this->collectionPhotoRules(required: false)
            + $this->contentRules($kind)
            + [
                'delete_images' => ['nullable', 'array'],
                'delete_images.*' => [
                    'integer',
                    // Hanya foto milik koleksi ini yang boleh dihapus.
                    Rule::exists('gallery_images', 'id')->where('gallery_id', $gallery->id),
                ],
            ]
        );

        $deleteIds = collect($validated['delete_images'] ?? [])->map(fn ($id) => (int) $id)->unique();
        $newFiles = $request->file('photos', []);

        $remaining = $gallery->images()->whereNotIn('id', $deleteIds)->count();
        $total = $remaining + count($newFiles);

        if ($total < 1) {
            throw ValidationException::withMessages([
                'photos' => 'Koleksi ' . $this->kindLabel($kind) . ' minimal berisi 1 foto.',
            ]);
        }

        if ($total > Gallery::MAX_COLLECTION_IMAGES) {
            throw ValidationException::withMessages([
                'photos' => 'Koleksi ' . $this->kindLabel($kind) . ' maksimal ' . Gallery::MAX_COLLECTION_IMAGES
                    . " foto. Saat ini tersisa {$remaining} foto, sehingga hanya dapat menambah "
                    . max(0, Gallery::MAX_COLLECTION_IMAGES - $remaining) . ' foto.',
            ]);
        }

        $stored = $this->storePhotos($newFiles, self::COLLECTION_FOLDERS[$kind]);
        $removedPaths = collect();

        try {
            DB::transaction(function () use ($validated, $request, $gallery, $deleteIds, $stored, $kind, &$removedPaths) {
                // Serialisasi edit bersamaan pada koleksi yang sama.
                // MySQL/PostgreSQL: SELECT ... FOR UPDATE mengunci baris koleksi sampai commit.
                // SQLite: klausa lock diabaikan; penulisan sudah diserialisasi oleh
                // write-lock tingkat database (hanya satu transaksi penulis pada satu waktu).
                Gallery::whereKey($gallery->id)->lockForUpdate()->first();

                $data = [
                    'gallery_category_id' => $validated['gallery_category_id'],
                    'title' => $validated['title'],
                    'description' => $this->cleanText($validated['description'] ?? null, $kind),
                    'content' => $this->contentFor($validated, $kind),
                    'taken_at' => $validated['taken_at'] ?? null,
                    'featured' => $request->boolean('featured'),
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'status' => $validated['status'],
                    'updated_by' => auth()->id(),
                ];

                if ($gallery->title !== $validated['title']) {
                    $data['slug'] = $this->uniqueSlug($validated['title'], $gallery->id);
                }

                // Hapus foto yang dipilih user (dibatasi pada koleksi ini).
                $toDelete = $gallery->images()->whereIn('id', $deleteIds)->get();
                $removedPaths = $toDelete->pluck('image');
                GalleryImage::whereIn('id', $toDelete->pluck('id'))->delete();

                // Tambah foto baru setelah urutan terakhir.
                $nextOrder = (int) $gallery->images()->max('sort_order');

                foreach ($stored as $index => $path) {
                    $gallery->images()->create([
                        'image' => $path,
                        'sort_order' => $nextOrder + $index + 1,
                    ]);
                }

                // Pengaman akhir di dalam transaksi (mis. dua edit bersamaan):
                // koleksi tidak boleh pernah melebihi batas atau kosong.
                $finalCount = $gallery->images()->count();

                if ($finalCount < 1 || $finalCount > Gallery::MAX_COLLECTION_IMAGES) {
                    throw ValidationException::withMessages([
                        'photos' => 'Koleksi ' . $this->kindLabel($kind) . ' harus berisi 1–' . Gallery::MAX_COLLECTION_IMAGES
                            . " foto (hasil akhir: {$finalCount} foto). Perubahan dibatalkan.",
                    ]);
                }

                // Cover selalu mengikuti foto pertama koleksi.
                $data['image'] = $gallery->images()->value('image');

                $gallery->update($data);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($stored);

            throw $e;
        }

        foreach ($removedPaths as $path) {
            $this->deleteFileIfUnreferenced($path);
        }

        return redirect()
            ->route('galleries.index')
            ->with('success', $this->kindLabel($kind) . ' berhasil diperbarui.');
    }

    /**
     * Validasi foto koleksi: 1–5 file, hanya JPG/PNG/WEBP.
     *
     * - mimes/mimetypes  : diperiksa dari ISI file (finfo), bukan MIME kiriman browser
     * - extensions       : ekstensi nama file asli juga harus jpg/jpeg/png/webp
     * - image + dimensions: file harus dapat dibaca sebagai gambar raster (SVG ditolak)
     */
    private function collectionPhotoRules(bool $required): array
    {
        return [
            'photos' => [
                $required ? 'required' : 'nullable',
                'array',
                $required ? 'min:1' : 'min:0',
                'max:' . Gallery::MAX_COLLECTION_IMAGES,
            ],
            'photos.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'extensions:jpg,jpeg,png,webp',
                'dimensions:min_width=1,min_height=1',
                'max:5120',
            ],
        ];
    }

    /**
     * Simpan foto dengan nama acak buatan Laravel (bukan nama file dari user).
     *
     * Semua-atau-tidak-sama-sekali: bila salah satu file gagal disimpan,
     * file yang sudah tersimpan pada panggilan ini dihapus kembali.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    private function storePhotos(array $files, string $folder): array
    {
        $stored = [];

        try {
            foreach (array_values($files) as $index => $file) {
                $path = $file->store($folder, 'public');

                // store() mengembalikan false (bukan exception) bila penulisan gagal.
                if (!is_string($path) || $path === '') {
                    throw ValidationException::withMessages([
                        'photos' => 'Foto ke-' . ($index + 1) . ' gagal diunggah. Tidak ada foto yang disimpan, silakan coba lagi.',
                    ]);
                }

                $stored[] = $path;
            }
        } catch (Throwable $e) {
            Storage::disk('public')->delete($stored);

            throw $e;
        }

        return $stored;
    }

    /**
     * Hapus file hanya bila tidak lagi dipakai record Galeri mana pun.
     */
    private function deleteFileIfUnreferenced(?string $path): void
    {
        if (!$path) {
            return;
        }

        $stillUsed = Gallery::where('image', $path)->exists()
            || GalleryImage::where('image', $path)->exists();

        if (!$stillUsed && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Isi berita: wajib untuk Media Center; diabaikan untuk jenis lain.
     */
    private function contentRules(string $kind): array
    {
        if ($kind !== GalleryCategory::KIND_MEDIA_CENTER) {
            return [];
        }

        return [
            'content' => [
                'required',
                'string',
                'max:' . self::MAX_CONTENT_LENGTH,
                // Isi yang hanya berisi tag HTML akan kosong setelah dibersihkan.
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (trim(strip_tags((string) $value)) === '') {
                        $fail('Isi berita tidak boleh kosong (tag HTML tidak disimpan).');
                    }
                },
            ],
        ];
    }

    /**
     * Nilai kolom content: hanya Media Center yang memakainya.
     */
    private function contentFor(array $validated, string $kind): ?string
    {
        if ($kind !== GalleryCategory::KIND_MEDIA_CENTER) {
            return null;
        }

        return $this->cleanText($validated['content'] ?? null, $kind);
    }

    /**
     * CMS tidak memakai editor HTML: isi Media Center disimpan sebagai teks biasa.
     * Semua tag HTML dibuang di server (pertahanan berlapis); saat ditampilkan
     * tetap di-escape (Blade nl2br(e()) / React text node).
     * Jenis lain tidak diubah agar perilaku lama tetap sama.
     */
    private function cleanText(?string $value, string $kind): ?string
    {
        if ($value === null || $kind !== GalleryCategory::KIND_MEDIA_CENTER) {
            return $value;
        }

        $text = trim(strip_tags(str_replace(["\r\n", "\r"], "\n", $value)));

        return $text === '' ? null : $text;
    }

    private function kindLabel(string $kind): string
    {
        return $kind === GalleryCategory::KIND_MEDIA_CENTER ? 'Media Center' : 'Galeri Dokumentasi';
    }

    private function kindOf(Gallery $gallery): string
    {
        return $gallery->galleryCategory?->kind() ?? GalleryCategory::KIND_SINGLE;
    }

    private function categoryKind(mixed $categoryId): string
    {
        if (!is_numeric($categoryId)) {
            return GalleryCategory::KIND_SINGLE;
        }

        return GalleryCategory::find((int) $categoryId)?->kind() ?? GalleryCategory::KIND_SINGLE;
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
