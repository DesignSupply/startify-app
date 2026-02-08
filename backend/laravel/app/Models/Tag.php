<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopeOnlyDeleted($query)
    {
        return $query->where('is_deleted', true);
    }
}
