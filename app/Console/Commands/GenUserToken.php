<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user_id = $this->ask('input user_id');
        if ($user = User::find($user_id)) {
            if ($token = auth('api')->login($user)) {
                echo $token . PHP_EOL;
            } else {
                echo '401';
            }
        } else {
            echo 'user not exist';
        }
    }
}
