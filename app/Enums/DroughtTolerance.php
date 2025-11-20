<?php

namespace App\Enums;

enum DroughtTolerance: string
{
    use HasLabel;
    
    case LOW        = 'Low';
    case MODERATE   = 'Moderate';
    case HIGH       = 'High';

    public function label(): string
    {
        return match ($this) {
            self::LOW       => 'Low',
            self::MODERATE  => 'Moderate',
            self::HIGH      => 'High',
        };
    }
}
