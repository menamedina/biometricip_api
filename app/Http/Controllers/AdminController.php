<?php

namespace App\Http\Controllers;

use App\Exports\CargosTemplateExport;
use App\Exports\DepartamentosTemplateExport;
use App\Exports\EmpleadosTemplateExport;
use App\Helpers\TenantHelper;
use App\Imports\CargosImport;
use App\Imports\DepartamentosImport;
use App\Imports\EmpleadosImport;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empleador;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\TenantTabla;
use App\Models\User;
use App\Models\Visitante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function sedesIndex(): View
    {
        $empresas = auth()->user()->admin_tenant
            ? Empresa::orderBy('nombre')->get(['id', 'nombre'])
            : collect();

        return view('admin.sedes.index', compact('empresas'));
    }

    public function empleadosIndex(): View
    {
        $isAdminTenant = auth()->user()->admin_tenant;

        $deptos      = $isAdminTenant ? collect() : Departamento::orderBy('nombre')->get();
        $cargos      = $isAdminTenant ? collect() : Cargo::orderBy('nombre')->get();
        $horarios    = $isAdminTenant ? collect() : Horario::orderBy('nombre')->get();
        $sedes       = $isAdminTenant ? collect() : Sede::orderBy('nombre')->get();
        $empleadores = $isAdminTenant ? collect() : Empleador::where('is_active', true)->orderBy('nombre')->get();
        $empresas    = $isAdminTenant ? Empresa::orderBy('nombre')->get() : collect();

        return view('admin.empleados.index', compact('deptos', 'cargos', 'horarios', 'sedes', 'empleadores', 'empresas'));
    }

    public function empleadosTemplate(Request $request)
    {
        $authUser  = auth()->user();
        $empresaId = $authUser->admin_tenant
            ? (int) $request->input('empresa_id', 0)
            : (int) $authUser->empresa_id;

        if ($authUser->admin_tenant) {
            if (!$empresaId || !Empresa::where('id', $empresaId)->exists()) {
                abort(422, 'Empresa no válida.');
            }
            TenantHelper::switchTenant($empresaId);
        }

        return Excel::download(new EmpleadosTemplateExport($empresaId), 'plantilla_empleados.xlsx');
    }

    public function empleadosImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $authUser = auth()->user();

        if ($authUser->admin_tenant) {
            $empresaId = (int) $request->input('empresa_id', 0);
            if (!$empresaId || !Empresa::where('id', $empresaId)->exists()) {
                return response()->json(['message' => 'Empresa no válida.'], 422);
            }
            TenantHelper::switchTenant($empresaId);
        } else {
            $empresaId = (int) $authUser->empresa_id;
        }

        $import = new EmpleadosImport($empresaId);
        Excel::import($import, $request->file('file'));

        return response()->json([
            'created'      => $import->created,
            'updated'      => $import->updated,
            'skipped'      => count($import->skipped),
            'createdList'  => $import->createdList,
            'updatedList'  => $import->updatedList,
            'skippedList'  => $import->skipped,
        ]);
    }

    public function empleadosCatalogos(Request $request): JsonResponse
    {
        $empresaId = $request->integer('empresa_id');
        if ($empresaId && auth()->user()->admin_tenant) {
            TenantHelper::switchTenant($empresaId);
        }
        return response()->json([
            'departamentos' => Departamento::where('is_active', true)->orderBy('nombre')->get(),
            'cargos'        => Cargo::where('is_active', true)->orderBy('nombre')->get(),
            'horarios'      => Horario::where('is_active', true)->orderBy('nombre')->get(),
            'empleadores'   => Empleador::where('is_active', true)->orderBy('nombre')->get(),
        ]);
    }

    public function empleadosLideres(Request $request): JsonResponse
    {
        $authUser  = auth()->user();
        $empresaId = $request->integer('empresa_id') ?: $authUser->empresa_id;

        $lideres = User::where('empresa_id', $empresaId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_empleado']);

        return response()->json(['data' => $lideres]);
    }

    public function empleadosSedes(Request $request): JsonResponse
    {
        $empresaId = $request->integer('empresa_id');
        if ($empresaId && auth()->user()->admin_tenant) {
            TenantHelper::switchTenant($empresaId);
        }
        return response()->json([
            'data' => Sede::where('is_active', true)->orderBy('nombre')->get(),
        ]);
    }

    public function attendanceIndex(): View
    {
        return view('admin.attendance.index');
    }

    public function resumenIndex(): View
    {
        $sedes = \App\Models\Sede::orderBy('nombre')->get(['id', 'nombre']);
        return view('admin.resumen.index', compact('sedes'));
    }

    public function resumenRecords(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date',
        ]);

        $query = \App\Models\AttendanceRecord::query()
            ->select('id', 'user_id', 'sede_id', 'tipo', 'fecha_hora', 'metodo', 'observacion')
            ->with([
                'user:id,name,codigo_empleado,departamento_id',
                'sede:id,nombre',
            ])
            ->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to   . ' 23:59:59',
            ])
            ->orderBy('fecha_hora', 'asc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->get();

        return response()->json(['data' => $records]);
    }

    public function departamentosIndex(Request $request): View
    {
        $searchDepto = $request->input('search_depto', '');
        $searchCargo = $request->input('search_cargo', '');

        $deptos = Departamento::orderBy('nombre')
            ->when($searchDepto, fn($q) => $q->where(function ($q) use ($searchDepto) {
                $q->where('nombre', 'like', "%{$searchDepto}%")
                  ->orWhere('descripcion', 'like', "%{$searchDepto}%");
            }))
            ->get();

        $cargos = Cargo::orderBy('nombre')
            ->when($searchCargo, fn($q) => $q->where(function ($q) use ($searchCargo) {
                $q->where('nombre', 'like', "%{$searchCargo}%")
                  ->orWhere('descripcion', 'like', "%{$searchCargo}%");
            }))
            ->get();

        $allDeptoNames = Departamento::orderBy('nombre')->pluck('nombre');
        $allCargoNames = Cargo::orderBy('nombre')->pluck('nombre');

        return view('admin.departamentos.index', compact(
            'deptos', 'cargos', 'searchDepto', 'searchCargo', 'allDeptoNames', 'allCargoNames'
        ));
    }

    public function departamentosStore(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tenant.tbl_departamentos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Departamento::create($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Departamento creado.');
    }

    public function departamentosUpdate(Request $request, int $id)
    {
        $depto = Departamento::findOrFail($id);
        $data  = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tenant.tbl_departamentos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $depto->update($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Departamento actualizado.');
    }

    public function departamentosDestroy(Request $request, int $id)
    {
        $depto = Departamento::findOrFail($id);
        $back  = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        if (User::where('departamento_id', $id)->exists()) {
            $depto->update(['is_active' => false]);
            return redirect()->route('admin.departamentos.index', $back)
                ->with('success', 'El departamento tiene empleados asignados y fue deshabilitado en lugar de eliminado.');
        }

        $depto->delete();
        return redirect()->route('admin.departamentos.index', $back)
            ->with('success', 'Departamento eliminado.');
    }

    // ── Empleadores ──────────────────────────────────────────────────────────

    public function empleadoresIndex(): View
    {
        $empleadores = Empleador::orderBy('nombre')->get();
        return view('admin.empleadores.index', compact('empleadores'));
    }

    public function empleadoresStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:150|unique:tenant.tbl_empleador,nombre',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $empleador = Empleador::create($data);
        return response()->json(['success' => true, 'empleador' => $empleador]);
    }

    public function empleadoresUpdate(Request $request, int $id): JsonResponse
    {
        $empleador = Empleador::findOrFail($id);
        $data = $request->validate([
            'nombre'      => 'required|string|max:150|unique:tenant.tbl_empleador,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $empleador->update($data);
        return response()->json(['success' => true, 'empleador' => $empleador]);
    }

    public function empleadoresDestroy(int $id): JsonResponse
    {
        Empleador::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['success' => true, 'message' => 'Empleador desactivado.']);
    }

    public function departamentosTemplate()
    {
        return Excel::download(new DepartamentosTemplateExport(), 'plantilla_departamentos.xlsx');
    }

    public function departamentosImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        $back = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        $import = new DepartamentosImport();
        Excel::import($import, $request->file('file'));

        $msg = "Importación completada: {$import->imported} registros importados.";
        if ($import->skipped) {
            $msg .= ' Omitidos por nombre duplicado: ' . implode(', ', $import->skipped) . '.';
        }

        return redirect()->route('admin.departamentos.index', $back)->with('success', $msg);
    }

    public function cargosStore(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Cargo::create($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Cargo creado.');
    }

    public function cargosUpdate(Request $request, int $id)
    {
        $cargo = Cargo::findOrFail($id);
        $data  = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $cargo->update($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Cargo actualizado.');
    }

    public function cargosDestroy(Request $request, int $id)
    {
        $cargo = Cargo::findOrFail($id);
        $back  = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        if (User::where('cargo_id', $id)->exists()) {
            $cargo->update(['is_active' => false]);
            return redirect()->route('admin.departamentos.index', $back)
                ->with('success', 'El cargo tiene empleados asignados y fue deshabilitado en lugar de eliminado.');
        }

        $cargo->delete();
        return redirect()->route('admin.departamentos.index', $back)
            ->with('success', 'Cargo eliminado.');
    }

    public function cargosTemplate()
    {
        return Excel::download(new CargosTemplateExport(), 'plantilla_cargos.xlsx');
    }

    public function cargosImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        $back = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        $import = new CargosImport();
        Excel::import($import, $request->file('file'));

        $msg = "Importación completada: {$import->imported} registros importados.";
        if ($import->skipped) {
            $msg .= ' Omitidos por nombre duplicado: ' . implode(', ', $import->skipped) . '.';
        }

        return redirect()->route('admin.departamentos.index', $back)->with('success', $msg);
    }

    public function horariosIndex(): View
    {
        return view('admin.horarios.index');
    }

    public function permisosIndex(): View
    {
        return view('admin.permisos.index');
    }

    public function festivosIndex(): View
    {
        return view('admin.festivos.index');
    }

    public function empresasIndex(): View
    {
        return view('admin.empresas.index');
    }

    public function visitantesIndex(): View
    {
        $sedes = Sede::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre']);
        return view('admin.visitantes.index', compact('sedes'));
    }

    public function visitantesList(Request $request)
    {
        $query = Visitante::with(['sede', 'imagenes' => fn ($q) => $q->where('tipo', 'entrada')])
            ->orderBy('hora_entrada', 'desc');

        if ($request->filled('sede_id'))  $query->where('sede_id', $request->sede_id);
        if ($request->filled('desde'))    $query->whereDate('hora_entrada', '>=', $request->desde);
        if ($request->filled('hasta'))    $query->whereDate('hora_entrada', '<=', $request->hasta);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('cedula', 'like', "%{$s}%")->orWhere('nombre', 'like', "%{$s}%"));
        }
        if ($request->filled('estado')) {
            $request->estado === 'en_sede'
                ? $query->whereNull('hora_salida')
                : $query->whereNotNull('hora_salida');
        }

        // Exportar Excel
        if ($request->input('export') === 'xlsx') {
            $rows = $query->get()->map(function ($v) {
                $tz        = 'America/Bogota';
                $entrada   = \Carbon\Carbon::parse($v->getRawOriginal('hora_entrada'), $tz);
                $salidaRaw = $v->getRawOriginal('hora_salida');
                $salida    = $salidaRaw ? \Carbon\Carbon::parse($salidaRaw, $tz) : null;
                $fin       = $salida ?? \Carbon\Carbon::now($tz);
                $mins      = max(0, (int) $entrada->diffInMinutes($fin));
                $h = intdiv($mins, 60); $m = $mins % 60;
                return [
                    'Nombre'        => $v->nombre,
                    'Cédula'        => $v->cedula,
                    'Visita a'      => $v->persona_visita,
                    'Sede'          => $v->sede?->nombre,
                    'Empresa'       => $v->empresa,
                    'Teléfono'      => $v->telefono,
                    'EPS'           => $v->eps,
                    'ARL'           => $v->arl,
                    'Placa'         => $v->placa,
                    'Entrada'       => $entrada->format('d/m/Y H:i'),
                    'Salida'        => $salida?->format('d/m/Y H:i') ?? 'En sede',
                    'Tiempo en sede'=> $h > 0 ? "{$h}h {$m}m" : "{$m}m",
                ];
            });

            return Excel::download(
                new \App\Exports\SimpleCollectionExport($rows->toArray()),
                'visitantes_' . now()->format('Ymd_Hi') . '.xlsx'
            );
        }

        $visitantes = $query->paginate(min((int) $request->input('per_page', 50), 5000));
        $visitantes->getCollection()->transform(function ($v) {
            $v->imagen_entrada = $v->imagenes->isNotEmpty();
            // DB guarda en hora Colombia (APP_TIMEZONE); comparar en la misma zona
            $entrada   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $v->getRawOriginal('hora_entrada'));
            $salidaRaw = $v->getRawOriginal('hora_salida');
            $fin       = $salidaRaw
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $salidaRaw)
                : \Carbon\Carbon::now();
            $v->minutos_en_sede = max(0, (int) $entrada->diffInMinutes($fin));

            // Última inducción de esta cédula en esta sede
            $ultimaInduccion = Visitante::where('cedula', $v->cedula)
                ->where('sede_id', $v->sede_id)
                ->whereNotNull('induccion_fecha')
                ->orderBy('induccion_fecha', 'desc')
                ->value('induccion_fecha');

            if ($ultimaInduccion) {
                $carbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ultimaInduccion);
                $v->ultima_induccion_fecha  = $carbon->format('d/m/Y H:i');
                $v->ultima_induccion_hace   = $carbon->diffForHumans(now(), ['parts' => 2, 'join' => true]);
                $v->ultima_induccion_meses  = (int) $carbon->diffInMonths(now());
                $v->ultima_induccion_vencida = $carbon->lt(now()->subMonths(12));
            } else {
                $v->ultima_induccion_fecha   = null;
                $v->ultima_induccion_hace    = null;
                $v->ultima_induccion_meses   = null;
                $v->ultima_induccion_vencida = null;
            }

            unset($v->imagenes);
            return $v;
        });

        return response()->json($visitantes);
    }

    public function visitantesStats(Request $request): JsonResponse
    {
        $sedeId = $request->filled('sede_id') ? (int) $request->sede_id : null;

        $base = fn () => Visitante::when($sedeId, fn ($q) => $q->where('sede_id', $sedeId));

        $enPlanta = $base()->whereDate('hora_entrada', today())->whereNull('hora_salida')->count();

        $enDia = $base()->whereDate('hora_entrada', today())->count();

        $enMes = $base()
            ->whereYear('hora_entrada', now()->year)
            ->whereMonth('hora_entrada', now()->month)
            ->count();

        return response()->json([
            'en_planta' => $enPlanta,
            'en_dia'    => $enDia,
            'en_mes'    => $enMes,
        ]);
    }

    private function induccionRequerida(string $cedula, int $sedeId): bool
    {
        $ultimaInduccion = Visitante::where('cedula', $cedula)
            ->where('sede_id', $sedeId)
            ->whereNotNull('induccion_fecha')
            ->orderBy('induccion_fecha', 'desc')
            ->value('induccion_fecha');

        if (!$ultimaInduccion) return true;

        return \Carbon\Carbon::parse($ultimaInduccion)->lt(now()->subMonths(12));
    }

    public function visitantesInduccion(int $id): JsonResponse
    {
        $visitante = Visitante::findOrFail($id);
        $fecha = request()->input('fecha');
        $visitante->update([
            'induccion_fecha' => $fecha ? \Carbon\Carbon::parse($fecha) : now(),
        ]);
        return response()->json(['success' => true]);
    }

    public function visitantesBuscarCedula(Request $request): JsonResponse
    {
        $cedula = $request->input('cedula');
        $visitante = Visitante::where('cedula', $cedula)
            ->orderBy('hora_entrada', 'desc')
            ->first(['nombre', 'telefono', 'eps', 'arl', 'empresa', 'placa', 'persona_visita']);

        if (!$visitante) {
            return response()->json(['found' => false]);
        }

        return response()->json(['found' => true, 'data' => $visitante]);
    }

    public function visitantesObservacion(int $id): JsonResponse
    {
        $visitante = Visitante::findOrFail($id);
        $visitante->update(['observacion' => request()->input('observacion') ?: null]);
        return response()->json(['success' => true]);
    }

    public function visitantesForzarSalida(int $id): JsonResponse
    {
        $visitante = Visitante::findOrFail($id);
        if ($visitante->hora_salida) {
            return response()->json(['message' => 'Ya tiene salida registrada.'], 422);
        }
        $visitante->update(['hora_salida' => now()]);
        return response()->json(['success' => true]);
    }

    public function visitantesUpdate(Request $request, int $id): JsonResponse
    {
        $visitante = Visitante::findOrFail($id);

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'cedula'         => 'required|string|max:20',
            'telefono'       => 'nullable|string|max:20',
            'empresa'        => 'nullable|string|max:255',
            'eps'            => 'nullable|string|max:100',
            'arl'            => 'nullable|string|max:100',
            'placa'          => 'nullable|string|max:20',
            'persona_visita' => 'required|string|max:255',
            'hora_entrada'   => 'required|date',
            'hora_salida'    => 'nullable|date',
        ]);

        \DB::connection('tenant')->statement('SET @audit_user_id = ?', [auth()->id()]);

        $visitante->update([
            'nombre'         => strtoupper($request->nombre),
            'cedula'         => $request->cedula,
            'telefono'       => $request->telefono,
            'empresa'        => $request->empresa ? strtoupper($request->empresa) : null,
            'eps'            => $request->eps     ? strtoupper($request->eps)     : null,
            'arl'            => $request->arl     ? strtoupper($request->arl)     : null,
            'placa'          => $request->placa   ? strtoupper($request->placa)   : null,
            'persona_visita' => strtoupper($request->persona_visita),
            'hora_entrada'   => $request->hora_entrada,
            'hora_salida'    => $request->hora_salida ?: null,
        ]);

        return response()->json(['success' => true]);
    }

    public function visitantesLog(int $id): JsonResponse
    {
        $logs = \DB::connection('tenant')->table('tbl_visitantes_log')
            ->where('visitante_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($row) {
                $user = $row->user_id
                    ? \App\Models\User::find($row->user_id)?->name ?? 'Usuario #' . $row->user_id
                    : 'Sistema';
                return [
                    'evento'     => $row->evento,
                    'anterior'   => $row->anterior ? json_decode($row->anterior, true) : null,
                    'nuevo'      => $row->nuevo    ? json_decode($row->nuevo,    true) : null,
                    'usuario'    => $user,
                    'created_at' => $row->created_at,
                ];
            });

        return response()->json($logs);
    }

    public function visitantesFoto(int $id): JsonResponse
    {
        $imgs = \App\Models\VisitanteImagen::where('visitante_id', $id)->get()->keyBy('tipo');
        return response()->json([
            'entrada' => $imgs->get('entrada')?->foto_base64,
            'salida'  => $imgs->get('salida')?->foto_base64,
        ]);
    }

    public function visitantesStore(Request $request): JsonResponse
    {
        $request->validate([
            'sede_id'        => 'required|integer',
            'cedula'         => 'required|string|max:20',
            'nombre'         => 'required|string|max:255',
            'telefono'       => 'required|string|max:20',
            'eps'            => 'required|string|max:100',
            'arl'            => 'required|string|max:100',
            'empresa'        => 'required|string|max:255',
            'placa'          => 'nullable|string|max:20',
            'persona_visita' => 'required|string|max:255',
            'hora_entrada'   => 'nullable|date',
        ]);

        $induccionRequerida = $this->induccionRequerida($request->cedula, (int) $request->sede_id);

        $visitante = Visitante::create([
            'sede_id'             => $request->sede_id,
            'user_id'             => auth()->id(),
            'cedula'              => $request->cedula,
            'nombre'              => $request->nombre,
            'telefono'            => $request->telefono,
            'eps'                 => $request->eps,
            'arl'                 => $request->arl,
            'empresa'             => $request->empresa,
            'placa'               => $request->placa ? strtoupper($request->placa) : null,
            'persona_visita'      => $request->persona_visita,
            'hora_entrada'        => $request->hora_entrada ?? now(),
            'hora_salida'         => null,
            'induccion_requerida' => $induccionRequerida,
        ]);

        return response()->json(['success' => true, 'data' => $visitante]);
    }

    public function dispositivosIndex(): View
    {
        return view('admin.dispositivos.index');
    }

    public function tenantsIndex(): View
    {
        return view('admin.tenants.index');
    }

    public function tenantsCreate(): View
    {
        return view('admin.tenants.create');
    }

    public function tenantsTablas(): View
    {
        return view('admin.tenants.tablas');
    }

    public function tenantsDescargarSql(): Response
    {
        abort_unless(auth()->user()->admin_tenant ?? false, 403);

        $tablasEstructura = TenantTabla::getTablasEstructura();
        $tablasDatos      = TenantTabla::getTablasDatos();

        // Conectar a la primera BD tenant disponible para leer las estructuras
        // (las tablas tenant NO existen en la BD central)
        $primerTenant = DB::table('tenants')
            ->whereNotNull('db_name')
            ->orderBy('id')
            ->first();

        $usaTenantConn = false;
        if ($primerTenant) {
            try {
                TenantHelper::switchTenant((int) $primerTenant->empresa_id);
                $usaTenantConn = true;
            } catch (\Exception $e) {
                // Si no puede conectar continúa, cada tabla mostrará error
            }
        }

        $conn = $usaTenantConn ? DB::connection('tenant') : DB::connection('mysql');

        $sql   = [];
        $sql[] = "-- ================================================";
        $sql[] = "-- ESQUEMA PARA NUEVA BD TENANT — BiometricIP";
        $sql[] = "-- Generado: " . now()->format('Y-m-d H:i:s');
        if ($usaTenantConn) {
            $sql[] = "-- Estructura obtenida de: " . $primerTenant->db_name;
        }
        $sql[] = "-- Tablas de estructura: " . count($tablasEstructura);
        $sql[] = "-- ================================================";
        $sql[] = "";
        $sql[] = "SET FOREIGN_KEY_CHECKS=0;";
        $sql[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
        $sql[] = "SET time_zone = \"+00:00\";";
        $sql[] = "";
        $sql[] = "-- ================================================";
        $sql[] = "-- ESTRUCTURA DE TABLAS";
        $sql[] = "-- ================================================";
        $sql[] = "";

        foreach ($tablasEstructura as $tableName) {
            try {
                $rows = $conn->select("SHOW CREATE TABLE `{$tableName}`");
                if (empty($rows)) continue;

                $create = $rows[0]->{'Create Table'};
                $create = $this->removeForeignKeys($create);

                $sql[] = "-- Tabla: {$tableName}";
                $sql[] = "DROP TABLE IF EXISTS `{$tableName}`;";
                $sql[] = $create . ";";
                $sql[] = "";
            } catch (\Illuminate\Database\QueryException $e) {
                // 1146 = tabla no existe en esta BD — se omite silenciosamente
                if ($e->getCode() === '42S02') continue;
                $sql[] = "-- ERROR en {$tableName}: " . $e->getMessage();
                $sql[] = "";
            }
        }

        // Datos — siempre de BD central (solo aplica para tablas centrales con copiar_datos=true)
        if (!empty($tablasDatos)) {
            $sql[] = "-- ================================================";
            $sql[] = "-- DATOS (de BD central)";
            $sql[] = "-- ================================================";
            $sql[] = "";

            foreach ($tablasDatos as $tableName) {
                try {
                    $rows = DB::connection('mysql')->table($tableName)->get();
                    if ($rows->isEmpty()) continue;

                    $sql[] = "-- Datos: {$tableName}";
                    foreach ($rows as $row) {
                        $values = array_map(function ($v) {
                            if (is_null($v)) return 'NULL';
                            if (is_numeric($v)) return $v;
                            return "'" . addslashes((string) $v) . "'";
                        }, (array) $row);
                        $sql[] = "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");";
                    }
                    $sql[] = "";
                } catch (\Exception $e) {
                    $sql[] = "-- ERROR datos {$tableName}: " . $e->getMessage();
                    $sql[] = "";
                }
            }
        }

        $sql[] = "SET FOREIGN_KEY_CHECKS=1;";
        $sql[] = "";
        $sql[] = "-- FIN DEL SCRIPT";

        if ($usaTenantConn) {
            TenantHelper::switchToCentral();
        }

        $filename = 'biometricip_tenant_' . now()->format('Y-m-d_His') . '.sql';

        return response(implode("\n", $sql))
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Elimina las líneas CONSTRAINT/FOREIGN KEY de un CREATE TABLE
     * sin usar regex que pueda romper ENUMs u otros tipos con paréntesis.
     */
    private function removeForeignKeys(string $createSql): string
    {
        $lines  = explode("\n", $createSql);
        $clean  = [];
        $prevWasComma = false;

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            // Saltar líneas de CONSTRAINT ... FOREIGN KEY y FOREIGN KEY directas
            if (preg_match('/^\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN\s+KEY/i', $line) ||
                preg_match('/^\s*FOREIGN\s+KEY/i', $line)) {
                // Eliminar la coma de la línea anterior si la tiene al final
                if (!empty($clean)) {
                    $last = rtrim(end($clean));
                    if (substr($last, -1) === ',') {
                        $clean[count($clean) - 1] = substr($last, 0, -1);
                    }
                }
                continue;
            }

            $clean[] = $line;
        }

        return implode("\n", $clean);
    }
}
