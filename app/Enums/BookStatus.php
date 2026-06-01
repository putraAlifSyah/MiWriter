<?php

namespace App\Enums;

enum BookStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }
}
