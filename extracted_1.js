
/* Show phong chieu - last updated: {{ date('Y-m-d H:i:s') }} */
document.addEventListener('DOMContentLoaded', function() {
    const phongChieuId = {{ $phongChieu->id }};
    let selectedSeats = new Set();
    let currentSeatId = null;
    let currentRowHangId = null;
    let currentSeatEl = null;
    let currentSeatSiblings = []; // Mảng DOM elements thuộc cùng cặp couple (nếu có)

    // === TỰ ĐỘNG PHÁT HIỆN ĐỘ SÁNG NỀN VÀ CHỌN MÀU CHỮ PHÙ HỢP ===
    function getLuminance(hexColor) {
        // Chuyển hex -> RGB
        const hex = hexColor.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        // Tính luminance theo công thức WCAG
        const a = [r, g, b].map(v => {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
    }

    function applyAutoContrast() {
        document.querySelectorAll('.seat-interactive').forEach(seat => {
            // Ghế bảo trì: giữ style mặc định
            if (seat.classList.contains('seat-chip--maintenance')) return;
            const mauSac = seat.dataset.mauSac;
            if (!mauSac) return;
            const lum = getLuminance(mauSac);
            // Nếu luminance > 0.5 (nền sáng) thì dùng chữ đen
            // Nếu luminance <= 0.5 (nền tối) thì dùng chữ trắng
            if (lum > 0.5) {
                seat.classList.add('seat-light-bg');
                seat.classList.remove('seat-dark-bg');
            } else {
                seat.classList.add('seat-dark-bg');
                seat.classList.remove('seat-light-bg');
            }
        });
    }
    applyAutoContrast();

    // --- Elements ---
    const bulkActionsToolbar = document.getElementById('bulkActionsToolbar');
    const selectedCount = document.getElementById('selectedCount');
    const clearSelection = document.getElementById('clearSelection');
    const bulkLoaiGheSelect = document.getElementById('bulkLoaiGheSelect') || document.getElementById('bulkLoaiGhe');
    const applyBulkAction = document.getElementById('applyBulkAction');
    const btnToggleMaintenance = document.getElementById('btnToggleMaintenance');
    const popover = document.getElementById('seatInfoPopover');
    const rowChangeModal = document.getElementById('rowChangeModal');
    const rowModalLoaiGhe = document.getElementById('rowChangeModalLoaiGhe');

    // --- Helpers ---
    function findSeatEl(gheId) {
        return document.querySelector(`.seat-interactive[data-ghe-id="${gheId}"]`);
    }

    function updateSeatDOM(seatEl, loaiGhe, mauSac, trangThai, phuThu) {
        if (!seatEl) return;
        // Lưu lại mã ghế hiện tại (vì className sẽ bị reset)
        const maGhe = seatEl.dataset.maGhe;
        const isSelected = seatEl.classList.contains('selected');
        const isCouple = seatEl.classList.contains('seat-chip--couple');

        seatEl.dataset.loaiGhe = loaiGhe;
        seatEl.dataset.mauSac = mauSac;
        seatEl.dataset.trangThai = trangThai;
        seatEl.dataset.phuThu = phuThu;
        seatEl.title = maGhe + ' - ' + loaiGhe + ' (' + Number(phuThu).toLocaleString() + 'đ)';

        // Build className
        let className = 'seat-chip seat-interactive';
        if (trangThai === 'bao_tri') className += ' seat-chip--maintenance';
        if (isCouple) className += ' seat-chip--couple';
        if (isSelected) className += ' selected';
        seatEl.className = className;

        // Reset style + content
        if (trangThai === 'bao_tri') {
            seatEl.style.backgroundColor = '';
            seatEl.style.color = '#991b1b';
        } else {
            seatEl.style.backgroundColor = mauSac;
            seatEl.style.color = '#1a0b04';
        }
        // Tất cả ghế đều dùng chữ đen - không cần áp dụng auto contrast nữa
        // Đảm bảo label span bên trong giữ màu đen
        const labelSpans = seatEl.querySelectorAll('.seat-label, .seat-couple-left, .seat-couple-right');
        labelSpans.forEach(s => {
            s.style.color = trangThai === 'bao_tri' ? '#991b1b' : '#1a0b04';
        });
    }

    function updateBulkToolbar() {
        const count = selectedSeats.size;
        if (count > 0) {
            bulkActionsToolbar.classList.remove('hidden');
            selectedCount.textContent = count;
            renderSelectedSeatsChips();
        } else {
            bulkActionsToolbar.classList.add('hidden');
            renderSelectedSeatsChips();
        }
        updateBulkMaintenanceBtnLabel();
    }

    /**
     * Render danh sách chip tên ghế đã chọn vào #selectedSeatsList
     * - Với ghế đơn: hiển thị tên 1 chip
     * - Với ghế couple: hiển thị 1 chip với cả 2 tên (H1+H2) để user biết cả cặp được chọn
     * - Click vào chip để bỏ chọn ghế đó
     */
    function renderSelectedSeatsChips() {
        const list = document.getElementById('selectedSeatsList');
        if (!list) return;
        list.innerHTML = '';

        // Group theo DOM node (couple = 1 node, thường = 1 node)
        const groupMap = new Map();
        selectedSeats.forEach(id => {
            const el = document.querySelector(`.seat-interactive[data-ghe-id="${id}"]`);
            if (!el) return;
            // Tìm node đại diện: nếu là couple thì dùng node left, nếu là right thì dùng node left
            let key;
            if (el.classList.contains('seat-chip--couple')) {
                // Couple: 1 node = 1 cặp, dùng node đó luôn
                key = el.dataset.gheId + '|couple';
            } else {
                key = el.dataset.gheId;
            }
            if (!groupMap.has(key)) {
                groupMap.set(key, { els: [el], isCouple: el.classList.contains('seat-chip--couple') });
            } else {
                groupMap.get(key).els.push(el);
            }
        });

        // Sắp xếp theo thứ tự: A1, A2, ... rồi tới couple
        const sortedKeys = Array.from(groupMap.keys()).sort((a, b) => {
            const elA = groupMap.get(a).els[0];
            const elB = groupMap.get(b).els[0];
            return elA.getBoundingClientRect().top - elB.getBoundingClientRect().top
                || elA.getBoundingClientRect().left - elB.getBoundingClientRect().left;
        });

        sortedKeys.forEach(key => {
            const group = groupMap.get(key);
            const el = group.els[0];
            const isCouple = group.isCouple;
            const color = el.style.backgroundColor || '#666';
            const siblings = (() => {
                try {
                    const arr = JSON.parse(el.dataset.coupleSiblings || '[]');
                    return Array.isArray(arr) ? arr : [];
                } catch (e) { return []; }
            })();
            const label = isCouple && siblings.length > 1
                ? siblings.map(sid => {
                    const sibEl = document.querySelector(`.seat-interactive[data-ghe-id="${sid}"]`);
                    return sibEl ? sibEl.dataset.maGhe : `#${sid}`;
                }).join(' + ')
                : el.dataset.maGhe;

            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'inline-flex items-center gap-1 rounded-full border-2 border-[#f4c56a] bg-[#f4c56a]/15 px-2.5 py-1 text-xs font-bold text-[#f4c56a] hover:bg-[#f4c56a]/30 transition';
            chip.innerHTML = `
                <span class="inline-block h-2 w-2 rounded-full" style="background-color: ${color}"></span>
                <span>${label}</span>
                <i class="fa-solid fa-xmark text-[10px] opacity-70 hover:opacity-100"></i>
            `;
            chip.addEventListener('click', (e) => {
                e.stopPropagation();
                // Bỏ chọn cả cặp nếu là couple
                if (isCouple) {
                    siblings.forEach(sid => selectedSeats.delete(sid));
                } else {
                    selectedSeats.delete(el.dataset.gheId);
                }
                // Cập nhật DOM
                document.querySelectorAll('.seat-interactive.selected').forEach(dom => {
                    const sibArr = (() => {
                        try {
                            const arr = JSON.parse(dom.dataset.coupleSiblings || '[]');
                            return Array.isArray(arr) ? arr : [];
                        } catch (e) { return []; }
                    })();
                    if (sibArr.length > 1) {
                        // Couple: nếu không còn selectedSeats chứa id của nó thì bỏ
                        const stillSelected = sibArr.some(sid => selectedSeats.has(sid));
                        if (!stillSelected) dom.classList.remove('selected');
                    } else {
                        if (!selectedSeats.has(dom.dataset.gheId)) dom.classList.remove('selected');
                    }
                });
                updateBulkToolbar();
            });
            list.appendChild(chip);
        });
    }

    function setBtnLoading(btn, loading, originalText) {
        if (loading) {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang xử lý...';
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalText || originalText;
        }
    }

    function positionPopover(seatEl) {
        const rect = seatEl.getBoundingClientRect();
        const popoverW = 260;
        const popoverH = 320;
        let left = rect.right + 14;
        let top = rect.top - 20;

        if (left + popoverW > window.innerWidth - 16) {
            left = rect.left - popoverW - 14;
        }
        if (top + popoverH > window.innerHeight - 16) {
            top = window.innerHeight - popoverH - 16;
        }
        if (top < 16) top = 16;

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';
    }

    function showPopover(seatEl) {
        currentSeatEl = seatEl;
        currentSeatId = seatEl.dataset.gheId;
        const maGhe = seatEl.dataset.maGhe;
        const loaiGhe = seatEl.dataset.loaiGhe;
        const mauSac = seatEl.dataset.mauSac || '#666666';
        const phuThu = Number(seatEl.dataset.phuThu || 0);
        const trangThai = seatEl.dataset.trangThai;

        // Fill popover info
        document.getElementById('popoverMaGhe').textContent = maGhe;
        document.getElementById('popoverLoaiGhe').textContent = loaiGhe.toUpperCase();
        document.getElementById('popoverPhuThu').textContent = phuThu > 0 ? '+' + phuThu.toLocaleString() + 'đ' : 'Miễn phí';
        document.getElementById('popoverHeaderMaGhe').textContent = maGhe;

        // Set màu cho header gradient + color block thông qua CSS variable
        popover.style.setProperty('--popover-color', mauSac);

        // Status badge
        const statusEl = document.getElementById('popoverStatus');
        if (trangThai === 'bao_tri') {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400"><i class="fa-solid fa-wrench"></i> Đang bảo trì</span>';
        } else {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400"><i class="fa-solid fa-check-circle"></i> Hoạt động</span>';
        }

        // Maintenance button state
        const maintBtn = document.getElementById('popoverBtnMaintenance');
        if (trangThai === 'bao_tri') {
            maintBtn.innerHTML = '<i class="fa-solid fa-rotate mr-1.5"></i>Kích hoạt ghế';
            maintBtn.classList.add('seat-popover-btn--active');
        } else {
            maintBtn.innerHTML = '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì';
            maintBtn.classList.remove('seat-popover-btn--active');
        }

        // Hide type selector
        document.getElementById('popoverTypeSelector').classList.add('hidden');
        document.getElementById('popoverBtnChangeType').classList.remove('hidden');
        document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
        document.getElementById('popoverBtnDelete').classList.remove('hidden');

        positionPopover(seatEl);
        popover.classList.add('is-visible');
    }

    function hidePopover() {
        popover.classList.remove('is-visible');
        currentSeatEl = null;
    }

    // --- Bulk Toolbar ---
    clearSelection.addEventListener('click', function() {
        selectedSeats.clear();
        document.querySelectorAll('.seat-interactive.selected').forEach(el => el.classList.remove('selected'));
        updateBulkToolbar();
    });

    // Color preview for bulk select
    if (bulkLoaiGheSelect) {
        bulkLoaiGheSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const color = selected?.dataset?.color || '#666';
            const preview = document.getElementById('bulkColorPreview');
            if (preview) preview.style.backgroundColor = color;
        });
    }

    // Row modal color preview
    if (rowModalLoaiGhe) {
        rowModalLoaiGhe.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const color = selected?.dataset?.color || '#666';
            const preview = document.getElementById('rowModalColorPreview');
            if (preview) preview.style.backgroundColor = color;
        });
        const firstOpt = rowModalLoaiGhe.options[rowModalLoaiGhe.selectedIndex];
        const preview = document.getElementById('rowModalColorPreview');
        if (preview && firstOpt?.dataset?.color) preview.style.backgroundColor = firstOpt.dataset.color;
    }

    applyBulkAction.addEventListener('click', function() {
        if (selectedSeats.size === 0) return;

        const loaiGheId = bulkLoaiGheSelect?.value;
        if (!loaiGheId) {
            alert('Vui lòng chọn loại ghế để thay đổi.');
            return;
        }

        setBtnLoading(this, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ghe_ids: expandCoupleIds(Array.from(selectedSeats)),
                action: 'update_type',
                loai_ghe_id: loaiGheId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.updated_seats) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat-interactive.selected').forEach(el => el.classList.remove('selected'));
                updateBulkToolbar();
                bulkLoaiGheSelect.value = '';
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(this, false, '<i class="fa-solid fa-check mr-1.5"></i>Áp dụng'); });
    });

    btnToggleMaintenance?.addEventListener('click', function() {
        if (selectedSeats.size === 0) return;
        const action = this.dataset.bulkAction || 'maintenance';
        setBtnLoading(this, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ghe_ids: expandCoupleIds(Array.from(selectedSeats)),
                action: action
            })
        })
        .then(async res => ({ status: res.status, ct: res.headers.get('content-type') || '', body: await res.text() }))
        .then(({ status, ct, body }) => {
            const data = ct.includes('application/json') ? JSON.parse(body) : { success: false, message: body };
            if (data.success && data.updated_seats) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat-interactive.selected').forEach(el => el.classList.remove('selected'));
                updateBulkToolbar();
            } else {
                alert('Có lỗi: ' + (data.message || status));
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { updateBulkMaintenanceBtnLabel(); setBtnLoading(this, false); });
    });

    // Cập nhật nhãn nút bảo trì/hoạt động trong bulk toolbar theo trạng thái các ghế đã chọn
    function updateBulkMaintenanceBtnLabel() {
        const btn = btnToggleMaintenance;
        if (!btn) return;
        if (selectedSeats.size === 0) {
            btn.innerHTML = '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì';
            btn.dataset.bulkAction = '';
            return;
        }
        // Đếm trạng thái các ghế đã chọn (data-trang-thai lưu trên DOM)
        let soBaoTri = 0;
        let soHoatDong = 0;
        selectedSeats.forEach(id => {
            const el = document.querySelector(`.seat-interactive[data-ghe-id="${id}"]`);
            if (!el) return;
            const st = el.dataset.trangThai || 'hoat_dong';
            if (st === 'bao_tri') soBaoTri++;
            else soHoatDong++;
        });
        // Quy tắc nhãn:
        //   - Tất cả bảo trì  → "Kích hoạt" (action = activate)
        //   - Còn lại (đang hoạt động hoặc hỗn hợp) → "Bảo trì" (action = maintenance)
        if (soBaoTri > 0 && soHoatDong === 0) {
            btn.innerHTML = '<i class="fa-solid fa-circle-play mr-1.5"></i>Kích hoạt';
            btn.dataset.bulkAction = 'activate';
            btn.classList.add('border-emerald-500/40', 'text-emerald-300', 'bg-emerald-500/10');
            btn.classList.remove('border-white/10', 'text-white', 'bg-white/5');
        } else {
            btn.innerHTML = '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì';
            btn.dataset.bulkAction = 'maintenance';
            btn.classList.remove('border-emerald-500/40', 'text-emerald-300', 'bg-emerald-500/10');
            btn.classList.add('border-white/10', 'text-white', 'bg-white/5');
        }
    }

    // --- Seat Click ---
    const hoverTooltip = document.getElementById('seatHoverTooltip');
    const hoverMaGhe = document.getElementById('hoverMaGhe');
    const hoverLoaiGhe = document.getElementById('hoverLoaiGhe');

    function positionHoverTooltip(seatEl) {
        const rect = seatEl.getBoundingClientRect();
        const tipWidth = 160;
        let left = rect.left + rect.width / 2 - tipWidth / 2;
        let top = rect.top - 50; // hiện phía trên ghế

        // Đảm bảo không tràn mép
        if (left < 8) left = 8;
        if (left + tipWidth > window.innerWidth - 8) left = window.innerWidth - tipWidth - 8;
        if (top < 8) top = rect.bottom + 12; // đảo xuống dưới nếu sát mép trên

        hoverTooltip.style.left = left + 'px';
        hoverTooltip.style.top = top + 'px';
    }

    function showHoverTooltip(seatEl) {
        if (!hoverTooltip) return;
        hoverMaGhe.textContent = seatEl.dataset.maGhe;
        hoverLoaiGhe.textContent = (seatEl.dataset.loaiGhe || 'Ghế').toUpperCase() +
            (seatEl.dataset.trangThai === 'bao_tri' ? ' · Bảo trì' : '');
        positionHoverTooltip(seatEl);
        hoverTooltip.classList.add('is-visible');
    }

    function hideHoverTooltip() {
        if (!hoverTooltip) return;
        hoverTooltip.classList.remove('is-visible');
    }

    /**
     * Lấy tất cả ID ghế thuộc cùng 1 cặp couple (từ data-couple-siblings)
     * Nếu không phải couple, trả về [id hiện tại]
     * Trả về mảng các id DB (1 hoặc 2 phần tử)
     */
    function getCoupleSiblings(seatEl) {
        const raw = seatEl.dataset.coupleSiblings;
        if (!raw) return [seatEl.dataset.gheId];
        try {
            const arr = JSON.parse(raw);
            return Array.isArray(arr) && arr.length > 0 ? arr : [seatEl.dataset.gheId];
        } catch (e) {
            return [seatEl.dataset.gheId];
        }
    }

    /**
     * Lấy DOM elements thuộc cùng cặp couple.
     * Vì hiện tại mỗi cặp couple chỉ render 1 DOM node duy nhất, trả về [seatEl].
     * (Giữ hàm này cho tương thích - mọi highlight sẽ áp dụng lên chính node đó)
     */
    function getCoupleDOMElements(seatEl) {
        return [seatEl];
    }

    /**
     * Mở rộng danh sách ghế đã chọn: nếu 1 ghế couple được chọn, tự thêm cả cặp
     * Cũng tự đánh dấu .selected trên DOM tương ứng
     */
    function expandCoupleIds(ids) {
        const expanded = new Set(ids);
        ids.forEach(id => {
            const el = document.querySelector(`.seat-interactive[data-ghe-id="${id}"]`);
            if (!el) return;
            const siblingsIds = getCoupleSiblings(el);
            siblingsIds.forEach(sid => expanded.add(sid));
            // Đánh dấu selected trên chính DOM node couple (1 node = 1 cặp)
            if (siblingsIds.length > 1) {
                el.classList.add('selected');
            }
        });
        return Array.from(expanded);
    }

    document.querySelectorAll('.seat-interactive').forEach(seat => {
        seat.addEventListener('mouseenter', function() {
            showHoverTooltip(this);
        });
        seat.addEventListener('mouseleave', function() {
            hideHoverTooltip();
        });

        seat.addEventListener('click', function(e) {
            // Lấy tất cả ID ghế cùng cặp (1 hoặc 2 id)
            const siblingIds = getCoupleSiblings(this);
            const seatId = this.dataset.gheId;
            const isCoupleSeat = siblingIds.length > 1;

            if (e.ctrlKey || e.metaKey) {
                // Multi-select
                if (isCoupleSeat) {
                    // Toggle nguyên cặp: nếu đã chọn thì bỏ, ngược lại chọn
                    if (selectedSeats.has(seatId)) {
                        siblingIds.forEach(id => selectedSeats.delete(id));
                        this.classList.remove('selected');
                    } else {
                        siblingIds.forEach(id => selectedSeats.add(id));
                        this.classList.add('selected');
                    }
                } else {
                    if (selectedSeats.has(seatId)) {
                        selectedSeats.delete(seatId);
                        this.classList.remove('selected');
                    } else {
                        selectedSeats.add(seatId);
                        this.classList.add('selected');
                    }
                }
                updateBulkToolbar();
            } else {
                // Single click - show popover
                currentSeatId = seatId;
                currentSeatSiblings = siblingIds; // mảng 1 hoặc 2 id
                showPopover(this);
            }
        });

        seat.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            currentSeatId = this.dataset.gheId;
            currentSeatSiblings = getCoupleSiblings(this);
            showPopover(this);
        });
    });

    // --- Popover Actions ---
    document.getElementById('popoverBtnChangeType').addEventListener('click', function() {
        const typeSelector = document.getElementById('popoverTypeSelector');
        const selectEl = document.getElementById('popoverLoaiGheSelect');
        typeSelector.classList.remove('hidden');
        this.classList.add('hidden');
        document.getElementById('popoverBtnMaintenance').classList.add('hidden');
        document.getElementById('popoverBtnDelete').classList.add('hidden');

        // Set current type
        if (currentSeatEl) {
            selectEl.value = currentSeatEl.dataset.loaiGheId || '';
        }
        const selected = selectEl.options[selectEl.selectedIndex];
        const preview = document.getElementById('popoverColorPreview');
        if (preview) preview.style.backgroundColor = selected?.dataset?.color || '#666';

        selectEl.focus();
    });

    document.getElementById('popoverLoaiGheSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const preview = document.getElementById('popoverColorPreview');
        if (preview) preview.style.backgroundColor = selected?.dataset?.color || '#666';
    });

    document.getElementById('popoverCancelType').addEventListener('click', function() {
        document.getElementById('popoverTypeSelector').classList.add('hidden');
        document.getElementById('popoverBtnChangeType').classList.remove('hidden');
        document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
        document.getElementById('popoverBtnDelete').classList.remove('hidden');
    });

    document.getElementById('popoverConfirmType').addEventListener('click', function() {
        const loaiGheId = document.getElementById('popoverLoaiGheSelect').value;
        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/update-seat-type`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                ghe_id: currentSeatSiblings.length > 1 ? null : currentSeatId,
                ghe_ids: currentSeatSiblings.length > 1 ? currentSeatSiblings : null,
                loai_ghe_id: loaiGheId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cập nhật tất cả ghế cùng cặp couple
                if (data.updated_seats) {
                    data.updated_seats.forEach(s => {
                        const el = findSeatEl(s.id);
                        if (el) updateSeatDOM(el, s.loai_ghe, s.mau_sac, s.trang_thai, s.phu_thu);
                    });
                } else {
                    const seatEl = findSeatEl(currentSeatId);
                    if (seatEl) updateSeatDOM(seatEl, data.loai_ghe, data.mau_sac, data.trang_thai, data.phu_thu);
                }
                document.getElementById('popoverTypeSelector').classList.add('hidden');
                document.getElementById('popoverBtnChangeType').classList.remove('hidden');
                document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
                document.getElementById('popoverBtnDelete').classList.remove('hidden');
                const seatEl = findSeatEl(currentSeatId);
                showPopover(seatEl);
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, 'Xác nhận'); });
    });

    document.getElementById('popoverBtnMaintenance').addEventListener('click', function() {
        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/toggle-seat-maintenance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                ghe_id: currentSeatSiblings.length > 1 ? null : currentSeatId,
                ghe_ids: currentSeatSiblings.length > 1 ? currentSeatSiblings : null,
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cập nhật tất cả ghế cùng cặp couple
                if (data.updated_seats) {
                    data.updated_seats.forEach(s => {
                        const el = findSeatEl(s.id);
                        if (el) updateSeatDOM(el, s.loai_ghe, s.mau_sac, s.trang_thai, s.phu_thu);
                    });
                }
                const seatEl = findSeatEl(currentSeatId);
                showPopover(seatEl);
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì'); });
    });

    // --- Xóa ghế (đơn hoặc cả cặp couple) ---
    document.getElementById('popoverBtnDelete').addEventListener('click', function() {
        if (!currentSeatId) return;
        const btn = this;
        const targetCount = currentSeatSiblings.length > 1 ? currentSeatSiblings.length : 1;
        const label = currentSeatSiblings.length > 1
            ? 'Xóa cả cặp ghế couple này?'
            : 'Xóa ghế này?';
        if (!confirm(label + ' Hành động này không thể hoàn tác.')) return;

        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                ghe_ids: currentSeatSiblings.length > 1 ? currentSeatSiblings : [currentSeatId],
                action: 'delete',
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload để lấy seatMap mới
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-trash-can mr-1.5"></i>Xóa ghế'); });
    });

    // Hide popover on outside click
    document.addEventListener('click', function(e) {
        if (!popover.contains(e.target) && !e.target.classList.contains('seat-interactive')) {
            hidePopover();
        }
    });

    // Ẩn hover tooltip khi click chuột (vì popover sẽ hiện)
    document.addEventListener('mousedown', hideHoverTooltip);

    // --- Row Click Modal ---
    document.querySelectorAll('.seat-row__label--clickable').forEach(label => {
        label.addEventListener('click', function() {
            const row = this.closest('.seat-row');
            currentRowHangId = row.dataset.hangGheId;
            const tenHang = row.dataset.hang;

            document.getElementById('rowChangeModalTenHang').textContent = tenHang;
            const allRows = Array.from(document.querySelectorAll('.seat-row'));
            const rowIndex = allRows.indexOf(row);
            document.getElementById('rowChangeModalRowIndex').textContent = 'Hàng thứ ' + (rowIndex + 1);

            // Tính thống kê bảo trì trong hàng
            const seatsInRow = row.querySelectorAll('.seat-interactive');
            let soHoatDong = 0, soBaoTri = 0;
            seatsInRow.forEach(s => {
                if (s.dataset.trangThai === 'bao_tri') soBaoTri++;
                else soHoatDong++;
            });
            const total = seatsInRow.length;
            const statsEl = document.getElementById('rowMaintenanceStats');
            const btnEl = document.getElementById('rowMaintenanceBtn');
            if (total === 0) {
                statsEl.innerHTML = 'Hàng chưa có ghế';
            } else {
                statsEl.innerHTML = `<span class="text-emerald-400 font-semibold">${soHoatDong}</span> hoạt động · <span class="text-red-400 font-semibold">${soBaoTri}</span> bảo trì / ${total}`;
            }
            // Reset trạng thái nút (nếu lần trước bị disable)
            btnEl.disabled = false;
            // Nhãn nút: nếu tất cả đang bảo trì → "Kích hoạt" (xanh), ngược lại "Bảo trì" (cam)
            if (total > 0 && soHoatDong === 0) {
                btnEl.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i>Kích hoạt';
                btnEl.className = 'rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500/25 hover:border-emerald-500/60';
            } else {
                btnEl.innerHTML = '<i class="fa-solid fa-wrench mr-1"></i>Bảo trì';
                btnEl.className = 'rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60';
            }

            rowChangeModal.classList.remove('hidden');
        });
    });

    // Mở modal đổi loại hàng từ nút "Đổi loại" trong bảng danh sách hàng ghế
    document.querySelectorAll('[data-row-trigger]').forEach(btn => {
        btn.addEventListener('click', function() {
            const hangGheId = this.dataset.hangGheId;
            const tenHang = this.dataset.hang;
            if (!hangGheId) return;

            currentRowHangId = hangGheId;
            document.getElementById('rowChangeModalTenHang').textContent = tenHang;
            document.getElementById('rowChangeModalRowIndex').textContent = '';

            // Thống kê bảo trì dựa trên DOM đã render
            const rowEl = document.querySelector(`.seat-row[data-hang-ghe-id="${hangGheId}"]`);
            const seatsInRow = rowEl ? rowEl.querySelectorAll('.seat-interactive') : [];
            let soHoatDong = 0, soBaoTri = 0;
            seatsInRow.forEach(s => {
                if (s.dataset.trangThai === 'bao_tri') soBaoTri++;
                else soHoatDong++;
            });
            const total = seatsInRow.length;
            const statsEl = document.getElementById('rowMaintenanceStats');
            const btnEl = document.getElementById('rowMaintenanceBtn');
            if (total === 0) {
                statsEl.innerHTML = 'Hàng chưa có ghế';
            } else {
                statsEl.innerHTML = `<span class="text-emerald-400 font-semibold">${soHoatDong}</span> hoạt động · <span class="text-red-400 font-semibold">${soBaoTri}</span> bảo trì / ${total}`;
            }
            btnEl.disabled = false;
            if (total > 0 && soHoatDong === 0) {
                btnEl.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i>Kích hoạt';
                btnEl.className = 'rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500/25 hover:border-emerald-500/60';
            } else {
                btnEl.innerHTML = '<i class="fa-solid fa-wrench mr-1"></i>Bảo trì';
                btnEl.className = 'rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60';
            }

            rowChangeModal.classList.remove('hidden');
        });
    });

    document.getElementById('rowChangeModalCancel').addEventListener('click', function() {
        rowChangeModal.classList.add('hidden');
    });

    document.getElementById('rowChangeModalApply').addEventListener('click', function() {
        const loaiGheId = rowModalLoaiGhe.value;
        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/update-row-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ hang_ghe_id: currentRowHangId, loai_ghe_id: loaiGheId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.updated_seats) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                });
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-check mr-1.5"></i>Áp dụng'); rowChangeModal.classList.add('hidden'); });
    });

    rowChangeModal.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // --- Bảo trì cả hàng (toggle) ---
    // Lấy CSRF token: ưu tiên meta, fallback cookie XSRF-TOKEN (Laravel tự sinh)
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        if (match) {
            return decodeURIComponent(match[1]);
        }
        return '';
    }
    console.log('[DEBUG] CSRF token =', getCsrfToken().substring(0, 10) + '...');

    // Helper fetch an toàn:
    //   - Tự đính X-CSRF-TOKEN, X-Requested-With, Accept JSON
    //   - Nếu server trả 419 (CSRF token hết hạn) thì reload meta tag từ server (gọi /csrf-token) rồi retry 1 lần
    async function adminFetch(url, options = {}) {
        options.method = options.method || 'POST';
        options.credentials = options.credentials || 'same-origin';
        options.headers = Object.assign({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        let res = await fetch(url, options);

        if (res.status === 419) {
            // CSRF token mismatch → thử lấy token mới rồi gọi lại
            try {
                const refresh = await fetch('/admin/csrf-token', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                if (refresh.ok) {
                    const data = await refresh.json();
                    if (data && data.csrf_token) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.setAttribute('content', data.csrf_token);
                    }
                }
            } catch (e) { /* ignore */ }
            options.headers['X-CSRF-TOKEN'] = getCsrfToken();
            res = await fetch(url, options);
        }

        return res;
    }

    // Hàm được gọi bởi onclick inline trên nút #rowMaintenanceBtn
    // (Đặt trên window để inline onclick truy cập được)
    window.__rowMaintClick = async function(ev) {
        // ev có thể là event từ icon bên trong button
        const btn = ev.target.closest('#rowMaintenanceBtn') || document.getElementById('rowMaintenanceBtn');
        if (!btn) return;

        console.log('[rowMaintenanceBtn] click | hang=', currentRowHangId);

        if (!currentRowHangId) {
            alert('Vui lòng chọn hàng ghế trước.');
            return;
        }

            const isAllMaint = /Kích hoạt/i.test(btn.textContent);
            const action = isAllMaint ? 'activate' : 'maintenance';
            const confirmMsg = isAllMaint
                ? 'Kích hoạt lại toàn bộ ghế trong hàng này?'
                : 'Chuyển toàn bộ ghế trong hàng sang bảo trì?';
            if (!confirm(confirmMsg)) return;

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang xử lý...';

        try {
            const url = `/admin/phong-chieus/${phongChieuId}/toggle-row-maintenance`;
            console.log('[rowMaintenanceBtn] fetch', url, { hang_ghe_id: currentRowHangId });

            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ hang_ghe_id: currentRowHangId, action: action })
            });

            console.log('[rowMaintenanceBtn] response status', res.status);
            const contentType = res.headers.get('content-type') || '';
            let data;
            if (contentType.includes('application/json')) {
                data = await res.json();
            } else {
                // Server trả về HTML (có thể do CSRF/auth/404) → đọc text để debug
                const text = await res.text();
                console.error('[rowMaintenanceBtn] Non-JSON response (status ' + res.status + '):', text.substring(0, 500));
                alert('Server trả về HTML (status ' + res.status + '). Có thể CSRF token sai hoặc session hết hạn. Xem Console.');
                btn.innerHTML = originalHtml;
                return;
            }
            console.log('[rowMaintenanceBtn] data', data);

            if (!data.success) {
                alert(data.message || 'Có lỗi xảy ra');
                btn.innerHTML = originalHtml;
                return;
            }

            // Cập nhật DOM cho từng ghế
            (data.updated_seats || []).forEach(s => {
                const el = findSeatEl(s.id);
                if (!el) return;
                el.dataset.trangThai = s.trang_thai;
                if (s.trang_thai === 'bao_tri') {
                    el.classList.add('seat-chip--maintenance');
                    el.removeAttribute('style');
                    el.style.color = '#991b1b';
                } else {
                    el.classList.remove('seat-chip--maintenance');
                    const mauSac = el.dataset.mauSac || '#666';
                    el.removeAttribute('style');
                    el.style.backgroundColor = mauSac;
                    el.style.color = '#1a0b04';
                }
            });

            // Cập nhật stats + đổi nhãn nút
            const statsEl = document.getElementById('rowMaintenanceStats');
            const row = document.querySelector(`.seat-row[data-hang-ghe-id="${currentRowHangId}"]`);
            if (statsEl && row) {
                const seatsInRow = row.querySelectorAll('.seat-interactive');
                let soHoatDong = 0, soBaoTri = 0;
                seatsInRow.forEach(s => {
                    if (s.dataset.trangThai === 'bao_tri') soBaoTri++;
                    else soHoatDong++;
                });
                statsEl.innerHTML = `<span class="text-emerald-400 font-semibold">${soHoatDong}</span> hoạt động · <span class="text-red-400 font-semibold">${soBaoTri}</span> bảo trì / ${seatsInRow.length}`;
                if (seatsInRow.length > 0 && soHoatDong === 0) {
                    btn.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i>Kích hoạt';
                    btn.className = 'rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500/25 hover:border-emerald-500/60';
                } else {
                    btn.innerHTML = '<i class="fa-solid fa-wrench mr-1"></i>Bảo trì';
                    btn.className = 'rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60';
                }
            }
        } catch (err) {
            console.error('[rowMaintenanceBtn] error', err);
            alert('Có lỗi xảy ra: ' + (err.message || err));
            btn.innerHTML = originalHtml;
        } finally {
            btn.disabled = false;
        }
    };
    console.log('[OK] window.__rowMaintClick ready');

    // --- Xóa cả hàng ---
    document.getElementById('rowChangeModalDelete').addEventListener('click', function() {
        if (!currentRowHangId) return;
        const btn = this;
        if (!confirm('Xóa cả hàng ghế này? Tất cả ghế trong hàng sẽ bị xóa vĩnh viễn. Hành động này không thể hoàn tác.')) return;

        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/delete-row-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ hang_ghe_id: currentRowHangId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-trash-can mr-1.5"></i>Xóa hàng'); });
    });
});
