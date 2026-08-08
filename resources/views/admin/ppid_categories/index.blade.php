<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kategori PPID
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Kelola kategori dokumen informasi publik PPID.
                </p>
            </div>

            <a
                href="{{ route('ppid-categories.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
                + Tambah Kategori
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6 border-b border-gray-200">

                    <form
                        method="GET"
                        action="{{ route('ppid-categories.index') }}"
                        class="flex flex-col sm:flex-row gap-3"
                    >
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari kategori..."
                            class="w-full sm:flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >

                        <button
                            type="submit"
                            class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                        >
                            Cari
                        </button>

                        @if(request('search'))
                            <a
                                href="{{ route('ppid-categories.index') }}"
                                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                            >
                                Reset
                            </a>
                        @endif
                    </form>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    No
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Kategori
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Slug
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Urutan
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Status
                                </th>

                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                    Aksi
                                </th>
                            </tr>

                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse($categories as $category)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $categories->firstItem() + $loop->index }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $category->name }}
                                        </div>

                                        @if($category->description)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $category->description }}
                                            </div>
                                        @endif

                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $category->slug }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{ $category->sort_order }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">

                                        @if($category->is_active)

                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                Aktif
                                            </span>

                                        @else

                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                                Nonaktif
                                            </span>

                                        @endif

                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">

                                        <a
                                            href="{{ route('ppid-categories.edit', $category) }}"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('ppid-categories.destroy', $category) }}"
                                            class="inline"
                                            onsubmit="return confirm('Hapus kategori ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="px-6 py-12 text-center"
                                    >
                                        <div class="text-gray-500">
                                            Belum ada kategori PPID.
                                        </div>

                                        <a
                                            href="{{ route('ppid-categories.create') }}"
                                            class="inline-flex mt-4 text-sm text-indigo-600 hover:text-indigo-900"
                                        >
                                            + Tambah kategori pertama
                                        </a>
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if($categories->hasPages())

                    <div class="p-6 border-t border-gray-200">
                        {{ $categories->links() }}
                    </div>

                @endif

            </div>

        </div>
    </div>

</x-app-layout>
