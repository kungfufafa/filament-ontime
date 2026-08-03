<?php
    use Illuminate\View\ComponentAttributeBag;
    use function Filament\Support\generate_icon_html;

    $navigation = filament()->getNavigation();
    $hasDatabaseNotificationsInSidebar = mekaya_database_notifications_enabled()
        && enum_exists(\Filament\Enums\DatabaseNotificationsPosition::class)
        && mekaya_database_notifications_position() === \Filament\Enums\DatabaseNotificationsPosition::Sidebar;
    $hasUserMenuInSidebar = filament()->hasUserMenu()
        && enum_exists(\Filament\Enums\UserMenuPosition::class)
        && method_exists(filament(), 'getUserMenuPosition')
        && filament()->getUserMenuPosition() === \Filament\Enums\UserMenuPosition::Sidebar;
    $defaultCollapsedGroups = collect($navigation)
        ->filter(fn ($group): bool => filled($group->getLabel()) && $group->isCollapsed())
        ->map(fn ($group): string => $group->getLabel())
        ->values()
        ->all();
?>

<div class="h-full">
    <script>
        (() => {
            const readGroups = (key) => {
                try {
                    const groups = JSON.parse(localStorage.getItem(key))

                    return Array.isArray(groups) ? groups : null
                } catch {
                    return null
                }
            }
            const groups = readGroups('collapsedGroups')
                ?? readGroups('sidebar-collapsed-groups')
                ?? <?php echo \Illuminate\Support\Js::from($defaultCollapsedGroups)->toHtml() ?>

            try {
                localStorage.setItem('collapsedGroups', JSON.stringify(groups))
                localStorage.setItem('sidebar-collapsed-groups', JSON.stringify(groups))
            } catch {
                // The sidebar remains usable when browser storage is unavailable.
            }
        })()
    </script>

    <!-- Desktop Sidebar -->
    <aside
        id="mekaya-desktop-sidebar"
        class="mky-si hidden h-full lg:flex lg:shrink-0"
        x-bind:class="{ 'mky-si-collapsed': $store.sidebar.isCollapsed }"
        aria-label="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>"
    >
        <div class="mky-si-content h-full flex-1 overflow-hidden transition-[width] duration-200">
            <div class="from-primary-600 to-primary-100 dark:to-primary-600/10 h-1 bg-linear-to-br"></div>

            <div class="flex h-full flex-col">
                <!-- Header / Branding -->
                <div class="py-4 px-6 border-b border-dashed border-gray-200 dark:border-white/20">
                    <div class="relative flex items-center gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(mekaya()->hasBrandVisual()): ?>
                            <?php if (isset($component)) { $__componentOriginal4aad98a0a7e23b9d4e3436208d07584c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.brand','data' => ['class' => 'size-6 shrink-0','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-6 shrink-0','aria-hidden' => 'true']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c)): ?>
<?php $attributes = $__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c; ?>
<?php unset($__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4aad98a0a7e23b9d4e3436208d07584c)): ?>
<?php $component = $__componentOriginal4aad98a0a7e23b9d4e3436208d07584c; ?>
<?php unset($__componentOriginal4aad98a0a7e23b9d4e3436208d07584c); ?>
<?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div
                            class="mky-sidebar-brand-copy min-w-0 truncate overflow-hidden transition-all duration-200"
                            x-cloak
                            x-show="! $store.sidebar.isCollapsed"
                            x-transition:enter="transition-opacity delay-100 duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        >
                            <h4 class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo e(mekaya()->brandName()); ?>

                            </h4>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex min-h-0 flex-1 flex-col justify-between">
                    <div class="relative min-h-0 flex-1">
                        <!-- Top fade gradient -->
                        <div
                            class="pointer-events-none absolute top-0 right-0.5 left-0 z-10 h-6 bg-linear-to-b from-gray-50 to-transparent dark:from-gray-950"
                        ></div>

                        <div class="mky-si-scroll h-full overflow-y-auto">
                            <nav class="mky-si-nav px-3 py-3" aria-label="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>">
                                <?php echo $__env->make('mekaya::livewire.partials.mekaya-sidebar-navigation', [
                                    'navigation' => $navigation,
                                    'navigationIdPrefix' => 'desktop',
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </nav>
                        </div>

                        <!-- Bottom fade gradient -->
                        <div
                            class="pointer-events-none absolute right-0.5 bottom-0 left-0 z-10 h-6 bg-linear-to-t from-gray-50 to-transparent dark:from-gray-950"
                        ></div>
                    </div>

                    <!-- Footer -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->auth()->check() && ($hasDatabaseNotificationsInSidebar || $hasUserMenuInSidebar)): ?>
                        <div class="mky-sidebar border-t border-gray-200 px-3 pt-3 pb-6 dark:border-white/20">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasDatabaseNotificationsInSidebar && ($dbNotificationsComponent = mekaya_database_notifications_component())): ?>
                                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split($dbNotificationsComponent, [
                                    'lazy' => mekaya_database_notifications_is_lazy(),
                                ]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1143019587-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasUserMenuInSidebar): ?>
                                <?php if (isset($component)) { $__componentOriginalf72c4437b846e6919081d8fc29939c50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf72c4437b846e6919081d8fc29939c50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.user-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::user-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf72c4437b846e6919081d8fc29939c50)): ?>
