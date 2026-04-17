<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request, Project $project, ActivityLogger $log): RedirectResponse
    {
        $note = Note::create([
            'project_id' => $project->id,
            'author_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $log->record($request->user(), 'project.note_added', $project, ['note_id' => $note->id]);

        return redirect()->route('projects.show', $project)->with('status', 'Note added.');
    }

    public function destroy(Request $request, Project $project, Note $note, ActivityLogger $log): RedirectResponse
    {
        abort_unless($note->project_id === $project->id, 404);
        $this->authorize('delete', $note);

        $note->delete();
        $log->record($request->user(), 'project.note_deleted', $project, ['note_id' => $note->id]);

        return redirect()->route('projects.show', $project);
    }
}
