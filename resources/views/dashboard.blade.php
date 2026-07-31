<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            CMS Polda Papua Tengah
        </h2>
    </x-slot>

```
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <h3 class="text-2xl font-bold mb-2">
                    Selamat Datang di CMS Polda Papua Tengah
                </h3>

                <p>
                    Halo, {{ auth()->user()->name }}.
                    Anda login sebagai
                    <strong>{{ auth()->user()->role }}</strong>.
                </p>
            </div>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-600">
                    Berita
                </h4>
                <p class="text-3xl font-bold mt-2">
                    0
                </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-600">
                    Pengumuman
                </h4>
                <p class="text-3xl font-bold mt-2">
                    0
                </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-600">
                    Galeri
                </h4>
                <p class="text-3xl font-bold mt-2">
                    0
                </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-600">
                    Pengguna
                </h4>
                <p class="text-3xl font-bold mt-2">
                    1
                </p>
            </div>

        </div>


        <div class="mt-8 bg-white p-6 rounded-lg shadow">

            <h3 class="text-lg font-bold mb-4">
                Menu Cepat
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <a href="{{ route('news.index') }}"
                   class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Kelola Berita
                </a>

                <a href="#"
                   class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Kelola Pengumuman
                </a>

                <a href="#"
                   class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Kelola Galeri
                </a>

            </div>

        </div>

    </div>
</div>
```

</x-app-layout>
