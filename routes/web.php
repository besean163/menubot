<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use lib\Telegram\Telegram;
use Longman\TelegramBot\Request as TelegramBotRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::any('/', function (Request $request) {
    $telegram = new Telegram(env('BOT_TOKEN'), 'DevelopBot');


    $telegram->handle();
    // TelegramBotRequest::initialize($telegram);
    // TelegramBotRequest::sendMessage([
    //     'chat_id' => '275665865',
    //     'text' => 'work'
    // ]);
});
