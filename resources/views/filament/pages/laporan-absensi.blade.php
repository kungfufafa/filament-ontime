<x-filament-panels::page>
    <form wire:submit.prevent="mount" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                Hasil Rekap Absensi ({{ $this->reportData->count() }} Data)
            </h3>
            <button type="button" wire:click="exportExcel" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg flex items-center gap-2 shadow-sm">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                Export ke Excel
            </button>
        </div>
    </form>

    <div class="mt-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3">Perusahaan & Divisi</th>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Check In</th>
                        <th class="px-4 py-3">Check Out</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($this->reportData as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $row->employee?->company?->name }}
                                <span class="block text-xs font-normal text-gray-500">{{ $row->employee?->division?->name }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $row->employee?->full_name }}
                                <span class="block text-xs font-normal text-gray-500">NIP: {{ $row->employee?->nip }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $row->date ? $row->date->format('d M Y') : '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono">{{ $row->check_in ? $row->check_in->format('H:i:s') : '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono">{{ $row->check_out ? $row->check_out->format('H:i:s') : '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($row->status === 'late')
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                        Terlambat ({{ $row->late_minutes }}m)
                                    </span>
                                @elseif($row->status === 'leave')
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Cuti / Izin
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Hadir
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $row->notes ?? ($row->is_corrected ? 'Koreksi Absensi' : '-') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data absensi yang ditemukan untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
