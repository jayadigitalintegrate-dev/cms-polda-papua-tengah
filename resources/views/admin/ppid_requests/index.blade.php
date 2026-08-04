<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Permohonan Informasi PPID
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar permohonan informasi publik yang masuk melalui website Polda Papua Tengah.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                Total: {{ $ppidRequests->total() }} permohonan
            </div>

        </div>

    </x-slot>


    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUCCESS --}}
            @if (session('success'))

                <div class="mb-5 rounded-lg bg-green-100 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>

            @endif


            {{-- SEARCH & FILTER --}}
            <div class="bg-white shadow-sm sm:rounded-xl mb-6">

                <div class="p-5">

                    <form method="GET" action="{{ route('ppid-requests.index') }}">

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            {{-- SEARCH --}}
                            <div class="md:col-span-2">

                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cari Permohonan
                                </label>

                                <input id="search" type="search" name="search" value="{{ $search }}"
                                    placeholder="Tiket, nama, email, atau nomor identitas..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            </div>


                            {{-- STATUS --}}
                            <div>

                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Status
                                </label>

                                <select id="status" name="status"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                    <option value="">
                                        Semua Status
                                    </option>

                                    <option value="diterima" @selected($status === 'diterima')>
                                        Diterima
                                    </option>

                                    <option value="diverifikasi" @selected($status === 'diverifikasi')>
                                        Diverifikasi
                                    </option>

                                    <option value="diproses" @selected($status === 'diproses')>
                                        Diproses
                                    </option>

                                    <option value="selesai" @selected($status === 'selesai')>
                                        Selesai
                                    </option>

                                    <option value="ditolak" @selected($status === 'ditolak')>
                                        Ditolak
                                    </option>

                                </select>

                            </div>


                            {{-- BUTTON --}}
                            <div class="flex items-end gap-2">

                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                    Cari
                                </button>

                                <a href="{{ route('ppid-requests.index') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                                    Reset
                                </a>

                            </div>

                        </div>

                    </form>

                </div>

            </div>


            {{-- TABLE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl" x-data="{
                    selected: [],
                    allIds: @js($ppidRequests->pluck('id')->values()),

                    get isAllSelected() {
                        return this.selected.length === this.allIds.length && this.allIds.length > 0;
                    },

                    toggleAll(event) {
                        this.selected = event.target.checked ? [...this.allIds] : [];
                    },

                    submitDelete() {
                        if (this.selected.length === 0) return;
                        if (confirm('Yakin ingin menghapus data yang dipilih?')) {
                            this.$refs.deleteForm.submit();
                        }
                    },

                    submitExport() {
                        if (this.selected.length === 0) return;
                        this.$refs.exportForm.submit();
                    },

                    sendWhatsapp() {
                        if (this.selected.length === 0) {
                            alert('Silakan pilih minimal satu permohonan.');
                            return;
                        }

                        let pesan = '';
                        pesan += '====================================\n';
                        pesan += '      PPID POLDA PAPUA TENGAH\n';
                        pesan += '====================================\n\n';

                        this.selected.forEach((id, index) => {
                            const row = document.querySelector(`tr[data-id='${id}']`);
                            if (!row) return;

                            pesan += (index + 1) + '. PERMOHONAN PPID\n';
                            pesan += '------------------------------------\n';
                            pesan += 'Nomor Tiket : ' + row.dataset.ticket + '\n';
                            pesan += 'Nama        : ' + row.dataset.name + '\n';
                            pesan += 'Email       : ' + row.dataset.email + '\n';
                            pesan += 'Telepon     : ' + row.dataset.phone + '\n';
                            pesan += 'Status      : ' + row.dataset.status + '\n';
                            pesan += 'Informasi :\n';
                            pesan += row.dataset.information + '\n\n';
                        });

                        pesan += '====================================\n';
                        pesan += 'Dikirim melalui CMS PPID\n';
                        pesan += 'POLDA PAPUA TENGAH';

                        window.open(
                            'https://wa.me/?text=' + encodeURIComponent(pesan),
                            '_blank'
                        );
                    }
                }">

                {{-- BULK TOOLBAR --}}
                <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                        <div class="flex items-center gap-3">

                            <label class="inline-flex items-center gap-2">

                                <input type="checkbox" @change="toggleAll" :checked="isAllSelected"
                                    class="rounded border-gray-300 text-blue-600">

                                <span class="text-sm font-medium">
                                    Pilih Semua
                                </span>

                            </label>

                            <span x-show="selected.length" class="text-sm text-gray-500"
                                x-text="selected.length + ' data dipilih'"></span>

                        </div>

                        <div class="flex flex-wrap gap-2">

                            {{-- REMOVE --}}
                            <form x-ref="deleteForm" method="POST" action="{{ route('ppid-requests.bulk-delete') }}">

                                @csrf
                                @method('DELETE')

                                <template x-for="id in selected" :key="'delete-'+id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>

                                <button type="button" @click="submitDelete" :disabled="selected.length===0"
                                    :class="selected.length===0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700 text-white'"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold transition">
                                    Remove
                                </button>

                            </form>

                            {{-- SEND --}}
                            <button type="button" @click="sendWhatsapp" :disabled="selected.length===0"
                                :class="selected.length===0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700'"
                                class="rounded-lg px-4 py-2 font-semibold transition">
                                Send
                            </button>

                            {{-- EXPORT --}}
                            <form method="POST" x-ref="exportForm" action="{{ route('ppid-requests.export-pdf') }}">

                                @csrf

                                <template x-for="id in selected" :key="'export-'+id">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>

                                <button type="button" @click="submitExport" :disabled="selected.length===0"
                                    :class="selected.length===0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold transition">
                                    Export PDF
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="w-14 px-4 py-3 text-center"></th>

                                <th
                                    class="w-52 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Tiket
                                </th>

                                <th
                                    class="w-72 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Pemohon
                                </th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Informasi Yang Diminta
                                </th>

                                <th
                                    class="w-36 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Metode
                                </th>

                                <th
                                    class="w-40 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Status
                                </th>

                                <th
                                    class="w-36 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Tanggal
                                </th>

                                <th
                                    class="w-40 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-200">

                            @forelse ($ppidRequests as $ppidRequest)

                                <tr data-id="{{ $ppidRequest->id }}"
                                    data-ticket="{{ $ppidRequest->ticket }}"
                                    data-name="{{ $ppidRequest->name }}"
                                    data-email="{{ $ppidRequest->email }}"
                                    data-phone="{{ $ppidRequest->phone }}"
                                    data-status="{{ ucfirst($ppidRequest->status) }}"
                                    data-information="{{ strip_tags($ppidRequest->information) }}"
                                    class="hover:bg-gray-50"
                                    :class="selected.includes({{ $ppidRequest->id }}) ? 'bg-blue-50' : ''">

                                    {{-- CHECKBOX --}}
                                    <td class="px-4 py-4 text-center">

                                        <input type="checkbox" :value="{{ $ppidRequest->id }}" x-model="selected"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">

                                    </td>


                                    {{-- TIKET --}}
                                    <td class="px-6 py-4 whitespace-nowrap">

                                        <div class="font-semibold text-gray-900">
                                            {{ $ppidRequest->ticket }}
                                        </div>

                                    </td>


                                    {{-- PEMOHON --}}
                                    <td class="px-6 py-4">

                                        <div class="font-medium text-gray-900">
                                            {{ $ppidRequest->name }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $ppidRequest->email }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $ppidRequest->phone }}
                                        </div>

                                    </td>


                                    {{-- INFORMASI --}}
                                    <td class="px-6 py-4 max-w-sm">

                                        <div class="text-sm text-gray-700 line-clamp-2" title="{{ $ppidRequest->information }}">
                                            {{ $ppidRequest->information }}
                                        </div>

                                    </td>


                                    {{-- METODE --}}
                                    <td class="px-6 py-4 text-center align-middle">

                                        @if ($ppidRequest->delivery_method === 'softcopy')

                                            <span
                                                class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                                Softcopy
                                            </span>

                                        @elseif ($ppidRequest->delivery_method === 'hardcopy')

                                            <span
                                                class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">
                                                Hardcopy
                                            </span>

                                        @elseif ($ppidRequest->delivery_method === 'view')

                                            <span
                                                class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                                                Lihat Langsung
                                            </span>

                                        @else

                                            <span
                                                class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                                {{ ucfirst($ppidRequest->delivery_method) }}
                                            </span>

                                        @endif

                                    </td>


                                    {{-- STATUS --}}
                                    <td class="px-6 py-4 text-center align-middle">

                                        @if ($ppidRequest->status === 'diterima')

                                            <span
                                                class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                                Diterima
                                            </span>

                                        @elseif ($ppidRequest->status === 'diverifikasi')

                                            <span
                                                class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">
                                                Diverifikasi
                                            </span>

                                        @elseif ($ppidRequest->status === 'diproses')

                                            <span
                                                class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                                Diproses
                                            </span>

                                        @elseif ($ppidRequest->status === 'selesai')

                                            <span
                                                class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                                Selesai
                                            </span>

                                        @elseif ($ppidRequest->status === 'ditolak')

                                            <span
                                                class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                                Ditolak
                                            </span>

                                        @else

                                            <span
                                                class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                                {{ ucfirst($ppidRequest->status) }}
                                            </span>

                                        @endif

                                    </td>


                                    {{-- TANGGAL --}}
                                    <td class="px-6 py-4 text-center">

                                        <div>
                                            {{ $ppidRequest->created_at?->format('d/m/Y') }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $ppidRequest->created_at?->format('H:i') }}
                                        </div>

                                    </td>


                                    {{-- AKSI --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">

                                        <a href="{{ route('ppid-requests.show', $ppidRequest) }}"
                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                                            Lihat Detail
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">

                                        @if ($search || $status)

                                            Tidak ditemukan permohonan yang sesuai dengan pencarian/filter.

                                        @else

                                            Belum ada permohonan informasi PPID yang masuk.

                                        @endif

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- PAGINATION --}}
                @if ($ppidRequests->hasPages())

                    <div class="border-t border-gray-200 px-6 py-4">

                        {{ $ppidRequests->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>