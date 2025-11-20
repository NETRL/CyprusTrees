<?php

namespace App\Enums;

enum CanopyClass: string
{
    use HasLabel;

    case S  = 'S';
    case M  = 'M';
    case L  = 'L';

    public function label(): string
    {
        return match ($this) {
            self::S => 'Small',
            self::M => 'Medium',
            self::L => 'Large',
        };
    }

    public function short(): string
    {
        return $this->value; // S / M / L
    }

    public function display(): string
    {
        return $this->label() . ' (' . $this->short() . ')';
    }

    // Override the hasLabel trait to modify the final output.
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->display()
            ])
            ->all();
    }
}
