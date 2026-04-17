<?php

namespace App\Models;

use Database\Factories\ProjectFileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    /** @use HasFactory<ProjectFileFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'uploaded_by_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'checksum',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return sprintf('%.1f %s', $bytes, $units[$i]);
    }

    public function temporaryUrl(int $minutes = 5): ?string
    {
        $disk = Storage::disk($this->disk);

        if (method_exists($disk, 'temporaryUrl')) {
            try {
                return $disk->temporaryUrl($this->path, now()->addMinutes($minutes));
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
