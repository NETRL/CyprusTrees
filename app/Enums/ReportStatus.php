<?php

namespace App\Enums;

enum ReportStatus: string
{
    use HasLabel;
    
    case OPEN      = 'Open';
    case TRIAGED   = 'Triaged';
    case RESOLVED  = 'Resolved';

    public function label(): string
    {
        return match ($this) {
            self::OPEN     => 'Open',
            self::TRIAGED  => 'Triaged',
            self::RESOLVED => 'Resolved',
        };
    }
}
