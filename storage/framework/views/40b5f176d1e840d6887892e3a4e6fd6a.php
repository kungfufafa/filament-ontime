<?php
    $requireFaceRecognition = $requireFaceRecognition ?? false;
    $requireGps = $requireGps ?? false;
    $hasCheckedIn = $hasCheckedIn ?? false;
    $hasCheckedOut = $hasCheckedOut ?? false;
?>

<!-- TensorFlow.js & Face Landmarks Detection CDN -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireFaceRecognition): ?>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-core@4.17.0/dist/tf-core.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-converter@4.17.0/dist/tf-converter.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-backend-webgl@4.17.0/dist/tf-backend-webgl.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@1.0.5/dist/face-landmarks-detection.min.js"></script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div x-data="{
    uploading: false,
    submitMode: null,
    cameraActive: false,
    cameraError: null,
    facingMode: 'user',
    stream: null,
    isMirrored: true,

    // GPS State
    requireGps: <?php echo e($requireGps ? 'true' : 'false'); ?>,
    lat: null,
    lng: null,
    gpsLoading: false,
    gpsError: null,

    // Config & TensorFlow.js State
    requireFaceRecognition: <?php echo e($requireFaceRecognition ? 'true' : 'false'); ?>,
    detector: null,
    faceDetected: false,
    livenessPassed: !<?php echo e($requireFaceRecognition ? 'true' : 'false'); ?>,
    livenessStep: <?php echo e($requireFaceRecognition ? '\'Mempersiapkan Deteksi Wajah...\'' : '\'Kamera Siap. Posisikan wajah Anda.\''); ?>,
    faceFrames: 0,
    animFrameId: null,

    initGps() {
        if (!this.requireGps) return;
        this.gpsLoading = true;
        this.gpsError = null;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lat = pos.coords.latitude.toFixed(7);
                    this.lng = pos.coords.longitude.toFixed(7);
                    this.gpsLoading = false;
                },
                (err) => {
                    this.gpsLoading = false;
                    this.gpsError = 'Gagal mengambil GPS: ' + err.message;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            this.gpsLoading = false;
            this.gpsError = 'Browser tidak mendukung GPS';
        }
    },

    async initTfEngine() {
        if (!this.requireFaceRecognition) return;
        try {
            if (typeof tf !== 'undefined' && typeof faceLandmarksDetection !== 'undefined') {
                await tf.setBackend('webgl').catch(() => tf.setBackend('cpu'));
                await tf.ready();
                const model = faceLandmarksDetection.SupportedModels.MediaPipeFaceMesh;
                this.detector = await faceLandmarksDetection.createDetector(model, {
                    runtime: 'tfjs',
                    refineLandmarks: false,
                    maxFaces: 1
                });
                this.livenessStep = 'Menunggu Wajah Terdeteksi di Kamera...';
            } else {
                this.livenessStep = 'Posisikan Wajah Tegak di Depan Kamera';
            }
        } catch (e) {
            console.warn('TF Init Warning:', e);
            this.livenessStep = 'Posisikan Wajah Tegak di Depan Kamera';
        }
    },

    initCamera() {
        this.cameraError = null;
        this.stopCamera();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.cameraError = 'Browser Anda tidak mendukung akses kamera (perlu HTTPS atau Localhost).';
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: this.facingMode,
                width: { ideal: 640 },
                height: { ideal: 640 }
            },
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
                        this.livenessStep = 'Kamera Siap';
                    }
                }
            });
        })
        .catch((err) => {
            console.error('Akses kamera gagal:', err);
            this.cameraError = (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')
                ? 'Izin kamera ditolak. Silakan izinkan akses kamera di browser.'
                : 'Gagal membuka kamera: ' + (err.message || 'Error tidak diketahui');
            this.cameraActive = false;
        });
    },

    stopCamera() {
        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.$refs.video) {
            this.$refs.video.srcObject = null;
        }
        this.cameraActive = false;
    },

    toggleCamera() {
        this.facingMode = (this.facingMode === 'user') ? 'environment' : 'user';
        this.isMirrored = (this.facingMode === 'user');
        this.initCamera();
    },

    startTfLivenessDetection() {
        this.faceFrames = 0;
        this.livenessPassed = false;
        this.faceDetected = false;
        this.livenessStep = 'Mempersiapkan modul deteksi wajah...';

        let isProcessing = false;

        const checkCanvasFace = (video) => {
            try {
                const canvas = this.$refs.canvas || document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = 160;
                canvas.height = 160;
                ctx.drawImage(video, 0, 0, 160, 160);
                const imgData = ctx.getImageData(30, 30, 100, 100).data;
                let skinPixels = 0;
                let totalPixels = imgData.length / 4;
                for (let i = 0; i < imgData.length; i += 4) {
                    const r = imgData[i], g = imgData[i + 1], b = imgData[i + 2];
                    if (r > 60 && g > 40 && b > 20 && r > g && r > b && (Math.max(r, g, b) - Math.min(r, g, b) > 15) && Math.abs(r - g) > 15) {
                        skinPixels++;
                    }
                }
                return (skinPixels / totalPixels) > 0.22;
            } catch (e) {
                return false;
            }
        };

        const detectLoop = async () => {
            if (!this.cameraActive || !this.$refs.video) return;
            const video = this.$refs.video;

            if (video.readyState < 2 || video.videoWidth === 0) {
                this.animFrameId = requestAnimationFrame(detectLoop);
                return;
            }

            if (isProcessing) {
                this.animFrameId = requestAnimationFrame(detectLoop);
                return;
            }

            isProcessing = true;
            let detected = false;

            try {
                if (this.detector) {
                    const faces = await this.detector.estimateFaces(video, { flipHorizontal: false });
                    if (faces && faces.length > 0) detected = true;
                } else {
                    detected = checkCanvasFace(video);
                }
            } catch (e) {
                detected = checkCanvasFace(video);
            }

            if (detected) {
                this.faceDetected = true;
                this.faceFrames = (this.faceFrames || 0) + 1;
                if (!this.livenessPassed) {
                    if (this.faceFrames >= 8) {
                        this.livenessPassed = true;
                        this.livenessStep = 'Wajah Biometrik Terverifikasi!';
                    } else {
                        const pct = Math.round((this.faceFrames / 8) * 100);
                        this.livenessStep = `Mendeteksi Wajah Biometrik... (${pct}%)`;
                    }
                }
            } else {
                this.faceDetected = false;
                this.faceFrames = 0;
                this.livenessPassed = false;
                this.livenessStep = 'Posisikan wajah Anda di depan kamera.';
            }

            isProcessing = false;
            if (this.cameraActive) {
                this.animFrameId = requestAnimationFrame(detectLoop);
            }
        };

        this.initTfEngine().then(() => {
            detectLoop();
        });
    },

    async submitAttendance(mode) {
        if (this.uploading) return;
        this.submitMode = mode;

        if (this.requireFaceRecognition && !this.livenessPassed) {
            alert('Wajah belum terverifikasi oleh modul biometrik. Posisikan wajah Anda di depan kamera.');
            return;
        }

        if (this.requireGps && (!this.lat || !this.lng)) {
            alert('Sinyal GPS belum terdeteksi. Silakan izinkan akses lokasi di browser Anda dan klik Refresh GPS jika perlu.');
            this.initGps();
            return;
        }

        if (!this.$refs.video || !this.cameraActive) {
            alert('Kamera tidak aktif. Silakan aktifkan kamera terlebih dahulu.');
            return;
        }

        this.uploading = true;
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        const context = canvas.getContext('2d');

        const size = 480;
        canvas.width = size;
        canvas.height = size;

        const vW = video.videoWidth || 640;
        const vH = video.videoHeight || 480;
        const minDim = Math.min(vW, vH);
        const sx = (vW - minDim) / 2;
        const sy = (vH - minDim) / 2;

        context.save();
        if (this.isMirrored) {
            context.translate(size, 0);
            context.scale(-1, 1);
        }
        context.drawImage(video, sx, sy, minDim, minDim, 0, 0, size, size);
        context.restore();

        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>';

        const processLivewire = (photoPath) => {
            const currentLat = this.lat ? parseFloat(this.lat) : null;
            const currentLng = this.lng ? parseFloat(this.lng) : null;

            if (mode === 'check_in') {
                $wire.processCheckIn(photoPath, currentLat, currentLng).then(() => {
                    this.uploading = false;
                    this.submitMode = null;
                }).catch(() => {
                    this.uploading = false;
                    this.submitMode = null;
                });
            } else {
                $wire.processCheckOut(photoPath, currentLat, currentLng).then(() => {
                    this.uploading = false;
                    this.submitMode = null;
                }).catch(() => {
                    this.uploading = false;
                    this.submitMode = null;
                });
            }
        };

        const formData = new FormData();
        formData.append('photo_base64', dataUrl);

        if (canvas.toBlob) {
            canvas.toBlob((blob) => {
                if (blob) {
                    formData.append('photo', blob, 'selfie.jpg');
                }
                this.uploadSelfieApi(formData, csrfToken, processLivewire);
            }, 'image/jpeg', 0.8);
        } else {
            this.uploadSelfieApi(formData, csrfToken, processLivewire);
        }
    },

    uploadSelfieApi(formData, csrfToken, callback) {
        fetch('<?php echo e(route('upload.selfie')); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(async (res) => {
            const text = await res.text();
            let data = null;
            try {
                const start = text.indexOf('{');
                const end = text.lastIndexOf('}');
                if (start !== -1 && end !== -1) {
                    data = JSON.parse(text.substring(start, end + 1));
                }
            } catch (e) {}

            if (data && data.success) {
                callback(data.path);
            } else {
                this.uploading = false;
                this.submitMode = null;
                alert('Gagal mengunggah foto: ' + (data?.message || 'Server error'));
            }
        })
        .catch((err) => {
            this.uploading = false;
            this.submitMode = null;
            console.error('Upload error:', err);
            alert('Terjadi kesalahan koneksi saat mengunggah foto selfie.');
        });
    }
}"
x-init="initCamera(); initGps();"
x-on:unmount="stopCamera()"
class="space-y-4 max-w-sm mx-auto">

    <!-- Hidden Canvas -->
    <canvas x-ref="canvas" class="hidden"></canvas>

    <!-- Camera Feed Live Preview Container -->
    <div class="relative aspect-square w-full rounded-2xl overflow-hidden bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-lg">
        <video x-ref="video" autoplay playsinline muted :class="{ '-scale-x-100': isMirrored }" class="w-full h-full object-cover"></video>

        <!-- Face Bounding Box Overlay (TF.JS) -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireFaceRecognition): ?>
            <template x-if="cameraActive && !uploading">
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-48 h-56 border-2 border-dashed rounded-full transition-all duration-300 flex flex-col items-center justify-between py-3"
                         :class="livenessPassed ? 'border-emerald-400 bg-emerald-500/10 shadow-[0_0_25px_rgba(52,211,153,0.5)]' : 'border-amber-400 bg-amber-500/10 animate-pulse'">
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow"
                              :class="livenessPassed ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white'">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span x-text="livenessPassed ? 'TF.JS VERIFIED' : 'DETEKSI WAJAH'"></span>
                        </span>
                    </div>
                </div>
            </template>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Flip Camera Button (top right) -->
        <button type="button" @click="toggleCamera()" title="Ganti Kamera" class="absolute top-3 right-3 p-2 bg-black/50 hover:bg-black/80 backdrop-blur text-white rounded-full transition border border-white/20 shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </button>

        <!-- Loading Overlay when Submitting -->
        <template x-if="uploading">
            <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 text-white backdrop-blur-sm gap-2 z-20">
                <svg class="animate-spin w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-xs font-semibold text-gray-200" x-text="submitMode === 'check_in' ? 'Memproses Check In...' : 'Memproses Check Out...'"></span>
            </div>
        </template>

        <!-- Camera Error Message -->
        <template x-if="!cameraActive">
            <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-gray-400 bg-gray-900/95 space-y-2 z-10">
                <template x-if="!cameraError">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="animate-spin w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-xs text-gray-300">Membuka kamera & preview...</p>
                    </div>
                </template>
                <template x-if="cameraError">
                    <div class="space-y-2">
                        <p class="text-xs text-red-400" x-text="cameraError"></p>
                        <button type="button" @click="initCamera()" class="px-3 py-1.5 text-xs font-medium bg-gray-800 text-white rounded-lg border border-gray-700 hover:bg-gray-700">
                            Coba Lagi
                        </button>
                    </div>
                </template>
            </div>
        </template>

        <!-- Status Pill Overlay (Bottom inside video) -->
        <div class="absolute bottom-2 left-2 right-2 flex flex-col gap-1 z-10 pointer-events-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireFaceRecognition): ?>
                <div class="text-center text-[10px] py-1 px-2 rounded-md border flex items-center justify-center gap-1 font-medium transition backdrop-blur-md"
                     :class="livenessPassed ? 'text-emerald-300 bg-emerald-950/80 border-emerald-700/60' : 'text-amber-300 bg-amber-950/80 border-amber-700/60'">
                    <svg x-show="livenessPassed" class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span x-text="livenessStep"></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireGps): ?>
                <div class="flex items-center justify-between text-[10px] px-2.5 py-1 rounded-md bg-gray-950/80 backdrop-blur-md border border-gray-800 text-gray-300">
                    <div class="flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        <template x-if="gpsLoading">
                            <span class="text-amber-400 animate-pulse">Mencari GPS...</span>
                        </template>
                        <template x-if="!gpsLoading && lat">
                            <span class="text-emerald-400 font-mono"><span x-text="lat"></span>, <span x-text="lng"></span></span>
                        </template>
                        <template x-if="!gpsLoading && !lat">
                            <span class="text-red-400" x-text="gpsError || 'GPS belum terdeteksi'"></span>
                        </template>
                    </div>
                    <button type="button" @click="initGps()" class="text-[10px] text-gray-400 hover:text-white underline">
                        Refresh
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- SIDE-BY-SIDE ACTION BUTTONS (Check In & Check Out Berdampingan) -->
    <div class="grid grid-cols-2 gap-3 max-w-sm mx-auto">
        <!-- Tombol Check In -->
        <button type="button"
                @click="submitAttendance('check_in')"
                :disabled="uploading || !cameraActive || <?php echo e($hasCheckedIn ? 'true' : 'false'); ?> || (requireFaceRecognition && !livenessPassed)"
                class="py-3 px-3 text-xs sm:text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 <?php echo e($hasCheckedIn ? 'bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed border border-gray-300 dark:border-gray-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20 active:scale-95'); ?> disabled:opacity-50 disabled:cursor-not-allowed">
            <template x-if="uploading && submitMode === 'check_in'">
                <div class="flex items-center gap-1">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Proses...</span>
                </div>
            </template>
            <template x-if="!(uploading && submitMode === 'check_in')">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span><?php echo e($hasCheckedIn ? 'Sudah Check In' : 'Check In'); ?></span>
                </div>
            </template>
        </button>

        <!-- Tombol Check Out -->
        <button type="button"
                @click="submitAttendance('check_out')"
                :disabled="uploading || !cameraActive || <?php echo e(!$hasCheckedIn || $hasCheckedOut ? 'true' : 'false'); ?> || (requireFaceRecognition && !livenessPassed)"
                class="py-3 px-3 text-xs sm:text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 <?php echo e(!$hasCheckedIn || $hasCheckedOut ? 'bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed border border-gray-300 dark:border-gray-700' : 'bg-rose-600 hover:bg-rose-500 text-white shadow-rose-600/20 active:scale-95'); ?> disabled:opacity-50 disabled:cursor-not-allowed">
            <template x-if="uploading && submitMode === 'check_out'">
                <div class="flex items-center gap-1">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Proses...</span>
                </div>
            </template>
            <template x-if="!(uploading && submitMode === 'check_out')">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span><?php echo e($hasCheckedOut ? 'Sudah Check Out' : 'Check Out'); ?></span>
                </div>
            </template>
        </button>
    </div>
</div>
<?php /**PATH C:\Users\AHTAR\filament-ontime\resources\views/filament/components/camera-capture.blade.php ENDPATH**/ ?>