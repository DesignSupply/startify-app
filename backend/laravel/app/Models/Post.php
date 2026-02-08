<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'title',
        'body',
        'author',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
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
