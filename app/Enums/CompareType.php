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
            CompareType::ABSENT => 'bg-gray-600 text-gray-200',
            CompareType::CORRECT => 'bg-green-700 text-green-200',
            CompareType::PRESENT => 'bg-yellow-500 text-yellow-200',
        };
    }
}
