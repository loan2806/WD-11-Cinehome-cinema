import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    const bellBtn = document.getElementById('bellBtn');
    const notifyBox = document.getElementById('notifyBox');

    if (!bellBtn || !notifyBox) return;

    bellBtn.addEventListener('click', async function (e) {
        e.stopPropagation();

        notifyBox.classList.toggle('hidden');

        try {
            const res = await fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            if (res.ok) {
                const badge = document.getElementById('notifyBadge');
                if (badge) badge.remove();
            }

        } catch (err) {
            console.log('Mark read error:', err);
        }
    });

    document.addEventListener('click', function (e) {
        if (!bellBtn.contains(e.target) && !notifyBox.contains(e.target)) {
            notifyBox.classList.add('hidden');
        }
    });
});