@extends('layouts.admin')
@section('title', 'Asistente IA — Configuración')

@section('content')
<div class="container-fluid">

    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="ti ti-robot me-2 text-primary"></i>Asistente IA</h4>
                <p class="text-muted mb-0">Configura el proveedor y modelo de inteligencia artificial</p>
            </div>
            <button class="btn btn-outline-secondary btn-sm" id="btnTest" onclick="testConexion()">
                <i class="ti ti-plug me-1" id="btnTestIcon"></i>
                <span id="btnTestText">Probar conexión</span>
            </button>
        </div>
    </div>

    <div id="alerta" class="alert d-none mb-3"></div>

    <div class="row g-3">

        {{-- Configuración principal --}}
        <div class="col-lg-7">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-light py-2">
                    <span class="fw-semibold text-secondary" style="font-size:.85rem;">Proveedor y Modelo</span>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Proveedor</label>
                        <div class="row g-2" id="proveedorCards">
                            @foreach([
                                'anthropic' => ['label' => 'Anthropic',  'icon' => 'ti-brain',        'color' => '#b44'],
                                'openai'    => ['label' => 'OpenAI',     'icon' => 'ti-brand-openai', 'color' => '#10a37f'],
                                'deepseek'  => ['label' => 'DeepSeek',   'icon' => 'ti-rocket',       'color' => '#4f46e5'],
                                'glm'       => ['label' => 'GLM / Zhipu','icon' => 'ti-cpu',          'color' => '#0891b2'],
                            ] as $key => $info)
                            <div class="col-6 col-md-3">
                                <div class="proveedor-card {{ ($config?->proveedor ?? 'anthropic') === $key ? 'selected' : '' }}"
                                     data-proveedor="{{ $key }}" onclick="seleccionarProveedor('{{ $key }}')">
                                    <i class="ti {{ $info['icon'] }}" style="font-size:1.5rem;color:{{ $info['color'] }};"></i>
                                    <div class="mt-1 small fw-semibold">{{ $info['label'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="proveedor" value="{{ $config?->proveedor ?? 'anthropic' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Modelo</label>
                        <select id="modelo" class="form-select">
                            @foreach($modelos as $prov => $lista)
                                @foreach($lista as $key => $label)
                                <option value="{{ $key }}" data-proveedor="{{ $prov }}"
                                    {{ ($config?->modelo ?? 'claude-haiku-4-5-20251001') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">API Key</label>
                        <div class="input-group">
                            <input type="password" id="apiKey" class="form-control"
                                placeholder="{{ $config ? 'Dejar vacío para mantener la actual' : 'Ingresa tu API key' }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKey()">
                                <i class="ti ti-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        @if($config)
                        <div class="form-text text-success"><i class="ti ti-check me-1"></i>API key configurada</div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary" id="btnGuardar" onclick="guardar()">
                            <i class="ti ti-device-floppy me-1" id="btnGuardarIcon"></i>
                            <span id="btnGuardarText">Guardar configuración</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- System Prompt --}}
        <div class="col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-light py-2">
                    <span class="fw-semibold text-secondary" style="font-size:.85rem;">Prompt del sistema</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Define el comportamiento y contexto del asistente.</p>
                    <textarea id="systemPrompt" class="form-control" rows="8"
                        placeholder="Eres el asistente de BiometricIP...">{{ $config?->system_prompt }}</textarea>
                </div>
            </div>

            <div class="card shadow-lg border-0 mt-3">
                <div class="card-header bg-light py-2">
                    <span class="fw-semibold text-secondary" style="font-size:.85rem;">Estado</span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Asistente activo</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="activo"
                            {{ ($config?->activo ?? true) ? 'checked' : '' }} style="width:2.5rem;height:1.25rem;">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
.proveedor-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 8px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.proveedor-card:hover { border-color: #1ab394; background: #f0faf8; }
.proveedor-card.selected { border-color: #1ab394; background: #e0f5f1; }
</style>
@endpush

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const modelosPorProveedor = @json($modelos);

function seleccionarProveedor(prov) {
    document.querySelectorAll('.proveedor-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.proveedor-card[data-proveedor="${prov}"]`).classList.add('selected');
    document.getElementById('proveedor').value = prov;
    filtrarModelos(prov);
}

function filtrarModelos(prov) {
    const sel = document.getElementById('modelo');
    const opts = sel.querySelectorAll('option');
    let firstVisible = null;
    opts.forEach(o => {
        const visible = o.dataset.proveedor === prov;
        o.hidden = !visible;
        if (visible && !firstVisible) firstVisible = o;
    });
    if (firstVisible) sel.value = firstVisible.value;
}

function toggleApiKey() {
    const inp = document.getElementById('apiKey');
    const ico = document.getElementById('eyeIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'ti ti-eye' : 'ti ti-eye-off';
}

async function guardar() {
    const btn  = document.getElementById('btnGuardar');
    const icon = document.getElementById('btnGuardarIcon');
    const text = document.getElementById('btnGuardarText');

    btn.disabled = true;
    icon.className = 'spinner-border spinner-border-sm me-1';
    text.textContent = 'Guardando...';

    try {
        const payload = {
            proveedor:     document.getElementById('proveedor').value,
            modelo:        document.getElementById('modelo').value,
            api_key:       document.getElementById('apiKey').value || null,
            system_prompt: document.getElementById('systemPrompt').value || null,
            activo:        document.getElementById('activo').checked ? 1 : 0,
        };

        const res = await fetch('/admin/ai/config/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        mostrarAlerta(res.ok ? 'success' : 'danger', data.message);
    } catch (e) {
        mostrarAlerta('danger', 'Error de conexión: ' + e.message);
    } finally {
        btn.disabled = false;
        icon.className = 'ti ti-device-floppy me-1';
        text.textContent = 'Guardar configuración';
    }
}

async function testConexion() {
    const btn  = document.getElementById('btnTest');
    const icon = document.getElementById('btnTestIcon');
    const text = document.getElementById('btnTestText');

    btn.disabled = true;
    icon.className = 'spinner-border spinner-border-sm me-1';
    text.textContent = 'Probando...';

    try {
        const prov = document.getElementById('proveedor').value;
        const res = await fetch(`/admin/ai/config/test?proveedor=${prov}`);
        const data = await res.json();
        mostrarAlerta(res.ok ? 'success' : 'danger', data.message);
    } catch (e) {
        mostrarAlerta('danger', 'Error de conexión: ' + e.message);
    } finally {
        btn.disabled = false;
        icon.className = 'ti ti-plug me-1';
        text.textContent = 'Probar conexión';
    }
}

function mostrarAlerta(tipo, msg) {
    const el = document.getElementById('alerta');
    el.className = `alert alert-${tipo} mb-3`;
    el.textContent = msg;
}

// Inicializar filtro de modelos según proveedor actual
document.addEventListener('DOMContentLoaded', () => {
    filtrarModelos(document.getElementById('proveedor').value);
});
</script>
@endpush
