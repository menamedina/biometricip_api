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

@if(auth()->user()->role !== 'empleado')
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

{{-- Tarjetas de visitantes --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <h6 class="text-muted fw-semibold mb-0"><i class="fa-solid fa-id-card-clip me-1"></i> Visitantes</h6>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #23c6c8 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-info bg-opacity-10 text-info rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-building-user"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="vStatEnPlanta">--</h2>
                    <p class="text-muted mb-0 small text-truncate">En planta</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;background:rgba(111,66,193,.1);color:#6f42c1;">
                    <i class="fa-solid fa-calendar-day"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="vStatEnDia">--</h2>
                    <p class="text-muted mb-0 small text-truncate">En el día</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #e83e8c !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;background:rgba(232,62,140,.1);color:#e83e8c;">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="vStatEnMes">--</h2>
                    <p class="text-muted mb-0 small text-truncate">En el mes</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Barra de asistencia --}}
<div class="card border-0 shadow-sm mb-3" id="barraAsistencia">
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

@endif {{-- fin bloque no-empleado --}}

{{-- Resumen de horas semanales (todos los roles) --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <h6 class="text-muted fw-semibold mb-0"><i class="fa-solid fa-calendar-week me-1"></i> Resumen semana</h6>
    </div>
    <div class="col-6 col-xl-6">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #4F46E5 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="wkTotalHoras">--</h2>
                    <p class="text-muted mb-0 small text-truncate">Mis horas esta semana</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-6">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #0acf97 !important;">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="avatar-title bg-success bg-opacity-10 text-success rounded-2 fs-3" style="width:52px;height:52px;min-width:52px;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>
                <div class="overflow-hidden">
                    <h2 class="mb-0 fw-bold" id="wkPromedioDia">--</h2>
                    <p class="text-muted mb-0 small text-truncate">Promedio hrs/día</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Contenido principal --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold"><i class="fa-solid fa-list-check me-1 text-primary"></i> @if(auth()->user()->role === 'empleado') Mis registros de la semana @else Registros de hoy @endif</h5>
                <span id="recordCount" class="badge bg-secondary rounded-pill">0</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 fs-13">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">Empleado</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>QR</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTbody">
                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando...</td></tr>
                    </tbody>
                </table>
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
const csrfToken = '{{ csrf_token() }}';

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

const esEmpleado = {{ auth()->user()->role === 'empleado' ? 'true' : 'false' }};

async function loadWeeklyHours() {
    try {
        const res  = await fetch('/admin/attendance/weekly-hours', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        const set  = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        set('wkTotalHoras',  data.total_horas_semana ?? '--');
        set('wkPromedioDia', data.promedio_horas_dia  ?? '--');
        set('wkDias',         data.dias_con_asistencia ?? '--');
        set('wkEmpleados',    data.empleados_activos   ?? '--');
    } catch(e) { console.error('WeeklyHours:', e); }
}

async function loadVisitantesStats() {
    try {
        const res   = await fetch('/admin/visitantes/stats', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const stats = await res.json();
        document.getElementById('vStatEnPlanta').textContent = stats.en_planta ?? '--';
        document.getElementById('vStatEnDia').textContent    = stats.en_dia    ?? '--';
        document.getElementById('vStatEnMes').textContent    = stats.en_mes    ?? '--';
    } catch(e) { console.error('VisitantesStats:', e); }
}

async function loadDashboard() {
    if (!esEmpleado) try {
        const res   = await fetch('/admin/attendance/stats', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
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
        document.getElementById('statEnPlanta').textContent  = stats.en_planta ?? '--';
        document.getElementById('statEnDia').textContent     = stats.en_dia    ?? '--';
        document.getElementById('statEnMes').textContent     = stats.en_mes    ?? '--';

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
        let recordsUrl = '/admin/attendance/records?per_page=50&only_mine=' + (esEmpleado ? 1 : 0);
        if (esEmpleado) {
            const hoy = new Date();
            const lunes = new Date(hoy); lunes.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
            const domingo = new Date(lunes); domingo.setDate(lunes.getDate() + 6);
            const fmt = d => d.toISOString().slice(0, 10);
            recordsUrl += '&date_from=' + fmt(lunes) + '&date_to=' + fmt(domingo);
        }
        const res  = await fetch(recordsUrl, { headers: { 'X-CSRF-TOKEN': csrfToken } });
        const data = await res.json();
        const tbody = document.getElementById('attendanceTbody');

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fa-solid fa-inbox me-1"></i> Sin registros ${esEmpleado ? 'esta semana' : 'hoy'}</td></tr>`;
            document.getElementById('recordCount').textContent = '0';
        } else {
            document.getElementById('recordCount').textContent = data.data.length;
            tbody.innerHTML = data.data.map(r => {
                const name  = r.user?.name || 'N/A';
                const depto = r.user?.departamento || '';
                const dt    = new Date(r.fecha_hora);
                const fecha = dt.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
                const hora  = dt.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
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
                    <td class="text-nowrap">${fecha}</td>
                    <td class="text-nowrap">${(function() {
                        if (r.tipo !== 'entrada' || !r.horario) return hora;
                        const dt2    = new Date(r.fecha_hora);
                        const isoDay = dt2.getDay() === 0 ? 7 : dt2.getDay();
                        const dia    = (r.horario.dias || []).find(d => d.dia_semana === isoDay);
                        if (!dia?.hora_entrada) return hora;
                        const [hE, mE] = dia.hora_entrada.split(':').map(Number);
                        const limite = new Date(dt2);
                        limite.setHours(hE, mE + (dia.retardo_min || 0), 0, 0);
                        return (dt2 > limite
                            ? '<i class="fa-solid fa-circle-exclamation text-danger me-1" title="Tardanza"></i>'
                            : '<i class="fa-solid fa-circle-check text-success me-1" title="A tiempo"></i>'
                        ) + hora;
                    })()}</td>
                    <td>${tipoBadge(r.tipo)}</td>
                    <td><span class="badge bg-info-subtle text-info border border-info-subtle">${r.metodo}</span></td>
                    <td>${r.qr_validado
                        ? '<i class="fa-solid fa-circle-check text-success" title="QR Válido"></i>'
                        : '<i class="fa-solid fa-circle-xmark text-danger" title="QR Inválido"></i>'
                    }</td>
                </tr>`;
            }).join('');
        }

    } catch(e) { console.error('Attendance:', e); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadWeeklyHours();
    if (!esEmpleado) loadVisitantesStats();
    setInterval(() => {
        loadDashboard();
        loadWeeklyHours();
        if (!esEmpleado) loadVisitantesStats();
    }, 30000);
});
</script>
@endpush
