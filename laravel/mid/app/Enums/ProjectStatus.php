<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::OnHold => 'On Hold',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function colour(): string
    {
        return match ($this) {
            self::Pending => 'bg-slate-100 text-slate-700',
            self::Active => 'bg-emerald-100 text-emerald-700',
            self::OnHold => 'bg-amber-100 text-amber-700',
            self::Completed => 'bg-indigo-100 text-indigo-700',
            self::Cancelled => 'bg-rose-100 text-rose-700',
        };
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
