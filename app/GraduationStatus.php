<?php

namespace App;

enum GraduationStatus: string
{
    case STUDENT = 'student';
    case GRADUATING_SOON = 'graduating_soon';
    case RECENT_GRADUATE = 'recent_graduate';
    case GRADUATED = 'graduated';

    public function getLabel(): string
    {
        return match ($this) {
            self::STUDENT => 'Currently Studying',
            self::GRADUATING_SOON => 'Graduating Soon',
            self::RECENT_GRADUATE => 'Recent Graduate',
            self::GRADUATED => 'Graduated',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}
