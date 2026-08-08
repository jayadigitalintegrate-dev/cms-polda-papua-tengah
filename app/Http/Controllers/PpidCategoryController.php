<?php

namespace App\Http\Controllers;

use App\Models\PpidCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PpidCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PpidCategory::query();

        if ($request->filled('search')) {
            $keyword = trim($request->search);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $categories = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.ppid_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.ppid_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PpidCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('ppid-categories.index')
            ->with('success', 'Kategori PPID berhasil ditambahkan.');
    }

    public function show(PpidCategory $ppidCategory)
    {
        $ppidCategory->load('documents');

        return view(
            'admin.ppid_categories.show',
            compact('ppidCategory')
        );
    }

    public function edit(PpidCategory $ppidCategory)
    {
        return view(
            'admin.ppid_categories.edit',
            compact('ppidCategory')
        );
    }

    public function update(
        Request $request,
        PpidCategory $ppidCategory
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => auth()->id(),
        ];

        if ($ppidCategory->name !== $validated['name']) {
            $data['slug'] = Str::slug($validated['name']);
        }

        $ppidCategory->update($data);

        return redirect()
            ->route('ppid-categories.index')
            ->with('success', 'Kategori PPID berhasil diperbarui.');
    }

    public function destroy(PpidCategory $ppidCategory)
    {
        if ($ppidCategory->documents()->exists()) {
            return redirect()
                ->route('ppid-categories.index')
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh dokumen PPID.'
                );
        }

        $ppidCategory->delete();

        return redirect()
            ->route('ppid-categories.index')
            ->with('success', 'Kategori PPID berhasil dihapus.');
    }
}
