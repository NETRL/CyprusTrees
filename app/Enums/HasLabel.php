<?php

namespace App\Enums;

trait HasLabel
{
    public static function options(): array
    {
        return collect(static::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])
            ->all();
    }
}
