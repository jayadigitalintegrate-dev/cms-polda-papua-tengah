<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Executive Content Dashboard.
 * Seluruh query bersifat read-only terhadap tabel news dan announcements.
 */
class DashboardController extends Controller
{
    /**
     * Allowlist kategori editorial yang dihitung sebagai "Berita Artikel".
     * Kategori khusus (video, pengumuman, pengumuman-popup, ppid, dll.)
     * serta kategori baru tidak otomatis ikut dihitung.
     */
    private const EDITORIAL_CATEGORIES = [
        'berita-utama',
        'berita',
        'press-release',
        'himbauan',
        'kegiatan',
        'prestasi',
        'lalu-lintas',
        'kriminal',
    ];

    private const VIDEO_CATEGORY = 'video';

    private const TREND_DAYS = 14;

    private const RECENT_LIMIT = 8;

    public function __invoke(): View
    {
        $now = now();
        $today = $now->copy()->startOfDay();

        $newsCounts = $this->newsCounts($today, $now);
        $announcementCounts = $this->announcementCounts($today, $now);

        $kpi = [
            'news_live' => (int) $newsCounts->article_live,
            'video_live' => (int) $newsCounts->video_live,
            'popup_live' => (int) $announcementCounts->popup_live,
            'announcement_live' => (int) $announcementCounts->live,
        ];

        $status = [
            'draft_news' => (int) $newsCounts->draft,
            'draft_announcement' => (int) $announcementCounts->draft,
            'today_news' => (int) $newsCounts->today,
            'today_announcement' => (int) $announcementCounts->today,
            'total_article' => (int) $newsCounts->article_total,
            'total_video' => (int) $newsCounts->video_total,
            'total_announcement' => (int) $announcementCounts->total,
        ];

        $status['draft'] = $status['draft_news'] + $status['draft_announcement'];
        $status['today'] = $status['today_news'] + $status['today_announcement'];
        $status['total'] = $status['total_article']
            + $status['total_video']
            + $status['total_announcement'];

        return view('dashboard', [
            'kpi' => $kpi,
            'status' => $status,
            'trend' => $this->publicationTrend($today, $now),
            'recentActivities' => $this->recentActivities($now),
            'generatedAt' => $now,
        ]);
    }

