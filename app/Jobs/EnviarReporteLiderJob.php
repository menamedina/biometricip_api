<?php

namespace App\Jobs;

use App\Helpers\TenantHelper;
use App\Mail\ReporteLiderMail;
use App\Models\AttendanceRecord;
use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarReporteLiderJob
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $liderId,
        public readonly int $empresaId,
    ) {}

    public function handle(): void
    {
        Log::info('EnviarReporteLiderJob: iniciando', [
            'lider_id'   => $this->liderId,
            'empresa_id' => $this->empresaId,
        ]);

        $lider = User::find($this->liderId);

        if (!$lider || empty($lider->email)) {
            Log::warning('EnviarReporteLiderJob: líder no encontrado o sin email', [
                'lider_id'   => $this->liderId,
                'empresa_id' => $this->empresaId,
            ]);
            return;
        }

        Log::info('EnviarReporteLiderJob: líder encontrado', [
            'lider_id' => $lider->id,
            'nombre'   => $lider->name,
            'email'    => $lider->email,
        ]);

        $empresa = Empresa::find($this->empresaId);

        if (!$empresa) {
            Log::warning('EnviarReporteLiderJob: empresa no encontrada', ['empresa_id' => $this->empresaId]);
            return;
        }

        Log::info('EnviarReporteLiderJob: empresa encontrada', ['empresa' => $empresa->nombre]);

        // Cambiar conexión al tenant de la empresa
        Log::info('EnviarReporteLiderJob: cambiando a tenant', ['empresa_id' => $this->empresaId]);
        TenantHelper::switchTenant($this->empresaId);

        $fecha = now()->toDateString();

        Log::info('EnviarReporteLiderJob: construyendo reporte', ['fecha' => $fecha]);

        try {
            $datosReporte = $this->construirReporte($lider, $fecha);
        } finally {
            TenantHelper::switchToCentral();
        }

        Log::info('EnviarReporteLiderJob: reporte construido', [
            'empleados'     => $datosReporte['empleados']->count(),
            'resumen'       => $datosReporte['resumen'],
        ]);

        if ($datosReporte['empleados']->isEmpty()) {
            Log::info('EnviarReporteLiderJob: líder sin empleados activos, omitido', [
                'lider_id'   => $this->liderId,
                'empresa_id' => $this->empresaId,
            ]);
            return;
        }

        Log::info('EnviarReporteLiderJob: enviando email', ['to' => $lider->email]);

        Mail::to($lider->email, $lider->name)
            ->send(new ReporteLiderMail(
                lider:     $lider,
                empresa:   $empresa,
                empleados: $datosReporte['empleados'],
                resumen:   $datosReporte['resumen'],
                fecha:     $fecha,
            ));

        Log::info('EnviarReporteLiderJob: email enviado correctamente', [
            'lider_id'   => $this->liderId,
            'empresa_id' => $this->empresaId,
            'email'      => $lider->email,
            'fecha'      => $fecha,
            'empleados'  => $datosReporte['empleados']->count(),
        ]);
    }

    private function construirReporte(User $lider, string $fecha): array
    {
        // Empleados activos bajo este líder en esta empresa (conexión central)
        $empleados = User::where('lider_id', $this->liderId)
            ->where('empresa_id', $this->empresaId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($empleados->isEmpty()) {
            return ['empleados' => collect(), 'resumen' => []];
        }

        $empleadoIds = $empleados->pluck('id');

        // Registros de asistencia del día (conexión tenant)
        $registros = AttendanceRecord::whereIn('user_id', $empleadoIds)
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora')
            ->get()
            ->groupBy('user_id');

        // Permisos del día (conexión tenant)
        $permisos = Permiso::whereIn('user_id', $empleadoIds)
            ->where('fecha', $fecha)
            ->with('tipoPermiso')
            ->get()
            ->keyBy('user_id');

        // Departamentos y cargos (conexión tenant)
        $departamentos = DB::connection('tenant')
            ->table('tbl_departamentos')
            ->pluck('nombre', 'id');

        $cargos = DB::connection('tenant')
            ->table('tbl_cargos')
            ->pluck('nombre', 'id');

        // Construir detalle por empleado
        $detalleEmpleados = $empleados->map(function (User $emp) use ($registros, $permisos, $departamentos, $cargos) {
            $regsEmp = $registros->get($emp->id, collect());
            $permiso = $permisos->get($emp->id);

            $entrada = $regsEmp->where('tipo', 'entrada')->first();
            $salida  = $regsEmp->where('tipo', 'salida')->last();

            if ($permiso) {
                $estado = 'Con Permiso';
            } elseif ($entrada && $salida) {
                $estado = 'Entrada y Salida';
            } elseif ($entrada) {
                $estado = 'Entrada';
            } else {
                $estado = 'Sin Marcación';
            }

            return [
                'nombre'          => $emp->name,
                'cedula'          => $emp->cedula ?? '—',
                'codigo_empleado' => $emp->codigo_empleado ?? '—',
                'departamento'    => $departamentos[$emp->departamento_id] ?? '—',
                'cargo'           => $cargos[$emp->cargo_id] ?? '—',
                'entrada'         => $entrada ? $entrada->fecha_hora->format('H:i') : null,
                'salida'          => $salida  ? $salida->fecha_hora->format('H:i')  : null,
                'permiso'         => $permiso !== null,
                'permiso_tipo'    => $permiso?->tipoPermiso?->nombre ?? ($permiso ? 'Permiso' : null),
                'estado'          => $estado,
            ];
        });

        // Resumen
        $resumen = [
            'total'          => $detalleEmpleados->count(),
            'con_entrada'    => $detalleEmpleados->whereIn('estado', ['Entrada', 'Entrada y Salida'])->count(),
            'sin_marcacion'  => $detalleEmpleados->where('estado', 'Sin Marcación')->count(),
            'con_permiso'    => $detalleEmpleados->where('estado', 'Con Permiso')->count(),
        ];

        return [
            'empleados' => $detalleEmpleados,
            'resumen'   => $resumen,
        ];
    }
}
