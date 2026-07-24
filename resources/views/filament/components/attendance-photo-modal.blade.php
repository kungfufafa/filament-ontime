<div class="space-y-4 p-2 text-center">
    @if($record->check_in_photo)
        <div class="space-y-1">
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Foto Check-In ({{ $record->check_in ? $record->check_in->format('H:i:s') . ' WIB' : '-' }})</span>
            <img src="{{ str_starts_with($record->check_in_photo, 'http') ? $record->check_in_photo : asset('storage/' . $record->check_in_photo) }}" class="max-h-72 w-auto mx-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" />
        </div>
    @endif

    @if($record->check_out_photo)
        <div class="space-y-1">
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Foto Check-Out ({{ $record->check_out ? $record->check_out->format('H:i:s') . ' WIB' : '-' }})</span>
            <img src="{{ str_starts_with($record->check_out_photo, 'http') ? $record->check_out_photo : asset('storage/' . $record->check_out_photo) }}" class="max-h-72 w-auto mx-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" />
        </div>
    @endif
</div>
