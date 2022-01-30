<?php

namespace App;

use Illuminate\Support\Collection;

class Word
{
    /**
     * The list of letters compared to the answer.
     *
     * @var Collection
     */
    protected ?Collection $comparison = null;

    /**
     * Create a new Word instance.
     *
     * @var string
     *
     * @return void
     */
    public function __construct(protected string $value)
    {
    }

    /**
     * Compare the current word with another word.
     *
     * @param Word $word
     *
     * @return Word
     */
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

    /**
     * Check if the current word matches another word.
     *
     * @param Word $word
     *
     * @return bool
     */
    public function matches(self $matchTo): bool
    {
        return $this->value === $matchTo->value;
    }

    /**
     * Return a collection of letters in the word.
     *
     * @return Collection
     */
    public function letters(): Collection
    {
        return collect(str_split($this->value));
    }

    /**
     * Render the word based on the comparison result.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->letters()
            ->reduce(function ($output, $letter, $index) {
                $match = $this->comparison?->get($index) ?? null;

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
