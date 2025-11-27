<?php

namespace App\Enums;

enum ReportStatus: string
{
    use HasLabel;
    
    case OPEN      = 'open';
    case TRIAGED   = 'triaged';
    case RESOLVED  = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::OPEN     => 'Open',
            self::TRIAGED  => 'Triaged',
            self::RESOLVED => 'Resolved',
        };
    }
}