    /**
     * Agregasi berita dalam satu query.
     */
    private function newsCounts(Carbon $today, Carbon $now): object
    {
        [$articleSql, $articleBindings] = $this->articleCondition();

        return News::query()
            ->toBase()
            ->selectRaw(
                "SUM(CASE WHEN status = 'published' AND {$articleSql} THEN 1 ELSE 0 END) AS article_live",
                $articleBindings
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'published' AND category = ? THEN 1 ELSE 0 END) AS video_live",
                [self::VIDEO_CATEGORY]
            )
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft")
            ->selectRaw(
                "SUM(CASE WHEN status = 'published' AND ({$articleSql} OR category = ?)"
                . ' AND published_at BETWEEN ? AND ? THEN 1 ELSE 0 END) AS today',
                [...$articleBindings, self::VIDEO_CATEGORY, $today, $now]
            )
            ->selectRaw(
                "SUM(CASE WHEN {$articleSql} THEN 1 ELSE 0 END) AS article_total",
                $articleBindings
            )
            ->selectRaw(
                'SUM(CASE WHEN category = ? THEN 1 ELSE 0 END) AS video_total',
                [self::VIDEO_CATEGORY]
            )
            ->first();
    }

    /**
     * Agregasi pengumuman dalam satu query.
     * "Tayang" = published dan berada di dalam periode publish_start / publish_end.
     * "Terbit hari ini" hanya memakai publish_start (tanpa fallback created_at).
     */
    private function announcementCounts(Carbon $today, Carbon $now): object
    {
        $activeSql = "status = 'published'"
            . ' AND (publish_start IS NULL OR publish_start <= ?)'
            . ' AND (publish_end IS NULL OR publish_end >= ?)';

        return Announcement::query()
            ->toBase()
            ->selectRaw("SUM(CASE WHEN {$activeSql} THEN 1 ELSE 0 END) AS live", [$now, $now])
            ->selectRaw(
                "SUM(CASE WHEN {$activeSql} AND type = 'popup' THEN 1 ELSE 0 END) AS popup_live",
                [$now, $now]
            )
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft")
            ->selectRaw(
                "SUM(CASE WHEN status = 'published' AND publish_start BETWEEN ? AND ? THEN 1 ELSE 0 END) AS today",
                [$today, $now]
            )
            ->selectRaw('COUNT(*) AS total')
            ->first();
    }

    /**
     * Tren publikasi harian untuk N hari terakhir (termasuk hari ini).
     * News memakai published_at, pengumuman memakai publish_start.
     *
     * @return array{days: Collection, max: int, totals: array<string, int>, start: Carbon}
     */
    private function publicationTrend(Carbon $today, Carbon $now): array
    {
        $start = $today->copy()->subDays(self::TREND_DAYS - 1);

        [$articleSql, $articleBindings] = $this->articleCondition();

        $newsRows = News::query()
            ->toBase()
            ->where('status', 'published')
            ->whereIn('category', [...self::EDITORIAL_CATEGORIES, self::VIDEO_CATEGORY])
            ->whereBetween('published_at', [$start, $now])
            ->selectRaw('DATE(published_at) AS day')
            ->selectRaw("SUM(CASE WHEN {$articleSql} THEN 1 ELSE 0 END) AS article", $articleBindings)
            ->selectRaw(
                'SUM(CASE WHEN category = ? THEN 1 ELSE 0 END) AS video',
                [self::VIDEO_CATEGORY]
            )
            ->groupBy(DB::raw('DATE(published_at)'))
            ->get()
            ->keyBy('day');

        $announcementRows = Announcement::query()
            ->toBase()
            ->where('status', 'published')
            ->whereBetween('publish_start', [$start, $now])
            ->selectRaw('DATE(publish_start) AS day')
            ->selectRaw('COUNT(*) AS announcement')
            ->groupBy(DB::raw('DATE(publish_start)'))
            ->get()
            ->keyBy('day');

        $days = collect(range(0, self::TREND_DAYS - 1))
            ->map(function (int $offset) use ($start, $newsRows, $announcementRows) {
                $date = $start->copy()->addDays($offset);
                $key = $date->toDateString();

                $article = (int) ($newsRows[$key]->article ?? 0);
                $video = (int) ($newsRows[$key]->video ?? 0);
                $announcement = (int) ($announcementRows[$key]->announcement ?? 0);

                return [
                    'date' => $date,
                    'article' => $article,
                    'video' => $video,
                    'announcement' => $announcement,
                    'total' => $article + $video + $announcement,
                ];
            });

        return [
            'days' => $days,
            'max' => max(1, (int) $days->max('total')),
            'totals' => [
                'article' => $days->sum('article'),
                'video' => $days->sum('video'),
                'announcement' => $days->sum('announcement'),
            ],
            'start' => $start,
        ];
    }

    /**
     * Konten terbaru yang dipublish: berita editorial, video, dan pengumuman
     * yang memiliki publish_start.
     */
    private function recentActivities(Carbon $now): Collection
    {
        $news = News::query()
            ->with('newsCategory:slug,name')
            ->where('status', 'published')
            ->whereIn('category', [...self::EDITORIAL_CATEGORIES, self::VIDEO_CATEGORY])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->latest('published_at')
            ->limit(self::RECENT_LIMIT)
            ->get(['id', 'title', 'category', 'status', 'published_at'])
            ->map(fn (News $item) => [
                'title' => $item->title,
                'kind' => $item->category === self::VIDEO_CATEGORY ? 'video' : 'article',
                'kind_label' => $item->category === self::VIDEO_CATEGORY ? 'Video Berita' : 'Berita',
                'detail' => $item->newsCategory?->name ?? $item->category,
                'date' => $item->published_at,
                'status' => 'live',
                'status_label' => 'Tayang',
                'url' => route('news.edit', $item->id),
            ]);

        $announcements = Announcement::query()
            ->where('status', 'published')
            ->whereNotNull('publish_start')
            ->where('publish_start', '<=', $now)
            ->latest('publish_start')
            ->limit(self::RECENT_LIMIT)
            ->get(['id', 'title', 'type', 'status', 'publish_start', 'publish_end'])
            ->map(function (Announcement $item) use ($now) {
                [$status, $statusLabel] = $item->publish_end && $item->publish_end->lt($now)
                    ? ['ended', 'Berakhir']
                    : ['live', 'Tayang'];

                return [
                    'title' => $item->title,
                    'kind' => 'announcement',
                    'kind_label' => 'Pengumuman',
                    'detail' => match ($item->type) {
                        'popup' => 'Popup',
                        'banner' => 'Banner',
                        'info' => 'Informasi',
                        default => ucfirst((string) $item->type),
                    },
                    'date' => $item->publish_start,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'url' => route('announcements.edit', $item->id),
                ];
            });

        return $news
            ->concat($announcements)
            ->sortByDesc(fn (array $item) => $item['date']->getTimestamp())
            ->take(self::RECENT_LIMIT)
            ->values();
    }

    /**
     * Kondisi SQL "kategori editorial" beserta binding-nya.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    private function articleCondition(): array
    {
        $placeholders = implode(', ', array_fill(0, count(self::EDITORIAL_CATEGORIES), '?'));

        return ["category IN ({$placeholders})", self::EDITORIAL_CATEGORIES];
    }
}
