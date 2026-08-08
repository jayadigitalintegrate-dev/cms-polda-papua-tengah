<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <div class="flex">

                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- Dashboard --}}
                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                    >
                        Dashboard
                    </x-nav-link>

                    {{-- Berita --}}
                    <x-nav-link
                        :href="route('news.index')"
                        :active="request()->routeIs('news.*')"
                    >
                        Berita
                    </x-nav-link>

                    {{-- Pengaduan --}}
                    <x-nav-link
                        :href="route('complaints.index')"
                        :active="request()->routeIs('complaints.*')"
                    >
                        Pengaduan
                    </x-nav-link>

                    {{-- PPID DROPDOWN --}}
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center self-stretch px-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('ppid-requests.*') || request()->routeIs('ppid-categories.*') || request()->routeIs('ppid-documents.*')
                                    ? 'border-indigo-400 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            >
                                <span>PPID</span>

                                <svg
                                    class="ms-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">

                            <x-dropdown-link :href="route('ppid-requests.index')">
                                Permohonan Informasi
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('ppid-categories.index')">
                                Kategori PPID
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('ppid-documents.index')">
                                Dokumen PPID
                            </x-dropdown-link>

                        </x-slot>
                    </x-dropdown>

                    {{-- Pengumuman --}}
                    <x-nav-link href="#">
                        Pengumuman
                    </x-nav-link>

                    {{-- Galeri --}}
                    <x-nav-link href="#">
                        Galeri
                    </x-nav-link>

                    {{-- Pejabat --}}
                    <x-nav-link href="#">
                        Pejabat
                    </x-nav-link>

                </div>

            </div>


            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700">

                            <div>
                                {{ Auth::user()->name }}
                            </div>

                            <div class="ms-1">
                                ({{ Auth::user()->role }})
                            </div>

                        </button>

                    </x-slot>


                    <x-slot name="content">

                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>


                        <form method="POST" action="{{ route('logout') }}">

                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                            >
                                Log Out
                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>


            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400"
                >

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >

                        <path
                            :class="{'hidden': open, 'inline-flex': ! open}"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                    </svg>

                </button>

            </div>

        </div>

    </div>
</nav>
