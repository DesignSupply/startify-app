<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    protected $table = "refresh_tokens";

    public $incrementing = false;

    protected $keyType = "string";

    protected $fillable = [
        "id", "user_id", "token_hash", "ip", "ua", "revoked_at", "expires_at"
    ];

    protected $casts = [
        "revoked_at" => "datetime",
        "expires_at" => "datetime",
    ];

    protected static function booted(): void
    {
        static::creating(function (self $token): void {
            if (empty($token->id)) {
                $token->id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull("revoked_at")->where(function ($q) {
            $q->whereNull("expires_at")->orWhere("expires_at", ">", now());
        });
    }
}
