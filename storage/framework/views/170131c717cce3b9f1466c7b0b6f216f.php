

<div <?php echo e($attributes->twMerge(['class' => 'relative isolate flex min-h-dvh items-center justify-center px-4 py-8 sm:px-6'])); ?>>
    <div class="relative flex w-full max-w-sm flex-col gap-6 sm:gap-8">
        <div class="mky-auth-brand flex min-h-12 items-center justify-center text-center">
            <?php if (isset($component)) { $__componentOriginal4aad98a0a7e23b9d4e3436208d07584c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4aad98a0a7e23b9d4e3436208d07584c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.brand','data' => ['class' => 'h-12 w-auto max-w-full object-contain text-center text-xl font-semibold text-gray-950 dark:text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-12 w-auto max-w-full object-contain text-center text-xl font-semibold text-gray-950 dark:text-white']); ?>
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
        </div>

        <?php if (isset($component)) { $__componentOriginal8e28aca54344e95ca75d949a06e8b72c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e28aca54344e95ca75d949a06e8b72c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.card','data' => ['class' => 'w-full max-w-sm [&>div:first-of-type]:shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-full max-w-sm [&>div:first-of-type]:shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php echo e($slot); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e28aca54344e95ca75d949a06e8b72c)): ?>
<?php $attributes = $__attributesOriginal8e28aca54344e95ca75d949a06e8b72c; ?>
<?php unset($__attributesOriginal8e28aca54344e95ca75d949a06e8b72c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e28aca54344e95ca75d949a06e8b72c)): ?>
<?php $component = $__componentOriginal8e28aca54344e95ca75d949a06e8b72c; ?>
<?php unset($__componentOriginal8e28aca54344e95ca75d949a06e8b72c); ?>
<?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/components/auth-card.blade.php ENDPATH**/ ?>