@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="row mb-3 mt-3">
    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Dashboard</h4>
            <p class="text-muted mb-0">Resumen de asistencia — <span id="dashboardDate">{{ date('d/m/Y') }}</span></p>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
            <i class="fa-solid fa-circle-dot me-1 fa-xs" style="animation:pulse 1.5s infinite;"></i> En vivo
        </span>
    </div>
</div>

{{-- Tarjetas de estadísticas --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #4F46E5 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-users"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="statTotal">--</h2>
                    <p class="text-muted mb-0 small text-truncate">Empleados totales</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #0acf97 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-success bg-opacity-10 text-success rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-user-check"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="statPresentes">--</h2>
                    <p class="text-muted mb-0 small text-truncate">Presentes hoy</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #ed5565 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-danger bg-opacity-10 text-danger rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-user-xmark"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="statAusentes">--</h2>
                    <p class="text-muted mb-0 small text-truncate">Ausentes</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #f8ac59 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-warning bg-opacity-10 text-warning rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-clock"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="statTardanzas">--</h2>
                    <p class="text-muted mb-0 small text-truncate">Tardanzas</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Barra de asistencia --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Tasa de asistencia hoy</span>
            <span class="fw-bold text-primary" id="statPct">--%</span>
        </div>
        <div class="progress" style="height:10px; border-radius:8px;">
            <div id="attendanceBar" class="progress-bar bg-success" role="progressbar" style="width:0%; border-radius:8px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted"><i class="fa-solid fa-circle text-success me-1 fa-xs"></i><span id="barPresentes">0</span> presentes</small>
            <small class="text-muted"><span id="barAusentes">0</span> ausentes <i class="fa-solid fa-circle text-danger ms-1 fa-xs"></i></small>
        </div>
    </div>
</div>

{{-- Contenido principal --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold"><i class="fa-solid fa-list-check me-1 text-primary"></i> Registros de hoy</h5>
                <span id="recordCount" class="badge bg-secondary rounded-pill">0</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 fs-13">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">Empleado</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>QR</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTbody">
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold" id="qrCardTitle"><i class="fa-solid fa-qrcode me-1 text-primary"></i> QR de Sede Principal</h5>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshQR()" title="Actualizar QR">
                    <i class="fa-solid fa-rotate" id="qrRefreshIcon"></i>
                </button>
            </div>
            <div class="card-body text-center py-4">
                <div id="qrCodeContainer" class="d-inline-block p-3 bg-white rounded-3 border shadow-sm"></div>
                <div class="mt-3">
                    <p class="fw-semibold mb-0" id="qrSedeName">Sede Principal</p>
                    <p class="text-muted small mb-0" id="qrSedeInfo">Radio: 150m &bull; Se actualiza cada 30s</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0 fw-semibold"><i class="fa-solid fa-map-location-dot me-1 text-primary"></i> Mapa de Registros</h5>
            </div>
            <div class="card-body p-0">
                <div id="dashboardMap" style="height: 280px; border-radius: 0 0 8px 8px;"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
.table > :not(caption) > * > * { padding: 0.6rem 0.75rem; }
.employee-avatar {
    width: 32px; height: 32px; min-width: 32px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700;
    color: #fff;
}
</style>
@endpush
@endsection

@push('scripts')
<script>
let gMap = null, officeMarker = null, geoCircle = null, attendanceMarkers = [];
let qrSedeId = null, qrSedeName = null, qrSedeRadio = 150;
let gmapsReady = false;

const AVATAR_COLORS = ['#4F46E5','#0acf97','#f8ac59','#ed5565','#23c6c8','#7b70ef','#1c84c6'];
function avatarColor(name) {
    let h = 0; for (let c of (name||'A')) h = (h * 31 + c.charCodeAt(0)) & 0xffff;
    return AVATAR_COLORS[h % AVATAR_COLORS.length];
}
function avatarInitials(name) {
    const parts = (name||'?').trim().split(' ').filter(Boolean);
    return parts.length >= 2 ? (parts[0][0]+parts[1][0]).toUpperCase() : (name||'?')[0].toUpperCase();
}

function tipoLabel(tipo) {
    const map = { entrada: 'Entrada', salida: 'Salida', entrada_manual: 'Entrada manual', salida_manual: 'Salida manual' };
    return map[tipo] || tipo.replace(/_/g,' ');
}
function tipoBadge(tipo) {
    if (tipo.includes('entrada')) return `<span class="badge bg-success-subtle text-success border border-success-subtle">${tipoLabel(tipo)}</span>`;
    if (tipo.includes('salida'))  return `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${tipoLabel(tipo)}</span>`;
    return `<span class="badge bg-light text-dark">${tipoLabel(tipo)}</span>`;
}

function googleMapsReadyDashboard() {
    gmapsReady = true;
    initMap();
    loadDashboard();
    loadQR();
    setInterval(loadDashboard, 30000);
    setInterval(loadQR, 30000);
}

function initMap(lat, lng, radio, nombre, direccion) {
    lat    = lat    || 4.7110;
    lng    = lng    || -74.0721;
    radio  = radio  || 150;
    nombre = nombre || 'Sede';
    const center = { lat, lng };

    if (gMap) {
        gMap.setCenter(center);
        if (officeMarker) officeMarker.setMap(null);
        if (geoCircle)    geoCircle.setMap(null);
    } else {
        gMap = new google.maps.Map(document.getElementById('dashboardMap'), {
            center, zoom: 17,
            mapTypeControl: false, streetViewControl: false, fullscreenControl: false,
            styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] }]
        });
    }

    officeMarker = new google.maps.Marker({
        position: center, map: gMap, title: nombre,
        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: '#4F46E5', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 },
    });
    officeMarker.addListener('click', () => {
        new google.maps.InfoWindow({ content: `<b>${nombre}</b><br>${direccion || ''}` }).open(gMap, officeMarker);
    });
    geoCircle = new google.maps.Circle({
        map: gMap, center, radius: radio,
        strokeColor: '#4F46E5', strokeOpacity: 0.7, strokeWeight: 2,
        fillColor: '#4F46E5', fillOpacity: 0.07,
    });
}

