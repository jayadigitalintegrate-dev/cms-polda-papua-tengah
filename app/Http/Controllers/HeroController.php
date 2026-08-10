<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HeroController extends Controller
{
    /**
     * Daftar Hero.
     */
    public function index()
    {
        $heroes = Hero::orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.heroes.index', compact('heroes'));
    }

    /**
     * Form upload Hero.
     */
    public function create()
    {
        return view('admin.heroes.create');
    }

    /**
     * Upload Hero baru.
     *
     * mode:
     * - add         = menambahkan Hero ke Hero yang sudah ada
     * - replace_all = mengganti seluruh Hero lama
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mode' => [
                'required',
                'in:add,replace_all',
            ],

            'images' => [
                'required',
                'array',
                'min:1',
            ],

            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $uploadedPaths = [];
        $oldPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                &$uploadedPaths,
                &$oldPaths
            ) {
                /*
                 * MODE GANTI SEMUA
                 *
                 * Simpan daftar file lama terlebih dahulu.
                 * File lama TIDAK dihapus sebelum transaksi berhasil.
                 */
                if ($validated['mode'] === 'replace_all') {
                    $oldPaths = Hero::whereNotNull('image')
                        ->pluck('image')
                        ->filter()
                        ->values()
                        ->all();

                    Hero::query()->delete();

                    $nextSortOrder = 1;
                } else {
                    /*
                     * MODE TAMBAH
                     *
                     * Hero lama tetap dipertahankan.
                     */
                    $nextSortOrder = ((int) Hero::max('sort_order')) + 1;
                }

                /*
                 * Upload semua gambar baru.
                 */
                foreach ($request->file('images', []) as $image) {
                    $path = $image->store('heroes', 'public');

                    $uploadedPaths[] = $path;

                    Hero::create([
                        'image' => $path,
                        'status' => $validated['status'],
                        'sort_order' => $nextSortOrder++,
                    ]);
                }
            });

            /*
             * Database sudah berhasil commit.
             * Sekarang baru hapus file Hero lama.
             */
            if ($validated['mode'] === 'replace_all') {
                foreach ($oldPaths as $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        } catch (\Throwable $e) {
            /*
             * Jika proses gagal:
             * - database akan rollback
             * - file baru yang sempat terupload dihapus
             * - file Hero lama tetap aman
             */
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        $message = $validated['mode'] === 'replace_all'
            ? 'Semua Hero lama berhasil diganti dengan Hero baru.'
            : 'Hero baru berhasil ditambahkan.';

        return redirect()
            ->route('heroes.index')
            ->with('success', $message);
    }

    /**
     * Detail Hero.
     */
    public function show(Hero $hero)
    {
        return redirect()->route('heroes.edit', $hero);
    }

    /**
     * Form edit Hero.
     */
    public function edit(Hero $hero)
    {
        return view('admin.heroes.edit', compact('hero'));
    }

    /**
     * Update Hero.
     */
    public function update(Request $request, Hero $hero)
    {
        $validated = $request->validate([
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $oldImage = $hero->image;
        $newImage = null;

        try {
            /*
             * Upload gambar baru terlebih dahulu.
             */
            if ($request->hasFile('image')) {
                $newImage = $request->file('image')
                    ->store('heroes', 'public');

                $validated['image'] = $newImage;
            }

            /*
             * Update database.
             */
            $hero->update($validated);

            /*
             * Database berhasil diperbarui.
             * Sekarang baru hapus gambar lama.
             */
            if ($newImage && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        } catch (\Throwable $e) {
            /*
             * Jika update gagal, gambar baru dibersihkan.
             * Gambar lama tetap dipertahankan.
             */
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $e;
        }

        return redirect()
            ->route('heroes.index')
            ->with('success', 'Hero berhasil diperbarui.');
    }

    /**
     * Hapus Hero.
     */
    public function destroy(Hero $hero)
    {
        if ($hero->image) {
            Storage::disk('public')->delete($hero->image);
        }

        $hero->delete();

        return redirect()
            ->route('heroes.index')
            ->with('success', 'Hero berhasil dihapus.');
    }
}