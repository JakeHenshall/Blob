<?php

namespace App\Support;

enum TaskPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}
