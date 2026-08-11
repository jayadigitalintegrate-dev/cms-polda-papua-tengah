<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Official;

class OfficialController extends Controller
{
    public function index()
    {
        $officials = Official::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'photo',
                'name_id',
                'name_en',
                'rank',
                'position_id',
                'position_en',
                'nrp',
                'birth_place',
                'birth_date',
                'religion',
                'marital_status',
                'spouse',
                'children',
                'motto',
                'education',
                'assignments',
                'career',
                'awards',
                'sort_order',
                'status',
                'created_at',
                'updated_at',
            ])
            ->map(function (Official $item) {
                return [
                    'id' => $item->id,

                    'photo' => $item->photo,

                    'photo_url' => $item->photo
                        ? asset('storage/' . $item->photo)
                        : null,

                    'name_id' => $item->name_id,
                    'name_en' => $item->name_en,

                    'rank' => $item->rank,

                    'position_id' => $item->position_id,
                    'position_en' => $item->position_en,

                    'nrp' => $item->nrp,
                    'birth_place' => $item->birth_place,
                    'birth_date' => $item->birth_date,
                    'religion' => $item->religion,
                    'marital_status' => $item->marital_status,
                    'spouse' => $item->spouse,
                    'children' => $item->children,
                    'motto' => $item->motto,

                    'education' => $item->education ?? [],
                    'assignments' => $item->assignments ?? [],
                    'career' => $item->career ?? [],
                    'awards' => $item->awards ?? [],

                    'sort_order' => $item->sort_order,
                    'status' => $item->status,

                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

        return response()->json($officials);
    }
}
