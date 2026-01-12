<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->key)) {
                $model->key = \Illuminate\Support\Str::random(32); // Or Str::uuid()
            }
        });
    }
}
