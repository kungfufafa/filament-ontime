<?php
    use Filament\Support\Icons\Heroicon;
    use Filament\View\PanelsIconAlias;
    use Illuminate\View\ComponentAttributeBag;

    use function Filament\Support\generate_icon_html;

    $notificationsLabel = __('filament-panels::layout.actions.open_database_notifications.label');

    // `filled(0)` is true in Laravel, so compare against > 0 like Filament core.
    $hasUnreadNotifications = ((int) ($unreadNotificationsCount ?? 0)) > 0;

    if ($hasUnreadNotifications) {
        $notificationsLabel .= ': ' . $unreadNotificationsCount;
    }
?>


<button
    type="button"
    aria-label="<?php echo e($notificationsLabel); ?>"
    aria-haspopup="dialog"
    class="fi-topbar-database-notifications-btn mky-topbar-icon-control relative inline-flex items-center justify-center rounded-lg p-1 text-gray-500 ring-1 ring-gray-200 transition-colors hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:ring-white/10 dark:hover:bg-gray-800 dark:hover:text-white"
>
    <?php echo e(generate_icon_html(
            Heroicon::OutlinedBell,
            alias: PanelsIconAlias::TOPBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON,
            attributes: (new ComponentAttributeBag)->class(['size-6']),
        )); ?>


    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasUnreadNotifications): ?>
        <span
            class="absolute -top-1 -right-1 inline-flex min-w-4 items-center justify-center rounded-full bg-primary-500 px-1 text-[0.625rem] font-semibold leading-none text-white"
            aria-hidden="true"
        >
            <?php echo e($unreadNotificationsCount); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</button>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/vendor/filament-panels/components/topbar/database-notifications-trigger.blade.php ENDPATH**/ ?>