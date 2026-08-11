<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detail Pejabat
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Detail data pejabat Polda Papua Tengah.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('officials.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    ← Kembali
                </a>

                <a
                    href="{{ route('officials.edit', $official) }}"
                    class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-900"
                >
                    Edit Pejabat
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $education = is_array($official->education) ? array_values(array_filter($official->education)) : [];
        $assignments = is_array($official->assignments) ? array_values(array_filter($official->assignments)) : [];
        $career = is_array($official->career) ? array_values(array_filter($official->career)) : [];
        $awards = is_array($official->awards) ? array_values(array_filter($official->awards)) : [];

        $photoUrl = $official->photo
            ? asset('storage/' . ltrim($official->photo, '/'))
            : null;
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- ALERT SUCCESS --}}
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- IDENTITAS UTAMA --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Identitas Utama
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Informasi utama pejabat yang ditampilkan pada website.
                            </p>
                        </div>

                        <span
                            class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold
                            {{ $official->status === 'active'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-600' }}"
                        >
                            {{ $official->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-8 p-6 lg:grid-cols-[260px_1fr]">

                    {{-- FOTO --}}
                    <div>
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                            @if ($photoUrl)
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="{{ $official->name_id }}"
                                    class="aspect-[3/4] w-full object-cover"
                                >
                            @else
                                <div class="flex aspect-[3/4] items-center justify-center text-center text-sm text-gray-400">
                                    Foto belum tersedia
                                </div>
                            @endif
                        </div>

                        <div class="mt-3 text-center">
                            <p class="text-xs text-gray-500">
                                Urutan tampil: {{ $official->sort_order ?? 0 }}
                            </p>
                        </div>
                    </div>

                    {{-- DATA UTAMA --}}
                    <div class="space-y-6">

                        <div>
                            <h4 class="text-2xl font-bold text-gray-900">
                                {{ $official->name_id }}
                            </h4>

                            @if ($official->name_en)
                                <p class="mt-1 text-sm italic text-gray-500">
                                    {{ $official->name_en }}
                                </p>
                            @endif

                            @if ($official->position_id)
                                <p class="mt-3 text-base font-semibold text-gray-700">
                                    {{ $official->position_id }}
                                </p>
                            @endif

                            @if ($official->position_en)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $official->position_en }}
                                </p>
                            @endif
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Pangkat
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->rank ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    NRP
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->nrp ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Tempat Lahir
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->birth_place ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Tanggal Lahir
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->birth_date ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Agama
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->religion ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Status Perkawinan
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->marital_status ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Pasangan
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->spouse ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                    Jumlah Anak
                                </p>
                                <p class="mt-1 text-sm text-gray-800">
                                    {{ $official->children ?? 0 }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- MOTTO --}}
            @if ($official->motto)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Motto
                        </h3>
                    </div>

                    <div class="px-6 py-5">
                        <blockquote class="border-l-4 border-gray-300 pl-4 text-base italic leading-7 text-gray-700">
                            “{{ $official->motto }}”
                        </blockquote>
                    </div>
                </div>
            @endif

            {{-- PENDIDIKAN --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Pendidikan
                    </h3>
                </div>

                <div class="px-6 py-5">
                    @if (count($education))
                        <ol class="relative border-l border-gray-200">
                            @foreach ($education as $item)
                                <li class="mb-6 ml-6 last:mb-0">
                                    <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-400"></span>

                                    <p class="text-sm leading-6 text-gray-700">
                                        {{ $item }}
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-sm text-gray-500">
                            Data pendidikan belum tersedia.
                        </p>
                    @endif
                </div>
            </div>

            {{-- RIWAYAT PENUGASAN --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Riwayat Penugasan
                    </h3>
                </div>

                <div class="px-6 py-5">
                    @if (count($assignments))
                        <ol class="relative border-l border-gray-200">
                            @foreach ($assignments as $item)
                                <li class="mb-6 ml-6 last:mb-0">
                                    <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-400"></span>

                                    <p class="text-sm leading-6 text-gray-700">
                                        {{ $item }}
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-sm text-gray-500">
                            Data riwayat penugasan belum tersedia.
                        </p>
                    @endif
                </div>
            </div>

            {{-- RIWAYAT KARIER --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Riwayat Karier
                    </h3>
                </div>

                <div class="px-6 py-5">
                    @if (count($career))
                        <ol class="relative border-l border-gray-200">
                            @foreach ($career as $item)
                                <li class="mb-6 ml-6 last:mb-0">
                                    <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-400"></span>

                                    <p class="text-sm leading-6 text-gray-700">
                                        {{ $item }}
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-sm text-gray-500">
                            Data riwayat karier belum tersedia.
                        </p>
                    @endif
                </div>
            </div>

            {{-- PENGHARGAAN --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Penghargaan
                    </h3>
                </div>

                <div class="px-6 py-5">
                    @if (count($awards))
                        <ul class="space-y-3">
                            @foreach ($awards as $item)
                                <li class="flex gap-3 text-sm leading-6 text-gray-700">
                                    <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-gray-400"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">
                            Data penghargaan belum tersedia.
                        </p>
                    @endif
                </div>
            </div>

            {{-- INFORMASI SISTEM --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Informasi Sistem
                    </h3>
                </div>

                <div class="grid gap-5 px-6 py-5 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            ID
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            #{{ $official->id }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Status
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ $official->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Urutan
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ $official->sort_order ?? 0 }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Dibuat
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ $official->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- ACTION --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                <form
                    action="{{ route('officials.destroy', $official) }}"
                    method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pejabat ini?');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 transition hover:bg-red-100 sm:w-auto"
                    >
                        Hapus Pejabat
                    </button>
                </form>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a
                        href="{{ route('officials.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Kembali ke Daftar
                    </a>

                    <a
                        href="{{ route('officials.edit', $official) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-900"
                    >
                        Edit Data
                    </a>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>