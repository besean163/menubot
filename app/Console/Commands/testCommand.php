<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use lib\ObedApi;
use lib\Utils\Str;

class testCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testCommand';

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
        $msg = FoodSupplier::getBySupplier('2022-12-12', 1);
        // $text = 'рождество text  приходит hfg hfhdfgh';
        // $msg = Str::explodeStringByLimit($text, 20);
        echo "\n" . $msg;
        // print_r($msg);
        return Command::SUCCESS;
    }
}
