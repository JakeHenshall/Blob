@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $cards = [
                ['Active clients', $metrics['clients_total']],
                ['Active projects', $metrics['projects_active']],
                ['Open tasks', $metrics['tasks_open']],
                ['Overdue tasks', $metrics['tasks_overdue']],
            ];
        @endphp
        @foreach ($cards as [$label, $value])
            <div class="card p-4">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="text-3xl font-semibold mt-1">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="px-4 py-3 border-b border-slate-200 font-medium">My open tasks</div>
            <ul class="divide-y divide-slate-100">
                @forelse ($myTasks as $task)
                    <li class="px-4 py-3 text-sm flex items-center justify-between">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-slate-800 hover:underline">{{ $task->title }}</a>
                            <div class="text-slate-500">{{ $task->project?->name }}</div>
                        </div>
                        <div class="text-slate-500">
                            @if ($task->due_on)
                                Due {{ $task->due_on->format('d M') }}
                            @else
                                No due date
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-slate-500">Nothing assigned.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <div class="px-4 py-3 border-b border-slate-200 font-medium">Recent activity</div>
            <ul class="divide-y divide-slate-100">
                @forelse ($recentActivity as $log)
                    <li class="px-4 py-3 text-sm">
                        <div class="text-slate-700">
                            <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                            &middot;
                            <span class="text-slate-500">{{ $log->event }}</span>
                        </div>
                        <div class="text-slate-400 text-xs">{{ $log->created_at?->diffForHumans() }}</div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-slate-500">No activity yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
