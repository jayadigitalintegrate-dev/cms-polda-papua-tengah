<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-800">
                    Dokumen PPID
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola seluruh Dokumen PPID Polda Papua Tengah
                </p>

            </div>

            <a
                href="{{ route('ppid-documents.create') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">

                + Tambah Dokumen

            </a>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow">

                <div class="border-b p-6">

                    <form
                        method="GET"
                        action="{{ route('ppid-documents.index') }}"
                        class="grid grid-cols-1 gap-4 md:grid-cols-4">

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari dokumen..."
                            class="rounded-lg border-gray-300">

                        <select
                            name="category"
                            class="rounded-lg border-gray-300">

                            <option value="">
                                Semua Kategori
                            </option>

                            @foreach($categories as $category)

                                <option
                                    value="{{ $category->id }}"
                                    @selected(request('category')==$category->id)>

                                    {{ $category->name }}

                                </option>

                            @endforeach

                        </select>

                        <select
                            name="status"
                            class="rounded-lg border-gray-300">

                            <option value="">Semua Status</option>

                            <option
                                value="published"
                                @selected(request('status')=='published')>
                                Published
                            </option>

                            <option
                                value="draft"
                                @selected(request('status')=='draft')>
                                Draft
                            </option>

                        </select>

                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">

                            Cari

                        </button>

                    </form>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="w-12 px-4 py-3 text-center">
    <input
        id="check-all"
        type="checkbox"
        class="h-4 w-4 rounded border-gray-300 text-blue-600">
</th>
<th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-center">Tahun</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Download</th>
                                <th class="px-4 py-3 text-center">View</th>
                                <th class="px-4 py-3 text-center">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($documents as $document)

                                <tr class="border-t hover:bg-gray-50">

                                    
                                    <td class="px-4 py-4 text-center">
                                        <input
                                            type="checkbox"
                                            class="row-check h-4 w-4 rounded border-gray-300 text-blue-600"
                                            name="ids[]"
                                            value="{{ $document->id }}">
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $document->title }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ optional($document->category)->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        {{ $document->publication_year ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center">

                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            {{ $document->status == 'published'
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-yellow-100 text-yellow-700' }}">

                                            {{ ucfirst($document->status) }}

                                        </span>

                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        {{ $document->download_count }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        {{ $document->view_count }}
                                    </td>

                                    <td class="px-4 py-4">

                                        <div class="relative z-10 flex justify-center gap-2">

                                            <a
                                                href="{{ route('ppid-documents.show', $document) }}"
                                                class="relative z-20 cursor-pointer rounded bg-sky-600 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-sky-700">

                                                Detail

                                            </a>

                                            <a
                                                href="{{ route('ppid-documents.edit', $document) }}"
                                                class="relative z-20 cursor-pointer rounded bg-amber-500 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-amber-600">

                                                Edit

                                            </a>

                                            <form
                                                action="{{ route('ppid-documents.destroy', $document) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="w-full rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">

                                                    Hapus

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="py-10 text-center text-gray-500">

                                        Belum ada Dokumen PPID.

                                    </td>

                                </tr>

                            @endforelse


                        </tbody>

                    </table>

                </div>

                <div class="border-t p-6">

                    {{ $documents->links() }}

                </div>

            </div>

        </div>

    </div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const checkAll = document.getElementById('check-all');
    const rowChecks = () => document.querySelectorAll('.row-check');

    if (!checkAll) {
        return;
    }

    checkAll.addEventListener('change', function () {

        rowChecks().forEach(function (checkbox) {
            checkbox.checked = checkAll.checked;
        });

    });

    document.addEventListener('change', function (event) {

        if (!event.target.classList.contains('row-check')) {
            return;
        }

        const checks = Array.from(rowChecks());

        checkAll.checked =
            checks.length > 0 &&
            checks.every(function (checkbox) {
                return checkbox.checked;
            });

        checkAll.indeterminate =
            checks.some(function (checkbox) {
                return checkbox.checked;
            }) &&
            !checkAll.checked;

    });

});
</script>

</x-app-layout>

