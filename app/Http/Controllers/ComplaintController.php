<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Menampilkan daftar pengaduan masyarakat.
     */
    public function index()
    {
        $complaints = Complaint::query()
            ->latest()
            ->paginate(15);

        return view(
            'admin.complaints.index',
            compact('complaints')
        );
    }

    /**
     * Menampilkan detail satu pengaduan.
     */
    public function show(Complaint $complaint)
    {
        return view(
            'admin.complaints.show',
            compact('complaint')
        );
    }
}