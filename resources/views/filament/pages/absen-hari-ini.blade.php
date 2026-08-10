<x-filament-panels::page>
    @php
        $user = auth()->user();
        $employee = $user?->employee;
        $intern = $user?->intern;
        $freelancer = $user?->freelancer;

        // Gunakan profil yang tersedia
        $profile = $employee ?? $intern ?? $freelancer;
        $profileName = $employee?->full_name ?? $intern?->full_name ?? $freelancer?->full_name ?? $user?->name;
        $profileNip  = $employee?->nip ?? $intern?->nis ?? $freelancer?->freelancer_number ?? '-';
        $profileCompany = $employee?->company?->name ?? $intern?->company?->name ?? $freelancer?->company?->name ?? '-';
        $tipeLabel = $employee ? 'Karyawan' : ($intern ? 'Peserta Magang' : ($freelancer ? 'Freelancer' : '-'));

        $attendance = $this->todayAttendance;
        $policy = $this->companyPolicy;
    @endphp

    @if(!$profile)

        <div class="p-6 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-200">
            <h3 class="text-lg font-bold">Akun Terpisah Dari Data Karyawan</h3>
            <p class="mt-1 text-sm">Akun pengguna Anda belum terhubung ke data Employee. Harap hubungi Administrator untuk menghubungkan data profil Karyawan Anda.</p>
        </div>
    @else
        @if($policy?->require_face_recognition && empty($profile->master_face_photo))
            <div class="p-4 bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700/80 rounded-xl flex items-center justify-between gap-4 shadow-sm mb-2">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-amber-500 text-white rounded-lg shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Foto Master Wajah Belum Terdaftar</h4>
                        <p class="text-xs text-amber-700 dark:text-amber-300">Badan usaha Anda mewajibkan AI Face Recognition. Silakan daftarkan Foto Master Wajah Anda dengan mengeklik tombol <span class="font-semibold underline">Foto Master Wajah</span> di pojok kanan atas.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Informasi Karyawan & Kebijakan -->
            <div class="md:col-span-1 p-6 bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-xl">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Profil & Kebijakan Company</h3>
                <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div>
                        <span class="block text-xs font-medium text-gray-500">Nama & Tipe</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $profileName }} <span class="text-xs text-gray-500">({{ $tipeLabel }})</span></span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-500">ID / NIP & Badan Usaha</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $profileNip }} ({{ $profileCompany }})</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-500">Jam Masuk & Toleransi</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $policy?->work_start_time ?? '08:00' }} (Toleransi {{ $policy?->late_tolerance_minutes ?? 15 }} menit)</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-500">Aturan Absensi</span>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                Foto: {{ $policy?->require_photo ? 'Wajib' : 'Opsional' }}
                            </span>
                            <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                GPS: {{ $policy?->require_gps ? 'Wajib (Geofence ' . ($policy->geofence_radius_meters ?? 100) . 'm)' : 'Tidak Wajib' }}
                            </span>
                            <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                Face Recognition: {{ $policy?->require_face_recognition ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>

                    @if($policy?->require_face_recognition)
                        <!-- Master Face Photo Preview & Edit -->
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                            <span class="block text-xs font-medium text-gray-500 mb-1.5">Foto Master Biometrik</span>
                            @if(! empty($profile->master_face_photo))
                                @php
                                    $disk = config('filesystems.default');
                                    $masterPhotoUrl = '';
                                    try {
                                        if ($disk === 's3') {
                                            $masterPhotoUrl = \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($profile->master_face_photo, now()->addMinutes(60));
                                        } else {
                                            $masterPhotoUrl = \Illuminate\Support\Facades\Storage::disk($disk)->url($profile->master_face_photo);
                                        }
                                    } catch (\Exception $e) {
                                        $masterPhotoUrl = '';
                                    }
                                @endphp
                                <div class="flex items-center justify-between p-2.5 bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 rounded-lg">
                                    <div class="flex items-center gap-2.5">
                                        <div class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-emerald-500 shadow-sm shrink-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                            @if($masterPhotoUrl)
                                                <img src="{{ $masterPhotoUrl }}" class="w-full h-full object-cover" />
                                            @else
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span>Biometrik Terdaftar</span>
                                            </div>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">SIAP UNTUK ABSENSI</span>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <x-filament::button wire:click="mountAction('updateMasterFace')" size="xs" color="gray" icon="heroicon-m-pencil-square">
                                            Ubah
                                        </x-filament::button>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-between p-2.5 bg-amber-50/60 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 rounded-lg">
                                    <div class="text-xs text-amber-700 dark:text-amber-300">
                                        <span class="font-semibold block">Belum Terdaftar</span>
                                        <span class="text-[10px]">Daftarkan foto master wajah Anda</span>
                                    </div>
                                    <div class="shrink-0">
                                        <x-filament::button wire:click="mountAction('updateMasterFace')" size="xs" color="amber" icon="heroicon-m-camera">
                                            Daftar
                                        </x-filament::button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status Absensi Hari Ini & Kamera Live -->
            <div class="md:col-span-2 p-6 bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col justify-between space-y-6">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Presensi Mandiri Hari Ini</h3>
                        <span class="text-sm font-medium text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Check-In</span>
                            <div class="mt-1.5 text-2xl font-black text-gray-900 dark:text-white">
                                {{ $attendance?->check_in ? $attendance->check_in->format('H:i:s') . ' WIB' : '--:--:--' }}
                            </div>
                            @if($attendance?->status)
                                <div class="mt-2">
                                    @if($attendance->status === 'late')
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                            Terlambat ({{ $attendance->late_minutes }}m)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-md bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Check-Out</span>
                            <div class="mt-1.5 text-2xl font-black text-gray-900 dark:text-white">
                                {{ $attendance?->check_out ? $attendance->check_out->format('H:i:s') . ' WIB' : '--:--:--' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modul Kamera Live Preview & 1-Button Presensi -->
                @if($attendance && $attendance->check_out)
                    <div class="p-5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 rounded-xl text-center">
                        <div class="flex items-center justify-center gap-2 text-emerald-700 dark:text-emerald-300 font-bold text-base">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Absensi Lengkap Hari Ini</span>
                        </div>
                        <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">Anda telah menyelesaikan Check-In & Check-Out untuk hari ini. Terima kasih atas kerja keras Anda!</p>
                    </div>
                @else
                    <div x-data="{
                        cameraActive: false,
                        cameraError: null,
                        facingMode: 'user',
                        stream: null,
                        isMirrored: true,
                        submitting: false,

                        lat: null,
                        lng: null,
                        gpsStatus: 'Mengambil lokasi GPS...',

                        requireFaceRecognition: {{ ($policy?->require_face_recognition ?? false) ? 'true' : 'false' }},
                        requireGps: {{ ($policy?->require_gps ?? false) ? 'true' : 'false' }},
                        detector: null,
                        faceDetected: false,
                        livenessPassed: !{{ ($policy?->require_face_recognition ?? false) ? 'true' : 'false' }},
                        livenessStep: {{ ($policy?->require_face_recognition ?? false) ? '\'Mempersiapkan Deteksi Wajah...\'' : '\'Kamera Siap Presensi\'' }},
                        faceFrames: 0,
                        animFrameId: null,

                        async initTfEngine() {
                            if (!this.requireFaceRecognition) return;
                            try {
                                if (typeof tf !== 'undefined' && typeof faceLandmarksDetection !== 'undefined') {
                                    await tf.setBackend('webgl').catch(() => tf.setBackend('cpu'));
                                    await tf.ready();
                                    const model = faceLandmarksDetection.SupportedModels.MediaPipeFaceMesh;
                                    this.detector = await faceLandmarksDetection.createDetector(model, { runtime: 'tfjs', refineLandmarks: false, maxFaces: 1 });
                                    this.livenessStep = 'Menunggu Wajah Terdeteksi di Kamera...';
                                } else {
                                    this.livenessStep = 'Posisikan Wajah Tegak di Depan Kamera';
                                }
                            } catch (e) {
                                this.livenessStep = 'Posisikan Wajah Tegak di Depan Kamera';
                            }
                        },

                        initCamera() {
                            this.cameraError = null;
                            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                this.cameraError = 'Browser tidak mendukung akses kamera (perlu HTTPS).';
                                return;
                            }

                            navigator.mediaDevices.getUserMedia({
                                video: { facingMode: this.facingMode, width: { ideal: 640 }, height: { ideal: 640 } },
                                audio: false
                            })
                            .then((s) => {
                                this.stream = s;
                                this.cameraActive = true;
                                this.$nextTick(() => {
                                    if (this.$refs.video) {
                                        this.$refs.video.srcObject = s;
                                        this.$refs.video.play().catch(() => {});
                                        if (this.requireFaceRecognition) {
                                            this.startTfLivenessDetection();
                                        } else {
                                            this.livenessPassed = true;
                                        }
                                    }
                                });
                            })
                            .catch((err) => {
                                this.cameraError = 'Gagal membuka kamera: ' + (err.message || 'Izin ditolak');
                                this.cameraActive = false;
                            });

                            if (this.requireGps) {
                                this.getGps();
                            }
                        },

                        getGps() {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition((pos) => {
                                    this.lat = pos.coords.latitude;
                                    this.lng = pos.coords.longitude;
                                    this.gpsStatus = 'GPS Terdeteksi (' + this.lat.toFixed(4) + ', ' + this.lng.toFixed(4) + ')';
                                }, (err) => {
                                    this.gpsStatus = 'Gagal GPS: ' + err.message;
                                }, { enableHighAccuracy: true, timeout: 10000 });
                            } else {
                                this.gpsStatus = 'Browser tidak mendukung GPS';
                            }
                        },

                        stopCamera() {
                            if (this.animFrameId) cancelAnimationFrame(this.animFrameId);
                            if (this.stream) {
                                this.stream.getTracks().forEach(track => track.stop());
                                this.stream = null;
                            }
                            this.cameraActive = false;
                        },

                        startTfLivenessDetection() {
                            this.faceFrames = 0;
                            this.livenessPassed = false;
                            let isProcessing = false;

                            const detectLoop = async () => {
                                if (!this.cameraActive || !this.$refs.video) return;
                                const video = this.$refs.video;
                                if (video.readyState < 2 || video.videoWidth === 0 || isProcessing) {
                                    this.animFrameId = requestAnimationFrame(detectLoop);
                                    return;
                                }

                                isProcessing = true;
                                let detected = false;
                                try {
                                    if (this.detector) {
                                        const faces = await this.detector.estimateFaces(video, { flipHorizontal: false });
                                        if (faces && faces.length > 0) detected = true;
                                    }
                                } catch (e) {}

                                if (detected) {
                                    this.faceFrames++;
                                    if (!this.livenessPassed) {
                                        if (this.faceFrames >= 8) {
                                            this.livenessPassed = true;
                                            this.livenessStep = 'Wajah Biometrik Terverifikasi!';
                                        } else {
                                            const pct = Math.round((this.faceFrames / 8) * 100);
                                            this.livenessStep = `Mendeteksi Wajah... (${pct}%)`;
                                        }
                                    }
                                } else {
                                    this.faceFrames = 0;
                                    this.livenessPassed = false;
                                    this.livenessStep = 'Posisikan wajah Anda di depan kamera.';
                                }

                                isProcessing = false;
                                if (this.cameraActive) {
                                    this.animFrameId = requestAnimationFrame(detectLoop);
                                }
                            };

                            this.initTfEngine().then(() => detectLoop());
                        },

                        async doPresensi(type) {
                            if (this.submitting) return;
                            this.submitting = true;

                            let base64 = null;
                            if (this.$refs.video && this.cameraActive) {
                                const canvas = document.createElement('canvas');
                                const size = 480;
                                canvas.width = size;
                                canvas.height = size;
                                const ctx = canvas.getContext('2d');
                                const vW = this.$refs.video.videoWidth || 640;
                                const vH = this.$refs.video.videoHeight || 480;
                                const minDim = Math.min(vW, vH);
                                const sx = (vW - minDim) / 2;
                                const sy = (vH - minDim) / 2;

                                ctx.save();
                                if (this.isMirrored) {
                                    ctx.translate(size, 0);
                                    ctx.scale(-1, 1);
                                }
                                ctx.drawImage(this.$refs.video, sx, sy, minDim, minDim, 0, 0, size, size);
                                ctx.restore();

                                base64 = canvas.toDataURL('image/jpeg', 0.8);
                            }

                            try {
                                if (type === 'check_in') {
                                    await $wire.submitCheckIn(base64, this.lat, this.lng);
                                } else {
                                    await $wire.submitCheckOut(base64, this.lat, this.lng);
                                }
                            } finally {
                                this.submitting = false;
                            }
                        }
                    }"
                    x-init="initCamera()"
                    x-on:unmount="stopCamera()"
                    class="space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4">

                        <!-- Title Live Camera & Status Badges -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Live Camera Presensi
                            </span>
                            @if($policy?->require_gps)
                                <span class="text-[11px] text-gray-500 dark:text-gray-400" x-text="gpsStatus"></span>
                            @endif
                        </div>

                        <!-- Video Preview Container -->
                        <div class="relative w-full aspect-video sm:aspect-square max-w-sm mx-auto rounded-xl overflow-hidden bg-black border border-gray-300 dark:border-gray-700 shadow-md flex items-center justify-center">
                            <video x-ref="video" autoplay playsinline muted :class="{ '-scale-x-100': isMirrored }" class="w-full h-full object-cover"></video>

                            <!-- Bounding Oval Ring jika Face Recognition ON -->
                            @if($policy?->require_face_recognition)
                                <template x-if="cameraActive && !submitting">
                                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                        <div class="w-48 h-56 border-2 border-dashed rounded-full transition-all duration-300 flex flex-col items-center justify-between py-3"
                                             :class="livenessPassed ? 'border-emerald-400 bg-emerald-500/10 shadow-[0_0_20px_rgba(52,211,153,0.4)]' : 'border-amber-400 bg-amber-500/10 animate-pulse'">
                                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full flex items-center gap-1 shadow-sm"
                                                  :class="livenessPassed ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white'">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                                <span x-text="livenessPassed ? 'TF.JS VERIFIED' : 'DETEKSI WAJAH'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            @endif

                            <!-- Loading overlay saat submit -->
                            <template x-if="submitting">
                                <div class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center text-white space-y-2">
                                    <svg class="animate-spin w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span class="text-xs font-bold">Memproses Presensi...</span>
                                </div>
                            </template>

                            <!-- Error Camera -->
                            <template x-if="!cameraActive">
                                <div class="absolute inset-0 bg-gray-900/90 flex flex-col items-center justify-center p-4 text-center text-gray-400 space-y-2">
                                    <p class="text-xs text-red-400" x-text="cameraError || 'Membuka kamera...'"></p>
                                    <button type="button" @click="initCamera()" class="px-3 py-1 text-xs bg-gray-800 text-white rounded-lg border border-gray-700 hover:bg-gray-700">Coba Lagi</button>
                                </div>
                            </template>
                        </div>

                        <!-- Petunjuk visual Liveness jika Face Recognition ON -->
                        @if($policy?->require_face_recognition)
                            <div class="text-center text-[11px] py-1.5 px-3 rounded-md border max-w-sm mx-auto flex items-center justify-center gap-1.5 transition"
                                 :class="livenessPassed ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-300 dark:border-emerald-800' : 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800/50'">
                                <svg x-show="livenessPassed" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-text="livenessStep"></span>
                            </div>
                        @endif

                        <!-- Single Action Button: 1-Click Ambil Foto & Submit Presensi -->
                        <div class="pt-2 max-w-sm mx-auto">
                            @if(!$attendance)
                                <button type="button"
                                        @click="doPresensi('check_in')"
                                        :disabled="submitting || (requireFaceRecognition && !livenessPassed)"
                                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 active:scale-[0.99]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    <span>🟢 Check In Sekarang</span>
                                </button>
                            @elseif($attendance && !$attendance->check_out)
                                <button type="button"
                                        @click="doPresensi('check_out')"
                                        :disabled="submitting || (requireFaceRecognition && !livenessPassed)"
                                        class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-lg shadow-rose-600/20 active:scale-[0.99]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    <span>🔴 Check Out Sekarang</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
