<table>
    <thead>
        <!-- Baris 1: Judul Utama Laporan Bulanan (Warna Natural) -->
        <tr>
            <th colspan="<?php echo e(2 + ($totalDays * 2) + 5); ?>" style="font-size: 13pt; font-weight: bold; text-align: center; background-color: #F3F4F6; color: #111827; height: 32px; vertical-align: middle; border: 1px solid #D1D5DB;">
                REKAPITULASI ABSENSI BULANAN <?php echo e(strtoupper($workerTypeLabel ?? 'KARYAWAN & PEGAWAI')); ?>

            </th>
        </tr>
        <!-- Baris 2: Subtitle Informasi Periode & Filter -->
        <tr>
            <th colspan="<?php echo e(2 + ($totalDays * 2) + 5); ?>" style="font-size: 9.5pt; text-align: center; background-color: #F9FAFB; color: #4B5563; height: 24px; vertical-align: middle; border: 1px solid #D1D5DB;">
                Periode: <?php echo e($periodLabel); ?> | Tipe: <?php echo e($workerTypeLabel ?? 'Semua Tipe'); ?> | Badan Usaha: <?php echo e($companyName ?? 'Semua Badan Usaha'); ?> | Divisi: <?php echo e($divisionName ?? 'Semua Divisi'); ?>

            </th>
        </tr>
        <!-- Baris 3: Header Tingkat Bulan -->
        <tr>
            <th rowspan="3" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; vertical-align: middle; border: 1px solid #D1D5DB;">No</th>
            <th rowspan="3" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; vertical-align: middle; border: 1px solid #D1D5DB;">Nama Karyawan</th>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <th colspan="<?php echo e($month['col_span']); ?>" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; border: 1px solid #D1D5DB; vertical-align: middle;">
                    <?php echo e($month['label']); ?>

                </th>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <th colspan="5" style="font-weight: bold; text-align: center; background-color: #E5E7EB; color: #111827; border: 1px solid #D1D5DB; vertical-align: middle;">
                TOTAL REKAP
            </th>
        </tr>
        <!-- Baris 4: Header Tanggal -->
        <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $month['dates']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $isWeekend = $date->isSaturday() || $date->isSunday();
                        $bgDate = $isWeekend ? '#F3F4F6' : '#FFFFFF';
                        $dayNames = [0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'];
                        $dayLabel = $dayNames[$date->dayOfWeek] ?? $date->format('D');
                    ?>
                    <th colspan="2" style="font-weight: 600; text-align: center; background-color: <?php echo e($bgDate); ?>; color: #1F2937; border: 1px solid #D1D5DB; vertical-align: middle;">
                        <?php echo e($date->format('d')); ?> (<?php echo e($dayLabel); ?>)
                    </th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <!-- Header Rekap: Hadir (Ijo), Telat (Kuning), Cuti, Libur, Alpa (Merah) -->
            <th rowspan="2" style="font-weight: bold; text-align: center; background-color: #DCFCE7; color: #166534; vertical-align: middle; border: 1px solid #BBF7D0;">Hadir</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; background-color: #FEF9C3; color: #854D0E; vertical-align: middle; border: 1px solid #FEF08A;">Telat</th>
            <th rowspan="2" style="font-weight: 600; text-align: center; background-color: #F3F4F6; color: #374151; vertical-align: middle; border: 1px solid #D1D5DB;">Cuti</th>
            <th rowspan="2" style="font-weight: 600; text-align: center; background-color: #F3F4F6; color: #6B7280; vertical-align: middle; border: 1px solid #D1D5DB;">Libur</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; background-color: #FEE2E2; color: #991B1B; vertical-align: middle; border: 1px solid #FECACA;">Alpa</th>
        </tr>
        <!-- Baris 5: Sub-header Masuk & Keluar -->
        <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $month['dates']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $isWeekend = $date->isSaturday() || $date->isSunday();
                        $bgSub = $isWeekend ? '#F9FAFB' : '#FFFFFF';
                    ?>
                    <th style="font-size: 8.5pt; font-weight: normal; text-align: center; background-color: <?php echo e($bgSub); ?>; color: #4B5563; border: 1px solid #E5E7EB; vertical-align: middle;">Masuk</th>
                    <th style="font-size: 8.5pt; font-weight: normal; text-align: center; background-color: <?php echo e($bgSub); ?>; color: #4B5563; border: 1px solid #E5E7EB; vertical-align: middle;">Keluar</th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td style="text-align: center; vertical-align: middle; border: 1px solid #E5E7EB; color: #374151;"><?php echo e($index + 1); ?></td>
                <td style="font-weight: 500; vertical-align: middle; border: 1px solid #E5E7EB; color: #111827;"><?php echo e($row['name']); ?></td>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $month['dates']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
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
                        ?>
                        <td style="text-align: center; vertical-align: middle; background-color: <?php echo e($bgCell); ?>; color: <?php echo e($textColor); ?>; border: 1px solid #E5E7EB;">
                            <?php echo e($day['in']); ?>

                        </td>
                        <td style="text-align: center; vertical-align: middle; background-color: <?php echo e($bgCell); ?>; color: <?php echo e($textColor); ?>; border: 1px solid #E5E7EB;">
                            <?php echo e($day['out']); ?>

                        </td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <!-- Kolom Total Rekap: Hadir (Ijo), Telat (Kuning), Cuti, Libur, Alpa (Merah) -->
                <td style="text-align: center; font-weight: bold; color: #166534; background-color: #F0FDF4; vertical-align: middle; border: 1px solid #BBF7D0;"><?php echo e($row['summary']['hadir']); ?></td>
                <td style="text-align: center; font-weight: bold; color: #854D0E; background-color: #FEFCE8; vertical-align: middle; border: 1px solid #FEF08A;"><?php echo e($row['summary']['telat']); ?></td>
                <td style="text-align: center; font-weight: 600; color: #374151; background-color: #F9FAFB; vertical-align: middle; border: 1px solid #E5E7EB;"><?php echo e($row['summary']['cuti']); ?></td>
                <td style="text-align: center; font-weight: 600; color: #6B7280; background-color: #F9FAFB; vertical-align: middle; border: 1px solid #E5E7EB;"><?php echo e($row['summary']['libur']); ?></td>
                <td style="text-align: center; font-weight: bold; color: #991B1B; background-color: #FEF2F2; vertical-align: middle; border: 1px solid #FECACA;"><?php echo e($row['summary']['alpa']); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr>
                <td colspan="<?php echo e(2 + ($totalDays * 2) + 5); ?>" style="text-align: center; padding: 16px; color: #6B7280; font-style: italic; border: 1px solid #E5E7EB;">
                    Tidak ada data absensi untuk filter dan periode yang dipilih.
                </td>
            </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\Users\AHTAR\filament-ontime\resources\views/exports/attendance-report-matrix.blade.php ENDPATH**/ ?>