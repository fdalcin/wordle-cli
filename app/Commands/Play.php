<?php

namespace App\Commands;

use App\Word;
use App\Wordle;
use LaravelZero\Framework\Commands\Command;

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
     * Execute the console command.
     *
     * @param Wordle $wordle
     * @return int
     */
    public function handle(Wordle $wordle): int
    {
        /** @var array<int, string> */
        $answers = require app_path('words/answers.php');

        $answer = new Word(
            collect($answers)->random()
        );

        $wordle->play($answer);

        return 0;
    }
}
