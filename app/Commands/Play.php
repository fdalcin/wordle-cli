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
     * @return mixed
     */
    public function handle(Wordle $wordle)
    {
        $answer = new Word(
            collect(require app_path('words/answers.php'))->random()
        );

        $wordle->play($answer);

        return 0;
    }
}
