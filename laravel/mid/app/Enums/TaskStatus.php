<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function colour(): string
    {
        return match ($this) {
            self::Todo => 'bg-slate-100 text-slate-700',
            self::InProgress => 'bg-sky-100 text-sky-700',
            self::Completed => 'bg-emerald-100 text-emerald-700',
            self::Cancelled => 'bg-rose-100 text-rose-700',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Todo, self::InProgress], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
