<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use lib\ObedApi;

class testCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testCommand';

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
        User::where(
            [
                ['id', '>', 1],
                ['id', '<', 5],
            ]
        )->get()->each(function (User $user) {
            echo $user->name . "\n";
        });

        $user = User::find(2);


        $this->showStatus($user, 'name');
        $user->name = 'other_name_1';
        $this->showStatus($user, 'name');
        $user->save();
        $this->showStatus($user, 'name');
        print_r($user->getOriginal());

        // echo $user->name;
        return Command::SUCCESS;
    }

    private function showStatus(User $user, string $prop)
    {
        $status = 'no status';
        if ($user->isDirty($prop)) {
            $status = 'dirty';
        } elseif ($user->isClean($prop)) {
            $status =  'clean';
        }
        if ($user->wasChanged($prop)) {
            $status .= ' and was changed';
        }
        echo $status . "\n";
    }
}
