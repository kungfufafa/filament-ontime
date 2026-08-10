<?php
    use Illuminate\Support\Str;

    $rawStatePath = $getStatePath();

    $withoutDataPrefix = str_starts_with($rawStatePath, 'data.')
        ? substr($rawStatePath, 5)
        : $rawStatePath;

    $pathParts = explode('.', $withoutDataPrefix);
    array_pop($pathParts);

    $parentData = $getLivewire()->data ?? [];
    foreach ($pathParts as $part) {
        $parentData = is_array($parentData) ? ($parentData[$part] ?? []) : [];
    }

    $currentLat = isset($parentData[$getLatField()]) && $parentData[$getLatField()] !== ''
        ? (float) $parentData[$getLatField()]
        : null;
    $currentLng = isset($parentData[$getLngField()]) && $parentData[$getLngField()] !== ''
        ? (float) $parentData[$getLngField()]
        : null;

    $parentStatePath = Str::contains($rawStatePath, '.') ? Str::beforeLast($rawStatePath, '.') : 'data';
    $latStatePath    = $parentStatePath . '.' . $getLatField();
    $lngStatePath    = $parentStatePath . '.' . $getLngField();

    $mapId = 'map-' . md5($rawStatePath . '-' . now()->format('YmdH'));
?>

<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => $field]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


    <div
        x-data="mapPickerAlpine({
            initialLat: <?php echo e($currentLat !== null ? $currentLat : 'null'); ?>,
            initialLng: <?php echo e($currentLng !== null ? $currentLng : 'null'); ?>,
            latStatePath: <?php echo \Illuminate\Support\Js::from($latStatePath)->toHtml() ?>,
            lngStatePath: <?php echo \Illuminate\Support\Js::from($lngStatePath)->toHtml() ?>,
            mapId: <?php echo \Illuminate\Support\Js::from($mapId)->toHtml() ?>,
        })"
        x-init="init()"
        class="space-y-3"
    >
        
        <div class="flex flex-wrap gap-2">

            
            <div class="relative flex-1 min-w-[180px]">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    x-model="searchQuery"
                    @keydown.enter.prevent="searchAddress()"
                    @input="searchResults = []; noResults = false"
                    placeholder="Cari nama jalan, kota, atau tempat..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>

            
            <button
                type="button"
                @click="searchAddress()"
                :disabled="searching || !searchQuery.trim()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg x-show="searching" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 6 12 12h4z"/>
                </svg>
                <svg x-show="!searching" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Cari</span>
            </button>

            
            <button
                type="button"
                @click="detectGps()"
                :disabled="gettingGps"
                title="Deteksi koordinat GPS perangkat Anda saat ini"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg x-show="gettingGps" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 6 12 12h4z"/>
                </svg>
                <svg x-show="!gettingGps" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 1.104-.896 2-2 2s-2-.896-2-2 .896-2 2-2 2 .896 2 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7z"/>
                </svg>
                <span x-text="gettingGps ? 'Mendeteksi GPS...' : 'GPS Saya'"></span>
            </button>

        </div>

        
        <div
            x-show="searchResults.length > 0"
            x-cloak
            class="rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800 overflow-hidden"
        >
            <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                <span x-text="searchResults.length + ' hasil ditemukan — klik untuk memilih lokasi'"></span>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700 max-h-52 overflow-y-auto">
                <template x-for="(result, index) in searchResults" :key="index">
                    <li>
                        <button
                            type="button"
                            @click="selectResult(result)"
                            class="w-full text-left px-3 py-2.5 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/20 transition flex items-start gap-2.5"
                        >
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100 leading-snug" x-text="result.display_name.split(',').slice(0,3).join(', ')"></div>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="result.display_name"></div>
                            </div>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        
        <div
            x-show="noResults"
            x-cloak
            class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 px-3 py-2.5 text-sm"
        >
            <p class="font-medium text-amber-700 dark:text-amber-400">Lokasi tidak ditemukan di database OpenStreetMap.</p>
            <p class="mt-0.5 text-amber-600 dark:text-amber-500 text-xs">
                Coba kata kunci lebih singkat seperti nama kota atau kecamatan, gunakan tombol <strong>GPS Saya</strong> jika sedang berada di lokasi tersebut, atau klik/drag marker di peta secara manual.
            </p>
        </div>

        
        <div
            wire:ignore
            id="<?php echo e($mapId); ?>"
            class="w-full overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600"
            style="height: 380px; z-index: 0; position: relative;"
        ></div>

        
        <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60">

            
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Lat:</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-gray-100" x-text="lat !== null ? lat.toFixed(7) : '—'"></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Lng:</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-gray-100" x-text="lng !== null ? lng.toFixed(7) : '—'"></span>
                    </div>
                    <span
                        x-show="gpsAccuracy !== null"
                        class="text-xs text-emerald-600 dark:text-emerald-400"
                        x-text="'±' + gpsAccuracy + 'm akurasi GPS'"
                    ></span>
                </div>

                <button
                    type="button"
                    @click="useThisLocation()"
                    :disabled="lat === null || lng === null"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-success-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Gunakan Lokasi Ini
                </button>
            </div>

            
            <div x-show="detectedAddress || reverseLoading" class="flex items-start gap-2 border-t border-gray-200 dark:border-gray-700 pt-2">
                <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Alamat terdeteksi:&nbsp;</span>
                    <span x-show="!reverseLoading" class="text-xs text-gray-700 dark:text-gray-300 break-words" x-text="detectedAddress"></span>
                    <span x-show="reverseLoading" class="text-xs text-gray-400 italic">Mendeteksi alamat...</span>
                </div>
            </div>

        </div>

        
        <p class="text-xs text-gray-400 dark:text-gray-500">
            <strong>Cari alamat</strong> — ketik lalu Enter, pilih dari daftar hasil &nbsp;·&nbsp;
            <strong>GPS Saya</strong> — deteksi posisi perangkat &nbsp;·&nbsp;
            <strong>Klik/drag marker</strong> di peta untuk presisi manual &nbsp;·&nbsp;
            Tekan <strong>"Gunakan Lokasi Ini"</strong> untuk mengisi field latitude &amp; longitude.
        </p>

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('79cc5c19-9e5d-4a2f-af8a-284163947baa')): $__env->markAsRenderedOnce('79cc5c19-9e5d-4a2f-af8a-284163947baa'); ?>
    <?php $__env->startPush('styles'); ?>
        <link id="leaflet-css" rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
        <style>
            .leaflet-pane,.leaflet-tile,.leaflet-marker-icon,.leaflet-marker-shadow,
            .leaflet-tile-container,.leaflet-map-pane svg,.leaflet-map-pane canvas,
            .leaflet-zoom-box,.leaflet-image-layer,.leaflet-layer { position:absolute;left:0;top:0; }
            .leaflet-container { background:#ddd;outline:0; }
            [x-cloak] { display:none !important; }
        </style>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
        function mapPickerAlpine({ initialLat, initialLng, latStatePath, lngStatePath, mapId }) {
            return {
                map: null,
                marker: null,

                searchQuery: '',
                searching: false,
                searchResults: [],
                noResults: false,

                gettingGps: false,
                gpsAccuracy: null,

                detectedAddress: '',
                reverseLoading: false,

                lat: initialLat,
                lng: initialLng,

                // ── Init ──────────────────────────────────────────────────
                init() {
                    this.$nextTick(() => this.initMap());
                },

                initMap() {
                    const container = document.getElementById(mapId);
                    if (!container || !window.L) return;

                    // Clean up stale Leaflet state to prevent "already initialized" error
                    if (this.map && typeof this.map.remove === 'function') {
                        this.map.remove();
                    }
                    this.map   = null;
                    this.marker = null;
                    if (container._leaflet_id !== undefined) {
                        delete container._leaflet_id;
                    }

                    const defaultLat = this.lat ?? -6.2088;
                    const defaultLng = this.lng ?? 106.8456;
                    const zoom       = this.lat !== null ? 16 : 11;

                    this.map = window.L.map(mapId).setView([defaultLat, defaultLng], zoom);

                    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
                        maxZoom: 19,
                    }).addTo(this.map);

                    this.marker = window.L.marker([defaultLat, defaultLng], { draggable: true }).addTo(this.map);

                    // Drag marker → update coords + reverse geocode
                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.lat = pos.lat;
                        this.lng = pos.lng;
                        this.gpsAccuracy = null;
                        this.reverseGeocode(pos.lat, pos.lng);
                    });

                    // Click map → move marker + update coords + reverse geocode
                    this.map.on('click', (e) => {
                        this.lat = e.latlng.lat;
                        this.lng = e.latlng.lng;
                        this.gpsAccuracy = null;
                        this.searchResults = [];
                        this.noResults = false;
                        this.marker.setLatLng(e.latlng);
                        this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                    });
                },

                // ── Search via Nominatim (3-pass fallback) ────────────────
                async searchAddress() {
                    const q = this.searchQuery.trim();
                    if (!q) return;

                    this.searching = true;
                    this.searchResults = [];
                    this.noResults = false;
                    this.gpsAccuracy = null;

                    const nominatim = async (query, countryOnly = false) => {
                        const url = new URL('https://nominatim.openstreetmap.org/search');
                        url.searchParams.set('q', query);
                        url.searchParams.set('format', 'json');
                        url.searchParams.set('limit', '7');
                        url.searchParams.set('accept-language', 'id,en');
                        url.searchParams.set('addressdetails', '0');
                        if (countryOnly) url.searchParams.set('countrycodes', 'id');
                        const res = await fetch(url.toString(), {
                            headers: { 'User-Agent': 'OnTime-Attendance-App/1.0' },
                        });
                        return res.json();
                    };

                    try {
                        // Pass 1: Indonesia-scoped search
                        let data = await nominatim(q, true);

                        // Pass 2: global search (no country filter)
                        if (data.length === 0) {
                            data = await nominatim(q, false);
                        }

                        // Pass 3: strip street/number, keep district+city part
                        if (data.length === 0) {
                            const parts = q.split(',').map(s => s.trim()).filter(Boolean);
                            if (parts.length > 2) {
                                data = await nominatim(parts.slice(1).join(', '), true);
                            }
                        }

                        if (data.length > 0) {
                            this.searchResults = data;
                            // Auto-preview map at first result
                            const first = data[0];
                            this.map.setView(window.L.latLng(parseFloat(first.lat), parseFloat(first.lon)), 14);
                        } else {
                            this.noResults = true;
                        }
                    } catch (_) {
                        this.noResults = true;
                    } finally {
                        this.searching = false;
                    }
                },

                // ── Select from results dropdown ──────────────────────────
                selectResult(result) {
                    this.lat = parseFloat(result.lat);
                    this.lng = parseFloat(result.lon);
                    const latlng = window.L.latLng(this.lat, this.lng);
                    this.map.setView(latlng, 17);
                    this.marker.setLatLng(latlng);
                    this.searchResults = [];
                    this.noResults = false;
                    this.reverseGeocode(this.lat, this.lng);
                },

                // ── Reverse geocode via Nominatim ─────────────────────────
                async reverseGeocode(lat, lng) {
                    this.reverseLoading = true;
                    this.detectedAddress = '';
                    try {
                        const url = new URL('https://nominatim.openstreetmap.org/reverse');
                        url.searchParams.set('lat', lat);
                        url.searchParams.set('lon', lng);
                        url.searchParams.set('format', 'json');
                        url.searchParams.set('zoom', '18');
                        url.searchParams.set('accept-language', 'id,en');
                        url.searchParams.set('addressdetails', '1');

                        const res  = await fetch(url.toString(), {
                            headers: { 'User-Agent': 'OnTime-Attendance-App/1.0' },
                        });
                        const data = await res.json();

                        if (data && data.display_name) {
                            const a = data.address || {};
                            const parts = [
                                a.road || a.pedestrian || a.path || a.footway || '',
                                a.suburb || a.village || a.neighbourhood || '',
                                a.city_district || a.county || '',
                                a.city || a.town || a.municipality || '',
                                a.state || '',
                            ].filter(Boolean);
                            this.detectedAddress = parts.length > 0 ? parts.join(', ') : data.display_name;
                        }
                    } catch (_) {
                        // silent fail — informational only
                    } finally {
                        this.reverseLoading = false;
                    }
                },

                // ── GPS detection ─────────────────────────────────────────
                detectGps() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung fitur Geolocation / GPS.');
                        return;
                    }
                    this.gettingGps = true;
                    this.gpsAccuracy = null;
                    this.searchResults = [];
                    this.noResults = false;

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.lat = pos.coords.latitude;
                            this.lng = pos.coords.longitude;
                            this.gpsAccuracy = pos.coords.accuracy !== null
                                ? Math.round(pos.coords.accuracy)
                                : null;

                            const latlng = window.L.latLng(this.lat, this.lng);
                            this.map.setView(latlng, 18);
                            this.marker.setLatLng(latlng);
                            this.gettingGps = false;
                            this.reverseGeocode(this.lat, this.lng);
                        },
                        (err) => {
                            const msg = {
                                1: 'Akses lokasi ditolak. Izinkan akses lokasi di pengaturan browser.',
                                2: 'Posisi tidak tersedia. Pastikan GPS perangkat aktif.',
                                3: 'Waktu permintaan habis. Coba lagi.',
                            };
                            alert(msg[err.code] ?? ('Gagal mendapatkan GPS: ' + err.message));
                            this.gettingGps = false;
                        },
                        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                    );
                },

                // ── Push coordinates to Filament form fields ──────────────
                useThisLocation() {
                    if (this.lat === null || this.lng === null) return;
                    this.$wire.set(latStatePath, parseFloat(this.lat.toFixed(7)));
                    this.$wire.set(lngStatePath, parseFloat(this.lng.toFixed(7)));
                },
            };
        }
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\AHTAR\filament-ontime\resources\views/filament/forms/components/map-picker-field.blade.php ENDPATH**/ ?>