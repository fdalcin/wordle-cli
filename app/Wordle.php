<?php

namespace App;

use Illuminate\Support\Collection;
use function Termwind\render;

class Wordle
{
    /**
     * The list of words allowed to be used.
     *
     * @var Collection
     */
    protected Collection $allowedWords;

    /**
     * The list of words attempted in the game.
     *
     * @var Collection
     */
    protected Collection $attempts;

    /**
     * Create a new Wordle instance.
     *
     * @param Board $board
     * @param int $maxAttempts
     * @param int $maxLetters
     */
    public function __construct(protected Board $board, protected int $maxAttempts = 6, protected int $maxLetters = 5)
    {
        $this->allowedWords = collect(require app_path('words/allowed.php'));

        $this->attempts = collect();

        $this->board
            ->setMaxAttempts($this->maxAttempts)
            ->setMaxLetters($this->maxLetters);
    }

    /**
     * Play the game.
     *
     * @param Word $answer
     *
     * @return void
     */
    public function play(Word $answer): void
    {
        while ($this->attempts->count() < $this->maxAttempts) {
            $this->board->render($this->attempts);

            $attempt = $this->readAttempt()->compare($answer);
            $this->attempts->push($attempt);

            if ($attempt->matches($answer)) {
                $this->won($answer);

                return;
            }
        }

        $this->lost($answer);
    }

    /**
     * Finish the game with the win message.
     *
     * @param Word $answer
     *
     * @return void
     */
    protected function won(Word $answer): void
    {
        $message = match ($this->attempts->count()) {
            5, 6 => 'Phew, you got it!',
            default => 'You got it!',
        };

        $this->board->render($this->attempts);

        render(
            "<div class='ml-1 text-center text-green-500'>{$message} The word was '{$answer}'.</div>"
        );
    }

    /**
     * Finish the game with the lose message.
     *
     * @param Word $answer
     *
     * @return void
     */
    protected function lost(Word $answer): void
    {
        $this->board->render($this->attempts);

        render(
            "<div class='ml-1 text-center text-red-400'>You lost! The word was '{$answer}'.</div>"
        );
    }

    /**
     * Read the current attempt from the user.
     *
     * @return Word
     */
    protected function readAttempt(): Word
    {
        $attempt = '';

        while (true) {
            $char = $this->readCharacter();

            if ($this->isCharacterValid($char) && strlen($attempt) < $this->maxLetters) {
                $attempt .= $char;
            } elseif ($this->isDeleting($char)) {
                $attempt = substr($attempt, 0, -1);
            } elseif ($this->isAttempting($attempt, $char)) {
                if ($this->allowedWords->contains($attempt)) {
                    return new Word($attempt);
                }
            }

            $this->board->render($this->attempts, $attempt);
        }
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
