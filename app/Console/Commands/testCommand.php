<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
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
        // $msg = FoodSupplier::getBySupplier('2022-12-15', 2);
        // $msg = FoodSupplier::getByCategories('2022-12-15', 20);
        // $text = 'рождество text  приходит hfg hfhdfgh';
        // $msg = Str::explodeStringByLimit($text, 20);
        // echo "\n" . $msg;

        $client = new Client();
        $client->post('https://api.telegram.org/bot1858930058:AAFRaVAE3XyxFsiREylp9WKP-BXDiuZ5cms/sendMessage', [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json'
            ],
            RequestOptions::BODY => json_encode([
                'chat_id' => '275665865',
                'text' => "\u{274c}",
            ])
        ]);
        // print_r($msg);
        return Command::SUCCESS;
    }
}
