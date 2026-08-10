<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hero;

class HeroController extends Controller
{
    /**
     * Hero aktif untuk website.
     */
    public function index()
    {
        $heroes = Hero::active()
            ->get([
                'id',
                'image',
                'status',
                'sort_order',
            ])
            ->map(function (Hero $hero) {
                return [
                    'id' => $hero->id,
                    'image' => $hero->image,
                    'image_url' => $hero->image
                        ? asset('storage/' . $hero->image)
                        : null,
                    'sort_order' => $hero->sort_order,
                ];
            });

        return response()->json($heroes);
    }
}