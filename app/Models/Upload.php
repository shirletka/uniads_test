<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'token',
        'delete_token',
        'path',
        'original_name',
        'mime',
        'size',
        'created_at',
    ];
}
