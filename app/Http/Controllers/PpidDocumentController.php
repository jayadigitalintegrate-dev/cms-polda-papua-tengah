<?php

namespace App\Http\Controllers;

use App\Models\PpidCategory;
use App\Models\PpidDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PpidDocumentController extends Controller
{
    /**
     * Daftar dokumen PPID.
     */
    public function index(Request $request)
    {
        $query = PpidDocument::with('category');

        // Pencarian
        if ($request->filled('search')) {

            $keyword = trim($request->search);

            $query->where(function ($q) use ($keyword) {

                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('document_number', 'like', "%{$keyword}%");

            });

        }

        // Filter kategori
        if ($request->filled('category')) {

            $query->where(
                'ppid_category_id',
                $request->category
            );

        }

        // Filter status
        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }

        // Filter tahun
        if ($request->filled('year')) {

            $query->where(
                'publication_year',
                $request->year
            );

        }

        $documents = $query
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = PpidCategory::where(
            'is_active',
            true
        )
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return view(
            'admin.ppid_documents.index',
            compact(
                'documents',
                'categories'
            )
        );
    }

    /**
     * Form tambah dokumen.
     */
    public function create()
    {
        $categories = PpidCategory::where(
            'is_active',
            true
        )
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return view(
            'admin.ppid_documents.create',
            compact('categories')
        );
    }

    /**
     * Detail dokumen.
     */
    public function show(
        PpidDocument $ppidDocument
    ) {

        $ppidDocument->load('category');

        return view(
            'admin.ppid_documents.show',
            compact('ppidDocument')
        );

    }

    /**
     * Form edit.
     */
    public function edit(
        PpidDocument $ppidDocument
    ) {

        $categories = PpidCategory::where(
            'is_active',
            true
        )
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return view(
            'admin.ppid_documents.edit',
            compact(
                'ppidDocument',
                'categories'
            )
        );

    }

    /**
     * Simpan dokumen.
     * PART 2
     */
  public function store(Request $request)
{
    $validated = $request->validate([
        'ppid_category_id' => [
            'required',
            'exists:ppid_categories,id',
        ],

        'title' => [
            'required',
            'string',
            'max:255',
        ],

        'summary' => [
            'nullable',
            'string',
        ],

        'content' => [
            'nullable',
            'string',
        ],

        'document_number' => [
            'nullable',
            'string',
            'max:255',
        ],

        'publication_year' => [
            'nullable',
            'digits:4',
        ],

        'status' => [
            'required',
            'in:draft,published',
        ],

        'document' => [
            'required',
            'file',
            'mimes:pdf',
            'max:20480',
        ],

        'thumbnail' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:4096',
        ],
    ]);

    $documentPath = null;
    $thumbnailPath = null;
    $documentName = null;

    if ($request->hasFile('document')) {

        $file = $request->file('document');

        $documentName = $file->getClientOriginalName();

        $documentPath = $file->store(
            'ppid/documents',
            'public'
        );
    }

    if ($request->hasFile('thumbnail')) {

        $thumbnailPath = $request
            ->file('thumbnail')
            ->store(
                'ppid/thumbnails',
                'public'
            );
    }

    PpidDocument::create([

        'ppid_category_id' =>
            $validated['ppid_category_id'],

        'title' =>
            $validated['title'],

        'slug' =>
            \Illuminate\Support\Str::slug(
                $validated['title']
            ) . '-' . time(),

        'summary' =>
            $validated['summary'] ?? null,

        'content' =>
            $validated['content'] ?? null,

        'document_number' =>
            $validated['document_number'] ?? null,

        'document' =>
            $documentPath,

        'document_name' =>
            $documentName,

        'thumbnail' =>
            $thumbnailPath,

        'publication_year' =>
            $validated['publication_year'] ?? null,

        'sort_order' => 0,

        'status' =>
            $validated['status'],

        'published_at' =>
            $validated['status'] === 'published'
                ? now()
                : null,

        'download_count' => 0,

        'view_count' => 0,

        'created_by' => auth()->id(),
    ]);

    return redirect()
        ->route('ppid-documents.index')
        ->with(
            'success',
            'Dokumen PPID berhasil ditambahkan.'
        );
}

    /**
     * Update dokumen.
     * PART 3
     */
   public function update(
    Request $request,
    PpidDocument $ppidDocument
) {
    $validated = $request->validate([

        'ppid_category_id' => [
            'required',
            'exists:ppid_categories,id',
        ],

        'title' => [
            'required',
            'string',
            'max:255',
        ],

        'summary' => [
            'nullable',
            'string',
        ],

        'content' => [
            'nullable',
            'string',
        ],

        'document_number' => [
            'nullable',
            'string',
            'max:255',
        ],

        'publication_year' => [
            'nullable',
            'digits:4',
        ],

        'status' => [
            'required',
            'in:draft,published',
        ],

        'document' => [
            'nullable',
            'file',
            'mimes:pdf',
            'max:20480',
        ],

        'thumbnail' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:4096',
        ],

    ]);

    $data = [

        'ppid_category_id' => $validated['ppid_category_id'],

        'title' => $validated['title'],

        'summary' => $validated['summary'] ?? null,

        'content' => $validated['content'] ?? null,

        'document_number' => $validated['document_number'] ?? null,

        'publication_year' => $validated['publication_year'] ?? null,

        'status' => $validated['status'],

        'published_at' => $validated['status'] === 'published'
            ? ($ppidDocument->published_at ?? now())
            : null,

        'updated_by' => auth()->id(),

    ];

    if ($ppidDocument->title !== $validated['title']) {

        $data['slug'] = Str::slug($validated['title']) . '-' . time();

    }

    if ($request->hasFile('document')) {

        if (
            $ppidDocument->document &&
            Storage::disk('public')->exists($ppidDocument->document)
        ) {

            Storage::disk('public')->delete(
                $ppidDocument->document
            );

        }

        $file = $request->file('document');

        $data['document_name'] =
            $file->getClientOriginalName();

        $data['document'] = $file->store(
            'ppid/documents',
            'public'
        );

    }

    if ($request->hasFile('thumbnail')) {

        if (
            $ppidDocument->thumbnail &&
            Storage::disk('public')->exists($ppidDocument->thumbnail)
        ) {

            Storage::disk('public')->delete(
                $ppidDocument->thumbnail
            );

        }

        $data['thumbnail'] = $request
            ->file('thumbnail')
            ->store(
                'ppid/thumbnails',
                'public'
            );

    }

    $ppidDocument->update($data);

    return redirect()
        ->route(
            'ppid-documents.show',
            $ppidDocument
        )
        ->with(
            'success',
            'Dokumen PPID berhasil diperbarui.'
        );
}

