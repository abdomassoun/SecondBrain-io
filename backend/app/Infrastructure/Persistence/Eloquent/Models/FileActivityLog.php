<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class FileActivityLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'file_uuid',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
