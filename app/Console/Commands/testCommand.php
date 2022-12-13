<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
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
        // $msg = FoodSupplier::getByCategories('2022-12-12');
        $text = 'ывафыапвап text вапвыаыврвп sdgdf hfg hfhdfgh';
        $msg = FoodSupplier::getStringWithShift($text, 5);
        echo $msg;
        return Command::SUCCESS;
    }
}
