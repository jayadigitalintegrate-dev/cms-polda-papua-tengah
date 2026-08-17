<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Pengumuman
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi pengumuman website Polda Papua Tengah.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                    <div class="font-semibold mb-2">
                        Terdapat kesalahan pada data:
                    </div>

                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('announcements.update', $announcement) }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                {{-- INFORMASI UTAMA --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-800">
                            Informasi Pengumuman
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Isi informasi utama pengumuman.
                        </p>
                    </div>

                    <div class="space-y-5">

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                Judul <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title', $announcement->title) }}"
                                required
                                maxlength="255"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: Selamat Datang di Website Resmi Polda Papua Tengah"
                            >

                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Deskripsi Singkat
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Deskripsi singkat pengumuman..."
                            >{{ old('description', $announcement->description) }}</textarea>

                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">
                                Konten
                            </label>

                            <textarea
                                id="content"
                                name="content"
                                rows="8"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Isi lengkap pengumuman..."
                            >{{ old('content', $announcement->content) }}</textarea>

                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- MEDIA --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-800">
                            Media
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Tambahkan gambar dan lampiran PDF jika diperlukan.
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">
                                Gambar
                            </label>

                            <input
                                type="file"
                                id="image"
                                name="image"
                                accept=".webp,.png,.jpg,.jpeg,image/webp,image/png,image/jpeg"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium"
                            >

                            @if($announcement->image)
                            <div class="mb-3">
                                <p class="mb-2 text-xs font-medium text-gray-600">
                                    Gambar saat ini
                                </p>
                                <img
                                    src="{{ asset('storage/' . $announcement->image) }}"
                                    alt="{{ $announcement->title }}"
                                    class="h-32 w-auto rounded-lg border border-gray-200 object-cover"
                                >
                            </div>
                        @endif
                        <p class="mt-1 text-xs text-gray-500">
                                WEBP, PNG, JPG, atau JPEG. Maksimal 5 MB.
                            </p>

                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="attachment" class="block text-sm font-medium text-gray-700">
                                Lampiran PDF
                            </label>

                            <input
                                type="file"
                                id="attachment"
                                name="attachment"
                                accept=".pdf,application/pdf"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium"
                            >

                            @if($announcement->attachment)
                            <div class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <p class="text-xs font-medium text-gray-600">
                                    Lampiran saat ini
                                </p>
                                <p class="mt-1 text-sm text-gray-700">
                                    {{ $announcement->attachment_name ?? 'Dokumen PDF' }}
                                </p>
                                <a
                                    href="{{ asset('storage/' . $announcement->attachment) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-flex text-sm font-medium text-blue-600 hover:text-blue-800"
                                >
                                    Lihat PDF
                                </a>
                            </div>
                        @endif
                        <p class="mt-1 text-xs text-gray-500">
                                PDF maksimal 10 MB.
                            </p>

                            @error('attachment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- PENGATURAN --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-800">
                            Pengaturan Tampilan
                        </h3>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">
                                Tipe <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="type"
                                name="type"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">-- Pilih Tipe --</option>

                                <option value="popup" @selected(old('type', $announcement->type) === 'popup')>
                                    Popup
                                </option>

                                <option value="banner" @selected(old('type', $announcement->type) === 'banner')>
                                    Banner
                                </option>

                                <option value="info" @selected(old('type', $announcement->type) === 'info')>
                                    Informasi
                                </option>
                            </select>

                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">
                                Prioritas <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="priority"
                                name="priority"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="high" @selected(old('priority', $announcement->priority) === 'high')>
                                    High
                                </option>

                                <option value="medium" @selected(old('priority', $announcement->priority) === 'medium')>
                                    Medium
                                </option>

                                <option value="low" @selected(old('priority', $announcement->priority) === 'low')>
                                    Low
                                </option>
                            </select>

                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">
                                Urutan Tampilan
                            </label>

                            <input
                                type="number"
                                id="sort_order"
                                name="sort_order"
                                min="0"
                                value="{{ old('sort_order', $announcement->sort_order) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Semakin kecil nilainya, semakin awal urutannya.
                            </p>

                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center pt-7">
                            <label class="inline-flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="featured"
                                    value="1"
                                    @checked(old('featured', $announcement->featured))
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                >

                                <span>
                                    <span class="block text-sm font-medium text-gray-700">
                                        Featured
                                    </span>

                                    <span class="block text-xs text-gray-500">
                                        Tandai sebagai pengumuman unggulan.
                                    </span>
                                </span>
                            </label>
                        </div>

                    </div>
                </div>

                {{-- PUBLIKASI --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-800">
                            Publikasi
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Atur status dan periode tampil pengumuman.
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-3">

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="draft" @selected(old('status', $announcement->status) === 'draft')>
                                    Draft
                                </option>

                                <option value="published" @selected(old('status', $announcement->status) === 'published')>
                                    Published
                                </option>

                                <option value="expired" @selected(old('status', $announcement->status) === 'expired')>
                                    Expired
                                </option>

                                <option value="archived" @selected(old('status', $announcement->status) === 'archived')>
                                    Archived
                                </option>
                            </select>

                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="publish_start" class="block text-sm font-medium text-gray-700">
                                Mulai Publikasi
                            </label>

                            <input
                                type="datetime-local"
                                id="publish_start"
                                name="publish_start"
                                value="{{ old('publish_start', optional($announcement->publish_start)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('publish_start')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="publish_end" class="block text-sm font-medium text-gray-700">
                                Selesai Publikasi
                            </label>

                            <input
                                type="datetime-local"
                                id="publish_end"
                                name="publish_end"
                                value="{{ old('publish_end', optional($announcement->publish_end)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('publish_end')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ACTION --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <a
                        href="{{ route('announcements.index') }}"
                        class="inline-flex justify-center rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                    >
                        Simpan Pengumuman
                    </button>

                </div>

            </form>

        </div>
    </div>

</x-app-layout>
