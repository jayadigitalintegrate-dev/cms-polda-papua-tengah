<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoliceStation;

class PoliceStationController extends Controller
{
    public function index()
    {
        $policeStations = PoliceStation::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'name_id',
                'name_en',
                'chief_name',
                'chief_rank',
                'chief_nrp',
                'chief_photo',
                'jurisdiction',
                'address',
                'phone',
                'email',
                'website',
                'description',
                'sort_order',
                'status',
                'created_at',
                'updated_at',
            ])
            ->map(function (PoliceStation $item) {
                return [
                    'id' => $item->id,

                    'name_id' => $item->name_id,
                    'name_en' => $item->name_en,

                    'chief_name' => $item->chief_name,
                    'chief_rank' => $item->chief_rank,
                    'chief_nrp' => $item->chief_nrp,

                    'chief_photo' => $item->chief_photo,

                    'chief_photo_url' => $item->chief_photo
                        ? asset('storage/' . $item->chief_photo)
                        : null,

                    'jurisdiction' => $item->jurisdiction,
                    'address' => $item->address,
                    'phone' => $item->phone,
                    'email' => $item->email,
                    'website' => $item->website,
                    'description' => $item->description,

                    'sort_order' => $item->sort_order,
                    'status' => $item->status,

                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

        return response()->json($policeStations);
    }
}
