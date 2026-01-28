<?php

namespace App\Enums;

enum PlantingEventStatus: string
{
    use HasLabel;

    case DRAFT       = 'draft';
    case SCHEDULED   = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT       => 'Draft',
            self::SCHEDULED   => 'Scheduled',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED   => 'Completed',
            self::CANCELLED   => 'Cancelled',
        };
    }
}
