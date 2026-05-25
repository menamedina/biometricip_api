@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
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
let gMap, officeMarker, geoCircle, attendanceMarkers = [], gmapsReady = false;

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

    gMap = new google.maps.Map(document.getElementById('dashboardMap'), {
        center,
        zoom: 17,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });

    officeMarker = new google.maps.Marker({
        position: center,
        map: gMap,
        title: nombre,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#4F46E5',
            fillOpacity: 1,
            strokeColor: '#fff',
            strokeWeight: 2,
        },
    });
    officeMarker.addListener('click', () => {
        new google.maps.InfoWindow({ content: `<b>${nombre}</b><br>${direccion || ''}` }).open(gMap, officeMarker);
    });

    geoCircle = new google.maps.Circle({
        map: gMap,
        center,
        radius: radio,
        strokeColor: '#4F46E5',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#4F46E5',
        fillOpacity: 0.08,
    });
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
        attendanceMarkers.forEach(m => m.setMap(null));
        attendanceMarkers = [];
        if (data.data && gMap) {
            data.data.filter(r => r.lat && r.lng).forEach(r => {
                const isEntrada = r.tipo.includes('entrada');
                const m = new google.maps.Marker({
                    position: { lat: parseFloat(r.lat), lng: parseFloat(r.lng) },
                    map: gMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 7,
                        fillColor: isEntrada ? '#10b981' : '#ef4444',
                        fillOpacity: 0.9,
                        strokeColor: '#fff',
                        strokeWeight: 2,
                    },
                });
                m.addListener('click', () => {
                    new google.maps.InfoWindow({
                        content: `<b>${r.user?.name || 'N/A'}</b><br>${r.tipo.replace('_', ' ')}`
                    }).open(gMap, m);
                });
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
            if (gmapsReady) {
                initMap(parseFloat(sede.lat), parseFloat(sede.lng), sede.radio_mts, sede.nombre, sede.direccion);
            }
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
    if (gmapsReady) {
        initMap();
        loadDashboard();
        loadQR();
        setInterval(loadDashboard, 30000);
        setInterval(loadQR, 30000);
    }
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&loading=async&callback=googleMapsReadyDashboard"></script>
@endpush
