// ============================================================
// LQMS — zones.js
// Zones page: modal population helpers, delete confirmation,
// progress bar animation on load.
// Depends on: app.js (openModal, closeModal)
// ============================================================

// ── Override modal ────────────────────────────────────────────
function openOverrideModal(id, name, level) {
    document.getElementById('overrideZoneId').value   = id;
    document.getElementById('overrideZoneName').value = name;
    document.getElementById('overrideLevel').value    = level;
    openModal('overrideModal');
}

// ── Edit modal ────────────────────────────────────────────────
function openEditModal(id, name, floor, warn, crit, cap, desc, lat, lng) {
    document.getElementById('editZoneId').value    = id;
    document.getElementById('editZoneName').value  = name;
    document.getElementById('editZoneFloor').value = floor;
    document.getElementById('editZoneWarn').value  = warn;
    document.getElementById('editZoneCrit').value  = crit;
    document.getElementById('editZoneCap').value   = cap;
    document.getElementById('editZoneDesc').value  = desc;
    document.getElementById('editZoneLat').value   = lat;
    document.getElementById('editZoneLng').value   = lng;
    openModal('editZoneModal');
}

// ── Delete confirmation modal ─────────────────────────────────
// Replaces the native browser confirm() popup.
// Usage: <button onclick="confirmDelete('Z001', 'Reading Area')">
let _pendingDeleteForm = null;

function confirmDelete(zoneId, zoneName) {
    const label = document.getElementById('deleteZoneName');
    if (label) label.textContent = zoneName;

    // Wire the hidden form with the correct zone_id
    const form = document.getElementById('deleteZoneForm');
    if (form) {
        form.querySelector('[name="zone_id"]').value = zoneId;
    }
    _pendingDeleteForm = form;

    openModal('deleteConfirmModal');
}

function submitDelete() {
    if (_pendingDeleteForm) {
        _pendingDeleteForm.submit();
    }
}

// ── Progress bars ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // app.js already handles .db-bar-fill[data-pct].
    // Zone cards use .zone-prog-fill[data-pct] — trigger them here
    // with a slightly longer delay so the card entrance animation
    // completes first.
    document.querySelectorAll('.zone-prog-fill[data-pct]').forEach(el => {
        const pct = parseFloat(el.dataset.pct) || 0;
        setTimeout(() => { el.style.width = `${pct}%`; }, 160);
    });
});

// ── Map coordinate pickers ────────────────────────────────
// Shared NBSC campus center — both modals use the same coords
const PICKER_CENTER = [8.359282, 124.867826];
const PICKER_ZOOM   = 19;

function initMapPicker(containerId, latInputId, lngInputId, existingLat, existingLng) {
    const container = document.getElementById(containerId);
    if (!container || !window.L) return;

    // Leaflet keeps state on the div — destroy old instance if re-opening
    if (container._leaflet_id) {
        container._leaflet_id = null;
        container.innerHTML = '';
    }

    const center = (existingLat && existingLng)
        ? [parseFloat(existingLat), parseFloat(existingLng)]
        : PICKER_CENTER;

    const map = L.map(container, { center, zoom: PICKER_ZOOM });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 22
    }).addTo(map);

    let marker = null;

    // If editing, drop existing marker
    if (existingLat && existingLng) {
        marker = L.marker(center, { draggable: true }).addTo(map);
        bindMarker(marker, latInputId, lngInputId);
    }

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        document.getElementById(latInputId).value = lat.toFixed(7);
        document.getElementById(lngInputId).value = lng.toFixed(7);

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, { draggable: true }).addTo(map);
            bindMarker(marker, latInputId, lngInputId);
        }
    });

    // Fix blank tiles when modal animates open
    setTimeout(() => map.invalidateSize(), 220);
}

function bindMarker(marker, latId, lngId) {
    marker.on('dragend', (e) => {
        const pos = e.target.getLatLng();
        document.getElementById(latId).value  = pos.lat.toFixed(7);
        document.getElementById(lngId).value  = pos.lng.toFixed(7);
    });
}

// Hook into the existing openModal calls
const _origOpenModal = window.openModal;
window.openModal = function(id) {
    _origOpenModal(id);
    if (id === 'addZoneModal') {
        setTimeout(() => initMapPicker('addZonePicker', 'addZoneLat', 'addZoneLng', null, null), 100);
    }
};

// openEditModal already populates lat/lng fields before opening
const _origOpenEditModal = window.openEditModal;
window.openEditModal = function(id, name, floor, warn, crit, cap, desc, lat, lng) {
    _origOpenEditModal(id, name, floor, warn, crit, cap, desc, lat, lng);
    setTimeout(() => initMapPicker('editZonePicker', 'editZoneLat', 'editZoneLng', lat, lng), 100);
};
