<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen User
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Kelola akun operator CMS Polda Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('users.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
                + Tambah Operator
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Daftar Pengguna
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Akun Superadmin dilindungi dan tidak dapat dikelola dari halaman ini.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                        Nama
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                        Email
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                        Role
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                        Dibuat
                                    </th>

                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">

                                @forelse ($users as $user)

                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                            {{ $user->name }}
                                        </td>

                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            {{ $user->email }}
                                        </td>

                                        <td class="px-4 py-4 text-sm">
                                            @if ($user->role === 'superadmin')
                                                <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                    Superadmin
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                                    Operator
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            {{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">

                                                <a
                                                    href="{{ route('users.show', $user) }}"
                                                    class="inline-flex items-center px-3 py-2 rounded-md bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200"
                                                >
                                                    Lihat
                                                </a>

                                                @if ($user->role !== 'superadmin')

                                                    <a
                                                        href="{{ route('users.edit', $user) }}"
                                                        class="inline-flex items-center px-3 py-2 rounded-md bg-blue-100 text-blue-700 text-xs font-semibold hover:bg-blue-200"
                                                    >
                                                        Edit
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('users.destroy', $user) }}"
                                                        onsubmit="return confirm('Hapus user operator ini?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center px-3 py-2 rounded-md bg-red-100 text-red-700 text-xs font-semibold hover:bg-red-200"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </form>

                                                @endif

                                            </div>
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td
                                            colspan="5"
                                            class="px-4 py-8 text-center text-sm text-gray-500"
                                        >
                                            Belum ada user.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    @if ($users->hasPages())
                        <div class="mt-6">
                            {{ $users->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
