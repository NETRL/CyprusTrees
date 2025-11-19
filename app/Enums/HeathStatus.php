<?php

namespace App\Enums;

enum HealthStatus: string
{
    case HEALTHY   = 'healthy';
    case WATCH     = 'watch';
    case STRESSED  = 'stressed';
    case DISEASED  = 'diseased';
    case DEAD      = 'dead';

    public function label(): string
    {
        return match ($this) {
            self::HEALTHY   => 'Healthy',
            self::WATCH     => 'Needs Monitoring',
            self::STRESSED  => 'Stressed',
            self::DISEASED  => 'Diseased',
            self::DEAD      => 'Dead',
        };
    }
}
