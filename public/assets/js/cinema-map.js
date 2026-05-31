/**
 * Bản đồ rạp — Leaflet + OSM, geolocation, Haversine, API Laravel.
 * Phụ thuộc: Leaflet (CDN), biến window.CINEMA_MAP_API_URL (blade).
 */
(function () {
    'use strict';

    const apiUrl = window.CINEMA_MAP_API_URL;
    const singleCinema = window.CINEMA_MAP_SINGLE_CINEMA || null;
    const singleMode = singleCinema != null;

    if (!singleMode && !apiUrl) {
        console.error('Thiếu CINEMA_MAP_API_URL');
        return;
    }

    /** Bán kính Trái Đất (km) — chuẩn Haversine */
    const EARTH_RADIUS_KM = 6371;

    /**
     * Khoảng cách đường chim bay giữa hai điểm (độ → km).
     * @param {number} lat1
     * @param {number} lon1
     * @param {number} lat2
     * @param {number} lon2
     * @returns {number}
     */
    function haversineKm(lat1, lon1, lat2, lon2) {
        const toRad = (deg) => (deg * Math.PI) / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) *
                Math.cos(toRad(lat2)) *
                Math.sin(dLon / 2) *
                Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return EARTH_RADIUS_KM * c;
    }

    /**
     * Hiển thị khoảng cách thân thiện (km / m).
     * @param {number|null} km
     * @returns {string}
     */
    function formatDistance(km) {
        if (km == null || Number.isNaN(km)) {
            return '—';
        }
        if (km < 1) {
            return `${Math.round(km * 1000)} m`;
        }
        return `${km.toFixed(1)} km`;
    }

    /**
     * Link Google Maps chỉ đường (không cần Maps JS API key).
     * @param {number} lat
     * @param {number} lng
     */
    function buildDirectionsUrl(lat, lng) {
        return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    }

    /** DOM */
    const elMap = document.getElementById('cinema-map');
    const elList = document.getElementById('cinema-list');
    const elLoading = document.getElementById('location-loading');
    const elDenied = document.getElementById('location-denied-banner');
    const elFallback = document.getElementById('location-fallback');
    const elCity = document.getElementById('city-filter');
    const elListHeading = document.getElementById('list-heading');
    const elDetail = document.getElementById('cinema-detail-panel');
    const elDetailName = document.getElementById('detail-name');
    const elDetailAddress = document.getElementById('detail-address');
    const elDetailDistance = document.getElementById('detail-distance');
    const elDetailDirections = document.getElementById('detail-directions');
    const elShowDistance = document.getElementById('cinema-show-distance');
    const elSidebar = document.querySelector('.cinema-map-sidebar');
    const elLayout = document.querySelector('.cinema-map-layout');

    /** @type {L.Map|null} */
    let map = null;
    /** @type {L.LayerGroup|null} */
    let markerLayer = null;
    /** @type {L.Marker|null} */
    let userMarker = null;

    /** @type {{ id:number, name:string, address:string, city:string|null, latitude:number, longitude:number, status:string }[]} */
    let allCinemas = [];
    /** @type {string[]} */
    let cityOptions = [];

    /** @type {{ lat:number, lng:number }|null} */
    let userPos = null;
    let geolocationDenied = false;

    /** id rạp đang chọn (highlight list + popup) */
    let selectedId = null;

    function hasCoords(c) {
        return (
            c.latitude != null &&
            c.longitude != null &&
            !Number.isNaN(Number(c.latitude)) &&
            !Number.isNaN(Number(c.longitude))
        );
    }

    /**
     * Gắn khoảng cách Haversine vào mỗi rạp (thuộc tính distanceKm).
     * @param {object[]} cinemas
     */
    function attachDistances(cinemas) {
        if (!userPos) {
            cinemas.forEach((c) => {
                c.distanceKm = null;
            });
            return;
        }
        cinemas.forEach((c) => {
            if (!hasCoords(c)) {
                c.distanceKm = null;
                return;
            }
            c.distanceKm = haversineKm(
                userPos.lat,
                userPos.lng,
                Number(c.latitude),
                Number(c.longitude)
            );
        });
    }

    /**
     * Sắp xếp: có vị trí user → gần nhất trước (rạp không tọa độ xếp cuối); không có → theo tên.
     * @param {object[]} cinemas
     */
    function sortCinemas(cinemas) {
        const copy = [...cinemas];
        if (userPos) {
            copy.sort((a, b) => {
                const da = a.distanceKm;
                const db = b.distanceKm;
                if (da != null && db != null) {
                    return da - db;
                }
                if (da != null) {
                    return -1;
                }
                if (db != null) {
                    return 1;
                }
                return a.name.localeCompare(b.name, 'vi');
            });
        } else {
            copy.sort((a, b) => a.name.localeCompare(b.name, 'vi'));
        }
        return copy;
    }

    /**
     * Lọc theo thành phố (khi user từ chối định vị hoặc muốn thu hẹp).
     * @param {object[]} cinemas
     * @param {string} city — rỗng = tất cả
     */
    function filterByCity(cinemas, city) {
        if (!city) {
            return cinemas;
        }
        return cinemas.filter((c) => (c.city || '') === city);
    }

    function setListHeading() {
        if (!elListHeading) {
            return;
        }
        if (userPos) {
            elListHeading.textContent = 'Rạp gần bạn';
        } else {
            elListHeading.textContent = 'Danh sách rạp';
        }
    }

    /**
     * Chuẩn hóa dữ liệu rạp từ Blade (trang chi tiết).
     * @param {object} raw
     */
    function normalizeSingleCinema(raw) {
        return {
            id: Number(raw.id),
            name: String(raw.name || ''),
            address: String(raw.address || ''),
            city: raw.city != null ? String(raw.city) : null,
            latitude: raw.latitude != null ? Number(raw.latitude) : null,
            longitude: raw.longitude != null ? Number(raw.longitude) : null,
            status: String(raw.status || 'active'),
        };
    }

    function applySingleModeUI() {
        if (elSidebar) {
            elSidebar.classList.add('is-hidden');
        }
        if (elLayout) {
            elLayout.classList.add('cinema-map-layout--single');
        }
        if (elFallback) {
            elFallback.classList.add('is-hidden');
        }
        if (elDetail) {
            elDetail.classList.remove('is-hidden');
        }
    }

    /**
     * Popup HTML trên marker.
     * @param {object} c
     */
    function popupHtml(c) {
        const dist = formatDistance(c.distanceKm);
        const dirUrl = buildDirectionsUrl(Number(c.latitude), Number(c.longitude));
        return (
            `<strong style="color:#f5a623">${escapeHtml(c.name)}</strong><br>` +
            `<span style="color:#ccc;font-size:12px">${escapeHtml(c.address)}</span><br>` +
            `<span style="color:#f5a623;font-weight:700">${dist}</span><br>` +
            `<a href="${dirUrl}" target="_blank" rel="noopener noreferrer" style="color:#93c5fd;font-weight:700">Chỉ đường</a>`
        );
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function distanceMessage(c) {
        if (userPos != null && hasCoords(c)) {
            return `Khoảng cách từ bạn: ${formatDistance(c.distanceKm)}`;
        }
        if (userPos != null && !hasCoords(c)) {
            return 'Rạp chưa có tọa độ trên bản đồ — không tính được khoảng cách.';
        }
        return 'Bật định vị trình duyệt để xem khoảng cách từ vị trí của bạn.';
    }

    function showDetail(c) {
        const msg = distanceMessage(c);

        if (elDetail) {
            elDetail.classList.remove('is-hidden');
        }
        if (elDetailName) {
            elDetailName.textContent = c.name;
        }
        if (elDetailAddress) {
            elDetailAddress.textContent = c.address + (c.city ? ` — ${c.city}` : '');
        }
        if (elDetailDistance) {
            elDetailDistance.textContent = msg;
        }
        if (elShowDistance) {
            elShowDistance.textContent = msg;
        }
        if (elDetailDirections) {
            if (hasCoords(c)) {
                elDetailDirections.href = buildDirectionsUrl(
                    Number(c.latitude),
                    Number(c.longitude)
                );
                elDetailDirections.classList.remove('is-hidden');
            } else {
                elDetailDirections.classList.add('is-hidden');
            }
        }
    }

    function highlightListItem(id) {
        if (!elList) {
            return;
        }
        elList.querySelectorAll('button').forEach((btn) => {
            btn.classList.toggle('is-active', Number(btn.dataset.id) === id);
        });
    }

    /**
     * Vẽ / cập nhật danh sách và marker theo bộ rạp hiện tại.
     * @param {object[]} subset — đã gắn distanceKm và sắp xếp
     */
    function renderMapAndList(subset) {
        if (!map) {
            initMap(subset);
        } else {
            markerLayer.clearLayers();
            subset.forEach((c) => {
                if (hasCoords(c)) {
                    addCinemaMarker(c);
                }
            });
        }

        if (!elList) {
            return;
        }

        elList.innerHTML = '';
        subset.forEach((c) => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.id = String(c.id);
            const distLabel =
                c.distanceKm != null ? formatDistance(c.distanceKm) : '—';
            btn.innerHTML =
                `<span class="cinema-map-list-name">${escapeHtml(c.name)}</span>` +
                `<span class="cinema-map-list-meta">${escapeHtml(distLabel)} · ${escapeHtml(
                    c.city || ''
                )}</span>`;
            btn.addEventListener('click', () => {
                selectedId = c.id;
                highlightListItem(c.id);
                showDetail(c);
                const m = c._leafletMarker;
                if (m && map) {
                    map.setView(m.getLatLng(), Math.max(map.getZoom(), 13), { animate: true });
                    m.openPopup();
                }
            });
            li.appendChild(btn);
            elList.appendChild(li);
        });

        // Fit bounds có cả user + rạp (nếu có user)
        const bounds = L.latLngBounds([]);
        subset.forEach((c) => {
            if (hasCoords(c)) {
                bounds.extend([Number(c.latitude), Number(c.longitude)]);
            }
        });
        if (userPos) {
            bounds.extend([userPos.lat, userPos.lng]);
        }
        if (bounds.isValid() && map) {
            const withCoords = subset.filter((c) => hasCoords(c));
            if (withCoords.length === 1 && !userPos) {
                const one = withCoords[0];
                map.setView([Number(one.latitude), Number(one.longitude)], singleMode ? 14 : 12);
            } else {
                map.fitBounds(bounds, {
                    padding: [36, 36],
                    maxZoom: singleMode ? 15 : 12,
                });
            }
        } else if (map) {
            if (userPos) {
                map.setView([userPos.lat, userPos.lng], 12);
            } else {
                map.setView([21.0285, 105.8542], 11);
            }
        }
    }

    /**
     * @param {object} c
     */
    function addCinemaMarker(c) {
        const icon = L.divIcon({
            className: 'cinema-map-marker-wrap',
            html: '<div class="cinema-map-marker-pin"></div>',
            iconSize: [28, 36],
            iconAnchor: [14, 34],
            popupAnchor: [0, -30],
        });

        const m = L.marker([Number(c.latitude), Number(c.longitude)], { icon })
            .bindPopup(popupHtml(c))
            .on('click', () => {
                selectedId = c.id;
                highlightListItem(c.id);
                showDetail(c);
            });

        c._leafletMarker = m;
        markerLayer.addLayer(m);
    }

    function injectMarkerPinCss() {
        if (document.getElementById('cinema-map-marker-style')) {
            return;
        }
        const style = document.createElement('style');
        style.id = 'cinema-map-marker-style';
        style.textContent =
            '.cinema-map-marker-pin{width:22px;height:22px;border-radius:50%;background:#f5a623;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.45);}' +
            '.cinema-map-user-dot{width:16px;height:16px;border-radius:50%;background:#38bdf8;border:3px solid #fff;box-shadow:0 0 0 6px rgba(56,189,248,.35);}';
        document.head.appendChild(style);
    }

    /**
     * Khởi tạo Leaflet một lần.
     * @param {object[]} subset
     */
    function initMap(subset) {
        injectMarkerPinCss();
        const center = userPos
            ? [userPos.lat, userPos.lng]
            : [21.0285, 105.8542]; // Hà Nội mặc định khi chưa có GPS

        map = L.map(elMap, { scrollWheelZoom: true }).setView(center, 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        markerLayer = L.layerGroup().addTo(map);

        if (userPos) {
            const userIcon = L.divIcon({
                className: 'cinema-map-user-wrap',
                html: '<div class="cinema-map-user-dot"></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });
            // Gắn trực tiếp lên map (không dùng markerLayer) để clearLayers không xóa mất vị trí user.
            userMarker = L.marker([userPos.lat, userPos.lng], { icon: userIcon })
                .bindPopup('<strong>Bạn đang ở đây</strong>')
                .addTo(map);
        }

        subset.forEach((c) => {
            if (hasCoords(c)) {
                addCinemaMarker(c);
            }
        });
    }

    function fillCitySelect() {
        if (!elCity) {
            return;
        }
        elCity.innerHTML = '<option value="">— Tất cả —</option>';
        cityOptions.forEach((city) => {
            const opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            elCity.appendChild(opt);
        });
    }

    /**
     * Tính lại khoảng cách, sắp xếp, vẽ lại (khi đổi city hoặc sau khi có GPS).
     */
    function refreshView() {
        const cityVal = elCity ? elCity.value || '' : '';
        let subset = filterByCity(allCinemas, cityVal);
        attachDistances(subset);
        subset = sortCinemas(subset);
        setListHeading();
        renderMapAndList(subset);
        if (selectedId != null) {
            const found = subset.find((x) => x.id === selectedId);
            if (found) {
                highlightListItem(selectedId);
                showDetail(found);
            } else {
                selectedId = null;
                if (elDetail && !singleMode) {
                    elDetail.classList.add('is-hidden');
                }
            }
        }
    }

    function openSelectedMarkerPopup(c) {
        const m = c._leafletMarker;
        if (m && map) {
            map.setView(m.getLatLng(), Math.max(map.getZoom(), singleMode ? 14 : 13), {
                animate: true,
            });
            m.openPopup();
        }
    }

    async function loadCinemas() {
        const res = await fetch(apiUrl, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            throw new Error('Không tải được danh sách rạp');
        }
        const data = await res.json();
        allCinemas = data.cinemas || [];
        cityOptions = data.cities || [];
        fillCitySelect();
    }

    function requestGeolocation() {
        if (!navigator.geolocation) {
            geolocationDenied = true;
            if (elDenied) {
                elDenied.classList.remove('is-hidden');
            }
            if (elFallback && !singleMode) {
                elFallback.classList.remove('is-hidden');
            }
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    userPos = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                    };
                    geolocationDenied = false;
                    resolve();
                },
                () => {
                    geolocationDenied = true;
                    if (elDenied) {
                        elDenied.classList.remove('is-hidden');
                    }
                    if (elFallback && !singleMode) {
                        elFallback.classList.remove('is-hidden');
                    }
                    resolve();
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
            );
        });
    }

    async function boot() {
        try {
            if (singleMode) {
                allCinemas = [normalizeSingleCinema(singleCinema)];
                selectedId = allCinemas[0].id;
                await requestGeolocation();
            } else {
                await Promise.all([loadCinemas(), requestGeolocation()]);
            }
        } catch (e) {
            if (elLoading) {
                elLoading.textContent = 'Lỗi tải dữ liệu. Vui lòng thử lại sau.';
            }
            console.error(e);
            return;
        }

        if (elLoading) {
            elLoading.classList.add('is-hidden');
        }

        if (singleMode) {
            applySingleModeUI();
        } else if (elFallback) {
            if (geolocationDenied) {
                elFallback.classList.remove('is-hidden');
            } else {
                elFallback.classList.add('is-hidden');
            }
        }

        if (elCity) {
            elCity.addEventListener('change', () => {
                refreshView();
            });
        }

        refreshView();

        if (singleMode && allCinemas.length) {
            const c = allCinemas[0];
            showDetail(c);
            if (map) {
                setTimeout(() => {
                    map.invalidateSize();
                    openSelectedMarkerPopup(c);
                }, 150);
            }
        }
    }

    if (!elMap) {
        console.error('Thiếu phần tử #cinema-map');
        return;
    }

    boot();
})();
