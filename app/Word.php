<?php

namespace App;

use App\Enums\CompareType;
use Illuminate\Support\Collection;

class Word
{
    /**
     * The list of letters compared to the answer.
     *
     * @var ?Collection<int, CompareType>
     */
    protected ?Collection $comparison = null;

    /**
     * Create a new Word instance.
     *
     * @param string $value
     * @return void
     */
    public function __construct(protected string $value)
    {
    }

    /**
     * Compare the current word with another word.
     *
     * @param Word $compareTo
     * @return Word
     */
    public function compare(self $compareTo): self
    {
        $positions = collect(); // @phpstan-ignore-line
        $compareLetters = $compareTo->letters();

        $this->comparison = $this->letters()
            ->map(static function (string $letter, int $index) use ($compareLetters, $positions): CompareType {
                $position = $compareLetters->search(
                    fn (string $compareLetter, int $comparePosition) => $compareLetter === $letter && ! $positions->contains($comparePosition)
                );

                if ($position === false) {
                    return CompareType::ABSENT;
                }

                $positions->push($position);

                if ($index === $position) {
                    return CompareType::CORRECT;
                }

                return CompareType::PRESENT;
            });

        return $this;
    }

    /**
     * Check if the current word matches another word.
     *
     * @param Word $matchTo
     * @return bool
     */
    public function matches(self $matchTo): bool
    {
        return $this->value === $matchTo->value;
    }

    /**
     * Return a collection of letters in the word.
     *
     * @return Collection<int, string>
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
            ->reduce(function (string $output, string $letter, int $index): string {
                $color = $this->comparison?->get($index)?->color() ?? 'bg-gray-500 text-gray-100';

                return "{$output}<span class='mr-1 px-1 uppercase {$color}'>{$letter}</span>";
            }, '');
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
