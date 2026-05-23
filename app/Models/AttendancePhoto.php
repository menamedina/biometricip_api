<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePhoto extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_fotos_asistencia';

    protected $fillable = [
        'attendance_record_id',
        'foto_base64',
        'thumbnail_base64',
    ];

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
