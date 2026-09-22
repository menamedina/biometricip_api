<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ConfigRecargo;
use App\Models\ConceptoRecargo;
use App\Models\Festivo;
use App\Models\HorarioDia;
use App\Models\RecargoCalculado;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecargoCalculatorService
{
    private int $horaInicioNocturna;
    private int $horaFinNocturna;

    private Collection $festivos;
    private Collection $conceptos;

    /**
     * Calcula recargos para un conjunto de empleados en un período.
     *
     * @return array Datos agrupados por empleado con minutos por concepto
     */
    public function calcular(array $userIds, Carbon $desde, Carbon $hasta): array
    {
        // Cargar configuración de jornada nocturna desde BD
        $this->horaInicioNocturna = (int) ConfigRecargo::valor('hora_inicio_nocturna', '19');
        $this->horaFinNocturna    = (int) ConfigRecargo::valor('hora_fin_nocturna', '6');

        $this->festivos = Festivo::where('is_active', 1)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->pluck('fecha')
            ->map(fn($f) => Carbon::parse($f)->toDateString());

        $this->conceptos = ConceptoRecargo::where('is_active', 1)->get()->keyBy('codigo');

        // Cargar registros de asistencia del período
        $query = AttendanceRecord::query()
            ->select('id', 'user_id', 'sede_id', 'horario_id', 'tipo', 'fecha_hora')
            ->with(['user:id,name,codigo_empleado,departamento_id', 'horario:id,nombre', 'horario.dias'])
            ->whereBetween('fecha_hora', [$desde->startOfDay()->toDateTimeString(), $hasta->endOfDay()->toDateTimeString()])
            ->orderBy('fecha_hora', 'asc');

        if (!empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        $records = $query->get();

        // Agrupar por empleado + fecha
        $grupos = [];
        foreach ($records as $r) {
            $fecha = substr($r->fecha_hora, 0, 10);
            $key   = "{$r->user_id}_{$fecha}";
            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'user'      => $r->user,
                    'fecha'     => $fecha,
                    'registros' => [],
                    'horario'   => $r->horario,
                ];
            }
            $grupos[$key]['registros'][] = $r;
            if (!$grupos[$key]['horario'] && $r->horario) {
                $grupos[$key]['horario'] = $r->horario;
            }
        }

        // Calcular recargos por grupo
        $resultadosPorUsuario = [];
        $recargosParaGuardar  = [];

        foreach ($grupos as $g) {
            $userId  = $g['user']?->id;
            $fecha   = $g['fecha'];
            $horario = $g['horario'];

            if (!$userId) continue;

            // Emparejar entrada/salida
            $sorted   = collect($g['registros'])->sortBy('fecha_hora')->values();
            $sessions = $this->emparejarSesiones($sorted);

            // Obtener minutos contratados del día
            $carbonFecha     = Carbon::parse($fecha);
            $diaSemana       = (int) $carbonFecha->format('N'); // 1=Lun, 7=Dom
            $minutosContrato = $this->getMinutosContratados($horario, $diaSemana);

            // Dividir cada sesión en segmentos y clasificar
            $segmentosDia = [];
            foreach ($sessions as $s) {
                if (!$s['e'] || !$s['s']) continue;

                $start = Carbon::parse(str_replace(' ', 'T', $s['e']->fecha_hora));
                $end   = Carbon::parse(str_replace(' ', 'T', $s['s']->fecha_hora));

                if ($end->lte($start)) continue;

                $segmentos = $this->splitIntoSegments($start, $end);
                foreach ($segmentos as $seg) {
                    $segmentosDia[] = $seg;
                }
            }

            // Calcular minutos totales trabajados en el día
            $totalMinutosTrabajados = 0;
            foreach ($segmentosDia as $seg) {
                $totalMinutosTrabajados += $seg['minutos'];
            }

            // Descontar almuerzo del total para determinar extras
            $dia = collect($horario?->dias ?? [])->firstWhere('dia_semana', $diaSemana);
            $almuerzoMin = (int) ($dia->duracion_almuerzo_min ?? 0);
            $totalEfectivo = max(0, $totalMinutosTrabajados - $almuerzoMin);

            // Minutos extra = efectivo - contratado (si > 0)
            $minutosExtra = max(0, $totalEfectivo - $minutosContrato);

            // Los minutos regulares (no extra) son el total bruto menos los extras
            $minutosRegularesRestantes = $totalMinutosTrabajados - $minutosExtra;
            $conceptoMinutos = [];

            foreach ($segmentosDia as $seg) {
                $minSeg = $seg['minutos'];
                if ($minSeg <= 0) continue;

                // ¿Cuántos de estos minutos son regulares vs extras?
                $minRegular = 0;
                $minExtra   = 0;

                if ($minutosRegularesRestantes > 0) {
                    $minRegular = min($minSeg, $minutosRegularesRestantes);
                    $minutosRegularesRestantes -= $minRegular;
                    $minExtra = $minSeg - $minRegular;
                } else {
                    $minExtra = $minSeg;
                }

                // Clasificar minutos regulares
                if ($minRegular > 0) {
                    $codigo = $this->clasificarSegmento($seg['es_nocturno'], $seg['es_festivo_o_domingo'], false);
                    if ($codigo) {
                        $conceptoMinutos[$codigo] = ($conceptoMinutos[$codigo] ?? 0) + $minRegular;
                    }
                }

                // Clasificar minutos extras
                if ($minExtra > 0) {
                    $codigo = $this->clasificarSegmento($seg['es_nocturno'], $seg['es_festivo_o_domingo'], true);
                    if ($codigo) {
                        $conceptoMinutos[$codigo] = ($conceptoMinutos[$codigo] ?? 0) + $minExtra;
                    }
                }
            }

            // Acumular resultados por usuario
            if (!isset($resultadosPorUsuario[$userId])) {
                $resultadosPorUsuario[$userId] = [
                    'user_id'         => $userId,
                    'nombre'          => $g['user']->name ?? 'N/A',
                    'codigo_empleado' => $g['user']->codigo_empleado ?? '',
                    'departamento_id' => $g['user']->departamento_id,
                    'conceptos'       => [],
                    'total_recargo_min' => 0,
                ];
            }

            foreach ($conceptoMinutos as $codigo => $mins) {
                $resultadosPorUsuario[$userId]['conceptos'][$codigo] =
                    ($resultadosPorUsuario[$userId]['conceptos'][$codigo] ?? 0) + $mins;
                $resultadosPorUsuario[$userId]['total_recargo_min'] += $mins;

                // Preparar para guardar en BD
                $conceptoModel = $this->conceptos->get($codigo);
                if ($conceptoModel) {
                    $recargosParaGuardar[] = [
                        'user_id'             => $userId,
                        'fecha'               => $fecha,
                        'horario_id'          => $horario?->id,
                        'concepto_recargo_id' => $conceptoModel->id,
                        'minutos'             => $mins,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }
            }
        }

        // Persistir resultados con upsert
        if (!empty($recargosParaGuardar)) {
            foreach (array_chunk($recargosParaGuardar, 100) as $chunk) {
                RecargoCalculado::upsert(
                    $chunk,
                    ['user_id', 'fecha', 'concepto_recargo_id'],
                    ['minutos', 'horario_id', 'updated_at']
                );
            }
        }

        return array_values($resultadosPorUsuario);
    }

    /**
     * Emparejar registros de entrada/salida en sesiones.
     */
    private function emparejarSesiones(Collection $sorted): array
    {
        $sessions    = [];
        $openEntrada = null;

        foreach ($sorted as $r) {
            if ($r->tipo === 'entrada') {
                if ($openEntrada) {
                    $sessions[] = ['e' => $openEntrada, 's' => null];
                }
                $openEntrada = $r;
            } elseif ($r->tipo === 'salida') {
                $sessions[] = ['e' => $openEntrada, 's' => $r];
                $openEntrada = null;
            }
        }

        if ($openEntrada) {
            $sessions[] = ['e' => $openEntrada, 's' => null];
        }

        return $sessions;
    }

    /**
     * Divide una sesión de trabajo en segmentos homogéneos cortando en:
     * - Cada medianoche (00:00)
     * - 06:00 (fin jornada nocturna)
     * - 19:00 (inicio jornada nocturna)
     *
     * @return array Segmentos con keys: start, end, minutos, fecha_calendario, es_nocturno, es_festivo_o_domingo
     */
    private function splitIntoSegments(Carbon $start, Carbon $end): array
    {
        // Generar puntos de corte entre start y end
        $cutPoints = [$start->copy(), $end->copy()];

        $current = $start->copy()->startOfDay();
        $limit   = $end->copy()->addDay()->startOfDay();

        while ($current->lt($limit)) {
            // Medianoche
            $midnight = $current->copy()->startOfDay();
            if ($midnight->gt($start) && $midnight->lt($end)) {
                $cutPoints[] = $midnight;
            }

            // 06:00
            $sixAm = $current->copy()->setTime($this->horaFinNocturna, 0, 0);
            if ($sixAm->gt($start) && $sixAm->lt($end)) {
                $cutPoints[] = $sixAm;
            }

            // 19:00
            $sevenPm = $current->copy()->setTime($this->horaInicioNocturna, 0, 0);
            if ($sevenPm->gt($start) && $sevenPm->lt($end)) {
                $cutPoints[] = $sevenPm;
            }

            $current->addDay();
        }

        // Ordenar y eliminar duplicados
        $cutPoints = collect($cutPoints)
            ->map(fn($p) => $p->copy())
            ->sortBy(fn($p) => $p->timestamp)
            ->values()
            ->unique(fn($p) => $p->timestamp)
            ->values();

        // Crear segmentos entre puntos consecutivos
        $segments = [];
        for ($i = 0; $i < $cutPoints->count() - 1; $i++) {
            $segStart = $cutPoints[$i];
            $segEnd   = $cutPoints[$i + 1];
            $minutos  = $segStart->diffInMinutes($segEnd);

            if ($minutos <= 0) continue;

            $hora           = (int) $segStart->format('H');
            $esNocturno     = ($hora >= $this->horaInicioNocturna || $hora < $this->horaFinNocturna);
            $fechaCalendario = $segStart->toDateString();
            $diaSemana       = (int) $segStart->format('w'); // 0=Dom, 6=Sáb

            $segments[] = [
                'start'                => $segStart,
                'end'                  => $segEnd,
                'minutos'              => $minutos,
                'fecha_calendario'     => $fechaCalendario,
                'es_nocturno'          => $esNocturno,
                'es_festivo_o_domingo' => $diaSemana === 0 || $this->festivos->contains($fechaCalendario),
            ];
        }

        return $segments;
    }

    /**
     * Clasifica un segmento en el código de concepto de recargo.
     * Retorna null si no genera recargo (diurno ordinario regular).
     */
    private function clasificarSegmento(bool $esNocturno, bool $esFestivo, bool $esExtra): ?string
    {
        if ($esExtra) {
            if ($esNocturno && $esFestivo) return 'EXTRA_NOCT_DOM';
            if ($esNocturno)               return 'EXTRA_NOCT';
            if ($esFestivo)                return 'EXTRA_DIUR_DOM';
            return 'EXTRA_DIUR';
        }

        // Regular
        if ($esNocturno && $esFestivo) return 'FEST_NOCT';
        if ($esNocturno)               return 'NOCT_ORD';
        if ($esFestivo)                return 'FEST_DIUR';

        // Diurno ordinario regular: sin recargo
        return null;
    }

    /**
     * Obtiene los minutos contratados para un día según el horario.
     */
    private function getMinutosContratados($horario, int $diaSemana): int
    {
        if (!$horario) return 0;

        $dia = collect($horario->dias ?? [])->firstWhere('dia_semana', $diaSemana);

        if (!$dia || !$dia->hora_entrada || !$dia->hora_salida) {
            return 0;
        }

        [$hE, $mE] = explode(':', substr($dia->hora_entrada, 0, 5));
        [$hS, $mS] = explode(':', substr($dia->hora_salida, 0, 5));

        $diff = ((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE);
        if ($diff < 0) $diff += 1440;

        $diff -= (int)($dia->duracion_almuerzo_min ?? 0);

        return max(0, $diff);
    }
}
