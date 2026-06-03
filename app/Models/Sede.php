<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_sedes';

    protected $guarded = [];

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
            's'   => $this->codigo,
            'n'   => $this->nombre,
            't'   => $timeSlot,
            'h'   => $hash,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'r'   => $this->radio_mts,
        ]);
    }

    public function validateQRValue(string $qrValue): bool
    {
        return $this->validateQRValueAtTime($qrValue, time());
    }

    public function validateQRValueAtTime(string $qrValue, int $timestamp): bool
    {
        $data = json_decode($qrValue, true);
        if (!$data || !isset($data['s'], $data['t'], $data['h'])) {
            return false;
        }

        if ($data['s'] !== $this->codigo) {
            return false;
        }

        $slot = (int) floor($timestamp / 30);
        if (abs($slot - (int) $data['t']) > 1) {
            return false;
        }

        $expectedHash = substr(base64_encode($this->codigo . $this->secret_key . $data['t']), 0, 12);
        return hash_equals($expectedHash, $data['h']);
    }

    public function generateV3QRUrl(string $webToken): string
    {
        $tok = substr(hash_hmac('sha256', $this->codigo . $this->secret_key, $this->qr_v3_token), 0, 32);
        return rtrim(config('app.url'), '/') . '/asistencia/' . $webToken . '/' . $this->codigo . '/' . $tok;
    }

    public function validateV3Token(string $token): bool
    {
        if (!$this->qr_v3_token) {
            return false;
        }
        $expected = substr(hash_hmac('sha256', $this->codigo . $this->secret_key, $this->qr_v3_token), 0, 32);
        return hash_equals($expected, $token);
    }

    public function generateStaticQRValue(): string
    {
        $tok = substr(hash_hmac('sha256', $this->codigo . $this->secret_key, $this->qr_static_token), 0, 32);
        return json_encode([
            'v'   => 2,
            's'   => $this->codigo,
            'n'   => $this->nombre,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'r'   => $this->radio_mts,
            'tok' => $tok,
        ]);
    }

    public function validateStaticQRValue(string $qrValue): bool
    {
        if (!$this->qr_static_token) {
            return false;
        }

        $data = json_decode($qrValue, true);
        if (!$data || ($data['v'] ?? null) !== 2 || !isset($data['s'], $data['tok'])) {
            return false;
        }

        if ($data['s'] !== $this->codigo) {
            return false;
        }

        $expected = substr(hash_hmac('sha256', $this->codigo . $this->secret_key, $this->qr_static_token), 0, 32);
        return hash_equals($expected, $data['tok']);
    }
}
