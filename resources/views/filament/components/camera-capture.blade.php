<div x-data="{
    photoPath: $wire.entangle('{{ $getStatePath() }}'),
    previewUrl: null,
    uploading: false,
    cameraActive: false,
    cameraError: null,
    facingMode: 'user',
    stream: null,
    isMirrored: true,

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

    takeSnapshot() {
        if (!this.$refs.video || !this.cameraActive) return;

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
        const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '{{ csrf_token() }}';

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
                <img :src="previewUrl || ('{{ rtrim(Storage::disk('s3')->url(''), '/') }}/' + photoPath)" class="w-full h-full object-cover" />
            </div>
            <div class="flex items-center justify-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Foto selfie berhasil disimpan</span>
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

                <!-- Pesan Kamera Belum Aktif / Error -->
                <template x-if="!cameraActive">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-gray-400 bg-gray-900/90 space-y-2">
                        <template x-if="!cameraError">
                            <p class="text-xs">Membuka kamera...</p>
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
                        <span>Mengunggah foto...</span>
                    </div>
                </template>
            </div>

            <!-- Petunjuk visual bahwa foto belum diambil -->
            <div class="text-center text-[11px] text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 py-1.5 px-3 rounded-md border border-amber-200 dark:border-amber-800/50 max-w-xs mx-auto flex items-center justify-center">
                <span>Klik <strong>"Ambil Foto"</strong> sebelum menekan Submit.</span>
            </div>

            <!-- Action Buttons -->
            <template x-if="cameraActive && !uploading">
                <div class="flex items-center justify-center gap-2 max-w-xs mx-auto">
                    <button type="button" @click="takeSnapshot()" class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg text-xs transition flex items-center justify-center gap-1.5 shadow-sm animate-pulse hover:animate-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Ambil Foto</span>
                    </button>
                    <button type="button" @click="toggleCamera()" class="py-2 px-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-xs transition border border-gray-300 dark:border-gray-600">
                        Ganti Kamera
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>
