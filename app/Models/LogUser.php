<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogUser extends Model
{
    use HasFactory;

    protected $table = 'log_users';

    // Como nossa migration não usa $table->timestamps() (apenas a coluna login_at),
    // desativamos os timestamps automáticos padrão do Laravel
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'ip_address',
        'user_agent',
        'login_at',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
        ];
    }

    /**
     * Usuário que realizou o acesso.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
