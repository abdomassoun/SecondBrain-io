<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes,
        HasUuid;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'name',
        'original_name',
        'size',
        'mime_type',
        'extension',
        'path',
        'owner_uuid',
        'upload_date',
    ];

    protected $casts = [
        'size' => 'integer',
        'upload_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_uuid', 'uuid');
    }

    public function activityLogs()
    {
        return $this->hasMany(FileActivityLog::class, 'file_uuid', 'uuid');
    }

    public function toDomainEntity(): \App\Domain\Files\Entities\File
    {
        return new \App\Domain\Files\Entities\File(
            id: $this->id,
            uuid: $this->uuid,
            name: $this->name,
            originalName: $this->original_name,
            size: $this->size,
            mimeType: $this->mime_type,
            extension: $this->extension,
            path: $this->path,
            ownerUuid: $this->owner_uuid,
            uploadDate: $this->upload_date,
        );
    }
}
