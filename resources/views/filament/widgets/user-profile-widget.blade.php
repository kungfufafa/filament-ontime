@php
    $info = $this->getUserInfo();
@endphp

<x-filament-widgets::widget class="fi-wi-user-profile">
    <div class="fi-section rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <!-- User Title Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-200 dark:border-white/10">
            <div>
                <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ $info['name'] }}
                </h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    {{ $info['email'] }} &bull; NIP: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $info['nip'] }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md text-gray-700 dark:bg-white/10 dark:text-gray-300">
                    Role: {{ $info['roles'] }}
                </span>
            </div>
        </div>

        <!-- Clean Key-Value Grid -->
        <dl class="grid grid-cols-2 gap-4 mt-4 sm:grid-cols-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Divisi</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $info['division'] }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Jabatan</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $info['job_title'] }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Level Jabatan</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $info['job_level'] }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Badan Usaha / Company</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white truncate">{{ $info['company'] }}</dd>
            </div>
        </dl>
    </div>
</x-filament-widgets::widget>