public function bulkDelete(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {

        return back()->with(
            'error',
            'Tidak ada dokumen yang dipilih.'
        );

    }

    $documents = PpidDocument::whereIn('id', $ids)->get();

    foreach ($documents as $document) {

        if (
            $document->document &&
            Storage::disk('public')->exists($document->document)
        ) {

            Storage::disk('public')->delete(
                $document->document
            );

        }

        if (
            $document->thumbnail &&
            Storage::disk('public')->exists($document->thumbnail)
        ) {

            Storage::disk('public')->delete(
                $document->thumbnail
            );

        }

        $document->delete();

    }

    return back()->with(
        'success',
        count($ids).' dokumen berhasil dihapus.'
    );
}

public function bulkPublish(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {

        return back()->with(
            'error',
            'Tidak ada dokumen dipilih.'
        );

    }

    PpidDocument::whereIn('id', $ids)->update([

        'status' => 'published',

        'published_at' => now(),

        'updated_by' => auth()->id(),

    ]);

    return back()->with(
        'success',
        count($ids).' dokumen berhasil dipublish.'
    );
}

public function bulkDraft(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {

        return back()->with(
            'error',
            'Tidak ada dokumen dipilih.'
        );

    }

    PpidDocument::whereIn('id', $ids)->update([

        'status' => 'draft',

        'published_at' => null,

        'updated_by' => auth()->id(),

    ]);

    return back()->with(
        'success',
        count($ids).' dokumen berhasil dijadikan draft.'
    );
}






  public function destroy(
    PpidDocument $ppidDocument
) {

    if (
        $ppidDocument->document &&
        Storage::disk('public')->exists($ppidDocument->document)
    ) {

        Storage::disk('public')->delete(
            $ppidDocument->document
        );

    }

    if (
        $ppidDocument->thumbnail &&
        Storage::disk('public')->exists($ppidDocument->thumbnail)
    ) {

        Storage::disk('public')->delete(
            $ppidDocument->thumbnail
        );

    }

    $ppidDocument->delete();

    return redirect()
        ->route('ppid-documents.index')
        ->with(
            'success',
            'Dokumen PPID berhasil dihapus.'
        );

}

}
