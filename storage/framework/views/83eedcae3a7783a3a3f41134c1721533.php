<div
    x-data="{ theme: null }"
    x-init="
        $watch('theme', () => {
            $dispatch('theme-changed', theme)
        })

        theme = localStorage.getItem('theme') || 'system'
    "
    class="fi-theme-switcher grid grid-flow-col gap-x-1 p-0.5 rounded-lg bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10"
>
    <?php if (isset($component)) { $__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.theme-switcher.button','data' => ['icon' => 'heroicon-o-sun','theme' => 'light']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::theme-switcher.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-sun','theme' => 'light']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d)): ?>
<?php $attributes = $__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d; ?>
<?php unset($__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d)): ?>
<?php $component = $__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d; ?>
<?php unset($__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.theme-switcher.button','data' => ['icon' => 'heroicon-o-moon','theme' => 'dark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::theme-switcher.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-moon','theme' => 'dark']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d)): ?>
<?php $attributes = $__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d; ?>
<?php unset($__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d)): ?>
<?php $component = $__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d; ?>
<?php unset($__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.theme-switcher.button','data' => ['icon' => 'heroicon-o-computer-desktop','theme' => 'system']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::theme-switcher.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-computer-desktop','theme' => 'system']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d)): ?>
<?php $attributes = $__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d; ?>
<?php unset($__attributesOriginal988c6a1c63d2d127e5cb85daa8a1516d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d)): ?>
<?php $component = $__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d; ?>
<?php unset($__componentOriginal988c6a1c63d2d127e5cb85daa8a1516d); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/components/theme-switcher/index.blade.php ENDPATH**/ ?>