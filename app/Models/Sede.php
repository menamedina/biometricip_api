<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_sedes';

    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'lat',
        'lng',
        'radio_mts',
        'secret_key',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'radio_mts' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function generateQRValue(int $timeSlot): string
    {
        $hash = substr(base64_encode($this->codigo . $this->secret_key . $timeSlot), 0, 12);
        return json_encode([
            's' => $this->codigo,
            'n' => $this->nombre,
            't' => $timeSlot,
            'h' => $hash,
        ]);
    }

    public function validateQRValue(string $qrValue): bool
    {
        $data = json_decode($qrValue, true);
        if (!$data || !isset($data['s'], $data['t'], $data['h'])) {
            return false;
        }

        if ($data['s'] !== $this->codigo) {
            return false;
        }

        $currentSlot = (int) floor(time() / 30);
        if (abs($currentSlot - (int) $data['t']) > 1) {
            return false;
        }

        $expectedHash = substr(base64_encode($this->codigo . $this->secret_key . $data['t']), 0, 12);
        return hash_equals($expectedHash, $data['h']);
    }
}
