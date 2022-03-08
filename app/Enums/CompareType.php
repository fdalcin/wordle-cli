<?php

namespace App\Enums;

enum CompareType
{
    case ABSENT;
    case CORRECT;
    case PRESENT;

    /**
     * Return the color classes for the given compare type.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::ABSENT => 'bg-gray-600 text-gray-200',
            self::CORRECT => 'bg-green-700 text-green-200',
            self::PRESENT => 'bg-yellow-500 text-yellow-200',
        };
    }
}
