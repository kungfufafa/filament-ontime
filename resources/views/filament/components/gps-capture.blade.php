<div x-data="{
    loading: false,
    errorMessage: '',
    lat: '',
    lng: '',
    getLocation() {
        this.loading = true;
        this.errorMessage = '';
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lat = pos.coords.latitude.toFixed(7);
                    this.lng = pos.coords.longitude.toFixed(7);
                    
                    const type = '{{ $type ?? "check_in" }}';
                    const statePath = '{{ $getStatePath() }}';
                    const basePath = statePath.includes('.') ? statePath.substring(0, statePath.lastIndexOf('.')) : '';
                    const latKey = basePath ? (basePath + '.' + type + '_lat') : (type + '_lat');
                    const lngKey = basePath ? (basePath + '.' + type + '_lng') : (type + '_lng');

                    $wire.set(latKey, this.lat);
                    $wire.set(lngKey, this.lng);

                    this.loading = false;
                },
                (err) => {
                    this.loading = false;
                    this.errorMessage = 'Gagal mengambil GPS: ' + err.message + '. Silakan izinkan akses lokasi di browser.';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            this.loading = false;
            this.errorMessage = 'Browser Anda tidak mendukung Geolocation.';
        }
    }
}" x-init="getLocation()"
    class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Deteksi GPS Otomatis</span>
            </div>
            <template x-if="loading">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                    <svg class="animate-spin w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Mencari Sinyal GPS...</span>
                </span>
            </template>
            <template x-if="!loading && lat">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                    <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>GPS Terdeteksi</span>
                </span>
            </template>
        </div>

        <button type="button" @click="getLocation()"
            class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg text-xs transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span>Refresh GPS</span>
        </button>
    </div>

    <template x-if="lat && lng && !loading">
        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-mono font-medium">
            Koordinat: <span x-text="lat"></span>, <span x-text="lng"></span>
        </p>
    </template>

    <template x-if="errorMessage">
        <p class="text-xs text-red-600 dark:text-red-400 font-medium" x-text="errorMessage"></p>
    </template>

    @error('check_in_lat')
        <p class="text-xs text-red-600 dark:text-red-400 font-semibold pt-1">Sinyal GPS belum terdeteksi. Silakan klik "Refresh GPS".</p>
    @enderror
    @error('check_out_lat')
        <p class="text-xs text-red-600 dark:text-red-400 font-semibold pt-1">Sinyal GPS belum terdeteksi. Silakan klik "Refresh GPS".</p>
    @enderror
</div>