async function loadDashboard() {
    try {
        const res   = await fetch('/api/attendance/stats', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
        const stats = await res.json();
        const total    = stats.total_empleados || 0;
        const presentes = stats.presentes || 0;
        const ausentes  = stats.ausentes  || 0;
        const tardanzas = stats.tardanzas || 0;
        const pct = total > 0 ? Math.round((presentes / total) * 100) : 0;

        document.getElementById('statTotal').textContent     = total;
        document.getElementById('statPresentes').textContent = presentes;
        document.getElementById('statAusentes').textContent  = ausentes;
        document.getElementById('statTardanzas').textContent = tardanzas;

        // Barra de asistencia
        document.getElementById('statPct').textContent   = pct + '%';
        document.getElementById('barPresentes').textContent = presentes;
        document.getElementById('barAusentes').textContent  = ausentes;
        const bar = document.getElementById('attendanceBar');
        bar.style.width = pct + '%';
        bar.setAttribute('aria-valuenow', pct);
        bar.className = 'progress-bar ' + (pct >= 80 ? 'bg-success' : pct >= 50 ? 'bg-warning' : 'bg-danger');
    } catch(e) { console.error('Stats:', e); }

    try {
        const res  = await fetch('/api/attendance?per_page=20', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
        const data = await res.json();
        const tbody = document.getElementById('attendanceTbody');

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fa-solid fa-inbox me-1"></i> Sin registros hoy</td></tr>';
            document.getElementById('recordCount').textContent = '0';
        } else {
            document.getElementById('recordCount').textContent = data.data.length;
            tbody.innerHTML = data.data.map(r => {
                const name  = r.user?.name || 'N/A';
                const depto = r.user?.departamento || '';
                const hora  = new Date(r.fecha_hora).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
                const color = avatarColor(name);
                const initials = avatarInitials(name);
                return `<tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="employee-avatar" style="background:${color};">${initials}</span>
                            <div>
                                <div class="fw-semibold">${name}</div>
                                ${depto ? `<small class="text-muted">${depto}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="text-nowrap">${hora}</td>
                    <td>${tipoBadge(r.tipo)}</td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle">${r.metodo}</span></td>
                    <td>${r.qr_validado
                        ? '<i class="fa-solid fa-circle-check text-success" title="QR Válido"></i>'
                        : '<i class="fa-solid fa-circle-xmark text-danger" title="QR Inválido"></i>'
                    }</td>
                </tr>`;
            }).join('');
        }

        // Actualizar marcadores del mapa (Google Maps)
        attendanceMarkers.forEach(m => m.setMap(null));
        attendanceMarkers = [];
        if (data.data && gMap) {
            data.data.filter(r => r.lat && r.lng).forEach(r => {
                const isEntrada = r.tipo.includes('entrada');
                const m = new google.maps.Marker({
                    position: { lat: parseFloat(r.lat), lng: parseFloat(r.lng) }, map: gMap,
                    icon: { path: google.maps.SymbolPath.CIRCLE, scale: 7, fillColor: isEntrada ? '#0acf97' : '#ed5565', fillOpacity: 0.9, strokeColor: '#fff', strokeWeight: 2 },
                });
                m.addListener('click', () => {
                    new google.maps.InfoWindow({ content: `<b>${r.user?.name || 'N/A'}</b><br>${tipoLabel(r.tipo)}` }).open(gMap, m);
                });
                attendanceMarkers.push(m);
            });
        }
    } catch(e) { console.error('Attendance:', e); }
}

async function loadQR() {
    const icon = document.getElementById('qrRefreshIcon');
    icon.classList.add('fa-spin');
    try {
        if (!qrSedeId) {
            const res  = await fetch('/api/sedes', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
            const data = await res.json();
            const sede = data.data?.find(s => s.is_active) || data.data?.[0];
            if (!sede) {
                document.getElementById('qrCodeContainer').innerHTML = '<p class="text-muted small p-3">Sin sedes registradas</p>';
                return;
            }
            qrSedeId   = sede.id;
            qrSedeName = sede.nombre;
            qrSedeRadio = sede.radio_mts || 150;

            document.getElementById('qrCardTitle').innerHTML = `<i class="fa-solid fa-qrcode me-1 text-primary"></i> QR — ${sede.nombre}`;
            document.getElementById('qrSedeName').textContent = sede.nombre;
            document.getElementById('qrSedeInfo').textContent = `Radio: ${qrSedeRadio}m \u2022 Se actualiza cada 30s`;

            if (gmapsReady) initMap(parseFloat(sede.lat), parseFloat(sede.lng), qrSedeRadio, sede.nombre, sede.direccion);
        }

        const res  = await fetch(`/api/sedes/${qrSedeId}/qr`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
        const data = await res.json();
        if (!data.qr_value) return;

        document.getElementById('qrCodeContainer').innerHTML = '';
        new QRCode(document.getElementById('qrCodeContainer'), {
            text: data.qr_value, width: 180, height: 180,
            colorDark: '#1e293b', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    } catch(e) { console.error('QR:', e); }
    finally { setTimeout(() => icon.classList.remove('fa-spin'), 600); }
}
function refreshQR() { loadQR(); }

document.addEventListener('DOMContentLoaded', () => {
    if (!gmapsReady) {
        // Google Maps aún no cargó — loadDashboard y loadQR
        // se llaman desde googleMapsReadyDashboard()
        loadDashboard();
        loadQR();
    }
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&loading=async&callback=googleMapsReadyDashboard"></script>
@endpush
