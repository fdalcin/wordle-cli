<?php

namespace App;

use App\Enums\CompareType;
use App\Enums\Keys;
use Illuminate\Support\Collection;
use function Termwind\render;

class Wordle
{
    /**
     * The list of words allowed to be used.
     *
     * @var Collection<int, string>
     */
    protected Collection $allowedWords;

    /**
     * The list of words attempted in the game.
     *
     * @var Collection<int, Word>
     */
    protected Collection $attempts;

    /**
     * The list of letters that were guessed in the game.
     *
     * @var Collection<string, CompareType|null>
     */
    protected Collection $guessedLetters;

    /**
     * Create a new Wordle instance.
     *
     * @param Board $board
     * @param int $maxAttempts
     * @param int $maxLetters
     */
    public function __construct(protected Board $board, protected int $maxAttempts = 6, protected int $maxLetters = 5)
    {
        /** @var array<int, string> */
        $allowed = require app_path('words/allowed.php');

        $this->allowedWords = collect($allowed);

        $this->attempts = collect(); // @phpstan-ignore-line

        $this->guessedLetters = collect(); // @phpstan-ignore-line

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
            $this->board->render($this->attempts, $this->guessedLetters);

            $attempt = $this->readAttempt()->compare($answer);
            $this->attempts->push($attempt);
            $this->updateGuessedLetters($attempt);

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
            1 => 'Genius',
            2 => 'Magnificent',
            3 => 'Impressive',
            4 => 'Splendid',
            5 => 'Great',
            6 => 'Phew',
            default => '',
        };

        $this->board->render($this->attempts, $this->guessedLetters);

        render(
            "<div class='ml-1 text-center text-green-500'>{$message}!</div>"
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
        $this->board->render($this->attempts, $this->guessedLetters);

        render(
            "<div class='ml-1 text-center text-red-400'>You lost! The word was '{$answer}'.</div>"
        );
    }

    /**
     * Update the guessed letters list.
     *
     * @param Word $attempt
     *
     * @retrun void
     */
    protected function updateGuessedLetters(Word $attempt): void
    {
        $attempt->letters()
            ->each(function (string $letter, int $index) use ($attempt) {
                $type = $this->guessedLetters->get($letter);

                if ($type === CompareType::CORRECT) {
                    return;
                }

                $this->guessedLetters->put(
                    $letter,
                    $attempt->comparison()?->get($index) ?? CompareType::ABSENT
                );
            });
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

            $this->board->render($this->attempts, $this->guessedLetters, $attempt);
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
     * @return false|int
     */
    protected function isCharacterValid(string $char): false|int
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
        return in_array(Keys::tryFrom(ord($char)), [Keys::BACKSPACE, Keys::DELETE], true);
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
        return strlen($word) === $this->maxLetters && Keys::tryFRom(ord($char)) === Keys::ENTER;
    }
}
