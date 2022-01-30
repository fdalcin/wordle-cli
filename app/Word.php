<?php

namespace App;

use Illuminate\Support\Collection;

class Word
{
    protected ?Collection $comparison = null;

    /**
     * Create a new word instance.
     *
     * @var string
     *
     * @return void
     */
    public function __construct(protected string $value)
    {
    }

    public function compare(self $compareTo): self
    {
        $positions = collect();
        $compareLetters = $compareTo->letters();

        $this->comparison = $this->letters()
            ->map(static function ($letter, $index) use ($compareLetters, $positions) {
                $position = $compareLetters->search(
                    fn ($compareLetter, $comparePosition) => $compareLetter === $letter && ! $positions->contains($comparePosition)
                );

                if ($position === false) {
                    return 'not-found';
                }

                $positions->push($position);

                if ($index === $position) {
                    return 'found';
                }

                return 'out-of-order';
            });

        return $this;
    }

    public function matches(self $matchTo): bool
    {
        return $this->value === $matchTo->value;
    }

    public function letters(): Collection
    {
        return collect(str_split($this->value));
    }

    public function render(): string
    {
        return $this->letters()
            ->reduce(function ($output, $letter, $index) {
                $match = $this->comparison?->get($index) ?? '';

                $class = match ($match) {
                    'found' => 'bg-green-700 text-green-100',
                    'out-of-order' => 'bg-yellow-500 text-yellow-100',
                    'not-found' => 'bg-gray-600 text-gray-100',
                    default => 'bg-gray-500 text-gray-100',
                };

                return "{$output}<span class='mr-1 px-1 uppercase {$class}'>{$letter}</span>";
            });
    }

    /**
     * Returns the string representation of the word.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
