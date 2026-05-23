@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-1"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Dashboard</h4>
            <p class="text-muted mb-0">Resumen de asistencia — <span id="dashboardDate">{{ date('d/m/Y') }}</span></p>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-primary rounded-2 fs-4"><i class="fa-solid fa-users"></i></span>
                    <div>
                        <h3 class="mb-0" id="statTotal">--</h3>
                        <p class="text-muted mb-0 fs-13">Empleados totales</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-success rounded-2 fs-4"><i class="fa-solid fa-user-check"></i></span>
                    <div>
                        <h3 class="mb-0" id="statPresentes">--</h3>
                        <p class="text-muted mb-0 fs-13">Presentes hoy</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-danger rounded-2 fs-4"><i class="fa-solid fa-user-xmark"></i></span>
                    <div>
                        <h3 class="mb-0" id="statAusentes">--</h3>
                        <p class="text-muted mb-0 fs-13">Ausentes</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-title bg-warning rounded-2 fs-4"><i class="fa-solid fa-clock"></i></span>
                    <div>
                        <h3 class="mb-0" id="statTardanzas">--</h3>
                        <p class="text-muted mb-0 fs-13">Tardanzas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-list-check me-1"></i> Registros de hoy</h5>
                    <span class="badge bg-success">En vivo</span>
                </div>
                <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Empleado</th>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Método</th>
                                <th>QR Válido</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTbody">
                            <tr><td colspan="5" class="text-center text-muted py-3">Cargando registros...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-qrcode me-1"></i> QR de Sede Principal</h5>
                    <button class="btn btn-sm btn-primary" onclick="refreshQR()"><i class="fa-solid fa-rotate"></i></button>
                </div>
                <div class="card-body text-center">
                    <div id="qrCodeContainer" class="d-inline-block p-3 bg-white rounded-3 border"></div>
                    <p class="text-muted small mt-2 mb-0">
                        <strong>Sede Principal</strong> — Av. Reforma 222, CDMX<br>
                        Radio: 150m | Se actualiza cada 30s
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa-solid fa-map-location-dot me-1"></i> Mapa de Registros</h5>
                </div>
                <div class="card-body p-0">
                    <div id="dashboardMap" style="height: 300px; border-radius: 0 0 8px 8px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let map, officeMarker, geoCircle, attendanceMarkers = [];

function initMap() {
    map = L.map('dashboardMap').setView([19.4326, -99.1332], 14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    officeMarker = L.marker([19.4326, -99.1332], {
        icon: L.divIcon({
            html: '<div style="background:#4f46e5;color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 8px rgba(79,70,229,.5);border:2px solid #fff;">🏢</div>',
            className: '', iconSize: [28,28], iconAnchor: [14,14]
        })
    }).addTo(map).bindPopup('<b>Sede Principal</b><br>Av. Reforma 222, CDMX');

    geoCircle = L.circle([19.4326, -99.1332], {
        radius: 150, color: '#4f46e5', fillColor: '#4f46e5', fillOpacity: 0.08, weight: 2, dashArray: '8 4'
    }).addTo(map);

    setTimeout(() => map.invalidateSize(), 200);
}

async function loadDashboard() {
    try {
        const res = await fetch('/api/attendance/stats', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        const stats = await res.json();
        document.getElementById('statTotal').textContent = stats.total_empleados;
        document.getElementById('statPresentes').textContent = stats.presentes;
        document.getElementById('statAusentes').textContent = stats.ausentes;
        document.getElementById('statTardanzas').textContent = stats.tardanzas;
    } catch(e) { console.error('Stats:', e); }

    try {
        const res = await fetch('/api/attendance?per_page=20', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        const data = await res.json();
        const tbody = document.getElementById('attendanceTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin registros hoy</td></tr>';
        } else {
            tbody.innerHTML = data.data.map(r => `
                <tr>
                    <td><strong>${r.user?.name || 'N/A'}</strong><br><small class="text-muted">${r.user?.departamento || ''}</small></td>
                    <td>${new Date(r.fecha_hora).toLocaleTimeString('es-MX', {hour:'2-digit', minute:'2-digit'})}</td>
                    <td>${r.tipo.replace('_', ' ')}</td>
                    <td><span class="badge bg-info">${r.metodo}</span></td>
                    <td><span class="badge ${r.qr_validado ? 'bg-success' : 'bg-danger'}">${r.qr_validado ? 'Válido' : 'Inválido'}</span></td>
                </tr>
            `).join('');
        }

        // Update map markers
        attendanceMarkers.forEach(m => map.removeLayer(m));
        attendanceMarkers = [];
        if (data.data) {
            data.data.filter(r => r.lat && r.lng).forEach(r => {
                const m = L.circleMarker([r.lat, r.lng], {
                    radius: 6, fillColor: r.tipo.includes('entrada') ? '#10b981' : '#ef4444',
                    color: '#fff', weight: 2, fillOpacity: 0.9
                }).addTo(map).bindPopup(`<b>${r.user?.name || 'N/A'}</b><br>${r.tipo}`);
                attendanceMarkers.push(m);
            });
        }
    } catch(e) { console.error('Attendance:', e); }
}

let qrSedeId = null;

async function loadQR() {
    try {
        // Si no tenemos sede cargada, buscar la primera activa
        if (!qrSedeId) {
            const res  = await fetch('/api/sedes', { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
            const data = await res.json();
            const sede = data.data?.find(s => s.is_active) || data.data?.[0];
            if (!sede) {
                document.getElementById('qrCodeContainer').innerHTML = '<p class="text-muted small">Sin sedes registradas</p>';
                return;
            }
            qrSedeId = sede.id;
            document.querySelector('.card-header h5 + button').closest('.card').querySelector('.card-header h5').innerHTML =
                `<i class="fa-solid fa-qrcode me-1"></i> QR — ${sede.nombre}`;
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
}
function refreshQR() { loadQR(); }

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    loadDashboard();
    loadQR();
    setInterval(loadDashboard, 30000);
    setInterval(loadQR, 30000);
});
</script>
@endpush
