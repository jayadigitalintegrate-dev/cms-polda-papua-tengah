<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Jajaran Polres
            </h2>

            <div class="flex gap-2">
                <a href="{{ route('police-stations.edit', $policeStation) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Edit
                </a>

                <a href="{{ route('police-stations.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                        <div>
                            @if($policeStation->chief_photo)
                                <img
                                    src="{{ asset('storage/' . $policeStation->chief_photo) }}"
                                    alt="{{ $policeStation->name_id }}"
                                    class="w-full rounded-xl object-cover"
                                >
                            @else
                                <div class="w-full aspect-square bg-gray-100 rounded-xl flex items-center justify-center text-gray-500">
                                    Tidak ada foto
                                </div>
                            @endif
                        </div>

                        <div class="md:col-span-2 space-y-5">

                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">
                                    {{ $policeStation->name_id }}
                                </h1>

                                @if($policeStation->name_en)
                                    <p class="text-gray-500">
                                        {{ $policeStation->name_en }}
                                    </p>
                                @endif
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Kapolres</p>
                                <p class="font-semibold text-gray-800">
                                    {{ $policeStation->chief_rank }}
                                    {{ $policeStation->chief_name }}
                                </p>
                            </div>

                            @if($policeStation->chief_nrp)
                                <div>
                                    <p class="text-sm text-gray-500">NRP</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->chief_nrp }}
                                    </p>
                                </div>
                            @endif

                            @if($policeStation->jurisdiction)
                                <div>
                                    <p class="text-sm text-gray-500">Wilayah Hukum</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->jurisdiction }}
                                    </p>
                                </div>
                            @endif

                            @if($policeStation->address)
                                <div>
                                    <p class="text-sm text-gray-500">Alamat</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->address }}
                                    </p>
                                </div>
                            @endif

                            @if($policeStation->phone)
                                <div>
                                    <p class="text-sm text-gray-500">Telepon</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->phone }}
                                    </p>
                                </div>
                            @endif

                            @if($policeStation->email)
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->email }}
                                    </p>
                                </div>
                            @endif

                            @if($policeStation->website)
                                <div>
                                    <p class="text-sm text-gray-500">Website</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->website }}
                                    </p>
                                </div>
                            @endif

                            @if($policeStation->description)
                                <div>
                                    <p class="text-sm text-gray-500">Keterangan</p>
                                    <p class="text-gray-800">
                                        {{ $policeStation->description }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <p class="text-sm text-gray-500">Status</p>

                                @if($policeStation->status === 'active')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-700">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
