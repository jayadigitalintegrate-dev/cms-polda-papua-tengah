<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadValidationTest extends SecurityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_php_script_upload_is_rejected(): void
    {
        $this->uploadHero(UploadedFile::fake()->createWithContent('shell.php', '<?php echo "x"; ?>'))
            ->assertSessionHasErrors('images.0');

        $this->assertNothingStored();
    }

    public function test_php_script_disguised_as_image_is_rejected(): void
    {
        /*
         * Pakai file nyata, bukan UploadedFile::fake(): file fake melaporkan
         * MIME dari NAMA file, sehingga tidak menguji deteksi isi file.
         * Nama .jpg dan MIME klien "image/jpeg" sengaja dipalsukan.
         */
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, "<?php echo 'x'; ?>\n");

        try {
            $file = new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true);

            $this->uploadHero($file)->assertSessionHasErrors('images.0');
        } finally {
            @unlink($path);
        }

        $this->assertNothingStored();
    }

    public function test_svg_upload_is_rejected(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><rect width="1" height="1"/></svg>';

        $this->uploadHero(UploadedFile::fake()->createWithContent('logo.svg', $svg))
            ->assertSessionHasErrors('images.0');

        $this->assertNothingStored();
    }

    public function test_oversized_image_is_rejected(): void
    {
        $this->uploadHero(UploadedFile::fake()->image('big.jpg')->size(6000))
            ->assertSessionHasErrors('images.0');

        $this->assertNothingStored();
    }

    public function test_profile_photo_rejects_non_image(): void
    {
        $this->actingAs($this->user())
            ->post(route('settings.profile-photo.update'), [
                'profile_photo' => UploadedFile::fake()->createWithContent('avatar.phtml', '<?php echo 1; ?>'),
            ])
            ->assertSessionHasErrors('profile_photo');

        $this->assertNothingStored();
    }

    public function test_valid_image_is_stored_with_server_generated_name(): void
    {
        $this->uploadHero(UploadedFile::fake()->image('../../evil name.png', 20, 20))
            ->assertSessionHasNoErrors();

        $files = Storage::disk('public')->allFiles();

        $this->assertCount(1, $files);
        $this->assertStringStartsWith('heroes/', $files[0]);
        $this->assertStringNotContainsString('evil', $files[0]);
        $this->assertMatchesRegularExpression('#^heroes/[A-Za-z0-9]{40}\.png$#', $files[0]);
    }

    private function uploadHero(UploadedFile $file)
    {
        return $this->actingAs($this->user())->post(route('heroes.store'), [
            'mode' => 'add',
            'status' => 'active',
            'images' => [$file],
        ]);
    }

    private function user(): User
    {
        return User::factory()->create();
    }

    private function assertNothingStored(): void
    {
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
