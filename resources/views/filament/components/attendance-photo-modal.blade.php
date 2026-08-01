<div class="space-y-4 p-2 text-center">
    @php
        $disk = config('filesystems.default');
        $checkInUrl = '';
        if ($record->check_in_photo) {
            if (str_starts_with($record->check_in_photo, 'http')) {
                $checkInUrl = $record->check_in_photo;
            } else {
                $checkInUrl = $disk === 's3' 
                    ? Storage::disk($disk)->temporaryUrl($record->check_in_photo, now()->addMinutes(60))
                    : Storage::disk($disk)->url($record->check_in_photo);
            }
        }
        $checkOutUrl = '';
        if ($record->check_out_photo) {
            if (str_starts_with($record->check_out_photo, 'http')) {
                $checkOutUrl = $record->check_out_photo;
            } else {
                $checkOutUrl = $disk === 's3' 
                    ? Storage::disk($disk)->temporaryUrl($record->check_out_photo, now()->addMinutes(60))
                    : Storage::disk($disk)->url($record->check_out_photo);
            }
        }
    @endphp

    @if($record->check_in_photo)
        <div class="space-y-1">
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Foto Check-In ({{ $record->check_in ? $record->check_in->format('H:i:s') . ' WIB' : '-' }})</span>
            <img src="{{ $checkInUrl }}" class="max-h-72 w-auto mx-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" />
        </div>
    @endif

    @if($record->check_out_photo)
        <div class="space-y-1">
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Foto Check-Out ({{ $record->check_out ? $record->check_out->format('H:i:s') . ' WIB' : '-' }})</span>
            <img src="{{ $checkOutUrl }}" class="max-h-72 w-auto mx-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" />
        </div>
    @endif
</div>
