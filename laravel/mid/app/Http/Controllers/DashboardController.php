<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $projectsByStatus = Project::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        foreach (ProjectStatus::cases() as $status) {
            $projectsByStatus[$status->value] ??= 0;
        }

        $overdueTasks = Task::query()
            ->with(['project', 'assignee'])
            ->overdue()
            ->when(! $user->isStaff(), fn ($q) => $q->where('assigned_to', $user->id))
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        $myTasks = Task::query()
            ->with('project')
            ->open()
            ->where('assigned_to', $user->id)
            ->orderByRaw("CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5 END")
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        $recentActivity = Activity::query()
            ->with(['causer', 'subject'])
            ->latest()
            ->limit(15)
            ->get();

        return view('dashboard', compact(
            'projectsByStatus',
            'overdueTasks',
            'myTasks',
            'recentActivity',
        ));
    }
}
