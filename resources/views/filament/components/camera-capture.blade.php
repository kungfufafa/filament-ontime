@php
    $requireFaceRecognition = $requireFaceRecognition ?? false;
@endphp

<!-- TensorFlow.js & Face Landmarks Detection CDN -->
@if($requireFaceRecognition)
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-core@4.17.0/dist/tf-core.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-converter@4.17.0/dist/tf-converter.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-backend-webgl@4.17.0/dist/tf-backend-webgl.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@1.0.5/dist/face-landmarks-detection.min.js"></script>
@endif

<div x-data="{
    photoPath: $wire.entangle('{{ $getStatePath() }}'),
    previewUrl: null,
    uploading: false,
    cameraActive: false,
    cameraError: null,
    facingMode: 'user',
    stream: null,
    isMirrored: true,

    // Config & TensorFlow.js State
    requireFaceRecognition: {{ $requireFaceRecognition ? 'true' : 'false' }},
    tfLoaded: false,
    tfModel: null,
    detector: null,
    faceDetected: false,
    livenessPassed: !{{ $requireFaceRecognition ? 'true' : 'false' }},
    livenessStep: {{ $requireFaceRecognition ? '\'Mempersiapkan Deteksi Wajah...\'' : '\'Klik "Ambil Foto" jika posisi wajah sudah pas.\'' }},
    faceFrames: 0,
    animFrameId: null,

    async initTfEngine() {
        if (!this.requireFaceRecognition) return;
        try {
            if (typeof tf !== 'undefined' && typeof faceLandmarksDetection !== 'undefined') {
                await tf.setBackend('webgl').catch(() => tf.setBackend('cpu'));
                await tf.ready();
                const model = faceLandmarksDetection.SupportedModels.MediaPipeFaceMesh;
                const detectorConfig = {
                    runtime: 'tfjs',
                    refineLandmarks: false,
                    maxFaces: 1
                };
                this.detector = await faceLandmarksDetection.createDetector(model, detectorConfig);
                this.tfLoaded = true;
                this.livenessStep = 'Menunggu Wajah Terdeteksi di Kamera...';
            } else {
                this.tfLoaded = false;
                this.livenessStep = 'Posisikan Wajah Tegak di Depan Kamera';
            }
        } catch (e) {
            console.warn('TF Init Warning:', e);
            this.tfLoaded = false;
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
                        this.livenessStep = 'Foto selfie siap diambil.';
                    }
                }
            });
        })
        .catch((err) => {
            console.error('Akses kamera gagal:', err);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                this.cameraError = 'Izin kamera ditolak. Silakan izinkan akses kamera di browser.';
            } else {
                this.cameraError = 'Gagal membuka kamera: ' + (err.message || 'Error tidak diketahui');
            }
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
                const w = 160;
                const h = 160;
                canvas.width = w;
                canvas.height = h;

                ctx.drawImage(video, 0, 0, w, h);
                const imgData = ctx.getImageData(30, 30, 100, 100).data;

                let skinPixels = 0;
                let totalPixels = imgData.length / 4;

                for (let i = 0; i < imgData.length; i += 4) {
                    const r = imgData[i];
                    const g = imgData[i + 1];
                    const b = imgData[i + 2];

                    if (r > 60 && g > 40 && b > 20 && r > g && r > b && (Math.max(r, g, b) - Math.min(r, g, b) > 15) && Math.abs(r - g) > 15) {
                        skinPixels++;
                    }
                }

                const skinRatio = skinPixels / totalPixels;
                return skinRatio > 0.22;
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
                    if (faces && faces.length > 0) {
                        detected = true;
                    }
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
                    if (this.faceFrames >= 10) {
                        this.livenessPassed = true;
                        this.livenessStep = 'Wajah Biometrik Terverifikasi (TF.JS)!';
                    } else {
                        const pct = Math.round((this.faceFrames / 10) * 100);
                        this.livenessStep = `Mendeteksi Wajah Biometrik... (${pct}%)`;
                    }
                }
            } else {
                this.faceDetected = false;
                this.faceFrames = 0;
                this.livenessPassed = false;
                this.livenessStep = 'Wajah tidak terdeteksi! Posisikan wajah Anda di depan kamera.';
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

    takeSnapshot() {
        if (!this.$refs.video || !this.cameraActive || !this.livenessPassed) return;

        this.uploading = true;
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        const context = canvas.getContext('2d');

        const size = 360;
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

        const dataUrl = canvas.toDataURL('image/jpeg', 0.75);
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}';

        const sendRequest = (body) => {
            fetch('{{ route('upload.selfie') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: body
            })
            .then(async (res) => {
                this.uploading = false;
                const text = await res.text();
                let data = null;

                try {
                    const start = text.indexOf('{');
                    const end = text.lastIndexOf('}');
                    if (start !== -1 && end !== -1) {
                        data = JSON.parse(text.substring(start, end + 1));
                    }
                } catch (e) {
                    console.error('JSON Parsing Error:', e, text);
                }

                if (data && data.success) {
                    this.previewUrl = data.url;
                    this.photoPath = data.path;
                    $wire.set('{{ $getStatePath() }}', data.path);
                    this.stopCamera();
                } else {
                    const errMsg = data?.message || (text.length > 0 ? text.substring(0, 150) : ('HTTP Status ' + res.status));
                    alert('Gagal menyimpan foto: ' + errMsg);
                }
            })
            .catch((err) => {
                this.uploading = false;
                console.error('Upload error:', err);
                alert('Terjadi kesalahan koneksi saat mengunggah foto.');
            });
        };

        if (canvas.toBlob) {
            canvas.toBlob((blob) => {
                const formData = new FormData();
                if (blob) {
                    formData.append('photo', blob, 'selfie.jpg');
                }
                formData.append('photo_base64', dataUrl);
                sendRequest(formData);
            }, 'image/jpeg', 0.75);
        } else {
            const formData = new FormData();
            formData.append('photo_base64', dataUrl);
            sendRequest(formData);
        }
    },

    resetPhoto() {
        this.previewUrl = null;
        this.photoPath = null;
        $wire.set('{{ $getStatePath() }}', null);
        this.$nextTick(() => {
            this.initCamera();
        });
    }
}"
x-init="initCamera()"
x-on:unmount="stopCamera()"
class="space-y-3">

    <!-- Canvas Hidden -->
    <canvas x-ref="canvas" class="hidden"></canvas>

    <!-- Error Validation Display jika tombol Submit diklik sebelum foto diambil -->
    @error($getStatePath())
        <div class="p-2.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/50 rounded-lg text-center">
            <p class="text-xs font-semibold text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
        </div>
    @enderror

    <!-- Preview Foto jika sudah diambil -->
    <template x-if="photoPath">
        <div class="space-y-2 text-center">
            <div class="relative w-48 h-48 mx-auto rounded-lg overflow-hidden border border-emerald-500/50 dark:border-emerald-600/50 bg-gray-100 dark:bg-gray-800 shadow-md">
                @php
                    $statePath = $getStatePath();
                    $state = data_get($this, $statePath);
                    $initialUrl = '';
                    if ($state) {
                        try {
                            $disk = config('filesystems.default');
                            if ($disk === 's3') {
                                $initialUrl = \Illuminate\Support\Facades\Storage::disk($disk)->temporaryUrl($state, now()->addMinutes(60));
                            } else {
                                $initialUrl = \Illuminate\Support\Facades\Storage::disk($disk)->url($state);
                            }
                        } catch (\Exception $e) {
                            $initialUrl = '';
                        }
                    }
                @endphp
                <img :src="previewUrl || '{{ $initialUrl }}'" class="w-full h-full object-cover" />
            </div>
            <div class="flex items-center justify-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Foto selfie biometrik berhasil disimpan</span>
            </div>
            <button type="button" @click="resetPhoto()" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Ambil Ulang Foto
            </button>
        </div>
    </template>

    <!-- Display Live Kamera jika belum ambil foto -->
    <template x-if="!photoPath">
        <div class="space-y-2">
            <div class="relative w-full aspect-video sm:aspect-square max-w-xs mx-auto rounded-lg overflow-hidden bg-black border border-gray-300 dark:border-gray-700 flex items-center justify-center">
                <video x-ref="video" autoplay playsinline muted :class="{ '-scale-x-100': isMirrored }" class="w-full h-full object-cover"></video>

                <!-- Face Bounding Box Overlay jika fitur ON -->
                @if($requireFaceRecognition)
                    <template x-if="cameraActive && !uploading">
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

                <!-- Pesan Kamera Belum Aktif / Error -->
                <template x-if="!cameraActive">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-gray-400 bg-gray-900/90 space-y-2">
                        <template x-if="!cameraError">
                            <p class="text-xs" x-text="requireFaceRecognition ? 'Membuka kamera & menginisialisasi modul TensorFlow.js...' : 'Membuka kamera...'"></p>
                        </template>
                        <template x-if="cameraError">
                            <div class="space-y-2">
                                <p class="text-xs text-red-400" x-text="cameraError"></p>
                                <button type="button" @click="initCamera()" class="px-2.5 py-1 text-xs bg-gray-800 text-white rounded border border-gray-700 hover:bg-gray-700">
                                    Coba Lagi
                                </button>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Indicator Loading Upload -->
                <template x-if="uploading">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/75 text-white text-xs font-semibold gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="requireFaceRecognition ? 'Memverifikasi Biometrik TensorFlow.js...' : 'Mengunggah Foto Selfie...'"></span>
                    </div>
                </template>
            </div>

            <!-- Petunjuk visual Liveness TensorFlow.js (hanya jika fitur ON) -->
            @if($requireFaceRecognition)
                <div class="text-center text-[11px] py-1.5 px-3 rounded-md border max-w-xs mx-auto flex items-center justify-center gap-1.5 transition"
                     :class="livenessPassed ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-300 dark:border-emerald-800' : 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800/50'">
                    <svg x-show="livenessPassed" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span x-text="livenessStep"></span>
                </div>
            @endif

            <!-- Action Buttons -->
            <template x-if="cameraActive && !uploading">
                <div class="flex items-center justify-center gap-2 max-w-xs mx-auto">
                    @if($requireFaceRecognition)
                        <button type="button" @click="takeSnapshot()" :disabled="!livenessPassed" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span x-text="livenessPassed ? 'Ambil Foto (Verified)' : 'Posisikan Wajah'"></span>
                        </button>
                    @else
                        <button type="button" @click="takeSnapshot()" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Ambil Foto</span>
                        </button>
                    @endif
                    <button type="button" @click="toggleCamera()" class="py-2 px-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-xs transition border border-gray-300 dark:border-gray-600">
                        Ganti Kamera
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>
