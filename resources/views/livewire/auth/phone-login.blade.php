<x-mekaya::auth-card>
    <header class="flex flex-col items-center justify-center py-3 text-center">
        <div class="flex items-center justify-center space-y-2 rounded-lg bg-white p-2.5 shadow ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700/80">
            <svg class="size-6 text-emerald-500 fill-current" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
        </div>

        <h1 class="mt-4 font-heading text-lg font-medium text-gray-950 dark:text-white">
            {{ $this->getHeading() }}
        </h1>

        <p class="mt-1 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ $this->getSubheading() }}
        </p>
    </header>

    <div class="mt-6">
        @if ($errorMessage)
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 dark:bg-red-950/50 dark:border-red-800 dark:text-red-400 text-sm">
                {{ $errorMessage }}
            </div>
        @endif

        @if ($successMessage)
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-400 text-sm">
                {{ $successMessage }}
            </div>
        @endif

        @if ($step === 1)
            <form wire:submit.prevent="requestOtp" class="space-y-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-950 dark:text-white mb-1">
                        Nomor WhatsApp
                    </label>
                    <input type="text" wire:model="phone" id="phone" placeholder="081234567890" autofocus
                        class="fi-input block w-full rounded-lg border-none bg-white py-2 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-amber-500">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <x-filament::button type="submit" wire:loading.attr="disabled" class="w-full">
                    <span wire:loading.remove>Kirim Kode OTP</span>
                    <span wire:loading>Mengirim...</span>
                </x-filament::button>
            </form>
        @else
            <form wire:submit.prevent="verifyOtp" class="space-y-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Nomor: </span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $phone }}</span>
                    </div>
                    <button type="button" wire:click="changePhone" class="text-amber-600 dark:text-amber-400 hover:underline font-medium">
                        Ubah Nomor
                    </button>
                </div>

                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-950 dark:text-white mb-1">
                        Kode OTP (6 Digit)
                    </label>
                    <input type="text" wire:model="otp" id="otp" placeholder="123456" maxlength="6" autofocus
                        class="fi-input block w-full text-center tracking-[0.4em] text-xl font-bold rounded-lg border-none bg-white py-2.5 px-3 text-amber-600 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-amber-400 dark:ring-white/20 dark:focus:ring-amber-500">
                    @error('otp')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-gray-600 dark:text-gray-400 cursor-pointer">
                        <input type="checkbox" wire:model="remember" class="rounded border-gray-300 dark:border-gray-700 text-amber-600 focus:ring-amber-500">
                        <span class="ml-2">Ingat saya</span>
                    </label>

                    <div>
                        @if ($cooldown > 0)
                            <span class="text-gray-400" wire:poll.1000ms="$set('cooldown', {{ max(0, $cooldown - 1) }})">
                                Kirim ulang ({{ $cooldown }}s)
                            </span>
                        @else
                            <button type="button" wire:click="resendOtp" class="text-amber-600 dark:text-amber-400 hover:underline font-medium">
                                Kirim Ulang OTP
                            </button>
                        @endif
                    </div>
                </div>

                <x-filament::button type="submit" wire:loading.attr="disabled" class="w-full">
                    <span wire:loading.remove>Verifikasi & Login</span>
                    <span wire:loading>Memverifikasi...</span>
                </x-filament::button>
            </form>
        @endif

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-800 text-center">
            <a href="/admin/login" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium inline-flex items-center space-x-1">
                <span>atau Login dengan Email & Password</span>
            </a>
        </div>
    </div>
</x-mekaya::auth-card>
