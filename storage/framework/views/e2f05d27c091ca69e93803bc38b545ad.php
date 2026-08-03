<?php
    $info = $this->getUserInfo();
?>

<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => ['class' => 'fi-wi-user-profile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'fi-wi-user-profile']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="fi-section rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <!-- User Title Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-200 dark:border-white/10">
            <div>
                <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    <?php echo e($info['name']); ?>

                </h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    <?php echo e($info['email']); ?> &bull; NIP: <span class="font-medium text-gray-700 dark:text-gray-300"><?php echo e($info['nip']); ?></span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md text-gray-700 dark:bg-white/10 dark:text-gray-300">
                    Role: <?php echo e($info['roles']); ?>

                </span>
            </div>
        </div>

        <!-- Clean Key-Value Grid -->
        <dl class="grid grid-cols-2 gap-4 mt-4 sm:grid-cols-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Divisi</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white"><?php echo e($info['division']); ?></dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Jabatan</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white"><?php echo e($info['job_title']); ?></dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Level Jabatan</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white"><?php echo e($info['job_level']); ?></dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Badan Usaha / Company</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-950 dark:text-white truncate"><?php echo e($info['company']); ?></dd>
            </div>
        </dl>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH C:\Users\AHTAR\filament-ontime\resources\views/filament/widgets/user-profile-widget.blade.php ENDPATH**/ ?>