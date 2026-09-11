<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiConfig extends Model
{
    protected $table = 'tbl_ai_config';

    protected $fillable = [
        'empresa_id',
        'proveedor',
        'modelo',
        'api_key',
        'system_prompt',
        'activo',
    ];

    protected $hidden = ['api_key'];

    public function setApiKeyAttribute(string $value): void
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    public function getApiKeyDecrypted(): string
    {
        try {
            return Crypt::decryptString($this->attributes['api_key']);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function forEmpresa(?int $empresaId): ?self
    {
        return self::where('empresa_id', $empresaId)->first()
            ?? self::whereNull('empresa_id')->first();
    }
}
