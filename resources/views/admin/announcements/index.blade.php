<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pengumuman
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola pengumuman banner, popup, dan informasi website.
                </p>
            </div>

            <a
                href="{{ route('announcements.create') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                + Tambah Pengumuman
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 rounded-lg bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    #
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Pengumuman
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Tipe
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Prioritas
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Status
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Periode
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($announcements as $announcement)
                                <tr class="hover:bg-gray-50">

                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600">
                                        {{ $announcements->firstItem() + $loop->index }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">

                                            @if($announcement->image)
                                                <img
                                                    src="{{ asset('storage/' . $announcement->image) }}"
                                                    alt="{{ $announcement->title }}"
                                                    class="h-14 w-20 rounded-lg object-cover"
                                                >
                                            @else
                                                <div class="flex h-14 w-20 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                                                    Tanpa gambar
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <a
                                                    href="{{ route('announcements.show', $announcement) }}"
                                                    class="font-semibold text-gray-800 hover:text-blue-600"
                                                >
                                                    {{ $announcement->title }}
                                                </a>

                                                @if($announcement->featured)
                                                    <span class="ml-2 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                                        Featured
                                                    </span>
                                                @endif

                                                @if($announcement->description)
                                                    <p class="mt-1 max-w-md truncate text-sm text-gray-500">
                                                        {{ $announcement->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">
                                            {{ ucfirst($announcement->type) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        @php
                                            $priorityClass = match($announcement->priority) {
                                                'high' => 'bg-red-100 text-red-700',
                                                'low' => 'bg-gray-100 text-gray-700',
                                                default => 'bg-yellow-100 text-yellow-700',
                                            };
                                        @endphp

                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $priorityClass }}">
                                            {{ ucfirst($announcement->priority) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        @php
                                            $statusClass = match($announcement->status) {
                                                'published' => 'bg-green-100 text-green-700',
                                                'draft' => 'bg-gray-100 text-gray-700',
                                                'expired' => 'bg-orange-100 text-orange-700',
                                                'archived' => 'bg-slate-100 text-slate-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp

                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($announcement->status) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600">
                                        @if($announcement->publish_start)
                                            {{ $announcement->publish_start->format('d/m/Y H:i') }}
                                        @else
                                            -
                                        @endif

                                        <span class="text-gray-400">→</span>

                                        @if($announcement->publish_end)
                                            {{ $announcement->publish_end->format('d/m/Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right">
                                        <div class="flex justify-end gap-2">

                                            <a
                                                href="{{ route('announcements.show', $announcement) }}"
                                                class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200"
                                            >
                                                Lihat
                                            </a>

                                            <a
                                                href="{{ route('announcements.edit', $announcement) }}"
                                                class="rounded-lg bg-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                action="{{ route('announcements.destroy', $announcement) }}"
                                                method="POST"
                                                onsubmit="return confirm('Hapus pengumuman ini?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200"
                                                >
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="text-sm font-medium text-gray-500">
                                            Belum ada pengumuman.
                                        </div>

                                        <a
                                            href="{{ route('announcements.create') }}"
                                            class="mt-3 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700"
                                        >
                                            Tambah pengumuman pertama
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($announcements->hasPages())
                    <div class="border-t border-gray-200 px-4 py-4">
                        {{ $announcements->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
