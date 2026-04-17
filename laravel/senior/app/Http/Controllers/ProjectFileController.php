<?php

namespace App\Http\Controllers;

use App\Actions\Projects\UploadProjectFileAction;
use App\Http\Requests\UploadProjectFileRequest;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectFileController extends Controller
{
    public function store(UploadProjectFileRequest $request, Project $project, UploadProjectFileAction $action): RedirectResponse
    {
        $action->execute($request->user(), $project, $request->file('file'));

        return redirect()->route('projects.show', $project)->with('status', 'File uploaded.');
    }

    public function download(Project $project, ProjectFile $file): StreamedResponse
    {
        abort_unless($file->project_id === $project->id, 404);
        $this->authorize('view', $file);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function destroy(Project $project, ProjectFile $file, ActivityLogger $log): RedirectResponse
    {
        abort_unless($file->project_id === $project->id, 404);
        $this->authorize('delete', $file);

        $file->delete();
        $log->record(request()->user(), 'project.file_deleted', $project, ['file_id' => $file->id]);

        return redirect()->route('projects.show', $project);
    }
}
