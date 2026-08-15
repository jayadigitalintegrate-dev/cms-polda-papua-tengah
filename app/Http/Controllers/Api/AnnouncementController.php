<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        $now = now();

        $announcements = Announcement::query()
            ->where('status', 'published')
            ->where('type', 'popup')
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('publish_start')
                    ->orWhere('publish_start', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('publish_end')
                    ->orWhere('publish_end', '>=', $now);
            })
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'title',
                'slug',
                'description',
                'content',
                'image',
                'attachment',
                'attachment_name',
                'priority',
                'type',
                'publish_start',
                'publish_end',
                'featured',
                'status',
                'sort_order',
                'created_at',
                'updated_at',
            ])
            ->map(function (Announcement $item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'content' => $item->content,

                    'image' => $item->image,
                    'image_url' => $item->image
                        ? asset('storage/' . $item->image)
                        : null,

                    'attachment' => $item->attachment,
                    'attachment_name' => $item->attachment_name,
                    'attachment_url' => $item->attachment
                        ? asset('storage/' . $item->attachment)
                        : null,

                    'priority' => $item->priority,
                    'type' => $item->type,

                    'publish_start' => $item->publish_start,
                    'publish_end' => $item->publish_end,

                    'featured' => (bool) $item->featured,
                    'status' => $item->status,
                    'sort_order' => (int) $item->sort_order,

                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            })
            ->values();

        return response()->json($announcements);
    }
}