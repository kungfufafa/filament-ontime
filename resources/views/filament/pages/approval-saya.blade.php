<x-filament-panels::page>
    @php
        $pendingLeaves = $this->pendingLeaveRequests;
        $pendingOvertimes = $this->pendingOvertimeRequests;
        $pendingCorrections = $this->pendingCorrections;
        $totalPending = $pendingLeaves->count() + $pendingOvertimes->count() + $pendingCorrections->count();
    @endphp

    <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-950 border border-primary-200 dark:border-primary-800 rounded-xl flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-primary-900 dark:text-primary-100">Pusat Persetujuan (Approval Dashboard)</h3>
            <p class="text-xs text-primary-700 dark:text-primary-300">Menampilkan semua item pengajuan yang membutuhkan tindakan persetujuan Anda saat ini.</p>
        </div>
        <div class="px-3 py-1 bg-primary-600 text-white font-black text-sm rounded-lg">
            {{ $totalPending }} Menunggu
        </div>
    </div>

    @if($totalPending === 0)
        <div class="p-12 text-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
            <x-heroicon-o-check-circle class="w-12 h-12 mx-auto text-green-500" />
            <h3 class="mt-3 text-base font-bold text-gray-900 dark:text-white">Tidak Ada Antrean Approval</h3>
            <p class="mt-1 text-sm text-gray-500">Semua pengajuan yang membutuhkan tindakan Anda telah diproses.</p>
        </div>
    @else
        <!-- Section Cuti / Izin -->
        @if($pendingLeaves->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-info-500" />
                    Pengajuan Cuti / Izin ({{ $pendingLeaves->count() }})
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($pendingLeaves as $item)
                        <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between border-b pb-2 dark:border-gray-800">
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $item->employee?->full_name }}</span>
                                        <span class="text-xs text-gray-500 block">{{ $item->employee?->company?->name }} - {{ $item->employee?->division?->name }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-info-50 text-info-700 dark:bg-info-950 dark:text-info-300">
                                        Tahap {{ $item->current_step }} dari {{ $item->approvalSteps->max('step_order') }}
                                    </span>
                                </div>
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <p><strong>Jenis:</strong> {{ match($item->leave_type) { 'annual_leave' => 'Cuti Tahunan', 'permission' => 'Izin', 'sick' => 'Sakit', default => $item->leave_type } }} ({{ $item->days_count }} Hari)</p>
                                    <p><strong>Rentang:</strong> {{ $item->start_date->format('d M Y') }} - {{ $item->end_date->format('d M Y') }}</p>
                                    <p><strong>Alasan:</strong> {{ $item->reason }}</p>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t dark:border-gray-800 flex items-center justify-end gap-2">
                                <button type="button" wire:click="rejectRequest('leave', {{ $item->id }})" class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950 border border-red-200 rounded-lg">
                                    Tolak
                                </button>
                                <button type="button" wire:click="approveRequest('leave', {{ $item->id }})" class="px-4 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">
                                    Setujui
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section Lembur -->
        @if($pendingOvertimes->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-5 h-5 text-warning-500" />
                    Pengajuan Lembur ({{ $pendingOvertimes->count() }})
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($pendingOvertimes as $item)
                        <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between border-b pb-2 dark:border-gray-800">
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $item->employee?->full_name }}</span>
                                        <span class="text-xs text-gray-500 block">{{ $item->employee?->company?->name }} - {{ $item->employee?->division?->name }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-warning-50 text-warning-700 dark:bg-warning-950 dark:text-warning-300">
                                        Tahap {{ $item->current_step }} dari {{ $item->approvalSteps->max('step_order') }}
                                    </span>
                                </div>
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <p><strong>Tanggal Lembur:</strong> {{ $item->date->format('d M Y') }}</p>
                                    <p><strong>Jam:</strong> {{ $item->start_time }} - {{ $item->end_time }} (Estimasi: {{ $item->duration_minutes }}m)</p>
                                    <p><strong>Tugas:</strong> {{ $item->reason }}</p>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t dark:border-gray-800 flex items-center justify-end gap-2">
                                <button type="button" wire:click="rejectRequest('overtime', {{ $item->id }})" class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950 border border-red-200 rounded-lg">
                                    Tolak
                                </button>
                                <button type="button" wire:click="approveRequest('overtime', {{ $item->id }})" class="px-4 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">
                                    Setujui
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section Koreksi Absensi -->
        @if($pendingCorrections->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-o-document-check class="w-5 h-5 text-primary-500" />
                    Koreksi Absensi ({{ $pendingCorrections->count() }})
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($pendingCorrections as $item)
                        <div class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between border-b pb-2 dark:border-gray-800">
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $item->employee?->full_name }}</span>
                                        <span class="text-xs text-gray-500 block">{{ $item->employee?->company?->name }} - {{ $item->employee?->division?->name }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                                        Tahap {{ $item->current_step }} dari {{ $item->approvalSteps->max('step_order') }}
                                    </span>
                                </div>
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <p><strong>Tanggal Absensi:</strong> {{ $item->date->format('d M Y') }}</p>
                                    <p><strong>Usulan Check-In:</strong> {{ $item->corrected_check_in ? $item->corrected_check_in->format('H:i') : '-' }} | <strong>Check-Out:</strong> {{ $item->corrected_check_out ? $item->corrected_check_out->format('H:i') : '-' }}</p>
                                    <p><strong>Alasan:</strong> {{ $item->reason }}</p>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t dark:border-gray-800 flex items-center justify-end gap-2">
                                <button type="button" wire:click="rejectRequest('correction', {{ $item->id }})" class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950 border border-red-200 rounded-lg">
                                    Tolak
                                </button>
                                <button type="button" wire:click="approveRequest('correction', {{ $item->id }})" class="px-4 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">
                                    Setujui
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
