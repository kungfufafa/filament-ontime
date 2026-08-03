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
                        </div>
                    </div>

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
                </div>
            </div>

            <!-- Status Absensi Hari Ini -->
            <div class="md:col-span-2 p-6 bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Status Absensi Hari Ini</h3>
                        <span class="text-sm font-medium text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Check-In</span>
                            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">
                                {{ $attendance?->check_in ? $attendance->check_in->format('H:i:s') . ' WIB' : '--:--:--' }}
                            </div>
                            @if($attendance?->status)
                                <div class="mt-2">
                                    @if($attendance->status === 'late')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                            Terlambat ({{ $attendance->late_minutes }}m)
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-md bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Check-Out</span>
                            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">
                                {{ $attendance?->check_out ? $attendance->check_out->format('H:i:s') . ' WIB' : '--:--:--' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Check-In & Check-Out -->
                <div class="mt-6 flex flex-wrap items-center gap-4">
                    @if(!$attendance)
                        {{ $this->checkInAction }}
                    @elseif($attendance && !$attendance->check_out)
                        {{ $this->checkOutAction }}
                    @else
                        <div class="px-4 py-2 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm font-semibold">
                            ✓ Absensi Lengkap Hari Ini (Check In & Check Out Selesai)
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
