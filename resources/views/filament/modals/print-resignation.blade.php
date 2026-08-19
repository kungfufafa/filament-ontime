<div class="p-6 bg-white text-gray-900 rounded-lg shadow-sm border border-gray-200 font-sans" id="print-area">
    <!-- Header Kop Perusahaan -->
    <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
        <h2 class="text-xl font-bold uppercase tracking-wider text-gray-900">{{ $record->employee->company->name ?? 'PERUSAHAAN' }}</h2>
        <p class="text-xs text-gray-600">Divisi {{ $record->employee->division->name ?? '-' }} | Official Document</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-6">
        <h3 class="text-lg font-bold uppercase underline">SURAT KETERANGAN PENGUNDURAN DIRI</h3>
        <p class="text-xs text-gray-500">Nomor: RESIGN/{{ $record->id }}/{{ $record->created_at->format('Y/m') }}</p>
    </div>

    <!-- Isi Surat -->
    <div class="space-y-4 text-sm text-gray-800 leading-relaxed">
        <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
        
        <table class="w-full text-sm my-3 border-collapse">
            <tr>
                <td class="w-40 py-1 font-semibold">Nama Lengkap</td>
                <td class="py-1">: {{ $record->employee->full_name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">NIP Karyawan</td>
                <td class="py-1">: {{ $record->employee->nip }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Badan Usaha</td>
                <td class="py-1">: {{ $record->employee->company->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Divisi / Posisi</td>
                <td class="py-1">: {{ $record->employee->division->name ?? '-' }} / {{ $record->employee->jobTitle->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Tanggal Surat</td>
                <td class="py-1">: {{ $record->resignation_date ? $record->resignation_date->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Hari Kerja Terakhir</td>
                <td class="py-1">: <span class="font-bold text-red-600">{{ $record->last_working_day ? $record->last_working_day->format('d F Y') : '-' }}</span></td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Status Pengajuan</td>
                <td class="py-1">: 
                    <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $record->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ strtoupper($record->status) }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="p-3 bg-gray-50 rounded border border-gray-200">
            <p class="font-semibold mb-1">Alasan Pengunduran Diri:</p>
            <p class="italic text-gray-700">{{ $record->reason }}</p>
        </div>

        @if($record->handover_notes)
        <div class="p-3 bg-gray-50 rounded border border-gray-200">
            <p class="font-semibold mb-1">Catatan Serah Terima Tugas / Aset:</p>
            <p class="text-gray-700">{{ $record->handover_notes }}</p>
        </div>
        @endif

        <p class="mt-4">Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <!-- Tanda Tangan -->
    <div class="grid grid-cols-2 gap-8 mt-12 text-center text-xs">
        <div>
            <p class="mb-16">Pemohon (Karyawan),</p>
            <p class="font-bold underline">{{ $record->employee->full_name }}</p>
        </div>
        <div>
            <p class="mb-16">Mengetahui (HRD / Management),</p>
            <p class="font-bold underline">___________________________</p>
        </div>
    </div>

    <!-- Tombol Print Modal -->
    <div class="mt-8 text-right print:hidden">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700 transition">
            🖨️ Cetak / Print Dokumen
        </button>
    </div>
</div>
