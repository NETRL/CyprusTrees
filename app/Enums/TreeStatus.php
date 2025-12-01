<?php

namespace App\Enums;

enum TreeStatus: string
{
    use HasLabel; 


    case EXISTING = 'existing';
    case NEWLY_PLANTED = 'newly_planted';
    case PROPOSED = 'proposed';
    case DEAD = 'dead'; 
    case REMOVED = 'removed';
    case STUMP = 'stump';
    case MISSING = 'missing';
    case UNKNOWN = 'unknown';
    case PENDING_REMOVAL = 'pending_removal';
    case VACANT_PIT = 'vacant_pit';


    public function label(): string
    {
        return match ($this) {
            self::EXISTING      => 'Existing',
            self::NEWLY_PLANTED => 'Newly Planted',
            self::PROPOSED      => 'Proposed Planting',
            self::DEAD          => 'Dead',
            self::REMOVED       => 'Removed',
            self::STUMP         => 'Stump Remaining',
            self::MISSING       => 'Missing (Location is Empty)',
            self::UNKNOWN       => 'Unknown Status',
            self::PENDING_REMOVAL => 'Pending Removal',
            self::VACANT_PIT    => 'Vacant Planting Pit',
        };
    }
}