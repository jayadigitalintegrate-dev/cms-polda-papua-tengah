<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Dokumen PPID
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Perbarui informasi dokumen PPID
                </p>

            </div>

            <a href="{{ route('ppid-documents.index') }}"
                class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700">

                Kembali

            </a>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl shadow">

                <form action="{{ route('ppid-documents.update', $ppidDocument) }}" method="POST"
                    enctype="multipart/form-data">

                    @csrf
                @method('PUT')





                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Kategori PPID
                            </label>

                            <select name="ppid_category_id" class="w-full rounded-lg border-gray-300">

                                @foreach($categories as $category)

                                    <option value="{{ $category->id }}" @selected(old('ppid_category_id', $ppidDocument->ppid_category_id)==$category->id)>
                                        {{ $category->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Judul Dokumen
                            </label>

                            <input type="text" name="title" value="{{ old('title', $ppidDocument->title) }}" class="w-full rounded-lg border-gray-300">

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Nomor Dokumen
                            </label>

                            <input type="text" name="document_number" value="{{ old('document_number', $ppidDocument->document_number) }}" class="w-full rounded-lg border-gray-300">

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Tahun Publikasi
                            </label>

                            <input type="number" name="publication_year" value="{{ old('publication_year', $ppidDocument->publication_year) }}" class="w-full rounded-lg border-gray-300">

                        </div>


                        <div class="md:col-span-2">

                            <label class="block text-sm font-semibold mb-2">
                                Ringkasan
                            </label>

                            <textarea name="summary" rows="3" class="w-full rounded-lg border-gray-300">{{ old('summary', $ppidDocument->summary) }}</textarea>

                        </div>

                        <div class="md:col-span-2">

                            <label class="block text-sm font-semibold mb-2">
                                Isi Dokumen
                            </label>

                            <textarea name="content" rows="8" class="w-full rounded-lg border-gray-300">{{ old('content', $ppidDocument->content) }}</textarea>

                        </div>

                        <div>

    <label class="block text-sm font-semibold mb-2">
        Dokumen PDF
    </label>

    @if($ppidDocument->document)

        <div class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-4">

            <div class="mb-2 text-sm font-semibold text-gray-700">
                PDF saat ini
            </div>

            <div class="flex flex-wrap items-center gap-3">

                <span class="text-sm text-gray-600">
                    {{ $ppidDocument->document_name ?? basename($ppidDocument->document) }}
                </span>

                <a
                    href="{{ asset('storage/' . $ppidDocument->document) }}"
                    target="_blank"
                    class="rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700">

                    Lihat PDF

                </a>

            </div>

        </div>

    @else

        <div class="mb-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">
            Belum ada file PDF.
        </div>

    @endif

    <label class="mb-2 block text-xs font-medium text-gray-500">
        Upload PDF baru hanya jika ingin mengganti dokumen lama.
    </label>

    <input
        type="file"
        name="document"
        accept=".pdf,application/pdf"
        class="w-full rounded-lg border-gray-300">

</div>


                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Status
                            </label>

                            <select name="status" class="w-full rounded-lg border-gray-300">

                                <option value="draft" @selected(old('status', $ppidDocument->status)=='draft')>
                                    Draft
                                </option>

                                <option value="published" @selected(old('status', $ppidDocument->status)=='published')>
                                    Published
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="border-t bg-gray-50 px-6 py-4 flex justify-end gap-3">

                        <a href="{{ route('ppid-documents.index') }}"
                            class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600">

                            Batal

                        </a>

                        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">

                            Perbarui Dokumen

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>









