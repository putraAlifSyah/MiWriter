<?php

namespace App\Enums;

enum CharacterRole: string
{
    case Protagonist = 'protagonist';
    case Antagonist = 'antagonist';
    case Supporting = 'supporting';
    case Minor = 'minor';

    public function label(): string
    {
        return match ($this) {
            self::Protagonist => 'Protagonist',
            self::Antagonist => 'Antagonist',
            self::Supporting => 'Supporting',
            self::Minor => 'Minor',
        };
    }
}
