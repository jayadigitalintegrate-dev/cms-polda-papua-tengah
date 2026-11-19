<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NewsEngagementController extends Controller
{
    /** One view per anonymous browser identifier, not per page refresh. */
    public function view(Request $request, string $slug): JsonResponse
    {
        $news = $this->publishedNews($slug);
        $visitorHash = $this->visitorHash($request, $news);

        DB::table('news_views')->insertOrIgnore([
            'news_id' => $news->id,
            'visitor_hash' => $visitorHash,
            'created_at' => now(),
        ]);

        return $this->statistics($news, $visitorHash);
    }

    /** Idempotent, explicit like/unlike; repeated requests cannot add likes. */
    public function like(Request $request, string $slug): JsonResponse
    {
        $news = $this->publishedNews($slug);
        $validated = $request->validate([
            'visitor_id' => ['required', 'string', 'uuid'],
            'liked' => ['required', 'boolean'],
        ]);
        $visitorHash = $this->hashFor($news, $validated['visitor_id']);

        if ((bool) $validated['liked']) {
            DB::table('news_likes')->insertOrIgnore([
                'news_id' => $news->id,
                'visitor_hash' => $visitorHash,
                'created_at' => now(),
            ]);
        } else {
            DB::table('news_likes')
                ->where('news_id', $news->id)
                ->where('visitor_hash', $visitorHash)
                ->delete();
        }

        return $this->statistics($news, $visitorHash);
    }

    private function publishedNews(string $slug): News
    {
        return News::query()
            ->where('status', 'published')
            ->whereNotIn('category', ['pengumuman-popup', 'pengumuman-resmi'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function visitorHash(Request $request, News $news): string
    {
        $validated = $request->validate([
            'visitor_id' => ['required', 'string', 'uuid'],
        ]);

        return $this->hashFor($news, $validated['visitor_id']);
    }

    private function hashFor(News $news, string $visitorId): string
    {
        $key = config('app.key');
        if (!is_string($key) || $key === '') {
            throw new RuntimeException('Application key is not configured');
        }

        // Domain separation prevents matching one browser across news items.
        return hash_hmac('sha256', $news->id . ':' . strtolower($visitorId), $key);
    }

    private function statistics(News $news, string $visitorHash): JsonResponse
    {
        $views = DB::table('news_views')->where('news_id', $news->id)->count();
        $likes = DB::table('news_likes')->where('news_id', $news->id)->count();
        $liked = DB::table('news_likes')
            ->where('news_id', $news->id)
            ->where('visitor_hash', $visitorHash)
            ->exists();

        return response()->json([
            'views' => $views,
            'likes' => $likes,
            'liked' => $liked,
        ])->header('Cache-Control', 'no-store');
    }
}
