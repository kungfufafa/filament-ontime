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
