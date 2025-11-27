<?php

namespace App\Enums;

enum DroughtTolerance: string
{
    use HasLabel;
    
    case LOW        = 'low';
    case MODERATE   = 'moderate';
    case HIGH       = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW       => 'Low',
            self::MODERATE  => 'Moderate',
            self::HIGH      => 'High',
        };
    }
}
