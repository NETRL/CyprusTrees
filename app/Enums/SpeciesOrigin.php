<?php

namespace App\Enums;

enum SpeciesOrigin: string
{
    use HasLabel;
    
    case NATIVE     = 'native';   // Naturally occurs in Cyprus
    case ENDEMIC    = 'endemic';  // Only occurs in Cyprus
    case EXOTIC     = 'exotic';   // Introduced from elsewhere

    public function label(): string
    {
        return match ($this) {
            self::NATIVE    => 'Native',
            self::ENDEMIC   => 'Endemic',
            self::EXOTIC    => 'Exotic',
        };
    }
}
