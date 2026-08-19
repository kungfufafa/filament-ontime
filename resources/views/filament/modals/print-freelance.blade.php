<div class="p-6 bg-white text-gray-900 rounded-lg shadow-sm border border-gray-200 font-sans">
    <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
        <h2 class="text-xl font-bold uppercase tracking-wider text-gray-900">{{ $record->company->name ?? 'PERUSAHAAN' }}</h2>
        <p class="text-xs text-gray-600">Divisi {{ $record->division->name ?? '-' }} | Contract / Freelance Worker</p>
    </div>

    <div class="text-center mb-6">
        <h3 class="text-lg font-bold uppercase underline">SURAT KETERANGAN KERJA FREELANCE</h3>
        <p class="text-xs text-gray-500">Nomor: FREELANCE/{{ $record->id }}/{{ date('Y/m') }}</p>
    </div>

    <div class="space-y-4 text-sm text-gray-800 leading-relaxed">
        <p>Menerangkan bahwa mitra kerja freelance di bawah ini:</p>
        
        <table class="w-full text-sm my-3 border-collapse">
            <tr>
                <td class="w-40 py-1 font-semibold">Nama Lengkap</td>
                <td class="py-1">: {{ $record->full_name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">ID / No. Freelance</td>
                <td class="py-1">: {{ $record->freelancer_number }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Keahlian / Vendor</td>
                <td class="py-1">: {{ $record->institution ?: '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Penempatan Divisi</td>
                <td class="py-1">: {{ $record->company->name ?? '-' }} ({{ $record->division->name ?? '-' }})</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">PIC / Supervisor</td>
                <td class="py-1">: {{ $record->supervisor ? $record->supervisor->full_name : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Masa Kontrak Kerja</td>
                <td class="py-1">: {{ $record->start_date ? $record->start_date->format('d F Y') : '-' }} s/d {{ $record->end_date ? $record->end_date->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Status Kontrak</td>
                <td class="py-1">: <span class="px-2 py-0.5 text-xs font-semibold rounded bg-purple-100 text-purple-800">{{ strtoupper($record->status) }}</span></td>
            </tr>
        </table>

        <p class="mt-4">Telah menyelesaikan / sedang menjalankan proyek sesuai dengan kontrak kerja sama freelance yang telah disepakati.</p>
    </div>

    <div class="grid grid-cols-2 gap-8 mt-12 text-center text-xs">
        <div>
            <p class="mb-16">PIC / Supervisor Perusahaan,</p>
            <p class="font-bold underline">{{ $record->supervisor ? $record->supervisor->full_name : 'Supervisor' }}</p>
        </div>
        <div>
            <p class="mb-16">Pimpinan Perusahaan / HRD,</p>
            <p class="font-bold underline">___________________________</p>
        </div>
    </div>

    <div class="mt-8 text-right print:hidden">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700 transition">
            🖨️ Cetak / Print Dokumen
        </button>
    </div>
</div>
