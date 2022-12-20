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
        $image = imagecreate(500, 500);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 150, 130));
        imagefilledarc(
            $image,
            200,
            200,
            150,
            150,
            0,
            300,
            imagecolorallocate($image, 30, 100, 15),
            IMG_ARC_EDGED
        );
        imagesetthickness($image, 3);
        imagefilledarc(
            $image,
            200,
            200,
            150,
            150,
            0,
            300,
            imagecolorallocate($image, 0, 0, 0),
            IMG_ARC_NOFILL | IMG_ARC_EDGED
        );
        // imagefill($image, 100, 90, imagecolorallocate($image, 50, 50, 50));
        imagejpeg($image, '/home/besean/testJPEG.jpg', 100);
        return Command::SUCCESS;
    }
}
