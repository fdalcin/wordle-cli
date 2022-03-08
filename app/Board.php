<?php

namespace App;

use App\Enums\CompareType;
use Illuminate\Support\Collection;
use function Termwind\render;
use function Termwind\terminal;

class Board
{
    /**
     * The title of the game.
     *
     * @var string
     */
    protected string $title;

    /**
     * The number of attempts.
     *
     * @var int
     */
    protected int $maxAttempts;

    /**
     * The number of letters in a word.
     *
     * @var int
     */
    protected int $maxLetters;

    /**
     * Create a new Board instance.
     */
    public function __construct()
    {
        $this->title = config('app.name').' '.config('app.version');
    }

    /**
     * Set the maximum number of attempts.
     *
     * @param int $maxAttempts
     *
     * @return Board
     */
    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    /**
     * Set the maximum number of letters.
     *
     * @param int $maxLetters
     *
     * @return Board
     */
    public function setMaxLetters(int $maxLetters): self
    {
        $this->maxLetters = $maxLetters;

        return $this;
    }

    /**
     * Render the board.
     *
     * @param Collection<int, Word> $attempts
     * @param Collection<string, CompareType|null> $guessedLetters
     * @param string $attempt
     *
     * @return void
     */
    public function render(Collection $attempts, Collection $guessedLetters, string $attempt = ''): void
    {
        terminal()->clear();

        $this->heading();

        $this->previous($attempts);

        $pendingAttempts = $this->maxAttempts - $attempts->count();

        if ($pendingAttempts > 0) {
            $this->current($attempt);
        }

        if ($pendingAttempts - 1 > 0) {
            $this->pending($pendingAttempts - 1);
        }

        $this->newline();

        $this->keyboard($guessedLetters);

        $this->newline();
    }

    /**
     * Render the heading.
     *
     * @return void
     */
    protected function heading(): void
    {
        render(
            "<div class='ml-1 w-1/3 p-1 bg-green-700 text-green-100 text-center uppercase'>{$this->title}</div>"
        );
    }

    /**
     * Render the previous attempts.
     *
     * @param Collection<int, Word> $attempts
     *
     * @return void
     */
    protected function previous(Collection $attempts): void
    {
        $attempts->each(
            fn (Word $attempt) => render("<div class='ml-1 mt-1'>{$attempt->render()}</div>")
        );
    }

    /**
     * Render the current attempt.
     *
     * @param string $attempt
     *
     * @return void
     */
    protected function current(string $attempt): void
    {
        $word = new Word(
            str_pad($attempt, $this->maxLetters)
        );

        render("<div class='ml-1 mt-1 text-center'>{$word->render()}</div>");
    }

    /**
     * Render the keyboard.
     *
     * @param Collection<string, CompareType|null> $guessedLetters
     *
     * @return void
     */
    protected function keyboard(Collection $guessedLetters): void
    {
        $rows = collect([
            ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p'],
            ['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l'],
            ['z', 'x', 'c', 'v', 'b', 'n', 'm'],
        ]);

        $rows->each(function (array $row, int $index) use ($guessedLetters) {
            $output = collect($row)->reduce(
                function (string $output, string $letter) use ($guessedLetters) {
                    $color = $guessedLetters->get($letter)?->color() ?? 'bg-gray-700 text-gray-200';

                    return $output."<span class='mr-1 px-1 uppercase {$color}'>{$letter}</span>";
                },
                ''
            );

            $margin = match ($index) {
                1 => 'ml-3',
                2 => 'ml-5',
                default => 'ml-1',
            };

            render("<div class='{$margin} mt-1 text-center'>{$output}</div>");
        });
    }

    /**
     * Render the pending attempts.
     *
     * @param int $pendingAttempts
     *
     * @return void
     */
    protected function pending(int $pendingAttempts): void
    {
        collect(range(1, $pendingAttempts))
            ->each(function () {
                $output = collect(range(1, $this->maxLetters))->reduce(
                    fn ($output) => "{$output}<span class='mr-1 px-1 bg-gray-700 text-gray-200'>&nbsp;</span>"
                );

                render("<div class='ml-1 mt-1 text-center'>{$output}</div>");
            });
    }

    /**
     * Render a new line.
     *
     * @return void
     */
    protected function newLine(): void
    {
        render('');
    }
}
