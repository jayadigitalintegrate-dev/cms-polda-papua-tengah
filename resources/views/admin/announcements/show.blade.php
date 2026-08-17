<x-app-layout>

<x-slot name="header">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Pengumuman
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Detail pengumuman website Polda Papua Tengah.
        </p>
    </div>
</x-slot>

<div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-8">

            <div class="flex flex-wrap items-center gap-2 mb-4">

                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                    {{ ucfirst($announcement->type) }}
                </span>

                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                    {{ ucfirst($announcement->priority) }}
                </span>

                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                    {{ ucfirst($announcement->status) }}
                </span>

                @if($announcement->featured)
                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">
                        Featured
                    </span>
                @endif

            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                {{ $announcement->title }}
            </h1>

            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 mb-6">

                <span>
                    <strong>Mulai:</strong>
                    {{ optional($announcement->publish_start)->format('d M Y H:i') ?? '-' }}
                </span>

                <span>
                    <strong>Selesai:</strong>
                    {{ optional($announcement->publish_end)->format('d M Y H:i') ?? '-' }}
                </span>

                <span>
                    <strong>Urutan:</strong>
                    {{ $announcement->sort_order }}
                </span>

            </div>

            @if($announcement->image)

                <div class="mb-8">
                    <img
                        src="{{ asset('storage/' . $announcement->image) }}"
                        alt="{{ $announcement->title }}"
                        class="w-full max-h-[520px] object-contain rounded-lg border border-gray-200 bg-gray-50"
                    >
                </div>

            @endif

            @if($announcement->description)

                <div class="bg-gray-100 rounded-lg p-4 italic mb-8 text-gray-700">
                    {{ $announcement->description }}
                </div>

            @endif

            @if($announcement->content)

                <div class="prose max-w-none text-gray-800">
                    {!! nl2br(e($announcement->content)) !!}
                </div>

            @endif

            @if($announcement->attachment)

                <hr class="my-8">

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">

                    <h3 class="text-lg font-semibold text-gray-800">
                        Lampiran
                    </h3>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ $announcement->attachment_name ?? 'Dokumen PDF' }}
                    </p>

                    <a
                        href="{{ asset('storage/' . $announcement->attachment) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Lihat PDF
                    </a>

                </div>

            @endif

            <hr class="my-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">

                <a
                    href="{{ route('announcements.index') }}"
                    class="inline-flex justify-center rounded-lg bg-gray-600 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                >
                    Kembali
                </a>

                <a
                    href="{{ route('announcements.edit', $announcement) }}"
                    class="inline-flex justify-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Edit Pengumuman
                </a>

            </div>

        </div>

    </div>
</div>

</x-app-layout>
