<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tambah Dokumen PPID
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Tambahkan dokumen PPID baru.
                </p>

            </div>

            <a
                href="{{ route('ppid-documents.index') }}"
                class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700">

                Kembali

            </a>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl shadow">

                <form
                    action="{{ route('ppid-documents.store') }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Kategori PPID
                            </label>

                            <select
                                name="ppid_category_id"
                                class="w-full rounded-lg border-gray-300">

                                @foreach($categories as $category)

                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Judul Dokumen
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="w-full rounded-lg border-gray-300">

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Nomor Dokumen
                            </label>

                            <input
                                type="text"
                                name="document_number"
                                class="w-full rounded-lg border-gray-300">

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Tahun Publikasi
                            </label>

                            <input
                                type="number"
                                name="publication_year"
                                class="w-full rounded-lg border-gray-300">

                        </div>


                        <div class="md:col-span-2">

                            <label class="block text-sm font-semibold mb-2">
                                Ringkasan
                            </label>

                            <textarea
                                name="summary"
                                rows="3"
                                class="w-full rounded-lg border-gray-300"></textarea>

                        </div>

                        <div class="md:col-span-2">

                            <label class="block text-sm font-semibold mb-2">
                                Isi Dokumen
                            </label>

                            <textarea
                                name="content"
                                rows="8"
                                class="w-full rounded-lg border-gray-300"></textarea>

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Upload PDF
                            </label>

                            <input
                                type="file"
                                name="document"
                                accept=".pdf"
                                class="w-full rounded-lg border-gray-300">

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Thumbnail
                            </label>

                            <input
                                type="file"
                                name="thumbnail"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="w-full rounded-lg border-gray-300">

                        </div>

                        <div>

                            <label class="block text-sm font-semibold mb-2">
                                Status
                            </label>

                            <select
                                name="status"
                                class="w-full rounded-lg border-gray-300">

                                <option value="draft">
                                    Draft
                                </option>

                                <option value="published">
                                    Published
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="border-t bg-gray-50 px-6 py-4 flex justify-end gap-3">

                        <a
                            href="{{ route('ppid-documents.index') }}"
                            class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600">

                            Batal

                        </a>

                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">

                            Simpan Dokumen

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>

