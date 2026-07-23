<div x-data="{
    photoPath: $wire.entangle('{{ $getStatePath() }}'),
    previewUrl: null,
    uploading: false,
    cameraActive: false,
    initCamera() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 480 }, height: { ideal: 480 }, facingMode: 'user' } })
                .then((s) => {
                    $refs.video.srcObject = s;
                    this.cameraActive = true;
                })
                .catch((err) => {
                    console.error('Akses kamera gagal:', err);
                });
        }
    },
    takeSnapshot() {
        this.uploading = true;
        const video = $refs.video;
        const canvas = $refs.canvas;
        const context = canvas.getContext('2d');
        
        canvas.width = 300;
        canvas.height = 300;
        
        const minDim = Math.min(video.videoWidth || 480, video.videoHeight || 480);
        const sx = ((video.videoWidth || 480) - minDim) / 2;
        const sy = ((video.videoHeight || 480) - minDim) / 2;

        context.drawImage(video, sx, sy, minDim, minDim, 0, 0, 300, 300);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.5);

        fetch('{{ route('upload.selfie') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ photo_base64: dataUrl })
        })
        .then(res => res.text())
        .then(text => {
            this.uploading = false;
            try {
                const jsonStart = text.indexOf('{');
                const jsonEnd = text.lastIndexOf('}');
                if (jsonStart !== -1 && jsonEnd !== -1) {
                    const data = JSON.parse(text.substring(jsonStart, jsonEnd + 1));
                    if (data.success) {
                        this.previewUrl = data.url;
                        this.photoPath = data.path;
                        $wire.set('{{ $getStatePath() }}', data.path);
                    } else {
                        alert('Gagal mengunggah foto selfie: ' + (data.message || 'Unknown error'));
                    }
                } else {
                    console.error('Response server bukan JSON:', text);
                    alert('Terjadi kendala pada server saat menyimpan foto.');
                }
            } catch (e) {
                console.error('JSON Parse Error:', e, text);
                alert('Gagal memproses respon server.');
            }
        })
        .catch(err => {
            this.uploading = false;
            console.error('Error uploading photo:', err);
            alert('Terjadi kesalahan koneksi saat menyimpan foto.');
        });
    },
    resetPhoto() {
        this.previewUrl = null;
        this.photoPath = null;
        $wire.set('{{ $getStatePath() }}', null);
    }
}" x-init="initCamera()" class="space-y-3">
    
    <label class="block text-sm font-semibold text-gray-900 dark:text-white">
        Foto Selfie Absensi Kamera <span class="text-red-500">*</span>
    </label>

    <!-- Canvas (Hidden) -->
    <canvas x-ref="canvas" class="hidden"></canvas>

    <!-- Preview Output Foto (Jika Sudah Diambil & Diupload) -->
    <template x-if="photoPath">
        <div class="relative rounded-xl overflow-hidden border-2 border-emerald-500 bg-black">
            <img :src="previewUrl || ('/storage/' + photoPath)" class="w-full max-h-64 object-contain mx-auto" />
            <div class="p-2 bg-emerald-600 text-white text-xs font-bold text-center flex items-center justify-between">
                <span>✓ Foto Selfie Tersimpan Permanen</span>
                <button type="button" @click="resetPhoto()" class="px-2 py-1 bg-white text-emerald-800 rounded hover:bg-gray-100 transition">
                    Ulangi Foto
                </button>
            </div>
        </div>
    </template>

    <!-- Live WebCam Video (Jika Foto Belum Diambil) -->
    <template x-if="!photoPath">
        <div class="space-y-3">
            <div class="relative rounded-xl overflow-hidden bg-black border border-gray-300 dark:border-gray-700 aspect-video flex items-center justify-center">
                <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                
                <template x-if="!cameraActive">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-gray-400 bg-gray-900/90">
                        <svg class="w-10 h-10 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <p class="text-xs">Mohon izinkan akses kamera di browser Anda untuk melakukan selfie.</p>
                    </div>
                </template>

                <template x-if="uploading">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-white bg-black/80">
                        <span class="animate-spin text-2xl mb-1">⏳</span>
                        <p class="text-xs font-bold">Mengunggah Foto Selfie...</p>
                    </div>
                </template>
            </div>

            <!-- Tombol Ambil Foto Selfie -->
            <template x-if="cameraActive && !uploading">
                <button type="button" @click="takeSnapshot()" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition flex items-center justify-center gap-2 shadow-sm">
                    <span>📸 Ambil Foto Selfie Kamera</span>
                </button>
            </template>
        </div>
    </template>
</div>
