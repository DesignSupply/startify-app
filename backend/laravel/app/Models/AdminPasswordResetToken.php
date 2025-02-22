<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPasswordResetToken extends Model
{
    protected string $table = 'admin_password_reset_tokens';
    protected string $keyType = 'string';
    protected string $primaryKey = 'email';
    public bool $timestamps = false;
    protected array $fillable = [
        'email',
        'token',
        'created_at',
    ];
}
