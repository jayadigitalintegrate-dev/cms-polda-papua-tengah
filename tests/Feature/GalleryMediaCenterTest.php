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

class GalleryMediaCenterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private GalleryCategory $mediaCenter;

    private GalleryCategory $documentation;

    private GalleryCategory $operasional;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(GalleryCategorySeeder::class);

        $this->user = User::factory()->create();
        $this->mediaCenter = GalleryCategory::where('slug', GalleryCategory::MEDIA_CENTER_SLUG)->firstOrFail();
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
            fn (int $i) => UploadedFile::fake()->image("mc-{$i}." . $types[$i % 3], 640, 480),
            range(0, $count - 1)
        );
    }

    private function storeMedia(array $photos, array $extra = [])
    {
        return $this->actingAs($this->user)->post('/galleries', $extra + [
            'title' => 'Kapolda Resmikan Posko Terpadu',
            'gallery_category_id' => $this->mediaCenter->id,
            'description' => 'Ringkasan kegiatan peresmian posko.',
            'content' => "Paragraf pertama berita.\n\nParagraf kedua berita.",
            'taken_at' => '2026-10-07',
            'status' => 'published',
            'photos' => $photos,
        ]);
    }

    private function apiItems(): array
    {
        return $this->getJson('/api/galleries')->assertOk()->json('data');
    }

    public function test_media_center_is_seventh_active_category_and_form_has_editorial_fields(): void
    {
        $this->assertSame(
            ['Kegiatan Pimpinan', 'Pelayanan Publik', 'Operasional', 'Sosial', 'Event', 'Galeri Dokumentasi', 'Media Center'],
            GalleryCategory::where('is_active', true)->orderBy('sort_order')->pluck('name')->all()
        );
        $this->assertSame(GalleryCategory::KIND_MEDIA_CENTER, $this->mediaCenter->kind());

        $this->actingAs($this->user)->get('/galleries/create')->assertOk()
            ->assertSee('Media Center')
            ->assertSee('name="content"', false)
            ->assertSee('name="photos[]"', false)
            ->assertSee('Batal');
    }

    public function test_store_creates_one_record_with_content_and_child_images(): void
    {
        $this->storeMedia($this->photos(5))->assertSessionHasNoErrors()->assertRedirect(route('galleries.index'));

        $this->assertSame(1, Gallery::count());
        $item = Gallery::with('images')->firstOrFail();
        $this->assertSame($this->mediaCenter->id, $item->gallery_category_id);
        $this->assertSame("Paragraf pertama berita.\n\nParagraf kedua berita.", $item->content);
        $this->assertSame('Ringkasan kegiatan peresmian posko.', $item->description);
        $this->assertCount(5, $item->images);
        $this->assertSame($item->images->first()->image, $item->image, 'cover = foto pertama');
        foreach ($item->images as $image) {
            $this->assertStringStartsWith('gallery/media-center/', $image->image);
            Storage::disk('public')->assertExists($image->image);
        }
    }

    public function test_content_is_required_and_photos_limited_to_five(): void
    {
        $this->storeMedia($this->photos(1), ['content' => ''])->assertSessionHasErrors('content');
        // Isi yang hanya berisi tag menjadi kosong setelah dibersihkan → ditolak.
        $this->storeMedia($this->photos(1), ['content' => '<b></b> <script></script>'])
            ->assertSessionHasErrors(['content' => 'Isi berita tidak boleh kosong (tag HTML tidak disimpan).']);
        $this->storeMedia([])->assertSessionHasErrors('photos');
        $this->storeMedia($this->photos(6))->assertSessionHasErrors('photos');

        $this->assertSame(0, Gallery::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function formatProvider(): array
    {
        return ['JPG' => ['jpg', 'image/jpeg'], 'PNG' => ['png', 'image/png'], 'WEBP' => ['webp', 'image/webp']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function test_each_allowed_format_is_accepted(string $ext, string $mime): void
    {
        $this->storeMedia([UploadedFile::fake()->image("foto.{$ext}", 800, 600)])->assertSessionHasNoErrors();

        $item = Gallery::with('images')->firstOrFail();
        $this->assertSame($mime, Storage::disk('public')->mimeType($item->images[0]->image));
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        $cases = [
            'pdf' => UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf'),
            'svg' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'gif' => UploadedFile::fake()->image('animasi.gif'),
            'php disguised as jpg' => UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo 1; ?>'),
        ];

        foreach ($cases as $label => $file) {
            $this->storeMedia([$this->photos(1)[0], $file])->assertSessionHasErrors('photos.1', "{$label} seharusnya ditolak");
        }

        $this->assertSame(0, Gallery::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_html_is_stripped_from_content_and_escaped_in_admin(): void
    {
        $this->storeMedia($this->photos(1), [
            'description' => 'Ringkas <b>tebal</b><script>alert("d")</script>',
            'content' => "Halo <script>alert('x')</script><img src=x onerror=alert(1)>dunia\n<a href=\"javascript:alert(1)\">tautan</a>",
        ])->assertSessionHasNoErrors();

        $item = Gallery::firstOrFail();
        $this->assertStringNotContainsString('<', $item->content);
        $this->assertStringNotContainsString('<', $item->description);
        $this->assertSame("Halo alert('x')dunia\ntautan", $item->content);

        // Data lama yang (misalnya) berisi HTML tetap di-escape saat ditampilkan di form edit.
        $item->forceFill(['content' => '<script>alert(9)</script>'])->save();
        $this->actingAs($this->user)->get("/galleries/{$item->id}/edit")->assertOk()
            ->assertDontSee('<script>alert(9)</script>', false)
            ->assertSee('&lt;script&gt;alert(9)&lt;/script&gt;', false);
    }

    public function test_draft_is_hidden_from_public_api_and_published_is_shown(): void
    {
        $this->storeMedia($this->photos(1), ['status' => 'draft', 'title' => 'Draft MC']);
        $this->assertSame([], $this->apiItems());

        $this->storeMedia($this->photos(5), ['title' => 'Published MC']);
        $items = $this->apiItems();
        $this->assertCount(1, $items);

        $mc = $items[0];
        $this->assertSame('Published MC', $mc['title']);
        $this->assertSame('media_center', $mc['type']);
        $this->assertTrue($mc['is_collection']);
        $this->assertSame($this->mediaCenter->id, $mc['category_id']);
        $this->assertSame($this->mediaCenter->id, $mc['gallery_category_id']);
        $this->assertSame('Media Center', $mc['category']['name']);
        $this->assertSame("Paragraf pertama berita.\n\nParagraf kedua berita.", $mc['content']);
        $this->assertSame('Ringkasan kegiatan peresmian posko.', $mc['description']);
        $this->assertSame('2026-10-07', $mc['date']);
        $this->assertSame('published', $mc['status']);
        $this->assertCount(5, $mc['images']);
        $this->assertSame($mc['images'][0]['image_url'], $mc['image_url']);
        foreach ($mc['images'] as $image) {
            $this->assertArrayHasKey('id', $image);
            $this->assertArrayHasKey('sort_order', $image);
            $this->assertStringStartsWith('http', $image['image_url']);
        }
    }

    public function test_edit_title_content_and_delete_one_photo_keeps_others(): void
    {
        $this->storeMedia($this->photos(3));
        $item = Gallery::with('images')->firstOrFail();
        [$first, $second, $third] = $item->images->all();

        $this->actingAs($this->user)->get("/galleries/{$item->id}/edit")->assertOk()
            ->assertSee('name="content"', false)
            ->assertSee('Paragraf pertama berita.')
            ->assertSee('name="delete_images[]"', false);

        $this->actingAs($this->user)->put("/galleries/{$item->id}", [
            'title' => 'Judul Baru',
            'gallery_category_id' => $this->mediaCenter->id,
            'description' => 'Deskripsi baru',
            'content' => 'Isi berita yang sudah diperbarui.',
            'status' => 'published',
            'delete_images' => [$second->id],
        ])->assertSessionHasNoErrors();

        $item->refresh()->load('images');
        $this->assertSame('Judul Baru', $item->title);
        $this->assertSame('Isi berita yang sudah diperbarui.', $item->content);
        $this->assertSame([$first->id, $third->id], $item->images->pluck('id')->all());
        Storage::disk('public')->assertMissing($second->image);
        Storage::disk('public')->assertExists($first->image);
        Storage::disk('public')->assertExists($third->image);

        $api = $this->apiItems()[0];
        $this->assertSame('Judul Baru', $api['title']);
        $this->assertSame('Isi berita yang sudah diperbarui.', $api['content']);
        $this->assertCount(2, $api['images']);

        // Tambah sampai batas: 2 + 3 = 5 diterima, lalu foto ke-6 ditolak.
        $payload = ['title' => 'Judul Baru', 'gallery_category_id' => $this->mediaCenter->id, 'content' => 'Isi', 'status' => 'published'];
        $this->actingAs($this->user)->put("/galleries/{$item->id}", $payload + ['photos' => $this->photos(3)])->assertSessionHasNoErrors();
        $this->assertCount(5, $item->fresh()->images);
        $this->actingAs($this->user)->put("/galleries/{$item->id}", $payload + ['photos' => $this->photos(1)])->assertSessionHasErrors('photos');
        $this->assertCount(5, $item->fresh()->images);
    }

    public function test_failed_upload_on_update_rolls_back_and_leaves_no_files(): void
    {
        $this->storeMedia($this->photos(2));
        $item = Gallery::with('images')->firstOrFail();
        $ids = $item->images->pluck('id')->all();
        $files = Storage::disk('public')->allFiles();

        $writes = 0;
        $real = Storage::disk('public');
        $disk = \Mockery::mock($real)->makePartial();
        $disk->shouldReceive('putFileAs')->andReturnUsing(function (...$args) use (&$writes, $real) {
            return ++$writes === 2 ? false : $real->putFileAs(...$args);
        });
        Storage::set('public', $disk);

        $this->actingAs($this->user)->put("/galleries/{$item->id}", [
            'title' => 'Tidak boleh tersimpan',
            'gallery_category_id' => $this->mediaCenter->id,
            'content' => 'Tidak boleh tersimpan',
            'status' => 'published',
            'delete_images' => [$ids[0]],
            'photos' => $this->photos(2),
        ])->assertSessionHasErrors('photos');

        $item->refresh()->load('images');
        $this->assertSame('Kapolda Resmikan Posko Terpadu', $item->title);
        $this->assertSame("Paragraf pertama berita.\n\nParagraf kedua berita.", $item->content);
        $this->assertSame($ids, $item->images->pluck('id')->all());
        $this->assertEqualsCanonicalizing($files, Storage::disk('public')->allFiles());
    }

    public function test_delete_cleans_child_images_and_files_only_of_that_item(): void
    {
        $this->storeMedia($this->photos(3));
        $this->storeMedia($this->photos(2), ['title' => 'Konten Lain']);
        [$target, $other] = Gallery::with('images')->orderBy('id')->get()->all();
        $targetPaths = $target->images->pluck('image')->all();

        $this->actingAs($this->user)->delete("/galleries/{$target->id}")->assertRedirect(route('galleries.index'));

        $this->assertNull(Gallery::find($target->id));
        $this->assertSame(0, GalleryImage::where('gallery_id', $target->id)->count());
        foreach ($targetPaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
        foreach ($other->images as $image) {
            Storage::disk('public')->assertExists($image->image);
        }
    }

    public function test_kind_cannot_switch_between_media_center_documentation_and_single(): void
    {
        $this->storeMedia($this->photos(1));
        $item = Gallery::firstOrFail();

        foreach ([$this->documentation, $this->operasional] as $target) {
            $this->actingAs($this->user)->put("/galleries/{$item->id}", [
                'title' => $item->title, 'gallery_category_id' => $target->id,
                'content' => 'x', 'status' => 'published',
            ])->assertSessionHasErrors('gallery_category_id');
        }
        $this->assertSame($this->mediaCenter->id, $item->fresh()->gallery_category_id);

        // Dropdown edit hanya berisi kategori berjenis sama.
        $html = $this->actingAs($this->user)->get("/galleries/{$item->id}/edit")->assertOk()->getContent();
        $this->assertSame(['Media Center'], $this->categoryOptions($html));
    }

    public function test_other_kinds_cannot_be_switched_into_media_center(): void
    {
        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Patroli', 'gallery_category_id' => $this->operasional->id, 'status' => 'published',
            'image' => UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Album', 'gallery_category_id' => $this->documentation->id, 'status' => 'published',
            'photos' => $this->photos(2),
        ])->assertSessionHasNoErrors();

        foreach (Gallery::orderBy('id')->get() as $item) {
            $before = $item->gallery_category_id;

            $this->actingAs($this->user)->put("/galleries/{$item->id}", [
                'title' => $item->title, 'gallery_category_id' => $this->mediaCenter->id,
                'content' => 'Isi berita', 'status' => 'published',
                'photos' => $this->photos(1),
            ])->assertSessionHasErrors('gallery_category_id');

            $this->assertSame($before, $item->fresh()->gallery_category_id, "{$item->title} tetap di kategori asal");
            $this->assertNull($item->fresh()->content);

            // Media Center tidak ditawarkan di dropdown edit jenis lain.
            $options = $this->categoryOptions($this->actingAs($this->user)->get("/galleries/{$item->id}/edit")->assertOk()->getContent());
            $this->assertNotEmpty($options);
            $this->assertNotContains('Media Center', $options);
        }

        $this->assertSame(2, GalleryImage::count(), 'foto Album tidak bertambah');
    }

    public function test_documentation_and_single_items_never_store_content(): void
    {
        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Album', 'gallery_category_id' => $this->documentation->id, 'status' => 'published',
            'content' => 'harus diabaikan', 'description' => 'Deskripsi <b>album</b>',
            'photos' => $this->photos(2),
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->user)->post('/galleries', [
            'title' => 'Patroli', 'gallery_category_id' => $this->operasional->id, 'status' => 'published',
            'content' => 'harus diabaikan', 'image' => UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHasNoErrors();

        $this->assertSame([null, null], Gallery::orderBy('id')->pluck('content')->all());
        // Perilaku lama Galeri Dokumentasi tidak berubah: deskripsi disimpan apa adanya.
        $this->assertSame('Deskripsi <b>album</b>', Gallery::where('title', 'Album')->value('description'));

        $types = collect($this->apiItems())->pluck('type', 'title');
        $this->assertSame('documentation', $types['Album']);
        $this->assertSame('single', $types['Patroli']);
        $this->assertNull(collect($this->apiItems())->firstWhere('title', 'Album')['content']);
    }
}
