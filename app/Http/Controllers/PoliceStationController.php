<?php

namespace App\Http\Controllers;

use App\Models\PoliceStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PoliceStationController extends Controller
{
    public function index()
    {
        $policeStations = PoliceStation::orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12);

        return view('admin.police_stations.index', compact('policeStations'));
    }

    public function create()
    {
        return view('admin.police_stations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePoliceStation($request);

        if ($request->hasFile('chief_photo')) {
            $validated['chief_photo'] = $request->file('chief_photo')
                ->store('police-stations', 'public');
        }

        PoliceStation::create($validated);

        return redirect()
            ->route('police-stations.index')
            ->with('success', 'Data jajaran Polres berhasil ditambahkan.');
    }

    public function show(PoliceStation $policeStation)
    {
        return view('admin.police_stations.show', compact('policeStation'));
    }

    public function edit(PoliceStation $policeStation)
    {
        return view('admin.police_stations.edit', compact('policeStation'));
    }

    public function update(Request $request, PoliceStation $policeStation)
    {
        $validated = $this->validatePoliceStation($request);

        if ($request->hasFile('chief_photo')) {
            if ($policeStation->chief_photo) {
                Storage::disk('public')->delete($policeStation->chief_photo);
            }

            $validated['chief_photo'] = $request->file('chief_photo')
                ->store('police-stations', 'public');
        }

        $policeStation->update($validated);

        return redirect()
            ->route('police-stations.index')
            ->with('success', 'Data jajaran Polres berhasil diperbarui.');
    }

    public function destroy(PoliceStation $policeStation)
    {
        if ($policeStation->chief_photo) {
            Storage::disk('public')->delete($policeStation->chief_photo);
        }

        $policeStation->delete();

        return redirect()
            ->route('police-stations.index')
            ->with('success', 'Data jajaran Polres berhasil dihapus.');
    }

    private function validatePoliceStation(Request $request): array
    {
        return $request->validate([
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

            'chief_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'chief_rank' => [
                'nullable',
                'string',
                'max:255',
            ],

            'chief_nrp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'chief_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'jurisdiction' => [
                'nullable',
                'string',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'website' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
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
