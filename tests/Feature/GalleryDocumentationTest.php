<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryImage;
use App\Models\User;
use Database\Seeders\GalleryCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryDocumentationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private GalleryCategory $documentation;

    private GalleryCategory $operasional;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(GalleryCategorySeeder::class);

        $this->user = User::factory()->create();
        $this->documentation = GalleryCategory::where('slug', GalleryCategory::DOCUMENTATION_SLUG)->firstOrFail();
        $this->operasional = GalleryCategory::where('slug', 'operasional')->firstOrFail();
    }

    /**
     * Nama opsi pada <select id="gallery_category_id"> (tanpa placeholder).
     *
     * @return array<int, string>
     */
    private function categoryOptions(string $html): array
    {
        preg_match('/<select[^>]*id="gallery_category_id"[^>]*>(.*?)<\/select>/s', $html, $select);
        preg_match_all('/<option[^>]*value="(\d+)"[^>]*>\s*(.*?)\s*<\/option>/s', $select[1] ?? '', $options);

        return $options[2];
    }
    private function photos(int $count): array
    {
        $types = ['jpg', 'png', 'webp'];

        return array_map(
            fn (int $i) => UploadedFile::fake()->image("foto-{$i}." . $types[$i % 3], 640, 480),
            range(0, $count - 1)
        );
    }

    private function storeCollection(array $photos, array $extra = [])
    {
        return $this->actingAs($this->user)->post('/galleries', $extra + [
            'title' => 'Dokumentasi HUT Bhayangkara',
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
            'photos' => $photos,
        ]);
    }

    public function test_seeder_adds_documentation_as_sixth_active_category(): void
    {
        $this->assertSame(
            ['Kegiatan Pimpinan', 'Pelayanan Publik', 'Operasional', 'Sosial', 'Event', 'Galeri Dokumentasi', 'Media Center'],
            GalleryCategory::where('is_active', true)->orderBy('sort_order')->pluck('name')->all()
        );

        $this->actingAs($this->user)->get('/galleries/create')->assertOk()
            ->assertSee('Galeri Dokumentasi')
            ->assertSee('name="photos[]"', false);
    }

    public function test_store_five_photos_creates_one_collection_with_five_children(): void
    {
        $this->storeCollection($this->photos(5))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('galleries.index'));

        $this->assertSame(1, Gallery::count());
        $gallery = Gallery::first();
        $this->assertSame($this->documentation->id, $gallery->gallery_category_id);
        $this->assertCount(5, $gallery->images);
        $this->assertSame([1, 2, 3, 4, 5], $gallery->images->pluck('sort_order')->all());
        $this->assertSame($gallery->images->first()->image, $gallery->image, 'cover = foto pertama');

        foreach ($gallery->images as $image) {
            $this->assertStringStartsWith('gallery/dokumentasi/', $image->image);
            Storage::disk('public')->assertExists($image->image);
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function singleFormatProvider(): array
    {
        return [
            'JPG' => ['jpg', 'image/jpeg'],
            'PNG' => ['png', 'image/png'],
            'WEBP' => ['webp', 'image/webp'],
        ];
    }

    /**
     * @dataProvider singleFormatProvider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('singleFormatProvider')]
    public function test_collection_with_one_photo_of_each_allowed_format(string $ext, string $mime): void
    {
        $this->storeCollection([UploadedFile::fake()->image("satu-foto.{$ext}", 800, 600)])
            ->assertSessionHasNoErrors();

        $gallery = Gallery::with('images')->firstOrFail();
        $this->assertCount(1, $gallery->images);
        $this->assertSame($gallery->images[0]->image, $gallery->image);
        $this->assertStringEndsWith(".{$ext}", $gallery->image);
        $this->assertSame($mime, Storage::disk('public')->mimeType($gallery->image));
    }

    /**
     * Ganti disk "public" dengan proxy yang memanggil $onWrite pada setiap putFileAs().
     * $onWrite(int $writeNumber, callable $realWrite) menentukan hasil penulisan.
     */
    private function interceptWrites(callable $onWrite): object
    {
        $state = (object) ['writes' => 0];
        $real = Storage::disk('public');
        $disk = \Mockery::mock($real)->makePartial();
        $disk->shouldReceive('putFileAs')->andReturnUsing(
            function (...$args) use ($state, $onWrite, $real) {
                return $onWrite(++$state->writes, fn () => $real->putFileAs(...$args));
            }
        );
        Storage::set('public', $disk);

        return $state;
    }

    /**
     * Disk "public" yang menolak penulisan ke-N (store() mengembalikan false).
     */
    private function failOnWrite(int $failingWrite): object
    {
        return $this->interceptWrites(
            fn (int $n, callable $write) => $n === $failingWrite ? false : $write()
        );
    }

    public function test_failed_upload_midway_leaves_no_record_or_files_on_store(): void
    {
        $state = $this->failOnWrite(3);

        $this->storeCollection($this->photos(5))
            // Pesan ini hanya berasal dari storePhotos() versi baru.
            ->assertSessionHasErrors(['photos' => 'Foto ke-3 gagal diunggah. Tidak ada foto yang disimpan, silakan coba lagi.']);

        $this->assertSame(3, $state->writes, 'berhenti tepat di foto ke-3; foto 4–5 tidak diproses');
        $this->assertSame(0, Gallery::count());
        $this->assertSame(0, GalleryImage::count());
        $this->assertSame([], Storage::disk('public')->allFiles(), 'foto 1–2 yang sempat tersimpan harus dibersihkan');
    }

    public function test_failed_upload_midway_leaves_collection_unchanged_on_update(): void
    {
        $this->storeCollection($this->photos(2));
        $gallery = Gallery::with('images')->firstOrFail();
        $beforeIds = $gallery->images->pluck('id')->all();
        $beforePaths = $gallery->images->pluck('image')->all();
        $beforeCover = $gallery->image;
        $filesBefore = Storage::disk('public')->allFiles();

        $state = $this->failOnWrite(2);

        $this->actingAs($this->user)->put("/galleries/{$gallery->id}", [
            'title' => 'Judul Baru Tidak Boleh Tersimpan',
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
            'delete_images' => [$beforeIds[0]],   // permintaan hapus juga tidak boleh dijalankan
            'photos' => $this->photos(3),
        ])->assertSessionHasErrors(['photos' => 'Foto ke-2 gagal diunggah. Tidak ada foto yang disimpan, silakan coba lagi.']);

        $this->assertSame(2, $state->writes);

        // Koleksi lama utuh: record, judul, cover.
        $gallery->refresh()->load('images');
        $this->assertSame('Dokumentasi HUT Bhayangkara', $gallery->title);
        $this->assertSame($beforeCover, $gallery->image);

        // GalleryImage lama tetap ada (ID & path sama), termasuk yang diminta dihapus.
        $this->assertSame($beforeIds, $gallery->images->pluck('id')->all());
        $this->assertSame($beforePaths, $gallery->images->pluck('image')->all());
        $this->assertSame(2, GalleryImage::count());

        // File lama tetap ada; tidak ada file baru parsial.
        foreach ($beforePaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
        $this->assertEqualsCanonicalizing($filesBefore, Storage::disk('public')->allFiles());
    }

    public function test_limit_is_enforced_inside_transaction_when_photo_added_concurrently(): void
    {
        $this->storeCollection($this->photos(4));
        $gallery = Gallery::with('images')->firstOrFail();
        $filesBefore = Storage::disk('public')->allFiles();

        // Pra-cek request ini melihat 4 foto (4 + 1 = 5 → lolos). Selama upload berlangsung,
        // request lain menambah 1 foto ke koleksi yang sama (simulasi edit bersamaan).
        $this->interceptWrites(function (int $n, callable $write) use ($gallery) {
            GalleryImage::create(['gallery_id' => $gallery->id, 'image' => 'gallery/dokumentasi/dari-request-lain.jpg', 'sort_order' => 99]);

            return $write();
        });

        $this->actingAs($this->user)->put("/galleries/{$gallery->id}", [
            'title' => $gallery->title,
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
            'photos' => $this->photos(1),
        ])->assertSessionHasErrors(['photos' => 'Koleksi Galeri Dokumentasi harus berisi 1–5 foto (hasil akhir: 6 foto). Perubahan dibatalkan.']);

        // Transaksi dibatalkan: tetap 5 (4 lama + 1 dari request lain), bukan 6.
        $this->assertSame(5, $gallery->images()->count());
        // File yang diunggah request ini dihapus kembali.
        $this->assertEqualsCanonicalizing($filesBefore, Storage::disk('public')->allFiles());
    }

    public function test_sixth_photo_is_rejected_on_store(): void
    {
        $this->storeCollection($this->photos(6))->assertSessionHasErrors('photos');

        $this->assertSame(0, Gallery::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_collection_requires_at_least_one_photo(): void
    {
        $this->storeCollection([])->assertSessionHasErrors('photos');
        $this->assertSame(0, Gallery::count());
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        $cases = [
            'pdf' => UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf'),
            'svg' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>'),
            'gif' => UploadedFile::fake()->image('animasi.gif'),
            'php disguised as jpg' => UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo "x"; ?>'),
            'png content with .php name' => UploadedFile::fake()->image('shell.php'),
            'exe' => UploadedFile::fake()->createWithContent('setup.exe', "MZ\x90\x00binary"),
        ];

        foreach ($cases as $label => $file) {
            $this->storeCollection([$this->photos(1)[0], $file])
                ->assertSessionHasErrors('photos.1', "{$label} seharusnya ditolak");
        }

        $this->assertSame(0, Gallery::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_edit_shows_existing_photos_and_add_up_to_limit(): void
    {
        $this->storeCollection($this->photos(3));
        $gallery = Gallery::first();

        $this->actingAs($this->user)->get("/galleries/{$gallery->id}/edit")->assertOk()
            ->assertSee('name="delete_images[]"', false)
            ->assertSee('Tambah Foto');

        $payload = [
            'title' => 'Dokumentasi HUT Bhayangkara (Edit)',
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
        ];

        // 3 + 2 = 5 → diterima, foto lama tetap ada.
        $oldPaths = $gallery->images->pluck('image')->all();
        $this->actingAs($this->user)->put("/galleries/{$gallery->id}", $payload + ['photos' => $this->photos(2)])
            ->assertSessionHasNoErrors();
        $gallery->refresh();
        $this->assertCount(5, $gallery->images);
        foreach ($oldPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // Foto ke-6 → ditolak, koleksi tidak berubah.
        $this->actingAs($this->user)->put("/galleries/{$gallery->id}", $payload + ['photos' => $this->photos(1)])
            ->assertSessionHasErrors('photos');
        $this->assertCount(5, $gallery->fresh()->images);
    }

    public function test_edit_delete_selected_photos_and_replace_within_limit(): void
    {
        $this->storeCollection($this->photos(5));
        $gallery = Gallery::first();
        $first = $gallery->images[0];
        $second = $gallery->images[1];

        $this->actingAs($this->user)->put("/galleries/{$gallery->id}", [
            'title' => $gallery->title,
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
            'delete_images' => [$first->id, $second->id],
            'photos' => $this->photos(2),
        ])->assertSessionHasNoErrors();

        $gallery->refresh()->load('images');
        $this->assertCount(5, $gallery->images);
        $this->assertFalse($gallery->images->contains('id', $first->id));
        Storage::disk('public')->assertMissing($first->image);
        Storage::disk('public')->assertMissing($second->image);
        $this->assertSame($gallery->images->first()->image, $gallery->image, 'cover ikut pindah');
        Storage::disk('public')->assertExists($gallery->image);

        // Tidak boleh mengosongkan koleksi.
        $this->actingAs($this->user)->put("/galleries/{$gallery->id}", [
            'title' => $gallery->title,
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
            'delete_images' => $gallery->images->pluck('id')->all(),
        ])->assertSessionHasErrors('photos');
        $this->assertCount(5, $gallery->fresh()->images);
    }

    public function test_cannot_delete_photos_of_another_collection(): void
    {
        $this->storeCollection($this->photos(2));
        $this->storeCollection($this->photos(2), ['title' => 'Koleksi Lain']);
        [$mine, $other] = Gallery::orderBy('id')->get()->all();

        $this->actingAs($this->user)->put("/galleries/{$mine->id}", [
            'title' => $mine->title,
            'gallery_category_id' => $this->documentation->id,
            'status' => 'published',
            'delete_images' => [$other->images[0]->id],
        ])->assertSessionHasErrors('delete_images.0');

        $this->assertCount(2, $other->fresh()->images);
        Storage::disk('public')->assertExists($other->images[0]->image);
    }

    public function test_delete_collection_cleans_child_files_but_not_other_collections(): void
    {
        $this->storeCollection($this->photos(5));
        $this->storeCollection($this->photos(2), ['title' => 'Koleksi Lain']);
        [$target, $other] = Gallery::orderBy('id')->get()->all();
        $targetPaths = $target->images->pluck('image')->all();
        $otherPaths = $other->images->pluck('image')->all();

        $this->actingAs($this->user)->delete("/galleries/{$target->id}")
            ->assertRedirect(route('galleries.index'));

        $this->assertNull(Gallery::find($target->id));
        $this->assertSame(0, GalleryImage::where('gallery_id', $target->id)->count());
        foreach ($targetPaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
        foreach ($otherPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
        $this->assertSame(7, GalleryCategory::count());
    }

    public function test_type_cannot_switch_between_collection_and_single(): void
    {
        $this->storeCollection($this->photos(2));
        $collection = Gallery::first();

        $this->actingAs($this->user)->put("/galleries/{$collection->id}", [
            'title' => $collection->title,
            'gallery_category_id' => $this->operasional->id,
            'status' => 'published',
        ])->assertSessionHasErrors('gallery_category_id');
        $this->assertSame($this->documentation->id, $collection->fresh()->gallery_category_id);
    }

    public function test_regular_categories_keep_single_photo_behaviour(): void
    {
        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Patroli Malam',
            'gallery_category_id' => $this->operasional->id,
            'status' => 'published',
            'image' => UploadedFile::fake()->image('patroli.jpg'),
            'photos' => $this->photos(3), // diabaikan untuk kategori reguler
        ])->assertSessionHasNoErrors();

        $gallery = Gallery::first();
        $this->assertStringStartsWith('gallery/', $gallery->image);
        $this->assertStringNotContainsString('dokumentasi', $gallery->image);
        $this->assertSame(0, $gallery->images()->count());

        // Kategori reguler tetap wajib foto tunggal.
        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Tanpa Foto',
            'gallery_category_id' => $this->operasional->id,
            'status' => 'published',
        ])->assertSessionHasErrors('image');

        $html = $this->actingAs($this->user)->get("/galleries/{$gallery->id}/edit")->assertOk()
            ->assertSee('name="image"', false)
            ->assertDontSee('name="delete_images[]"', false)
            ->getContent();
        $this->assertSame(['Kegiatan Pimpinan', 'Pelayanan Publik', 'Operasional', 'Sosial', 'Event'], $this->categoryOptions($html));
    }

    public function test_api_returns_one_collection_with_child_images(): void
    {
        $this->storeCollection($this->photos(5));
        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Patroli Malam',
            'gallery_category_id' => $this->operasional->id,
            'status' => 'published',
            'image' => UploadedFile::fake()->image('patroli.jpg'),
        ]);

        $response = $this->getJson('/api/galleries')->assertOk();

        $this->assertContains('Galeri Dokumentasi', array_column($response->json('categories'), 'name'));
        $this->assertCount(7, $response->json('categories'));
        $this->assertCount(2, $response->json('data'));

        $collection = collect($response->json('data'))->firstWhere('is_collection', true);
        $this->assertSame($this->documentation->id, $collection['gallery_category_id']);
        $this->assertSame('Galeri Dokumentasi', $collection['category']['name']);
        $this->assertSame(5, $collection['images_count']);
        $this->assertCount(5, $collection['images']);
        $this->assertSame($collection['images'][0]['image_url'], $collection['image_url']);
        foreach ($collection['images'] as $image) {
            $this->assertSame(['id', 'image', 'image_url', 'sort_order'], array_keys($image));
            $this->assertStringStartsWith('http', $image['image_url']);
        }

        $single = collect($response->json('data'))->firstWhere('is_collection', false);
        $this->assertSame('Operasional', $single['category']['name']);
        $this->assertSame([], $single['images']);
        $this->assertNotNull($single['image_url']);
    }
}
