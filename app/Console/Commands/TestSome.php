<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use Illuminate\Console\Command;

class TestSome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testsome';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скрипт для тестирования чего нибудь.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $foodSupplier = FoodSupplier::create([
            'name' => 'testFoodSupplier',
            'sourceId' => 'someId',
        ]);
        $foodSupplier->save();
        return Command::SUCCESS;
    }
}
