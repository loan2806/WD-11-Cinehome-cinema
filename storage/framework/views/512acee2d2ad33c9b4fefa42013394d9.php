<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['type' => 'success', 'message' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['type' => 'success', 'message' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variant = match ($type) {
        'success' => [
            'bg' => 'bg-emerald-500/15',
            'border' => 'border-emerald-500/40',
            'icon' => 'fa-circle-check',
            'iconColor' => 'text-emerald-300',
            'text' => 'text-emerald-100',
            'fallback' => 'Thao tác thành công',
        ],
        'error' => [
            'bg' => 'bg-red-500/15',
            'border' => 'border-red-500/40',
            'icon' => 'fa-circle-exclamation',
            'iconColor' => 'text-red-300',
            'text' => 'text-red-100',
            'fallback' => 'Có lỗi xảy ra',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/15',
            'border' => 'border-amber-500/40',
            'icon' => 'fa-triangle-exclamation',
            'iconColor' => 'text-amber-300',
            'text' => 'text-amber-100',
            'fallback' => 'Thông báo',
        ],
        default => [
            'bg' => 'bg-white/10',
            'border' => 'border-white/20',
            'icon' => 'fa-circle-info',
            'iconColor' => 'text-[#d99a32]',
            'text' => 'text-white',
            'fallback' => 'Thông báo',
        ],
    };
?>

<div
    data-admin-toast
    class="admin-toast"
>
    <div class="flex items-start gap-3 rounded-2xl border <?php echo e($variant['border']); ?> <?php echo e($variant['bg']); ?> bg-[#101010]/95 px-4 py-3 shadow-2xl shadow-black/40 backdrop-blur-xl">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">
            <i class="fa-solid <?php echo e($variant['icon']); ?> <?php echo e($variant['iconColor']); ?> text-lg"></i>
        </div>

        <p class="<?php echo e($variant['text']); ?> min-w-0 flex-1 break-words pt-1 text-sm font-bold leading-snug">
            <?php echo e($message ?? $variant['fallback']); ?>

        </p>

        <button
            type="button"
            data-toast-close
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-white/70 transition hover:bg-white/20 hover:text-white"
            aria-label="Đóng thông báo"
        >
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('61a10091-5957-46e6-a772-79b76fb2ad8d')): $__env->markAsRenderedOnce('61a10091-5957-46e6-a772-79b76fb2ad8d'); ?>
    <style>
        .admin-toast {
            pointer-events: auto;
            width: 100%;
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
            animation: adminToastIn 0.28s ease both;
        }

        .admin-toast.is-hiding {
            opacity: 0;
            transform: translateX(16px);
        }

        @keyframes adminToastIn {
            from {
                opacity: 0;
                transform: translateX(16px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>

    <script>
        (function() {
            function bootAdminToasts() {
                var toasts = document.querySelectorAll('[data-admin-toast]');

                toasts.forEach(function(toast, index) {
                    if (toast.dataset.toastReady === '1') {
                        return;
                    }

                    toast.dataset.toastReady = '1';

                    var dismiss = function() {
                        toast.classList.add('is-hiding');
                        setTimeout(function() {
                            if (toast.parentElement) {
                                toast.remove();
                            }
                        }, 300);
                    };

                    var closeButton = toast.querySelector('[data-toast-close]');
                    if (closeButton) {
                        closeButton.addEventListener('click', dismiss);
                    }

                    setTimeout(dismiss, 3500 + (index * 250));
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootAdminToasts);
            } else {
                bootAdminToasts();
            }
        })();
    </script>
<?php endif; ?>
<?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/components/toast.blade.php ENDPATH**/ ?>