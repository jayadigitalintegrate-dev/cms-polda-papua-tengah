<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <!-- LEFT SIDE -->
            <div class="flex">

                <!-- Logo / Application Name -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- DASHBOARD --}}
                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                    >
                        Dashboard
                    </x-nav-link>


                    {{-- ===================================================== --}}
                    {{-- BERITA --}}
                    {{-- ===================================================== --}}

                    <x-dropdown align="left" width="56">

                        <x-slot name="trigger">

                            <button
                                class="inline-flex items-center self-stretch px-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('news.*')
                                    ? 'border-indigo-400 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            >

                                <span>Berita</span>

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

                            {{-- Dashboard Berita --}}
                            <x-dropdown-link
                                :href="route('news.index')"
                            >
                                Dashboard Berita
                            </x-dropdown-link>


                            {{-- KATEGORI BERITA --}}
                            <div class="border-t border-gray-100 my-1"></div>

                            <div
                                class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                            >
                                Kategori Berita
                            </div>


                            {{-- Berita Utama --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'berita-utama'])"
                            >
                                Berita Utama
                            </x-dropdown-link>


                            {{-- Berita / Artikel --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'berita'])"
                            >
                                Berita / Artikel
                            </x-dropdown-link>


                            {{-- Press Release --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'press-release'])"
                            >
                                Press Release
                            </x-dropdown-link>


                            {{-- Himbauan --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'himbauan'])"
                            >
                                Himbauan
                            </x-dropdown-link>


                            {{-- Kegiatan --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'kegiatan'])"
                            >
                                Kegiatan
                            </x-dropdown-link>


                            {{-- Prestasi --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'prestasi'])"
                            >
                                Prestasi
                            </x-dropdown-link>


                            {{-- Lalu Lintas --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'lalu-lintas'])"
                            >
                                Lalu Lintas
                            </x-dropdown-link>


                            {{-- Kriminal --}}
                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'kriminal'])"
                            >
                                Kriminal
                            </x-dropdown-link>


                            {{-- BERITA VIDEO --}}
                            <div class="border-t border-gray-100 my-1"></div>

                            <x-dropdown-link
                                :href="route('news.index', ['category' => 'video'])"
                            >
                                Berita Video
                            </x-dropdown-link>
                          {{-- HERO --}}
                          <div class="border-t border-gray-100 my-1"></div>

                          <x-dropdown-link
                              :href="route('heroes.index')"
                              :active="request()->routeIs('heroes.*')"
                          >
                              Hero
                          </x-dropdown-link>

                        </x-slot>

                    </x-dropdown>


                    {{-- ===================================================== --}}
                    {{-- PENGADUAN --}}
                    {{-- ===================================================== --}}

                    <x-nav-link
                        :href="route('complaints.index')"
                        :active="request()->routeIs('complaints.*')"
                    >
                        Pengaduan
                    </x-nav-link>


                    {{-- ===================================================== --}}
                    {{-- PENGUMUMAN --}}
                    {{-- ===================================================== --}}

                    <x-dropdown align="left" width="56">

                        <x-slot name="trigger">

                            <button
                                class="inline-flex items-center self-stretch px-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('announcements.*')
                                    ? 'border-indigo-400 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            >

                                <span>Pengumuman</span>

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

                            <x-dropdown-link
                                :href="route('announcements.index')"
                            >
                                Semua Pengumuman
                            </x-dropdown-link>

                            <x-dropdown-link
                                :href="route('announcements.index', ['type' => 'popup'])"
                            >
                                Pengumuman Popup
                            </x-dropdown-link>

                        </x-slot>

                    </x-dropdown>


                    {{-- ===================================================== --}}
                    {{-- PPID --}}
                    {{-- ===================================================== --}}

                    <x-dropdown align="left" width="56">

                        <x-slot name="trigger">

                            <button
                                class="inline-flex items-center self-stretch px-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('ppid-*.*')
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

                            {{-- Permohonan Informasi --}}
                            <x-dropdown-link
                                :href="route('ppid-requests.index')"
                            >
                                Permohonan Informasi
                            </x-dropdown-link>


                            {{-- Kategori PPID --}}
                            <x-dropdown-link
                                :href="route('ppid-categories.index')"
                            >
                                Kategori PPID
                            </x-dropdown-link>


                            {{-- Dokumen PPID --}}
                            <x-dropdown-link
                                :href="route('ppid-documents.index')"
                            >
                                Dokumen PPID
                            </x-dropdown-link>

                        </x-slot>

                    </x-dropdown>


                    {{-- ===================================================== --}}
                    {{-- GALERI --}}
                    {{-- ===================================================== --}}

                    <x-dropdown align="left" width="56">

                        <x-slot name="trigger">

                            <button
                                class="inline-flex items-center self-stretch px-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('galleries.*') || request()->routeIs('gallery-categories.*')
                                    ?'border-indigo-400 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            >

                                <span>Galeri</span>

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

                            {{-- Item Galeri --}}
                            <x-dropdown-link
                                :href="route('galleries.index')"
                            >
                                Galeri
                            </x-dropdown-link>


                            {{-- Kategori Galeri --}}
                            <x-dropdown-link
                                :href="route('gallery-categories.index')"
                            >
                                Kategori
                            </x-dropdown-link>

                        </x-slot>

                    </x-dropdown>


                    {{-- ===================================================== --}}
                    {{-- ===================================================== --}}
                    {{-- KONTAK --}}
                    <x-nav-link
                        :href="route('contact.edit')"
                        :active="request()->routeIs('contact.*')"
                    >
                        Kontak
                    </x-nav-link>

                    {{-- PEJABAT --}}
                    {{-- ===================================================== --}}

                    <x-dropdown align="left" width="56">

                        <x-slot name="trigger">

                            <button
                                class="inline-flex items-center self-stretch px-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('officials.*') || request()->routeIs('police-stations.*')
                                    ? 'border-indigo-400 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            >

                                <span>Pejabat</span>

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

                            {{-- Pejabat Polda Papua Tengah --}}
                            <x-dropdown-link
                                :href="route('officials.index')"
                            >
                                Pejabat Polda Papua Tengah
                            </x-dropdown-link>

                            {{-- Jajaran Polres Wilayah Hukum Papua Tengah --}}
                            <x-dropdown-link
                                :href="route('police-stations.index')"
                            >
                                Jajaran Polres Wilayah Hukum Papua Tengah
                            </x-dropdown-link>

                        </x-slot>

                    </x-dropdown>



                </div>
            </div>


            <!-- RIGHT SIDE / ACCOUNT -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                        >

                            <span class="text-xs text-gray-400">
                                {{ Auth::user()->role }}
                            </span>

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

                        {{-- Profile --}}
                        <x-dropdown-link
                            :href="route('profile.edit')"
                        >
                            Profile
                        </x-dropdown-link>


                        {{-- Kontak --}}
                        <x-dropdown-link
                            :href="route('contact.edit')"
                        >
                            Kontak
                        </x-dropdown-link>



                {{-- Settings --}}
                        @if (Auth::user()?->role === 'superadmin')

                            <x-dropdown-link
                                :href="route('settings.edit')"
                            >
                                Settings
                            </x-dropdown-link>

                        @endif


                        {{-- Logout --}}
                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >

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


            <!-- MOBILE MENU BUTTON -->
            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                >

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >

                        <path
                            :class="{
                                'hidden': open,
                                'inline-flex': ! open
                            }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{
                                'hidden': ! open,
                                'inline-flex': open
                            }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />

                    </svg>

                </button>

            </div>

        </div>

    </div>


    <!-- =============================================================== -->
        <!-- SECONDARY NAVIGATION / ADMINISTRATION -->
        @if (Auth::user()?->role === 'superadmin')
            <div class="hidden sm:flex items-center border-t border-gray-100 min-h-10">
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-end">

                        <x-dropdown align="right" width="56">

                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-1 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                {{ request()->routeIs('users.*') || request()->routeIs('settings.*')
                                    ? 'text-gray-900'
                                    : 'text-gray-500 hover:text-gray-700' }}"
                            >
                                <span>Administrasi</span>

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

                            {{-- Manajemen User --}}
                            <x-dropdown-link
                                :href="route('users.index')"
                            >
                                Manajemen User
                            </x-dropdown-link>

                            {{-- Pengaturan --}}
                            <x-dropdown-link
                                :href="route('settings.edit')"
                            >
                                Pengaturan
                            </x-dropdown-link>

                        </x-slot>

                        </x-dropdown>

                    </div>
                </div>
            </div>
        @endif

    <!-- MOBILE NAVIGATION -->
    <!-- =============================================================== -->

    <div
        :class="{
            'block': open,
            'hidden': ! open
        }"
        class="hidden sm:hidden border-t border-gray-100"
    >

        <div class="pt-2 pb-3 space-y-1">

            {{-- Dashboard --}}
            <x-responsive-nav-link
                :href="route('dashboard')"
                :active="request()->routeIs('dashboard')"
            >
                Dashboard
            </x-responsive-nav-link>


            {{-- Berita --}}
            <x-responsive-nav-link
                :href="route('news.index')"
                :active="request()->routeIs('news.*')"
            >
                Berita
            </x-responsive-nav-link>


            {{-- Pengaduan --}}
            <x-responsive-nav-link
                :href="route('complaints.index')"
                :active="request()->routeIs('complaints.*')"
            >
                Pengaduan
            </x-responsive-nav-link>

            {{-- Pengumuman --}}
            <x-responsive-nav-link
                :href="route('announcements.index')"
                :active="request()->routeIs('announcements.*')"
            >
                Pengumuman
            </x-responsive-nav-link>


            {{-- Pejabat --}}
        <div class="space-y-1">

            <div class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Pejabat
            </div>

            <x-responsive-nav-link
                :href="route('officials.index')"
                :active="request()->routeIs('officials.*')"
            >
                Pejabat Polda Papua Tengah
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('police-stations.index')"
                :active="request()->routeIs('police-stations.*')"
            >
                Jajaran Polres Wilayah Hukum Papua Tengah
            </x-responsive-nav-link>

        </div>

        {{-- PPID Requests --}}
            <x-responsive-nav-link
                :href="route('ppid-requests.index')"
                :active="request()->routeIs('ppid-requests.*')"
            >
                Permohonan PPID
            </x-responsive-nav-link>


            {{-- PPID Categories --}}
            <x-responsive-nav-link
                :href="route('ppid-categories.index')"
                :active="request()->routeIs('ppid-categories.*')"
            >
                Kategori PPID
            </x-responsive-nav-link>


            {{-- PPID Documents --}}
            <x-responsive-nav-link
                :href="route('ppid-documents.index')"
                :active="request()->routeIs('ppid-documents.*')"
            >
                Dokumen PPID
            </x-responsive-nav-link>


            {{-- Galeri --}}
            <div class="space-y-1">

                <div class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    Galeri
                </div>

                <x-responsive-nav-link
                    :href="route('galleries.index')"
                    :active="request()->routeIs('galleries.*')"
                >
                    Galeri
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('gallery-categories.index')"
                    :active="request()->routeIs('gallery-categories.*')"
                >
                    Kategori
                </x-responsive-nav-link>

            </div>


            {{-- User Management --}}
            @if (Auth::user()?->role === 'superadmin')

                <x-responsive-nav-link
                    :href="route('users.index')"
                    :active="request()->routeIs('users.*')"
                >
                    Manajemen User
                </x-responsive-nav-link>

            @endif

        </div>


        <!-- Mobile Account -->
        <div class="pt-4 pb-1 border-t border-gray-200">

            <div class="px-4">

                <div class="font-medium text-base text-gray-800">
                    {{ Auth::user()->name }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </div>

                <div class="mt-1 text-xs text-gray-400">
                    Role: {{ Auth::user()->role }}
                </div>

            </div>


            <div class="mt-3 space-y-1">

                {{-- Profile --}}
                <x-responsive-nav-link
                    :href="route('profile.edit')"
                >
                    Profile
                </x-responsive-nav-link>


                {{-- Kontak --}}
                <x-responsive-nav-link
                    :href="route('contact.edit')"
                >
                    Kontak
                </x-responsive-nav-link>


                {{-- Settings --}}
                @if (Auth::user()?->role === 'superadmin')

                    <x-responsive-nav-link
                        :href="route('settings.edit')"
                    >
                        Settings
                    </x-responsive-nav-link>

                @endif


                {{-- Logout --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        Log Out
                    </x-responsive-nav-link>

                </form>

            </div>

        </div>

    </div>

</nav>
