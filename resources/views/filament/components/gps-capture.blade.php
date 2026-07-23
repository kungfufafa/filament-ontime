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
                    
                    const isCheckIn = document.querySelector('[name*=\'check_in_lat\']') !== null;
                    const latKey = isCheckIn ? 'mountedActionsData.0.check_in_lat' : 'mountedActionsData.0.check_out_lat';
                    const lngKey = isCheckIn ? 'mountedActionsData.0.check_in_lng' : 'mountedActionsData.0.check_out_lng';

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
}" x-init="getLocation()" class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">📍 Deteksi GPS Otomatis</span>
            <template x-if="loading">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 animate-pulse">
                    ⏳ Mencari Sinyal GPS...
                </span>
            </template>
            <template x-if="!loading && lat">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                    ✓ GPS Terdeteksi
                </span>
            </template>
        </div>

        <button type="button" @click="getLocation()" class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg text-xs transition flex items-center gap-1">
            <span>🔄 Refresh GPS</span>
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
</div>
