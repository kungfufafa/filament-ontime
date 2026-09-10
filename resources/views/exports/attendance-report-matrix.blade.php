<table>
    <thead>
        <!-- Baris 1: Judul Utama Laporan Bulanan (Warna Natural) -->
        <tr>
            <th colspan="{{ 2 + ($totalDays * 2) + 5 }}" style="font-size: 13pt; font-weight: bold; text-align: center; background-color: #F3F4F6; color: #111827; height: 32px; vertical-align: middle; border: 1px solid #D1D5DB;">
                REKAPITULASI ABSENSI BULANAN {{ strtoupper($workerTypeLabel ?? 'KARYAWAN & PEGAWAI') }}
            </th>
        </tr>
        <!-- Baris 2: Subtitle Informasi Periode & Filter -->
        <tr>
            <th colspan="{{ 2 + ($totalDays * 2) + 5 }}" style="font-size: 9.5pt; text-align: center; background-color: #F9FAFB; color: #4B5563; height: 24px; vertical-align: middle; border: 1px solid #D1D5DB;">
                Periode: {{ $periodLabel }} | Tipe: {{ $workerTypeLabel ?? 'Semua Tipe' }} | Badan Usaha: {{ $companyName ?? 'Semua Badan Usaha' }} | Divisi: {{ $divisionName ?? 'Semua Divisi' }}
            </th>
        </tr>
        <!-- Baris 3: Header Tingkat Bulan -->
        <tr>
            <th rowspan="3" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; vertical-align: middle; border: 1px solid #D1D5DB;">No</th>
            <th rowspan="3" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; vertical-align: middle; border: 1px solid #D1D5DB;">Nama Karyawan</th>

            @foreach($months as $month)
                <th colspan="{{ $month['col_span'] }}" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; border: 1px solid #D1D5DB; vertical-align: middle;">
                    {{ $month['label'] }}
                </th>
            @endforeach

            <th colspan="5" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; border: 1px solid #D1D5DB; vertical-align: middle;">
                TOTAL REKAP
            </th>
        </tr>
        <!-- Baris 4: Header Tanggal -->
        <tr>
            @foreach($months as $month)
                @foreach($month['dates'] as $date)
                    @php
                        $isWeekend = $date->isSaturday() || $date->isSunday();
                        $bgDate = $isWeekend ? '#F3F4F6' : '#FFFFFF';
                        $dayNames = [0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'];
                        $dayLabel = $dayNames[$date->dayOfWeek] ?? $date->format('D');
                    @endphp
                    <th colspan="2" style="font-weight: 600; text-align: center; background-color: {{ $bgDate }}; color: #1F2937; border: 1px solid #D1D5DB; vertical-align: middle;">
                        {{ $date->format('d') }} ({{ $dayLabel }})
                    </th>
                @endforeach
            @endforeach

            <!-- Header Rekap: Hadir (Ijo), Telat (Kuning), Cuti, Libur, Alpa (Merah) -->
            <th rowspan="2" style="font-weight: bold; text-align: center; background-color: #DCFCE7; color: #166534; vertical-align: middle; border: 1px solid #BBF7D0;">Hadir</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; background-color: #FEF9C3; color: #854D0E; vertical-align: middle; border: 1px solid #FEF08A;">Telat</th>
            <th rowspan="2" style="font-weight: 600; text-align: center; background-color: #F3F4F6; color: #374151; vertical-align: middle; border: 1px solid #D1D5DB;">Cuti</th>
            <th rowspan="2" style="font-weight: 600; text-align: center; background-color: #F3F4F6; color: #6B7280; vertical-align: middle; border: 1px solid #D1D5DB;">Libur</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; background-color: #FEE2E2; color: #991B1B; vertical-align: middle; border: 1px solid #FECACA;">Alpa</th>
        </tr>
        <!-- Baris 5: Sub-header Masuk & Keluar -->
        <tr>
            @foreach($months as $month)
                @foreach($month['dates'] as $date)
                    @php
                        $isWeekend = $date->isSaturday() || $date->isSunday();
                        $bgSub = $isWeekend ? '#F9FAFB' : '#FFFFFF';
                    @endphp
                    <th style="font-size: 8.5pt; font-weight: normal; text-align: center; background-color: {{ $bgSub }}; color: #4B5563; border: 1px solid #E5E7EB; vertical-align: middle;">Masuk</th>
                    <th style="font-size: 8.5pt; font-weight: normal; text-align: center; background-color: {{ $bgSub }}; color: #4B5563; border: 1px solid #E5E7EB; vertical-align: middle;">Keluar</th>
                @endforeach
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $index => $row)
            <tr>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #E5E7EB; color: #374151;">{{ $index + 1 }}</td>
                <td style="font-weight: 500; vertical-align: middle; border: 1px solid #E5E7EB; color: #111827;">{{ $row['name'] }}</td>

                @foreach($months as $month)
                    @foreach($month['dates'] as $date)
                        @php
                            $dStr = $date->format('Y-m-d');
                            $day = $row['days'][$dStr] ?? ['in' => '-', 'out' => '-', 'type' => 'absent'];
                            $isWeekend = $date->isSaturday() || $date->isSunday();

                            $bgCell = '#FFFFFF';
                            $textColor = '#111827';

                            if ($day['type'] === 'late') {
                                // Telat: Aksen Kuning lembut
                                $bgCell = '#FEF9C3';
                                $textColor = '#854D0E';
                            } elseif ($day['type'] === 'present') {
                                // Hadir tepat waktu: Teks Hijau
                                $bgCell = '#FFFFFF';
                                $textColor = '#166534';
                            } elseif ($day['type'] === 'holiday' || $isWeekend) {
                                // Libur: Abu-abu netral
                                $bgCell = '#F9FAFB';
                                $textColor = '#6B7280';
                            } elseif ($day['type'] === 'leave') {
                                // Cuti: Biru lembut netral
                                $bgCell = '#EFF6FF';
                                $textColor = '#1D4ED8';
                            } elseif ($day['type'] === 'absent' && $day['in'] === '-') {
                                // Alpa pada hari kerja: Teks Merah
                                $bgCell = '#FFFFFF';
                                $textColor = '#DC2626';
                            }
                        @endphp
                        <td style="text-align: center; vertical-align: middle; background-color: {{ $bgCell }}; color: {{ $textColor }}; border: 1px solid #E5E7EB;">
                            {{ $day['in'] }}
                        </td>
                        <td style="text-align: center; vertical-align: middle; background-color: {{ $bgCell }}; color: {{ $textColor }}; border: 1px solid #E5E7EB;">
                            {{ $day['out'] }}
                        </td>
                    @endforeach
                @endforeach

                <!-- Kolom Total Rekap: Hadir (Ijo), Telat (Kuning), Cuti, Libur, Alpa (Merah) -->
                <td style="text-align: center; font-weight: bold; color: #166534; background-color: #F0FDF4; vertical-align: middle; border: 1px solid #BBF7D0;">{{ $row['summary']['hadir'] }}</td>
                <td style="text-align: center; font-weight: bold; color: #854D0E; background-color: #FEFCE8; vertical-align: middle; border: 1px solid #FEF08A;">{{ $row['summary']['telat'] }}</td>
                <td style="text-align: center; font-weight: 600; color: #374151; background-color: #F9FAFB; vertical-align: middle; border: 1px solid #E5E7EB;">{{ $row['summary']['cuti'] }}</td>
                <td style="text-align: center; font-weight: 600; color: #6B7280; background-color: #F9FAFB; vertical-align: middle; border: 1px solid #E5E7EB;">{{ $row['summary']['libur'] }}</td>
                <td style="text-align: center; font-weight: bold; color: #991B1B; background-color: #FEF2F2; vertical-align: middle; border: 1px solid #FECACA;">{{ $row['summary']['alpa'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 2 + ($totalDays * 2) + 5 }}" style="text-align: center; padding: 16px; color: #6B7280; font-style: italic; border: 1px solid #E5E7EB;">
                    Tidak ada data absensi untuk filter dan periode yang dipilih.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
