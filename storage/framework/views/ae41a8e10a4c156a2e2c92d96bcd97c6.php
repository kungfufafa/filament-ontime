<?php
    use Filament\Support\Enums\IconSize;
    use Illuminate\View\ComponentAttributeBag;

    use function Filament\Support\generate_href_html;
    use function Filament\Support\generate_icon_html;

    $isActive = $item->isActive();
    $hasActiveChildren = $item->isChildItemsActive();
    $childItems = $item->getChildItems();
    $hasChildItems = filled($childItems);
    $activeIcon = $item->getActiveIcon();
    $icon = ($isActive && $activeIcon) ? $activeIcon : $item->getIcon();
    $badge = $item->getBadge();
    $badgeColor = $item->getBadgeColor($badge);
    $badgeColor = is_string($badgeColor) ? $badgeColor : 'gray';
    $url = $item->getUrl();
?>

<li
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'mky-sidebar-item',
        'mky-sidebar-item-active' => $isActive || $hasActiveChildren,
        'mky-items-has-child' => $hasChildItems,
    ]); ?>"
>
    <a
        <?php echo e(generate_href_html($url, $item->shouldOpenUrlInNewTab())); ?>

        x-on:click="window.innerWidth < $store.sidebar.breakpoint && $store.sidebar.close()"
        x-tooltip="{
            content: <?php echo \Illuminate\Support\Js::from($item->getLabel())->toHtml() ?>,
            placement: document.dir === 'rtl' ? 'left' : 'right',
            theme: $store.theme,
            onShow: () => $store.sidebar.isCollapsed,
        }"
        class="mky-sidebar-item-link <?php echo e(($isActive || $hasActiveChildren) ? 'mky-active' : ''); ?>"
        <?php if($isActive): ?>
            aria-current="page"
        <?php endif; ?>
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($icon)): ?>
            <?php echo e(generate_icon_html(
                    $icon,
                    attributes: (new ComponentAttributeBag)->class(['mky-sidebar-item-icon']),
                    size: IconSize::Large,
                )); ?>

        <?php else: ?>
            <span class="mky-sidebar-item-dot"></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <span
            class="mky-sidebar-item-label"
            x-cloak
            x-show="! $store.sidebar.isCollapsed"
            x-transition:enter="transition-opacity duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            <?php echo e($item->getLabel()); ?>

        </span>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($badge) || $hasChildItems): ?>
            <span
                class="mky-sidebar-item-nav"
                x-cloak
                x-show="! $store.sidebar.isCollapsed"
            >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($badge)): ?>
                    <span class="mky-sidebar-item-badge mky-sidebar-item-badge-<?php echo e($badgeColor); ?>">
                        <?php echo e($badge); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasChildItems): ?>
                    <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => \Filament\Support\Icons\Heroicon::ChevronDown,'class' => 'mky-sidebar-item-toggle size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\Icons\Heroicon::ChevronDown),'class' => 'mky-sidebar-item-toggle size-4']); ?>
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
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasChildItems): ?>
        <ul class="mky-submenu <?php echo e($isActive || $hasActiveChildren ? 'block' : ''); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $childItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php echo $__env->make('mekaya::livewire.partials.mekaya-sidebar-item', [
                    'item' => $childItem,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</li>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/livewire/partials/mekaya-sidebar-item.blade.php ENDPATH**/ ?>