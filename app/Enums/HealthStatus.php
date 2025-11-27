<?php

namespace App\Enums;

enum HealthStatus: string
{
    use HasLabel;

    case EXCELLENT  = 'excellent';
    case GOOD       = 'good';
    case FAIR       = 'fair';
    case POOR       = 'poor';
    case CRITICAL   = 'critical';
    case DEAD       = 'dead';

    public function label(): string
    {
        return match ($this) {
            self::EXCELLENT => 'Excellent',
            self::GOOD      => 'Good',
            self::FAIR      => 'Fair',
            self::POOR      => 'Poor',
            self::CRITICAL  => 'Critical',
            self::DEAD      => 'Dead',
        };
    }
}
