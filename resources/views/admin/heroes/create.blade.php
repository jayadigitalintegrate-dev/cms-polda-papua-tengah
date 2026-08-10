<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    Upload Hero
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan atau ganti seluruh Hero yang tampil di website Polda Papua Tengah.
                </p>
            </div>

            <a
                href="{{ route('heroes.index') }}"
                class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
            >
                Kembali
            </a>
        </div>
    </x-slot>


    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            <form
                id="heroUploadForm"
                method="POST"
                action="{{ route('heroes.store') }}"
                enctype="multipart/form-data"
            >

                @csrf

                {{-- MODE --}}
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Pilih Tindakan
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Tentukan apakah gambar akan ditambahkan atau menggantikan seluruh Hero.
                    </p>


                    <div class="mt-5 grid gap-4 md:grid-cols-2">

                        {{-- TAMBAH --}}
                        <label
                            id="modeAddCard"
                            class="mode-card cursor-pointer rounded-xl border-2 border-indigo-500 bg-indigo-50 p-5"
                        >

                            <div class="flex items-start gap-3">

                                <input
                                    type="radio"
                                    name="mode"
                                    value="add"
                                    checked
                                    class="mt-1"
                                >

                                <div>
                                    <div class="font-semibold text-gray-900">
                                        Tambah Hero
                                    </div>

                                    <div class="mt-1 text-sm text-gray-600">
                                        Hero yang sudah ada tetap dipertahankan.
                                        Gambar baru hanya ditambahkan ke slider website.
                                    </div>
                                </div>

                            </div>

                        </label>


                        {{-- GANTI SEMUA --}}
                        <label
                            id="modeReplaceCard"
                            class="mode-card cursor-pointer rounded-xl border-2 border-gray-200 bg-white p-5"
                        >

                            <div class="flex items-start gap-3">

                                <input
                                    type="radio"
                                    name="mode"
                                    value="replace_all"
                                    class="mt-1"
                                >

                                <div>
                                    <div class="font-semibold text-gray-900">
                                        Ganti Semua Hero
                                    </div>

                                    <div class="mt-1 text-sm text-gray-600">
                                        Semua Hero lama akan diganti dengan gambar yang baru diupload.
                                    </div>

                                </div>

                            </div>

                        </label>

                    </div>


                    {{-- WARNING --}}
                    <div
                        id="replaceWarning"
                        class="mt-5 hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                    >
                        <strong>Perhatian:</strong>
                        mode ini akan menghapus seluruh Hero lama dari website
                        dan menggantinya dengan gambar yang Anda pilih.
                    </div>

                </div>


                {{-- UPLOAD --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-200 p-6">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Gambar Hero
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Pilih satu atau beberapa gambar sekaligus.
                            Format JPG, JPEG, PNG, atau WebP.
                            Maksimal 5 MB per gambar.
                        </p>

                    </div>


                    <div class="p-6">

                        <label
                            for="heroImages"
                            class="flex min-h-[190px] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 text-center transition hover:border-indigo-400 hover:bg-indigo-50"
                        >

                            <svg
                                class="mb-4 h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M7 16a4 4 0 01-.88-7.903A5.002 5.002 0 0117.9 6H18a4 4 0 010 8h-1m-5-4v8m0-8l-3 3m3-3l3 3"
                                />
                            </svg>

                            <span class="font-semibold text-gray-900">
                                Klik untuk memilih gambar Hero
                            </span>

                            <span class="mt-1 text-sm text-gray-500">
                                Bisa memilih satu atau beberapa gambar sekaligus
                            </span>

                            <input
                                id="heroImages"
                                name="images[]"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                                required
                                class="hidden"
                            >

                        </label>


                        <div
                            id="fileInfo"
                            class="mt-4 hidden rounded-lg bg-gray-50 p-4 text-sm text-gray-600"
                        ></div>


                        {{-- PREVIEW --}}
                        <div
                            id="previewSection"
                            class="mt-6 hidden"
                        >

                            <div class="mb-3 flex items-center justify-between">

                                <div>
                                    <h4 class="font-semibold text-gray-900">
                                        Preview Hero
                                    </h4>

                                    <p class="text-sm text-gray-500">
                                        Periksa gambar sebelum diproses.
                                    </p>
                                </div>

                            </div>


                            <div
                                id="previewGrid"
                                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                            ></div>

                        </div>

                    </div>


                    {{-- STATUS --}}
                    <div class="border-t border-gray-200 p-6">

                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-900"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="mt-2 w-full max-w-md rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        >

                            <option value="active">
                                Aktif — tampil di website
                            </option>

                            <option value="inactive">
                                Nonaktif — tidak tampil di website
                            </option>

                        </select>

                        <p class="mt-2 text-xs text-gray-500">
                            Hero dengan status aktif akan tersedia untuk slider Hero website.
                        </p>

                    </div>


                    {{-- ACTION --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 p-6">

                        <a
                            href="{{ route('heroes.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        >
                            Batal
                        </a>

                        <button
                            id="previewButton"
                            type="button"
                            disabled
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Preview & Publish
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>


    {{-- CONFIRMATION MODAL --}}
    <div
        id="publishModal"
        class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4"
    >

        <div class="flex min-h-full items-center justify-center">

            <div class="w-full max-w-5xl rounded-2xl bg-white shadow-2xl">

                <div class="flex items-center justify-between border-b border-gray-200 p-5">

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Preview Hero
                        </h3>

                        <p
                            id="modalDescription"
                            class="mt-1 text-sm text-gray-500"
                        ></p>
                    </div>

                    <button
                        id="closeModalButton"
                        type="button"
                        class="rounded-lg px-3 py-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                    >
                        ✕
                    </button>

                </div>


                <div class="max-h-[65vh] overflow-y-auto p-6">

                    <div
                        id="modalPreviewGrid"
                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                    ></div>

                </div>


                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 p-5 sm:flex-row sm:justify-end">

                    <button
                        id="cancelPublishButton"
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                    >
                        Kembali & Periksa
                    </button>

                    <button
                        id="confirmPublishButton"
                        type="button"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                    >
                        Ya, Publish Hero
                    </button>

                </div>

            </div>

        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const form = document.getElementById('heroUploadForm');
            const input = document.getElementById('heroImages');
            const previewButton = document.getElementById('previewButton');

            const previewSection = document.getElementById('previewSection');
            const previewGrid = document.getElementById('previewGrid');
            const modalPreviewGrid = document.getElementById('modalPreviewGrid');

            const fileInfo = document.getElementById('fileInfo');

            const modal = document.getElementById('publishModal');
            const modalDescription = document.getElementById('modalDescription');

            const closeModalButton = document.getElementById('closeModalButton');
            const cancelPublishButton = document.getElementById('cancelPublishButton');
            const confirmPublishButton = document.getElementById('confirmPublishButton');

            const modeAddCard = document.getElementById('modeAddCard');
            const modeReplaceCard = document.getElementById('modeReplaceCard');
            const replaceWarning = document.getElementById('replaceWarning');

            let selectedFiles = [];


            function getMode() {
                const checked = document.querySelector(
                    'input[name="mode"]:checked'
                );

                return checked ? checked.value : 'add';
            }


            function updateModeUI() {

                const mode = getMode();

                if (mode === 'replace_all') {

                    modeReplaceCard.classList.remove(
                        'border-gray-200',
                        'bg-white'
                    );

                    modeReplaceCard.classList.add(
                        'border-red-500',
                        'bg-red-50'
                    );

                    modeAddCard.classList.remove(
                        'border-indigo-500',
                        'bg-indigo-50'
                    );

                    modeAddCard.classList.add(
                        'border-gray-200',
                        'bg-white'
                    );

                    replaceWarning.classList.remove('hidden');

                } else {

                    modeAddCard.classList.remove(
                        'border-gray-200',
                        'bg-white'
                    );

                    modeAddCard.classList.add(
                        'border-indigo-500',
                        'bg-indigo-50'
                    );

                    modeReplaceCard.classList.remove(
                        'border-red-500',
                        'bg-red-50'
                    );

                    modeReplaceCard.classList.add(
                        'border-gray-200',
                        'bg-white'
                    );

                    replaceWarning.classList.add('hidden');
                }
            }


            function formatSize(bytes) {

                if (bytes < 1024 * 1024) {
                    return (bytes / 1024).toFixed(1) + ' KB';
                }

                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }


            function renderPreview() {

                previewGrid.innerHTML = '';
                modalPreviewGrid.innerHTML = '';

                if (selectedFiles.length === 0) {

                    previewSection.classList.add('hidden');
                    previewButton.disabled = true;

                    fileInfo.classList.add('hidden');

                    return;
                }


                previewSection.classList.remove('hidden');
                previewButton.disabled = false;

                fileInfo.classList.remove('hidden');

                fileInfo.textContent =
                    selectedFiles.length +
                    ' gambar dipilih untuk Hero.';


                selectedFiles.forEach((file, index) => {

                    const objectUrl = URL.createObjectURL(file);


                    // Preview kecil
                    const card = document.createElement('div');

                    card.className =
                        'overflow-hidden rounded-xl border border-gray-200 bg-white';


                    const image = document.createElement('img');

                    image.src = objectUrl;

                    image.alt = 'Preview Hero ' + (index + 1);

                    image.className =
                        'h-40 w-full object-cover';


                    const info = document.createElement('div');

                    info.className = 'p-3';

                    info.innerHTML = `
                        <div class="truncate text-sm font-semibold text-gray-900">
                            ${file.name}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            ${formatSize(file.size)}
                        </div>
                    `;


                    card.appendChild(image);
                    card.appendChild(info);

                    previewGrid.appendChild(card);


                    // Preview modal
                    const modalCard = document.createElement('div');

                    modalCard.className =
                        'overflow-hidden rounded-xl border border-gray-200 bg-gray-50';


                    const modalImage = document.createElement('img');

                    modalImage.src = objectUrl;

                    modalImage.alt = 'Hero ' + (index + 1);

                    modalImage.className =
                        'aspect-video w-full object-cover';


                    const modalInfo = document.createElement('div');

                    modalInfo.className = 'p-3';

                    modalInfo.innerHTML = `
                        <div class="text-sm font-semibold text-gray-900">
                            Hero ${index + 1}
                        </div>
                        <div class="mt-1 truncate text-xs text-gray-500">
                            ${file.name}
                        </div>
                    `;


                    modalCard.appendChild(modalImage);
                    modalCard.appendChild(modalInfo);

                    modalPreviewGrid.appendChild(modalCard);
                });
            }


            input.addEventListener('change', function () {

                selectedFiles = Array.from(input.files);

                renderPreview();
            });


            document
                .querySelectorAll('input[name="mode"]')
                .forEach(function (radio) {

                    radio.addEventListener('change', updateModeUI);

                });


            previewButton.addEventListener('click', function () {

                if (selectedFiles.length === 0) {
                    return;
                }

                const mode = getMode();

                if (mode === 'replace_all') {

                    modalDescription.textContent =
                        'Anda memilih Ganti Semua Hero. Hero lama akan diganti dengan ' +
                        selectedFiles.length +
                        ' gambar ini.';

                    confirmPublishButton.textContent =
                        'Ya, Ganti Semua Hero';

                } else {

                    modalDescription.textContent =
                        'Anda memilih Tambah Hero. ' +
                        selectedFiles.length +
                        ' gambar ini akan ditambahkan ke Hero yang sudah ada.';

                    confirmPublishButton.textContent =
                        'Ya, Tambahkan Hero';
                }


                modal.classList.remove('hidden');

                document.body.classList.add('overflow-hidden');
            });


            function closeModal() {

                modal.classList.add('hidden');

                document.body.classList.remove('overflow-hidden');
            }


            closeModalButton.addEventListener(
                'click',
                closeModal
            );

            cancelPublishButton.addEventListener(
                'click',
                closeModal
            );


            confirmPublishButton.addEventListener('click', function () {

                confirmPublishButton.disabled = true;

                confirmPublishButton.textContent =
                    'Memproses...';

                form.submit();

            });


            modal.addEventListener('click', function (event) {

                if (event.target === modal) {
                    closeModal();
                }

            });


            updateModeUI();

        });
    </script>

</x-app-layout>