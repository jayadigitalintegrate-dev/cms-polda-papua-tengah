<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Pejabat
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan data pejabat, pimpinan, atau pejabat utama Polda Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('officials.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                â† Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="font-semibold text-red-800">
                        Terdapat kesalahan pada data:
                    </div>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('officials.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                {{-- IDENTITAS UTAMA --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Identitas Utama
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Informasi utama pejabat yang akan ditampilkan pada website Polda Papua Tengah.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2">

                        {{-- FOTO --}}
                        <div class="md:col-span-2">
                            <label
                                for="photo"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Foto Pejabat
                            </label>

                            <input
                                id="photo"
                                name="photo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Format: JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.
                            </p>

                            @error('photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NAMA INDONESIA --}}
                        <div>
                            <label
                                for="name_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Nama Lengkap *
                            </label>

                            <input
                                id="name_id"
                                name="name_id"
                                type="text"
                                value="{{ old('name_id') }}"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: Nama Lengkap Pejabat"
                            >

                            @error('name_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NAMA INGGRIS --}}
                        <div>
                            <label
                                for="name_en"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Nama Bahasa Inggris
                            </label>

                            <input
                                id="name_en"
                                name="name_en"
                                type="text"
                                value="{{ old('name_en') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="English name (optional)"
                            >

                            @error('name_en')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PANGKAT --}}
                        <div>
                            <label
                                for="rank"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Pangkat
                            </label>

                            <input
                                id="rank"
                                name="rank"
                                type="text"
                                value="{{ old('rank') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: Brigjen Pol."
                            >

                            @error('rank')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                   {{-- JABATAN INDONESIA --}}
<div>
    <label
        for="position_id"
        class="block text-sm font-medium text-gray-700"
    >
        Jabatan *
    </label>

    <select
        id="position_id"
        name="position_id"
        required
        class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
    >
        <option value="">-- Pilih Jabatan --</option>

        @foreach ($positions as $position)
            <option
                value="{{ $position['value'] }}"
               data-position-en="{{ $position['position_en'] }}"
                @selected(old('position_id') === $position['value'])
            >
                {{ $position['label'] }}
            </option>
        @endforeach
    </select>

    @error('position_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>



</div>
                        {{-- JABATAN INGGRIS --}}
                        <div>
                            <label
                                for="position_en"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Jabatan Bahasa Inggris
                            </label>

                            <input
                                id="position_en"
                                name="position_en"
                                type="text"
                                value="{{ old('position_en') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="English position (optional)"
                            >

                            @error('position_en')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>


                {{-- BIODATA --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Biodata
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Informasi pribadi pejabat.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2">

                        {{-- NRP --}}
                        <div>
                            <label
                                for="nrp"
                                class="block text-sm font-medium text-gray-700"
                            >
                                NRP
                            </label>

                            <input
                                id="nrp"
                                name="nrp"
                                type="text"
                                value="{{ old('nrp') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- TEMPAT LAHIR --}}
                        <div>
                            <label
                                for="birth_place"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Tempat Lahir
                            </label>

                            <input
                                id="birth_place"
                                name="birth_place"
                                type="text"
                                value="{{ old('birth_place') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- TANGGAL LAHIR --}}
                        <div>
                            <label
                                for="birth_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Tanggal Lahir
                            </label>

                            <input
                                id="birth_date"
                                name="birth_date"
                                type="text"
                                value="{{ old('birth_date') }}"
                                placeholder="Contoh: 12 Januari 1975"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- AGAMA --}}
                        <div>
                            <label
                                for="religion"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Agama
                            </label>

                            <input
                                id="religion"
                                name="religion"
                                type="text"
                                value="{{ old('religion') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- STATUS PERKAWINAN --}}
                        <div>
                            <label
                                for="marital_status"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Status Perkawinan
                            </label>

                            <input
                                id="marital_status"
                                name="marital_status"
                                type="text"
                                value="{{ old('marital_status') }}"
                                placeholder="Contoh: Menikah"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- PASANGAN --}}
                        <div>
                            <label
                                for="spouse"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Nama Pasangan
                            </label>

                            <input
                                id="spouse"
                                name="spouse"
                                type="text"
                                value="{{ old('spouse') }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- ANAK --}}
                        <div>
                            <label
                                for="children"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Jumlah Anak
                            </label>

                            <input
                                id="children"
                                name="children"
                                type="number"
                                min="0"
                                value="{{ old('children', 0) }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- MOTTO --}}
                        <div class="md:col-span-2">
                            <label
                                for="motto"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Motto
                            </label>

                            <textarea
                                id="motto"
                                name="motto"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Motto atau prinsip hidup pejabat"
                            >{{ old('motto') }}</textarea>
                        </div>

                    </div>
                </div>


                {{-- RIWAYAT --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Riwayat Pendidikan, Penugasan, Karier & Penghargaan
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Masukkan satu data pada setiap baris.
                        </p>
                    </div>

                    <div class="space-y-6 px-6 py-6">

                        {{-- PENDIDIKAN --}}
                        <div>
                            <label
                                for="education_text"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Pendidikan
                            </label>

                            <textarea
                                id="education_text"
                                name="education_text"
                                rows="5"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh:
Akademi Kepolisian
PTIK
Sespim
Lemhannas"
                            >{{ old('education_text') }}</textarea>
                        </div>

                        {{-- PENUGASAN --}}
                        <div>
                            <label
                                for="assignments_text"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Riwayat Penugasan
                            </label>

                            <textarea
                                id="assignments_text"
                                name="assignments_text"
                                rows="5"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Satu penugasan setiap baris"
                            >{{ old('assignments_text') }}</textarea>
                        </div>

                        {{-- KARIER --}}
                        <div>
                            <label
                                for="career_text"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Riwayat Karier
                            </label>

                            <textarea
                                id="career_text"
                                name="career_text"
                                rows="5"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Satu riwayat karier setiap baris"
                            >{{ old('career_text') }}</textarea>
                        </div>

                        {{-- PENGHARGAAN --}}
                        <div>
                            <label
                                for="awards_text"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Penghargaan
                            </label>

                            <textarea
                                id="awards_text"
                                name="awards_text"
                                rows="5"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Satu penghargaan setiap baris"
                            >{{ old('awards_text') }}</textarea>
                        </div>

                    </div>
                </div>


                {{-- PENGATURAN --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Pengaturan Tampilan
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-2">

                        {{-- URUTAN --}}
                        <div>
                            <label
                                for="sort_order"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Urutan Tampilan
                            </label>

                            <input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                value="{{ old('sort_order', 0) }}"
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Angka lebih kecil akan tampil lebih dahulu.
                            </p>
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Status
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="active" @selected(old('status', 'active') === 'active')>
                                    Aktif
                                </option>

                                <option value="inactive" @selected(old('status') === 'inactive')>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                    </div>
                </div>


                {{-- ACTION --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('officials.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Simpan Pejabat
                    </button>

                </div>

            </form>

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const positionSelect = document.getElementById('position_id');
        const positionEnInput = document.getElementById('position_en');

        if (!positionSelect || !positionEnInput) {
            return;
        }

        function syncEnglishPosition() {
            const selectedOption =
                positionSelect.options[positionSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                positionEnInput.value = '';
                return;
            }

            positionEnInput.value =
                selectedOption.dataset.positionEn || '';
        }

        positionSelect.addEventListener('change', syncEnglishPosition);

        syncEnglishPosition();
    });
</script>

</x-app-layout>
