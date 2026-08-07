<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PpidDocument;
use Illuminate\Http\JsonResponse;

class PpidDocumentController extends Controller
{
    public function index(): JsonResponse
    {
        $documents = PpidDocument::with('category')
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'slug' => $document->slug,
                    'summary' => $document->summary,
                    'content' => $document->content,
                    'document_number' => $document->document_number,
                    'document_name' => $document->document_name,
                    'document_url' => $document->document_url,
                    'publication_year' => $document->publication_year,
                    'status' => $document->status,
                    'published_at' => $document->published_at,
                    'sort_order' => $document->sort_order,
                    'view_count' => $document->view_count,
                    'category' => $document->category ? [
                        'id' => $document->category->id,
                        'name' => $document->category->name,
                        'slug' => $document->category->slug,
                    ] : null,
                ];
            }),
        ]);
    }
}
