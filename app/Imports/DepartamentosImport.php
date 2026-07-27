<?php

namespace App\Imports;

use App\Models\Departamento;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DepartamentosImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public array $skipped  = [];
    public int   $imported = 0;

    private array $existing    = [];
    private array $seenInFile  = [];

    public function __construct()
    {
        $this->existing = Departamento::pluck('nombre')
            ->map(fn($n) => mb_strtolower(trim($n)))
            ->toArray();
    }

    public function model(array $row): ?Departamento
    {
        $nombre = trim($row['nombre'] ?? '');
        if ($nombre === '') return null;

        $key = mb_strtolower($nombre);

        if (in_array($key, $this->existing) || in_array($key, $this->seenInFile)) {
            $this->skipped[] = $nombre;
            return null;
        }

        $this->seenInFile[] = $key;
        $this->imported++;

        return new Departamento([
            'nombre'      => $nombre,
            'descripcion' => trim($row['descripcion'] ?? ''),
            'is_active'   => isset($row['is_active']) ? (bool) $row['is_active'] : true,
        ]);
    }
}
