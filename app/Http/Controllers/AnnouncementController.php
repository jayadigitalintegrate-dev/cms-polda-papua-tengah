<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $announcements = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],

            'image' => [
                'nullable',
                'image',
                'mimes:webp,png,jpg,jpeg',
                'max:5120',
            ],

            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],

            'priority' => [
                'required',
                'in:high,medium,low',
            ],

            'type' => [
                'required',
                'in:banner,popup,info',
            ],

            'publish_start' => ['nullable', 'date'],
            'publish_end' => ['nullable', 'date', 'after_or_equal:publish_start'],

            'featured' => ['nullable', 'boolean'],
            'status' => [
                'required',
                'in:published,draft,expired,archived',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $imagePath = null;
        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('announcements', 'public');
        }

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('announcements/attachments', 'public');

            $attachmentName = $request->file('attachment')
                ->getClientOriginalName();
        }

        Announcement::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,

            'image' => $imagePath,

            'attachment' => $attachmentPath,
            'attachment_name' => $attachmentName,

            'priority' => $validated['priority'],
            'type' => $validated['type'],

            'publish_start' => $validated['publish_start'] ?? null,
            'publish_end' => $validated['publish_end'] ?? null,

            'featured' => $request->boolean('featured'),

            'status' => $validated['status'],

            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],

            'image' => [
                'nullable',
                'image',
                'mimes:webp,png,jpg,jpeg',
                'max:5120',
            ],

            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],

            'priority' => [
                'required',
                'in:high,medium,low',
            ],

            'type' => [
                'required',
                'in:banner,popup,info',
            ],

            'publish_start' => ['nullable', 'date'],
            'publish_end' => ['nullable', 'date', 'after_or_equal:publish_start'],

            'featured' => ['nullable', 'boolean'],

            'status' => [
                'required',
                'in:published,draft,expired,archived',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            if (
                $announcement->image &&
                Storage::disk('public')->exists($announcement->image)
            ) {
                Storage::disk('public')->delete($announcement->image);
            }

            $validated['image'] = $request->file('image')
                ->store('announcements', 'public');
        }

        if ($request->hasFile('attachment')) {
            if (
                $announcement->attachment &&
                Storage::disk('public')->exists($announcement->attachment)
            ) {
                Storage::disk('public')->delete($announcement->attachment);
            }

            $validated['attachment'] = $request->file('attachment')
                ->store('announcements/attachments', 'public');

            $validated['attachment_name'] = $request
                ->file('attachment')
                ->getClientOriginalName();
        }

        $validated['featured'] = $request->boolean('featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $announcement->update($validated);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        if (
            $announcement->image &&
            Storage::disk('public')->exists($announcement->image)
        ) {
            Storage::disk('public')->delete($announcement->image);
        }

        if (
            $announcement->attachment &&
            Storage::disk('public')->exists($announcement->attachment)
        ) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
