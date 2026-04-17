<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Persist an uploaded file for a project and record the activity.
 *
 * The action captures metadata (size, MIME, sha256 checksum) from the
 * uploaded temp file BEFORE it is moved to the configured disk, then
 * creates the `ProjectFile` row inside a transaction together with the
 * activity log entry so both succeed or fail atomically.
 */
class UploadProjectFileAction
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Store the uploaded file on the configured disk and create the record.
     *
     * @throws \Illuminate\Database\QueryException on persistence failure
     * @throws \League\Flysystem\FilesystemException on storage failure
     */
    public function execute(User $actor, Project $project, UploadedFile $upload): ProjectFile
    {
        $disk = config('filesystems.default');

        $safeName = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $extension = strtolower($upload->getClientOriginalExtension() ?: 'bin');

        $mimeType = $upload->getMimeType() ?? 'application/octet-stream';
        $sizeBytes = $upload->getSize() ?: 0;
        $checksum = $this->checksum($upload);

        $directory = sprintf('projects/%d', $project->id);
        $filename = sprintf(
            '%s-%s.%s',
            now()->format('Ymd-His'),
            Str::lower(Str::random(8)),
            $extension,
        );

        $path = $upload->storeAs($directory, $filename, $disk);

        return DB::transaction(function () use ($actor, $project, $disk, $path, $safeName, $extension, $mimeType, $sizeBytes, $checksum) {
            $file = ProjectFile::create([
                'project_id' => $project->id,
                'uploaded_by_id' => $actor->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $safeName.'.'.$extension,
                'mime_type' => $mimeType,
                'size_bytes' => $sizeBytes,
                'checksum' => $checksum,
            ]);

            $this->activity->record($actor, 'project.file_uploaded', $project, [
                'file_id' => $file->id,
                'size_bytes' => $file->size_bytes,
                'mime_type' => $file->mime_type,
            ]);

            return $file;
        });
    }

    private function checksum(UploadedFile $upload): ?string
    {
        $realPath = $upload->getRealPath();

        if (! $realPath || ! is_readable($realPath)) {
            return null;
        }

        try {
            $hash = hash_file('sha256', $realPath);

            return $hash !== false ? $hash : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
