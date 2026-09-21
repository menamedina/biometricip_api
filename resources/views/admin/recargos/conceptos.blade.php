@extends('layouts.admin')
@section('title', 'Conceptos de Recargo')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-percentage me-2 text-primary"></i>Conceptos de Recargo</h4>
                    <p class="text-muted mb-0">Configuracion de porcentajes segun normativa colombiana (CST)</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="guardarConceptos()" id="btnGuardar">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    {{-- Configuración de jornada nocturna --}}
    <div class="card shadow-lg border-0 mb-3">
        <div class="card-body py-3 px-4">
            <h6 class="mb-3"><i class="ti ti-moon me-1"></i>Jornada Nocturna</h6>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Hora inicio nocturna</label>
                    <select id="horaInicioNocturna" class="form-select form-select-sm">
                        @for($h = 0; $h < 24; $h++)
                            <option value="{{ $h }}" {{ $configRecargos['hora_inicio_nocturna'] == $h ? 'selected' : '' }}>
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 ({{ $h >= 12 ? ($h == 12 ? '12' : $h - 12) . ':00 PM' : ($h == 0 ? '12' : $h) . ':00 AM' }})
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Hora fin nocturna</label>
                    <select id="horaFinNocturna" class="form-select form-select-sm">
                        @for($h = 0; $h < 24; $h++)
                            <option value="{{ $h }}" {{ $configRecargos['hora_fin_nocturna'] == $h ? 'selected' : '' }}>
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 ({{ $h >= 12 ? ($h == 12 ? '12' : $h - 12) . ':00 PM' : ($h == 0 ? '12' : $h) . ':00 AM' }})
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">
                        <i class="ti ti-info-circle me-1"></i>CST Colombia: 7:00 PM (19) a 6:00 AM (6)
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0" id="conceptosTable" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px;">Codigo</th>
                        <th>Nombre</th>
                        <th style="width:100px;" class="text-center">Extra</th>
                        <th style="width:100px;" class="text-center">Nocturno</th>
                        <th style="width:100px;" class="text-center">Festivo</th>
                        <th style="width:120px;" class="text-center">Porcentaje (%)</th>
                        <th style="width:100px;" class="text-center">Activo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conceptos as $c)
                    <tr data-id="{{ $c->id }}">
                        <td><code>{{ $c->codigo }}</code></td>
                        <td>{{ $c->nombre }}</td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input input-es-extra" type="checkbox"
                                       {{ $c->es_extra ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input input-es-nocturno" type="checkbox"
                                       {{ $c->es_nocturno ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input input-es-festivo" type="checkbox"
                                       {{ $c->es_festivo ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center input-porcentaje"
                                   value="{{ $c->porcentaje }}" min="0" max="999.99" step="0.01"
                                   style="width:90px;margin:0 auto;">
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input input-activo" type="checkbox"
                                       {{ $c->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3 border-0 shadow-sm">
        <div class="card-body py-2">
            <small class="text-muted">
                <i class="ti ti-info-circle me-1"></i>
                Los porcentajes se aplican sobre el valor de la hora ordinaria. Estos valores son configurables por empresa segun acuerdos internos, pero los valores por defecto corresponden al Codigo Sustantivo del Trabajo (CST) de Colombia.
            </small>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

async function guardarConceptos() {
    const btn  = document.getElementById('btnGuardar');
    const rows = document.querySelectorAll('#conceptosTable tbody tr');
    const conceptos = [];

    rows.forEach(row => {
        conceptos.push({
            id:          parseInt(row.dataset.id),
            es_extra:    row.querySelector('.input-es-extra').checked,
            es_nocturno: row.querySelector('.input-es-nocturno').checked,
            es_festivo:  row.querySelector('.input-es-festivo').checked,
            porcentaje:  parseFloat(row.querySelector('.input-porcentaje').value),
            is_active:   row.querySelector('.input-activo').checked,
        });
    });

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        const config = {
            hora_inicio_nocturna: document.getElementById('horaInicioNocturna').value,
            hora_fin_nocturna:    document.getElementById('horaFinNocturna').value,
        };

        const res = await fetch('{{ route("admin.recargos.conceptos.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ conceptos, config }),
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Guardado';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');

        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.disabled = false;
        }, 2000);

    } catch(e) {
        alert('Error al guardar: ' + e.message);
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios';
        btn.disabled = false;
    }
}
</script>
@endpush
