<?php

namespace App\Enums;

enum LocationType: string
{
    case City = 'city';
    case Building = 'building';
    case Landscape = 'landscape';
    case Realm = 'realm';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::City => 'City',
            self::Building => 'Building',
            self::Landscape => 'Landscape',
            self::Realm => 'Realm',
            self::Other => 'Other',
        };
    }
}
