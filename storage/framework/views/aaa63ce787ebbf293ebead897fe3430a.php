<div
    class="mky-header sticky top-0 z-20 flex h-16 shrink-0 border-b border-gray-200 bg-gray-50 lg:h-auto lg:rounded-tl-xl lg:py-2 dark:border-white/10 dark:bg-gray-950"
>
    <button
        type="button"
        @click.stop="$store.sidebar.open()"
        class="mky-topbar-icon-control inline-flex items-center justify-center border-r border-gray-200 px-4 text-gray-500 transition-colors hover:text-gray-700 lg:hidden dark:border-white/10 dark:text-gray-400 dark:hover:text-white"
        aria-label="<?php echo e(__('mekaya::ui.sidebar.open')); ?>"
        aria-controls="mekaya-mobile-sidebar"
        x-bind:aria-expanded="$store.sidebar.isOpen"
    >
        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('untitledui-menu-03'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-6','aria-hidden' => 'true']); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->isSidebarCollapsibleOnDesktop()): ?>
        <button
            type="button"
            x-on:click="$store.sidebar.toggleCollapse()"
            class="mky-topbar-icon-control hidden items-center justify-center border-r border-gray-200 px-4 text-gray-500 transition-colors hover:text-gray-700 lg:inline-flex dark:border-white/10 dark:text-gray-400 dark:hover:text-white"
            x-bind:aria-label="$store.sidebar.isCollapsed
                ? <?php echo \Illuminate\Support\Js::from(__('filament-panels::layout.actions.sidebar.expand.label'))->toHtml() ?>
                : <?php echo \Illuminate\Support\Js::from(__('filament-panels::layout.actions.sidebar.collapse.label'))->toHtml() ?>"
            x-bind:aria-expanded="! $store.sidebar.isCollapsed"
            aria-controls="mekaya-desktop-sidebar"
        >
            <svg
                class="size-6 text-gray-400 dark:text-gray-500"
                viewBox="0 0 24 24"
                stroke="currentColor"
                fill="none"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M2.74902 6.75C2.74902 5.09315 4.09217 3.75 5.74902 3.75H18.2507C19.9075 3.75 21.2507 5.09315 21.2507 6.75V17.25C21.2507 18.9069 19.9075 20.25 18.2507 20.25H5.74902C4.09217 20.25 2.74902 18.9069 2.74902 17.25V6.75Z"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path d="M10.25 3.75V20.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5.75 7.75L7.25 7.75" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5.75 11L7.25 11" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5.75 14.25L7.25 14.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex flex-1 items-center justify-between gap-4 px-4 lg:px-6">
        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START)); ?>


        <div class="flex flex-1">
            <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE)); ?>


            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->isGlobalSearchEnabled() && (!enum_exists(\Filament\Enums\GlobalSearchPosition::class) || !method_exists(filament(), 'getGlobalSearchPosition') || filament()->getGlobalSearchPosition() === \Filament\Enums\GlobalSearchPosition::Topbar)): ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split(Filament\Livewire\GlobalSearch::class);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2894900648-0', $__key);

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

            <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER)); ?>

        </div>
        <div class="fi-topbar-end flex shrink-0 items-center gap-x-3">
            <a
                href="<?php echo e(url('/')); ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="mky-topbar-icon-control hidden items-center justify-center rounded-lg p-1 text-gray-500 ring-1 ring-gray-200 transition-colors hover:bg-gray-50 hover:text-gray-700 lg:inline-flex dark:text-gray-400 dark:ring-white/10 dark:hover:bg-gray-800 dark:hover:text-white"
                aria-label="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>"
                title="<?php echo e(strip_tags((string) mekaya()->brandName())); ?>"
            >
                <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-globe-alt','class' => 'size-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-globe-alt','class' => 'size-6','aria-hidden' => 'true']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
            </a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->auth()->check()): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(mekaya_database_notifications_enabled() && (!enum_exists(\Filament\Enums\DatabaseNotificationsPosition::class) || mekaya_database_notifications_position() === null || mekaya_database_notifications_position() === \Filament\Enums\DatabaseNotificationsPosition::Topbar) && ($dbNotificationsComponent = mekaya_database_notifications_component())): ?>
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

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2894900648-1', $__key);

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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_END)); ?>


            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->auth()->check()): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filament()->hasUserMenu() && (!enum_exists(\Filament\Enums\UserMenuPosition::class) || !method_exists(filament(), 'getUserMenuPosition') || filament()->getUserMenuPosition() === \Filament\Enums\UserMenuPosition::Topbar)): ?>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/vendor/filament-panels/livewire/topbar.blade.php ENDPATH**/ ?>