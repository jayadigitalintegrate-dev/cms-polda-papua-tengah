<?php

namespace App\Http\Controllers;

use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfficialController extends Controller
{
    public function index()
    {
        $officials = Official::orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12);

        return view('admin.officials.index', compact('officials'));
    }

    public function create()
    {
        $positions = config('official_positions', []);

        return view('admin.officials.create', compact('positions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateOfficial($request);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')
                ->store('officials', 'public');
        }

        Official::create($validated);

        return redirect()
            ->route('officials.index')
            ->with('success', 'Data pejabat berhasil ditambahkan.');
    }

    public function show(Official $official)
    {
        return view('admin.officials.show', compact('official'));
    }

    public function edit(Official $official)
    {
        $positions = config('official_positions', []);

        return view('admin.officials.edit', compact('official', 'positions'));
    }

    public function update(Request $request, Official $official)
    {
        $validated = $this->validateOfficial($request);

        if ($request->hasFile('photo')) {
            if ($official->photo) {
                Storage::disk('public')->delete($official->photo);
            }

            $validated['photo'] = $request->file('photo')
                ->store('officials', 'public');
        }

        $official->update($validated);

        return redirect()
            ->route('officials.index')
            ->with('success', 'Data pejabat berhasil diperbarui.');
    }

    public function destroy(Official $official)
    {
        if ($official->photo) {
            Storage::disk('public')->delete($official->photo);
        }

        $official->delete();

        return redirect()
            ->route('officials.index')
            ->with('success', 'Data pejabat berhasil dihapus.');
    }

    private function validateOfficial(Request $request): array
    {
        $allowedPositions = collect(config('official_positions', []))
            ->pluck('value')
            ->all();

        return $request->validate([
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'name_id' => [
                'required',
                'string',
                'max:255',
            ],

            'name_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'rank' => [
                'nullable',
                'string',
                'max:255',
            ],

            'position_id' => [
                'required',
                'string',
                'in:' . implode(',', $allowedPositions),
            ],

            'position_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'nrp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'birth_place' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'string',
                'max:100',
            ],

            'religion' => [
                'nullable',
                'string',
                'max:100',
            ],

            'marital_status' => [
                'nullable',
                'string',
                'max:100',
            ],

            'spouse' => [
                'nullable',
                'string',
                'max:255',
            ],

            'children' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'motto' => [
                'nullable',
                'string',
            ],

            'education' => [
                'nullable',
                'array',
            ],

            'education.*' => [
                'nullable',
                'string',
                'max:500',
            ],

            'assignments' => [
                'nullable',
                'array',
            ],

            'assignments.*' => [
                'nullable',
                'string',
                'max:500',
            ],

            'career' => [
                'nullable',
                'array',
            ],

            'career.*' => [
                'nullable',
                'string',
                'max:500',
            ],

            'awards' => [
                'nullable',
                'array',
            ],

            'awards.*' => [
                'nullable',
                'string',
                'max:500',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);
    }
}