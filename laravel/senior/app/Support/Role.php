<?php

namespace App\Support;

enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Manager => 'Manager',
            self::User => 'User',
        };
    }
}
