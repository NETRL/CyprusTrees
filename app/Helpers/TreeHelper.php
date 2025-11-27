<?php

namespace App\Helpers;

use App\Enums\TreeSex;

class TreeHelper
{

    // Individual Allergenic Potential Score (IAPS)
    public static function calculateIAPS(int $opalsScore, string $sex): int
    {
        return match ($sex) {
            // Female trees are the only safe (non-allergenic) exception.
            TreeSex::FEMALE => 1,
            // Male, Monoecious, or Unknown sex defaults to the species' full OPALS potential.
            TreeSex::MALE, TreeSex::MONOECIOUS, TreeSex::UNKNOWN => $opalsScore,
            default => $opalsScore, // Default to the highest risk for safety
        };
    }
}
