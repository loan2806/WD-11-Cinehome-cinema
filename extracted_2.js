
(function() {
    const tooltip = document.getElementById('seatTooltip');
    const ttMaGhe = document.getElementById('seatTooltipMaGhe');
    const ttLoai = document.getElementById('seatTooltipLoai');
    const ttPhuThu = document.getElementById('seatTooltipPhuThu');

    document.addEventListener('mouseover', function(e) {
        const seat = e.target.closest('.seat-interactive');
        if (!seat) return;
        const maGhe = seat.dataset.maGhe || '';
        const loaiGhe = seat.dataset.loaiGhe || 'Thường';
        const phuThu = parseInt(seat.dataset.phuThu || 0);
        const trangThai = seat.dataset.trangThai || '';

        ttMaGhe.textContent = 'Ghế ' + maGhe;
        let loaiText = 'Loại: ' + loaiGhe;
        if (trangThai === 'bao_tri') loaiText += ' (Bảo trì)';
        ttLoai.textContent = loaiText;
        ttPhuThu.textContent = 'Phụ thu: ' + phuThu.toLocaleString() + 'đ';

        tooltip.style.opacity = '1';
    });

    document.addEventListener('mousemove', function(e) {
        if (tooltip.style.opacity === '0') return;
        const offsetX = 14;
        const offsetY = 14;
        let x = e.clientX + offsetX;
        let y = e.clientY + offsetY;
        // Đảm bảo tooltip không tràn ra ngoài viewport
        const rect = tooltip.getBoundingClientRect();
        if (x + rect.width > window.innerWidth) x = e.clientX - rect.width - offsetX;
        if (y + rect.height > window.innerHeight) y = e.clientY - rect.height - offsetY;
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
    });

    document.addEventListener('mouseout', function(e) {
        const seat = e.target.closest('.seat-interactive');
        if (!seat) return;
        tooltip.style.opacity = '0';
    });

    /* ============================================ */
    /* MODAL: THÊM GHẾ                              */
    /* ============================================ */
    const addSeatModal = document.getElementById('addSeatModal');
    const addSeatForm = document.getElementById('addSeatForm');
    const addSeatErrors = document.getElementById('addSeatModalErrors');
    const addSeatHangGhe = document.getElementById('addSeatHangGhe');
    const addSeatMaGhe = document.getElementById('addSeatMaGhe');
    const addSeatCot = document.getElementById('addSeatCot');
    const addSeatLoaiGhe = document.getElementById('addSeatLoaiGhe');
    const addSeatTrangThai = document.getElementById('addSeatTrangThai');
    const phongChieuIdAddSeat = document.getElementById('btnOpenAddSeat')?.dataset.phongId;

    function openAddSeatModal() {
        if (!addSeatModal) return;
        addSeatForm.reset();
        addSeatErrors.classList.add('hidden');
        addSeatErrors.innerHTML = '';
        addSeatModal.classList.remove('hidden');
        // Auto-suggest cột kế tiếp nếu đã chọn hàng
        addSeatHangGhe.dispatchEvent(new Event('change'));
    }

    function closeAddSeatModal() {
        addSeatModal.classList.add('hidden');
    }

    function showAddSeatErrors(errors) {
        addSeatErrors.classList.remove('hidden');
        const html = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
        addSeatErrors.innerHTML = html;
    }

    // Tự động gợi ý cột kế tiếp + mã ghế khi chọn hàng
    addSeatHangGhe?.addEventListener('change', async function() {
        const hangId = this.value;
        if (!hangId || !phongChieuIdAddSeat) {
            addSeatCot.value = 1;
            addSeatMaGhe.value = '';
            return;
        }
        // Lấy danh sách hàng để biết tên hàng + số ghế hiện tại
        try {
            const res = await fetch(`/admin/phong-chieus/${phongChieuIdAddSeat}/hang-ghes`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const hang = (json.data || []).find(h => String(h.id) === String(hangId));
            if (hang) {
                const nextCol = (hang.so_ghe || 0) + 1;
                addSeatCot.value = nextCol;
                addSeatMaGhe.value = hang.ten_hang + nextCol;
            }
        } catch (e) {
            console.error('Lỗi tải hàng:', e);
        }
    });

    document.getElementById('btnOpenAddSeat')?.addEventListener('click', openAddSeatModal);
    document.getElementById('addSeatModalClose')?.addEventListener('click', closeAddSeatModal);
    document.getElementById('addSeatCancel')?.addEventListener('click', closeAddSeatModal);
    addSeatModal?.addEventListener('click', function(e) {
        if (e.target === addSeatModal) closeAddSeatModal();
    });

    addSeatForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        addSeatErrors.classList.add('hidden');

        if (!phongChieuIdAddSeat) {
            showAddSeatErrors({ _global: ['Không xác định được ID phòng chiếu. Vui lòng tải lại trang.'] });
            return;
        }

        const payload = {
            hang_ghe_id: addSeatHangGhe.value,
            ma_ghe: addSeatMaGhe.value.trim(),
            cot: parseInt(addSeatCot.value, 10),
            loai_ghe_id: addSeatLoaiGhe.value,
            trang_thai: addSeatTrangThai.value,
        };

        if (!payload.hang_ghe_id || !payload.ma_ghe || !payload.cot || !payload.loai_ghe_id || !payload.trang_thai) {
            showAddSeatErrors({ _global: ['Vui lòng điền đầy đủ thông tin.'] });
            return;
        }

        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showAddSeatErrors({ _global: ['Không tìm thấy CSRF token. Vui lòng tải lại trang (F5).'] });
            return;
        }

        const btn = document.getElementById('addSeatSubmit');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang thêm...';

        try {
            const url = `/admin/phong-chieus/${phongChieuIdAddSeat}/create-seat`;
            console.log('[AddSeat] POST', url, payload);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            console.log('[AddSeat] Response status:', res.status);
            const data = await res.json().catch(() => ({ success: false, message: 'Server trả về response không phải JSON' }));
            if (!res.ok || !data.success) {
                if (data.errors) showAddSeatErrors(data.errors);
                else showAddSeatErrors({ _global: [data.message || `Lỗi HTTP ${res.status}`] });
                return;
            }
            closeAddSeatModal();
            location.reload();
        } catch (err) {
            console.error('[AddSeat] Fetch error:', err);
            const msg = err && err.message ? err.message : String(err);
            showAddSeatErrors({ _global: [`Lỗi kết nối: ${msg}. Vui lòng kiểm tra mạng và thử lại (F5 nếu cần).`] });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    /* ============================================ */
    /* MODAL: THÊM HÀNG                             */
    /* ============================================ */
    const addRowModal = document.getElementById('addRowModal');
    const addRowForm = document.getElementById('addRowForm');
    const addRowErrors = document.getElementById('addRowModalErrors');
    const addRowTenHang = document.getElementById('addRowTenHang');
    const addRowIsCouple = document.getElementById('addRowIsCouple');
    const addRowLoaiMacDinh = document.getElementById('addRowLoaiMacDinh');
    const addRowAuto = document.getElementById('addRowAuto');
    const addRowAutoBox = document.getElementById('addRowAutoBox');
    const addRowSoGhe = document.getElementById('addRowSoGhe');
    const addRowCotBatDau = document.getElementById('addRowCotBatDau');
    const addRowLoaiGhe = document.getElementById('addRowLoaiGhe');
    const addRowTrangThai = document.getElementById('addRowTrangThai');
    const addRowPreview = document.getElementById('addRowPreview');
    const phongChieuIdAddRow = document.getElementById('btnOpenAddRow')?.dataset.phongId;

    function updateAddRowPreview() {
        if (!addRowPreview) return;
        const ten = (addRowTenHang.value || 'A').trim();
        const so = parseInt(addRowSoGhe.value, 10) || 0;
        const start = parseInt(addRowCotBatDau.value, 10) || 1;
        if (so <= 0) { addRowPreview.textContent = `${ten}1, ${ten}2, ...`; return; }
        if (so <= 3) {
            const codes = [];
            for (let i = 0; i < so; i++) codes.push(ten + (start + i));
            addRowPreview.textContent = codes.join(', ');
        } else {
            addRowPreview.textContent = `${ten}${start}, ${ten}${start + 1}, ... ${ten}${start + so - 1}`;
        }
    }

    function updateAddRowAutoBox() {
        if (!addRowAutoBox) return;
        if (addRowAuto.checked) {
            addRowAutoBox.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            addRowAutoBox.classList.add('opacity-50', 'pointer-events-none');
        }
    }

    function openAddRowModal() {
        if (!addRowModal) return;
        addRowForm.reset();
        addRowAuto.checked = true;
        addRowErrors.classList.add('hidden');
        addRowErrors.innerHTML = '';
        updateAddRowAutoBox();
        updateAddRowPreview();
        addRowModal.classList.remove('hidden');
    }

    function closeAddRowModal() {
        addRowModal.classList.add('hidden');
    }

    function showAddRowErrors(errors) {
        addRowErrors.classList.remove('hidden');
        const html = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
        addRowErrors.innerHTML = html;
    }

    document.getElementById('btnOpenAddRow')?.addEventListener('click', openAddRowModal);
    document.getElementById('addRowModalClose')?.addEventListener('click', closeAddRowModal);
    document.getElementById('addRowCancel')?.addEventListener('click', closeAddRowModal);
    addRowModal?.addEventListener('click', function(e) {
        if (e.target === addRowModal) closeAddRowModal();
    });

    addRowTenHang?.addEventListener('input', updateAddRowPreview);
    addRowSoGhe?.addEventListener('input', updateAddRowPreview);
    addRowCotBatDau?.addEventListener('input', updateAddRowPreview);
    addRowAuto?.addEventListener('change', updateAddRowAutoBox);

    addRowForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        addRowErrors.classList.add('hidden');

        if (!phongChieuIdAddRow) {
            showAddRowErrors({ _global: ['Không xác định được ID phòng chiếu. Vui lòng tải lại trang.'] });
            return;
        }

        const tuDong = addRowAuto.checked;
        const soGhe = parseInt(addRowSoGhe.value, 10) || 0;
        const loaiGheId = addRowLoaiGhe.value;

        if (!addRowTenHang.value.trim()) {
            showAddRowErrors({ ten_hang: ['Vui lòng nhập tên hàng.'] });
            return;
        }
        if (tuDong && (!soGhe || soGhe < 1)) {
            showAddRowErrors({ so_ghe: ['Vui lòng nhập số ghế khi bật tự động tạo.'] });
            return;
        }
        if (tuDong && !loaiGheId) {
            showAddRowErrors({ loai_ghe_id: ['Vui lòng chọn loại ghế khi bật tự động tạo.'] });
            return;
        }

        const payload = {
            ten_hang: addRowTenHang.value.trim(),
            la_hang_couple: addRowIsCouple.checked,
            loai_ghe_mac_dinh_id: addRowLoaiMacDinh.value || null,
            tu_dong_tao_ghe: tuDong,
            so_ghe: tuDong ? soGhe : 0,
            cot_bat_dau: parseInt(addRowCotBatDau.value, 10) || 1,
            loai_ghe_id: tuDong ? loaiGheId : null,
            trang_thai: addRowTrangThai.value || 'hoat_dong',
        };

        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showAddRowErrors({ _global: ['Không tìm thấy CSRF token. Vui lòng tải lại trang (F5).'] });
            return;
        }

        const btn = document.getElementById('addRowSubmit');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang thêm...';

        try {
            const url = `/admin/phong-chieus/${phongChieuIdAddRow}/create-row`;
            console.log('[AddRow] POST', url, payload);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            console.log('[AddRow] Response status:', res.status);
            const data = await res.json().catch(() => ({ success: false, message: 'Server trả về response không phải JSON' }));
            if (!res.ok || !data.success) {
                if (data.errors) showAddRowErrors(data.errors);
                else showAddRowErrors({ _global: [data.message || `Lỗi HTTP ${res.status}`] });
                return;
            }
            closeAddRowModal();
            location.reload();
        } catch (err) {
            console.error('[AddRow] Fetch error:', err);
            const msg = err && err.message ? err.message : String(err);
            showAddRowErrors({ _global: [`Lỗi kết nối: ${msg}. Vui lòng kiểm tra mạng và thử lại (F5 nếu cần).`] });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    // ESC để đóng modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddSeatModal();
            closeAddRowModal();
        }
    });
})();