<?php $attributes = $__attributesOriginalf72c4437b846e6919081d8fc29939c50; ?>
<?php unset($__attributesOriginalf72c4437b846e6919081d8fc29939c50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf72c4437b846e6919081d8fc29939c50)): ?>
<?php $component = $__componentOriginalf72c4437b846e6919081d8fc29939c50; ?>
<?php unset($__componentOriginalf72c4437b846e6919081d8fc29939c50); ?>
<?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </aside>

    <div x-cloak x-show="$store.sidebar.isOpen" class="lg:hidden">
        <div
            class="mky-sidebar-backdrop fixed inset-0 z-40 bg-gray-950/50 backdrop-blur-xs dark:bg-gray-950/75"
            x-show="$store.sidebar.isOpen"
            x-transition:enter="transition-opacity duration-300 ease-linear"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-300 ease-linear"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="$store.sidebar.close()"
            aria-hidden="true"
        ></div>

        <div
            id="mekaya-mobile-sidebar"
            class="mky-sidebar-mobile-dialog pointer-events-none fixed inset-0 z-50 flex"
            role="dialog"
            aria-modal="true"
            aria-label="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>"
            x-trap.noscroll="$store.sidebar.isOpen"
        >
            <div
                x-cloak
                x-show="$store.sidebar.isOpen"
                x-transition:enter="transform transition duration-200 ease-in-out"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition duration-200 ease-in-out"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="mky-sidebar-mobile-panel pointer-events-auto relative flex w-full max-w-xs flex-col bg-white dark:bg-gray-900"
            >
                <div class="from-primary-600 to-primary-100 dark:to-primary-600/10 h-1 bg-linear-to-br"></div>

                <div class="flex h-full flex-col overflow-hidden">
                    <!-- Header / Branding -->
                    <div class="px-3 py-4">
                        <div
                            class="relative flex items-start gap-3 rounded-lg bg-white py-2 shadow-xs ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/20"
                        >
                            <a
                                href="<?php echo e(filament()->getUrl()); ?>"
                                class="shrink-0 rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                                aria-label="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>"
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(mekaya()->hasBrandVisual()): ?>
                                    <?php if (isset($component)) { $__componentOriginal4aad98a0a7e23b9d4e3436208d07584c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.brand','data' => ['class' => 'size-8','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-8','aria-hidden' => 'true']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c)): ?>
<?php $attributes = $__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c; ?>
<?php unset($__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4aad98a0a7e23b9d4e3436208d07584c)): ?>
<?php $component = $__componentOriginal4aad98a0a7e23b9d4e3436208d07584c; ?>
<?php unset($__componentOriginal4aad98a0a7e23b9d4e3436208d07584c); ?>
<?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="absolute inset-0"></span>
                            </a>

                            <div class="truncate">
                                <h4 class="font-heading truncate text-sm/4 font-medium text-gray-900 dark:text-white">
                                    <?php echo e(mekaya()->brandName()); ?>

                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex min-h-0 flex-1 flex-col justify-between">
                        <div class="relative min-h-0 flex-1">
                            <!-- Top fade gradient -->
                            <div
                                class="pointer-events-none absolute top-0 right-0.5 left-0 z-10 h-6 bg-linear-to-b from-gray-50 to-transparent dark:from-gray-950"
                            ></div>

                            <div class="mky-si-scroll h-full overflow-y-auto">
                            <nav class="mky-si-nav px-3 py-3" aria-label="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>">
                                    <?php echo $__env->make('mekaya::livewire.partials.mekaya-sidebar-navigation', [
                                        'navigation' => $navigation,
                                        'navigationIdPrefix' => 'mobile',
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </nav>
                            </div>

                            <!-- Bottom fade gradient -->
                            <div
                                class="pointer-events-none absolute right-0.5 bottom-0 left-0 z-10 h-6 bg-linear-to-t from-gray-50 to-transparent dark:from-gray-950"
                            ></div>
                        </div>

                        <!-- Footer -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->auth()->check() && ($hasDatabaseNotificationsInSidebar || $hasUserMenuInSidebar)): ?>
                            <div class="mky-sidebar border-t border-gray-200 px-3 pt-3 pb-6 dark:border-white/20">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasDatabaseNotificationsInSidebar && ($dbNotificationsComponent = mekaya_database_notifications_component())): ?>
                                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split($dbNotificationsComponent, [
                                        'lazy' => mekaya_database_notifications_is_lazy(),
                                    ]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1143019587-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasUserMenuInSidebar): ?>
                                    <?php if (isset($component)) { $__componentOriginalf72c4437b846e6919081d8fc29939c50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf72c4437b846e6919081d8fc29939c50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.user-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::user-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf72c4437b846e6919081d8fc29939c50)): ?>
