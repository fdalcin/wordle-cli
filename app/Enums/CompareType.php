<?php

namespace App\Enums;

enum CompareType
{
    case FOUND;
    case NOT_FOUND;
    case OUT_OF_ORDER;

    /**
     * Return the color classes for the given compare type.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            CompareType::FOUND => 'bg-green-700 text-green-200',
            CompareType::OUT_OF_ORDER => 'bg-yellow-500 text-yellow-200',
            CompareType::NOT_FOUND => 'bg-gray-600 text-gray-200',
        };
    }
}
