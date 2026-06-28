@props(['type' => 'success', 'message' => null])

@php
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
@endphp

<div
    data-admin-toast
    class="admin-toast"
>
    <div class="flex items-start gap-3 rounded-2xl border {{ $variant['border'] }} {{ $variant['bg'] }} bg-[#101010]/95 px-4 py-3 shadow-2xl shadow-black/40 backdrop-blur-xl">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">
            <i class="fa-solid {{ $variant['icon'] }} {{ $variant['iconColor'] }} text-lg"></i>
        </div>

        <p class="{{ $variant['text'] }} min-w-0 flex-1 break-words pt-1 text-sm font-bold leading-snug">
            {{ $message ?? $variant['fallback'] }}
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

@once
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
@endonce
