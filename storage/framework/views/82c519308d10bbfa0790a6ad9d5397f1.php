<?php
    $brandLogo = mekaya()->getBrandLogo();
    $brandName = mekaya()->brandName();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($brandLogo instanceof \Illuminate\Contracts\Support\Htmlable): ?>
    <?php echo $brandLogo->toHtml(); ?>

<?php elseif(filled($brandLogo)): ?>
    <img <?php echo e($attributes); ?> src="<?php echo e(str_contains($brandLogo, '://') ? $brandLogo : asset($brandLogo)); ?>" alt="<?php echo e(strip_tags((string) $brandName)); ?>" />
<?php elseif(filled($brandIcon = mekaya()->brandIconPath())): ?>
    <img <?php echo e($attributes); ?> src="<?php echo e(str_contains($brandIcon, '://') ? $brandIcon : asset($brandIcon)); ?>" alt="<?php echo e(strip_tags((string) $brandName)); ?>" />
<?php else: ?>
    <span <?php echo e($attributes); ?>><?php echo e($brandName); ?></span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Users\AHTAR\filament-ontime\vendor\kungfufafa\mekaya-theme\src/../resources/views/components/brand.blade.php ENDPATH**/ ?>