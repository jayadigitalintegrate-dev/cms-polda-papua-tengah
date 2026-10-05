<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-800">

                    Detail Dokumen PPID

                </h2>

                <p class="mt-1 text-sm text-gray-500">

                    Informasi lengkap dokumen PPID.

                </p>

            </div>

            <div class="flex items-center gap-2">

                <a href="{{ route('ppid-documents.edit', $ppidDocument) }}"
                    class="inline-flex h-10 items-center rounded-md bg-amber-500 px-5 text-sm font-semibold text-white hover:bg-amber-600">

                    Edit

                </a>

                <a href="{{ route('ppid-documents.index') }}"
                    class="inline-flex h-10 items-center rounded-md bg-gray-600 px-5 text-sm font-semibold text-white hover:bg-gray-700">

                    Kembali

                </a>

            </div>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow">

                <div class="border-b px-6 py-5">

                    <h3 class="text-lg font-semibold">

                        {{ $ppidDocument->title }}

                    </h3>

                </div>

                <div class="grid grid-cols-1 gap-8 p-6 lg:grid-cols-3">

                    <div>

                        @if($ppidDocument->thumbnail)

                            <img src="{{ Storage::url($ppidDocument->thumbnail) }}" alt="{{ $ppidDocument->title }}"
                                class="w-full rounded-lg border object-cover shadow">

                        @else

                            <div class="flex h-72 items-center justify-center rounded-lg border bg-gray-100 text-gray-400">

                                Tidak ada thumbnail

                            </div>

                        @endif

                    </div>

                    <div class="lg:col-span-2">

                        <table class="w-full">

                            <tbody>

                                <tr class="border-b">

                                    <td class="w-56 py-3 font-semibold text-gray-700">
                                        Kategori
                                    </td>

                                    <td class="py-3">
                                        {{ $ppidDocument->category->name ?? '-' }}
                                    </td>

                                </tr>

                                <tr class="border-b">

                                    <td class="py-3 font-semibold text-gray-700">
                                        Nomor Dokumen
                                    </td>

                                    <td class="py-3">
                                        {{ $ppidDocument->document_number ?: '-' }}
                                    </td>

                                </tr>

                                <tr class="border-b">

                                    <td class="py-3 font-semibold text-gray-700">
                                        Tahun Publikasi
                                    </td>

                                    <td class="py-3">
                                        {{ $ppidDocument->publication_year ?: '-' }}
                                    </td>

                                </tr>

                                <tr class="border-b">

                                    <td class="py-3 font-semibold text-gray-700">
                                        Status
                                    </td>

                                    <td class="py-3">

                                        @if($ppidDocument->status == 'published')

                                            <span
                                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Published
                                            </span>

                                        @else

                                            <span
                                                class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                                                Draft
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                                <tr class="border-b">

                                    <td class="py-3 font-semibold text-gray-700">
                                        Total Download
                                    </td>

                                    <td class="py-3">

                                        <span class="font-semibold">

                                            {{ number_format($ppidDocument->download_count) }}

                                        </span>

                                    </td>

                                </tr>

                                <tr class="border-b">

                                    <td class="py-3 font-semibold text-gray-700">
                                        Total View
                                    </td>

                                    <td class="py-3">

                                        <span class="font-semibold">

                                            {{ number_format($ppidDocument->view_count) }}

                                        </span>

                                    </td>

                                </tr>

                                <tr>

                                    <td class="py-3 font-semibold text-gray-700">
                                        File PDF
                                    </td>

                                    <td class="py-3">

                                        @if($ppidDocument->document)

                                            <a href="{{ route('ppid-documents.download', $ppidDocument) }}"
                                                class="inline-flex h-10 items-center rounded-md bg-red-600 px-5 text-sm font-semibold text-white hover:bg-red-700">

                                                Download PDF

                                            </a>

                                        @else

                                            <span class="text-gray-400">

                                                Tidak ada file PDF

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>
                    <div class="border-t p-6">

                        <h4 class="mb-3 text-lg font-semibold text-gray-800">

                            Ringkasan

                        </h4>

                        <div class="rounded-lg bg-gray-50 p-4 text-gray-700">

                            {!! nl2br(e($ppidDocument->summary ?? '-')) !!}

                        </div>

                    </div>

                    <div class="border-t p-6">

                        <h4 class="mb-3 text-lg font-semibold text-gray-800">

                            Isi Dokumen

                        </h4>

                        <div class="prose max-w-none">

                            @if ($ppidDocument->content)
                                {!! nl2br(e($ppidDocument->content)) !!}
                            @else
                                <p class="text-gray-400">Belum ada isi dokumen.</p>
                            @endif

                        </div>

                    </div>

                    <div class="border-t bg-gray-50 px-6 py-5">

                        <div class="flex items-center justify-between">

                            <div class="text-sm text-gray-500">

                                Dibuat:
                                {{ optional($ppidDocument->created_at)->format('d M Y H:i') }}

                                @if($ppidDocument->updated_at)

                                    <br>

                                    Diperbarui:
                                    {{ optional($ppidDocument->updated_at)->format('d M Y H:i') }}

                                @endif

                            </div>

                            <div class="flex gap-2">

                                <a href="{{ route('ppid-documents.edit', $ppidDocument) }}"
                                    class="inline-flex h-10 items-center rounded-md bg-amber-500 px-5 text-sm font-semibold text-white hover:bg-amber-600">

                                    Edit Dokumen

                                </a>

                                <a href="{{ route('ppid-documents.index') }}"
                                    class="inline-flex h-10 items-center rounded-md bg-gray-600 px-5 text-sm font-semibold text-white hover:bg-gray-700">

                                    Kembali

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

</x-app-layout>









</div>

</tbody>