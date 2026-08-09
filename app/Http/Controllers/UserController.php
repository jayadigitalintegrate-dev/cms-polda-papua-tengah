<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Daftar user.
     */
    public function index(): View
    {
        $users = User::query()
            ->select([
                'id',
                'name',
                'email',
                'role',
                'created_at',
            ])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Form tambah user operator.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Simpan user operator baru.
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return Redirect::route('users.index')
            ->with('success', 'User operator berhasil dibuat.');
    }

    /**
     * Detail user.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Form edit user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user operator.
     */
    public function update(
        UserUpdateRequest $request,
        User $user
    ): RedirectResponse {
        // Superadmin tidak boleh diedit melalui User Management.
        if ($user->role === 'superadmin') {
            abort(403, 'Akun Superadmin tidak dapat diubah melalui User Management.');
        }

        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];

        // Password hanya diganti jika memang diisi.
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Role selalu dipertahankan sebagai operator.
        $user->role = 'operator';

        $user->save();

        return Redirect::route('users.index')
            ->with('success', 'User operator berhasil diperbarui.');
    }

    /**
     * Hapus user operator.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Jangan pernah izinkan penghapusan Superadmin.
        if ($user->role === 'superadmin') {
            abort(403, 'Akun Superadmin tidak dapat dihapus.');
        }

        // Jangan izinkan Superadmin yang sedang login menghapus dirinya sendiri.
        if ($user->is(auth()->user())) {
            abort(403, 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user->delete();

        return Redirect::route('users.index')
            ->with('success', 'User operator berhasil dihapus.');
    }
}

