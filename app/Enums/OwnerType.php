<?php

namespace App\Enums;

enum OwnerType: string
{
    use HasLabel; 


    case PUBLIC  = 'public';
    case PRIVATE = 'private';
    

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC  => 'Public',
            self::PRIVATE => 'Private',
        };
    }
}