<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request, Project $project): RedirectResponse
    {
        $project->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return back()->with('status', 'Note added.');
    }

    public function destroy(Project $project, Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        abort_unless($note->project_id === $project->id, 404);

        $note->delete();

        return back()->with('status', 'Note deleted.');
    }
}
