<?php

namespace App\Support;

enum ProjectStatus: string
{
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::OnHold => 'On hold',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }
}
