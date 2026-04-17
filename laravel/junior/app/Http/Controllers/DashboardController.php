<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $clientsCount = $user->clients()->count();

        $projectsQuery = Project::query()
            ->whereHas('client', fn ($q) => $q->where('user_id', $user->id));

        $tasksQuery = Task::query()
            ->whereHas('project.client', fn ($q) => $q->where('user_id', $user->id));

        $stats = [
            'clients' => $clientsCount,
            'projects' => (clone $projectsQuery)->count(),
            'projects_active' => (clone $projectsQuery)->where('status', 'active')->count(),
            'tasks' => (clone $tasksQuery)->count(),
            'tasks_completed' => (clone $tasksQuery)->where('status', 'done')->count(),
            'tasks_overdue' => (clone $tasksQuery)
                ->where('status', '!=', 'done')
                ->whereDate('due_on', '<', now()->toDateString())
                ->count(),
        ];

        $recentTasks = (clone $tasksQuery)
            ->with('project.client')
            ->where('status', '!=', 'done')
            ->orderBy('due_on')
            ->limit(5)
            ->get();

        $recentProjects = (clone $projectsQuery)
            ->with('client')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentTasks', 'recentProjects'));
    }
}
