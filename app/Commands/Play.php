<?php

namespace App\Commands;

use App\Word;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;
use function Termwind\terminal;

class Play extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'play';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Play Wordle Game';

    /**
     * The number of times the user can guess.
     *
     * @var int
     */
    protected int $maxAttempts = 6;

    /**
     * The number of letters in the word.
     *
     * @var int
     */
    protected int $maxLetters = 5;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $attempts = collect();
        $answer = new Word(
            collect(require app_path('words/answers.php'))->random()
        );

        $allowerdWords = collect(require app_path('words/allowed.php'));

        while ($attempts->count() < $this->maxAttempts) {
            $this->render($attempts);

            $attempt = $this->readAttempt($attempts, $allowerdWords);
            $attempts->push($attempt->compare($answer));

            if ($attempt->matches($answer)) {
                $this->render($attempts);
                $this->info("You guessed the word!\n");

                return;
            }
        }

        $this->render($attempts);
        $this->info("The word was {$answer}.\n");
    }

    public function readAttempt(Collection $attempts, Collection $allowedWords): Word
    {
        $attempt = '';

        while (true) {
            $char = $this->readCharacter();

            if ($this->isCharacterValid($char) && strlen($attempt) < $this->maxLetters) {
                $attempt .= $char;
            } elseif ($this->isDeleting($char)) {
                $attempt = substr($attempt, 0, -1);
            } elseif ($this->isAttempting($attempt, $char)) {
                if ($allowedWords->contains($attempt)) {
                    return new Word($attempt);
                }
            }

            $this->render($attempts, $attempt);
        }
    }

    public function render(Collection $attempts, string $attempt = null): void
    {
        // Clear previous rendering
        terminal()->clear();

        // Render game title
        $title = config('app.name').' '.config('app.version');

        render(
            "<div class='ml-1 w-1/3 p-1 bg-green-700 text-green-100 text-center uppercase'>{$title}</div>"
        );

        // Previous attempts
        $attempts->each(
            fn (Word $attempt) => render("<div class='ml-1 mt-1'>{$attempt->render()}</div>")
        );

        $pendingAttempts = $this->maxAttempts - $attempts->count();

        // Current attempt
        if ($pendingAttempts > 0) {
            $attempt = new Word(
                str_pad($attempt ?? '', $this->maxLetters)
            );

            render("<div class='ml-1 mt-1 text-center'>{$attempt->render()}</div>");
        }

        // Pending attempts
        if ($pendingAttempts - 1 > 0) {
            collect(range(1, $pendingAttempts - 1))
            ->each(function () {
                $output = collect(range(1, $this->maxLetters))->reduce(
                    fn ($output) => "{$output}<span class='mr-1 px-1 bg-gray-700 text-gray-200'>&nbsp;</span>"
                );

                render("<div class='ml-1 mt-1 text-center'>{$output}</div>");
            });
        }

        render('');
    }

    /**
     * Read a single character from the user.
     *
     * @return string
     */
    protected function readCharacter(): string
    {
        return strtolower(
            trim(`bash -c "read -n 1 ANS ; echo \\\$ANS"`)
        );
    }

    /**
     * Determine if the given character is valid.
     *
     * @param string $char
     *
     * @return bool
     */
    protected function isCharacterValid(string $char): bool
    {
        return preg_match('/[a-z]/', $char);
    }

    /**
     * Determine if the user is deleting a letter.
     *
     * @param string $char
     *
     * @return bool
     */
    protected function isDeleting(string $char): bool
    {
        return in_array(ord($char), [126, 127]);
    }

    /**
     * Determine if the user is attempting to guess.
     *
     * @param string $word
     * @param string $char
     *
     * @return bool
     */
    protected function isAttempting(string $word, string $char): bool
    {
        return strlen($word) === $this->maxLetters && ord($char) === 0;
    }
}
