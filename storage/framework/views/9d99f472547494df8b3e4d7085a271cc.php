<?php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\View\ComponentAttributeBag;

    use function Filament\Support\generate_icon_html;
?>

<?php if (isset($component)) { $__componentOriginalbe7942f67016b754cdea117fbaee5062 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe7942f67016b754cdea117fbaee5062 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'mekaya::components.auth-card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mekaya::auth-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <header class="flex flex-col items-center justify-center py-3">
        <div class="flex items-center justify-center space-y-2 rounded-lg bg-white p-2 shadow ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700/80">
            <?php echo e(generate_icon_html(
                    Heroicon::ArrowRightEndOnRectangle,
                    attributes: (new ComponentAttributeBag)->class(['size-5']),
                )); ?>

        </div>

        <h1 class="mt-4 font-heading text-lg font-medium text-gray-950 dark:text-white">
            <?php echo e($this->getHeading()); ?>

        </h1>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($subheading = $this->getSubheading())): ?>
            <p class="mt-1 text-center text-sm text-gray-500 dark:text-gray-400">
                <?php echo e($subheading); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </header>

    <div class="mt-8">
        <?php echo e($this->content); ?>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe7942f67016b754cdea117fbaee5062)): ?>
<?php $attributes = $__attributesOriginalbe7942f67016b754cdea117fbaee5062; ?>
<?php unset($__attributesOriginalbe7942f67016b754cdea117fbaee5062); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe7942f67016b754cdea117fbaee5062)): ?>
<?php $component = $__componentOriginalbe7942f67016b754cdea117fbaee5062; ?>
<?php unset($__componentOriginalbe7942f67016b754cdea117fbaee5062); ?>
<?php endif; ?><?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/auth/login.blade.php ENDPATH**/ ?>