<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsEngagementTest extends TestCase
{
    use RefreshDatabase;

    private const VISITOR_A = '550e8400-e29b-41d4-a716-446655440000';
    private const VISITOR_B = '550e8400-e29b-41d4-a716-446655440001';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
    }

    private function news(string $slug = 'uji-engagement', string $status = 'published', string $category = 'berita'): News
    {
        return News::create([
            'title' => 'Uji Engagement',
            'slug' => $slug,
            'content' => 'Konten pengujian',
            'status' => $status,
            'category' => $category,
        ]);
    }

    public function test_view_count_is_unique_per_browser(): void
    {
        $this->news();
        $url = '/api/news/uji-engagement/view';

        $this->postJson($url, ['visitor_id' => self::VISITOR_A])
            ->assertOk()->assertJsonPath('views', 1);
        $this->postJson($url, ['visitor_id' => self::VISITOR_A])
            ->assertOk()->assertJsonPath('views', 1);
        $this->postJson($url, ['visitor_id' => self::VISITOR_B])
            ->assertOk()->assertJsonPath('views', 2);

        $this->assertDatabaseCount('news_views', 2);
    }

    public function test_like_and_unlike_are_idempotent(): void
    {
        $this->news();
        $url = '/api/news/uji-engagement/like';
        $like = ['visitor_id' => self::VISITOR_A, 'liked' => true];

        $this->postJson($url, $like)
            ->assertOk()->assertJsonPath('likes', 1)->assertJsonPath('liked', true);
        $this->postJson($url, $like)
            ->assertOk()->assertJsonPath('likes', 1);
        $this->postJson($url, ['visitor_id' => self::VISITOR_A, 'liked' => false])
            ->assertOk()->assertJsonPath('likes', 0)->assertJsonPath('liked', false);

        $this->assertDatabaseCount('news_likes', 0);
    }

    public function test_unpublished_and_announcements_cannot_be_counted(): void
    {
        $this->news('draft-uji', 'draft');
        $this->news('popup-uji', 'published', 'pengumuman-popup');

        foreach (['draft-uji', 'popup-uji'] as $slug) {
            $this->postJson("/api/news/{$slug}/view", ['visitor_id' => self::VISITOR_A])
                ->assertNotFound();
            $this->postJson("/api/news/{$slug}/like", ['visitor_id' => self::VISITOR_A, 'liked' => true])
                ->assertNotFound();
        }
        $this->assertDatabaseCount('news_views', 0);
        $this->assertDatabaseCount('news_likes', 0);
    }

    public function test_invalid_visitor_identifier_is_rejected(): void
    {
        $this->news();
        $this->postJson('/api/news/uji-engagement/view', ['visitor_id' => 'x'])
            ->assertUnprocessable();
        $this->postJson('/api/news/uji-engagement/like', ['visitor_id' => 'x', 'liked' => true])
            ->assertUnprocessable();
    }

    public function test_news_list_retains_existing_fields_and_adds_statistics(): void
    {
        $this->news();
        $this->postJson('/api/news/uji-engagement/view', ['visitor_id' => self::VISITOR_A]);
        $this->postJson('/api/news/uji-engagement/like', ['visitor_id' => self::VISITOR_A, 'liked' => true]);

        $this->getJson('/api/news')->assertOk()->assertJsonFragment([
            'slug' => 'uji-engagement',
            'views' => 1,
            'likes' => 1,
        ]);
    }
}
