    <p class="mt-1 text-sm text-gray-600">
        {{ __('Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.') }}
    </p>
</header>

<form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('put')

    {{-- Current Password --}}
    <div x-data="{ show: false }">
        <x-input-label
            for="update_password_current_password"
            :value="__('Current Password')"
        />

        <div class="relative mt-1">
            <x-text-input
                id="update_password_current_password"
                name="current_password"
                x-bind:type="show ? 'text' : 'password'"
                class="block w-full pe-12"
                autocomplete="current-password"
            />

            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
            >
                {{-- Mata terbuka --}}
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
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>

                {{-- Mata tertutup --}}
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
                        d="M3 3l18 18"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10.584 10.587a2 2 0 002.829 2.829"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9.88 5.09A9.77 9.77 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.76 9.76 0 01-4.043 5.07M6.228 6.228C4.482 7.42 3.155 9.1 2.458 12c.532 1.696 1.493 3.17 2.76 4.3"
                    />
                </svg>
            </button>
        </div>

        <x-input-error
            :messages="$errors->updatePassword->get('current_password')"
            class="mt-2"
        />
    </div>


    {{-- New Password + Strength --}}
    <div
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
        <x-input-label
            for="update_password_password"
            :value="__('New Password')"
        />

        <div class="relative mt-1">
            <x-text-input
                id="update_password_password"
                name="password"
                x-model="password"
                x-bind:type="show ? 'text' : 'password'"
                class="block w-full pe-12"
                autocomplete="new-password"
            />

            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
            >
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
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>

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
                        d="M3 3l18 18"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10.584 10.587a2 2 0 002.829 2.829"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9.88 5.09A9.77 9.77 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.76 9.76 0 01-4.043 5.07M6.228 6.228C4.482 7.42 3.155 9.1 2.458 12c.532 1.696 1.493 3.17 2.76 4.3"
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
            :messages="$errors->updatePassword->get('password')"
            class="mt-2"
        />
    </div>


    {{-- Confirm Password --}}
    <div x-data="{ show: false }">
        <x-input-label
            for="update_password_password_confirmation"
            :value="__('Confirm Password')"
        />

        <div class="relative mt-1">
            <x-text-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                x-bind:type="show ? 'text' : 'password'"
                class="block w-full pe-12"
                autocomplete="new-password"
            />

            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 end-0 flex items-center px-4 text-gray-500 hover:text-gray-700"
                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
            >
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
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>

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
                        d="M3 3l18 18"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10.584 10.587a2 2 0 002.829 2.829"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9.88 5.09A9.77 9.77 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.76 9.76 0 01-4.043 5.07M6.228 6.228C4.482 7.42 3.155 9.1 2.458 12c.532 1.696 1.493 3.17 2.76 4.3"
                    />
                </svg>
            </button>
        </div>

        <x-input-error
            :messages="$errors->updatePassword->get('password_confirmation')"
            class="mt-2"
        />
    </div>


    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >
                {{ __('Saved.') }}
            </p>
        @endif
    </div>
</form>