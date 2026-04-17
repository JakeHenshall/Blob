<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        $metrics = Cache::remember('dashboard.metrics.v1.user.'.$user->id, now()->addMinutes(1), function () use ($user) {
            $scope = $user->isManager();

            return [
                'clients_total' => $scope
                    ? Client::active()->count()
                    : Client::active()->where('owner_id', $user->id)->count(),
                'projects_active' => $scope
                    ? Project::where('status', 'active')->count()
                    : Project::where('status', 'active')->where('owner_id', $user->id)->count(),
                'tasks_open' => $scope
                    ? Task::open()->count()
                    : Task::open()->where('assignee_id', $user->id)->count(),
                'tasks_overdue' => $scope
                    ? Task::open()->whereDate('due_on', '<', now())->count()
                    : Task::open()
                        ->where('assignee_id', $user->id)
                        ->whereDate('due_on', '<', now())
                        ->count(),
            ];
        });

        $recentActivity = ActivityLog::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $myTasks = Task::with('project')
            ->open()
            ->where('assignee_id', $user->id)
            ->orderBy('due_on')
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'recentActivity', 'myTasks'));
    }
}