<?php $attributes = $__attributesOriginalf72c4437b846e6919081d8fc29939c50; ?>
<?php unset($__attributesOriginalf72c4437b846e6919081d8fc29939c50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf72c4437b846e6919081d8fc29939c50)): ?>
<?php $component = $__componentOriginalf72c4437b846e6919081d8fc29939c50; ?>
<?php unset($__componentOriginalf72c4437b846e6919081d8fc29939c50); ?>
<?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="pointer-events-auto z-10 p-2">
                <button
                    type="button"
                    x-show="$store.sidebar.isOpen"
                    @click="$store.sidebar.close()"
                    class="mky-sidebar-close-control flex size-11 items-center justify-center rounded-full bg-gray-900/60 text-white transition-colors hover:bg-gray-900/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    aria-label="<?php echo e(__('mekaya::ui.sidebar.close')); ?>"
                >
                    <span class="sr-only"><?php echo e(__('mekaya::ui.sidebar.close')); ?></span>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('untitledui-x-close'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5','aria-hidden' => 'true']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                </button>
            </div>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal028e05680f6c5b1e293abd7fbe5f9758 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-actions::components.modals','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-actions::modals'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758)): ?>
<?php $attributes = $__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758; ?>
<?php unset($__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal028e05680f6c5b1e293abd7fbe5f9758)): ?>
<?php $component = $__componentOriginal028e05680f6c5b1e293abd7fbe5f9758; ?>
<?php unset($__componentOriginal028e05680f6c5b1e293abd7fbe5f9758); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/livewire/mekaya-sidebar.blade.php ENDPATH**/ ?>