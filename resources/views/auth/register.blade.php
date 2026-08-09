<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

          <!-- Password -->
    <div
        class="mt-4"
        x-data="{
            show: false,
            password: '',

            get score() {
                let score = 0;

                if (this.password.length >= 8) score++;
                if (/[a-z]/.test(this.password)) score++;
                if (/[A-Z]/.test(this.password)) score++;
                if (/[0-9]/.test(this.password)) score++;
                if (/[^A-Za-z0-9]/.test(this.password)) score++;

                return score;
            },

            get label() {
                if (!this.password) return '';
                if (this.score <= 2) return 'Password lemah';
                if (this.score <= 3) return 'Password sedang';
                return 'Password kuat';
            },

            get barClass() {
                if (!this.password) return 'bg-gray-200';
                if (this.score <= 2) return 'bg-red-500';
                if (this.score <= 3) return 'bg-yellow-500';
                return 'bg-green-500';
            },

            get textClass() {
                if (!this.password) return 'text-gray-500';
                if (this.score <= 2) return 'text-red-600';
                if (this.score <= 3) return 'text-yellow-600';
                return 'text-green-600';
            }
        }"
    >
        <x-input-label for="password" :value="__('Password')" />

        <div class="relative mt-1">
            <x-text-input
                id="password"
                class="block mt-1 w-full pe-12"
                x-model="password"
                x-bind:type="show ? 'text' : 'password'"
                name="password"
                required
                autocomplete="new-password"
            />

            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
            >
                {{-- Mata tertutup --}}
                <svg
                    x-show="!show"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                    <circle cx="12" cy="12" r="3" />
                </svg>

                {{-- Mata terbuka --}}
                <svg
                    x-show="show"
                    x-cloak
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 4.24A9.953 9.953 0 0112 4c4.477 0 8.268 2.943 9.542 7a10.06 10.06 0 01-4.132 5.264M6.228 6.228A10.06 10.06 0 002.458 12c.545 1.736 1.69 3.29 3.247 4.29"
                    />
                </svg>
            </button>
        </div>

        {{-- Password Strength --}}
        <div class="mt-2">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                <div
                    class="h-full rounded-full transition-all duration-300"
                    :class="barClass"
                    :style="`width: ${password ? (score / 5) * 100 : 0}%`"
                ></div>
            </div>

            <p
                x-show="password"
                x-cloak
                class="mt-1 text-xs font-medium"
                :class="textClass"
                x-text="label"
            ></p>
        </div>

        <x-input-error
            :messages="$errors->get('password')"
            class="mt-2"
        />
    </div>

    <!-- Confirm Password -->
    <div
        class="mt-4"
        x-data="{ show: false }"
    >
        <x-input-label
            for="password_confirmation"
            :value="__('Confirm Password')"
        />

        <div class="relative mt-1">
            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full pe-12"
                x-bind:type="show ? 'text' : 'password'"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />

            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
            >
                {{-- Mata tertutup --}}
                <svg
                    x-show="!show"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                    <circle cx="12" cy="12" r="3" />
                </svg>

                {{-- Mata terbuka --}}
                <svg
                    x-show="show"
                    x-cloak
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 4.24A9.953 9.953 0 0112 4c4.477 0 8.268 2.943 9.542 7a10.06 10.06 0 01-4.132 5.264M6.228 6.228A10.06 10.06 0 002.458 12c.545 1.736 1.69 3.29 3.247 4.29"
                    />
                </svg>
            </button>
        </div>

        <x-input-error
            :messages="$errors->get('password_confirmation')"
            class="mt-2"
        />
    </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
