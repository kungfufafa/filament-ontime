<div class="p-6 bg-white text-gray-900 rounded-lg shadow-sm border border-gray-200 font-sans">
    <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
        <h2 class="text-xl font-bold uppercase tracking-wider text-gray-900">{{ $record->company->name ?? 'PERUSAHAAN' }}</h2>
        <p class="text-xs text-gray-600">Divisi {{ $record->division->name ?? '-' }} | Program Internship / Magang</p>
    </div>

    <div class="text-center mb-6">
        <h3 class="text-lg font-bold uppercase underline">SURAT KETERANGAN MAGANG</h3>
        <p class="text-xs text-gray-500">Nomor: MAGANG/{{ $record->id }}/{{ date('Y/m') }}</p>
    </div>

    <div class="space-y-4 text-sm text-gray-800 leading-relaxed">
        <p>Menerangkan bahwa peserta magang di bawah ini:</p>
        
        <table class="w-full text-sm my-3 border-collapse">
            <tr>
                <td class="w-40 py-1 font-semibold">Nama Peserta</td>
                <td class="py-1">: {{ $record->full_name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">NIS / ID Magang</td>
                <td class="py-1">: {{ $record->nis }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Asal Institusi</td>
                <td class="py-1">: {{ $record->institution }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Lokasi Penempatan</td>
                <td class="py-1">: {{ $record->company->name ?? '-' }} ({{ $record->division->name ?? '-' }})</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Pembimbing / Mentor</td>
                <td class="py-1">: {{ $record->mentor ? $record->mentor->full_name : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Periode Magang</td>
                <td class="py-1">: {{ $record->start_date ? $record->start_date->format('d F Y') : '-' }} s/d {{ $record->end_date ? $record->end_date->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Status Program</td>
                <td class="py-1">: <span class="px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-800">{{ strtoupper($record->status) }}</span></td>
            </tr>
        </table>

        <p class="mt-4">Telah melaksanakan kegiatan magang/praktik kerja lapangan di perusahaan kami sesuai dengan standar dan kebijakan yang berlaku.</p>
    </div>

    <div class="grid grid-cols-2 gap-8 mt-12 text-center text-xs">
        <div>
            <p class="mb-16">Pembimbing Magang,</p>
            <p class="font-bold underline">{{ $record->mentor ? $record->mentor->full_name : 'Mentor' }}</p>
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
