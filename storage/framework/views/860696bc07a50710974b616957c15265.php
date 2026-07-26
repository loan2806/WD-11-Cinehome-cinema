<div
    x-data="{
        show: false,
        title: '',
        message: '',
        icon: 'fa-circle-check',
        actionUrl: '',
        actionMethod: 'POST',
        actionBtn: 'Xác nhận',
        actionBtnClass: '',
        open(data) {
            this.title = data.title || '';
            this.message = data.message || '';
            this.icon = data.icon || 'fa-circle-check';
            this.actionUrl = data.actionUrl || '';
            this.actionMethod = data.actionMethod || 'POST';
            this.actionBtn = data.actionBtn || 'Xác nhận';
            this.actionBtnClass = data.actionBtnClass || 'bg-emerald-500 hover:bg-emerald-600';
            this.show = true;
        },
        close() {
            this.show = false;
        },
        submitForm() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = this.actionUrl;

            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name=csrf-token]')?.content || '';
            form.appendChild(csrf);

            if (this.actionMethod === 'DELETE') {
                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);
            }

            document.body.appendChild(form);
            form.submit();
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @open-modal.window="open($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="absolute inset-0 bg-black/70 backdrop-blur-sm"
    ></div>

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-white/15 bg-[#151515] shadow-2xl"
    >
        <div class="flex flex-col items-center gap-3 px-8 pt-8 pb-4 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/15 bg-white/5">
                <i class="fa-solid text-2xl leading-none" :class="icon"></i>
            </div>
            <h3 class="text-lg font-bold text-white" x-text="title"></h3>
            <p class="text-sm text-gray-400 leading-relaxed" x-text="message"></p>
        </div>

        <div class="flex items-center gap-3 border-t border-white/10 px-8 py-5">
            <button
                type="button"
                @click="close()"
                class="flex-1 rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10"
            >
                Đóng
            </button>

            <button
                type="button"
                @click="submitForm()"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition"
                :class="actionBtnClass"
                x-text="actionBtn"
            ></button>
        </div>
    </div>
</div>
<?php /**PATH E:\laragon\www\WD-11-Cinehome-cinema\resources\views/components/modal-confirm.blade.php ENDPATH**/ ?>