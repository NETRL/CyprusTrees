<?php

namespace App\Enums;

enum TreeSex: string
{
    use HasLabel;
    
    case MALE       = 'male';
    case FEMALE     = 'female';
    case MONOECIOUS = 'monoecious';
    case UNKNOWN    = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::MALE      => 'M (Male)',
            self::FEMALE    => 'F (Female)',
            self::MONOECIOUS   => 'M/F (Monoecious)',
            self::UNKNOWN   => 'Unknown',
        };
    }
}
