<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function colour(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700',
            self::Medium => 'bg-sky-100 text-sky-700',
            self::High => 'bg-amber-100 text-amber-700',
            self::Urgent => 'bg-rose-100 text-rose-700',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $p) => [$p->value => $p->label()])
            ->all();
    }
}
