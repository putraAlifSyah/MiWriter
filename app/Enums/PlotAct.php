<?php

namespace App\Enums;

enum PlotAct: string
{
    case Beginning = 'beginning';
    case Middle = 'middle';
    case End = 'end';

    public function label(): string
    {
        return match ($this) {
            self::Beginning => 'Beginning',
            self::Middle => 'Middle',
            self::End => 'End',
        };
    }
}
