<?php

use App\Models\Chat;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use lib\Telegram\Telegram;
use Longman\TelegramBot\TelegramLog;


Route::post('/', function (Request $request) {
    Log::error('WORK!');
    $telegram = new Telegram(env('BOT_TOKEN'), 'DevelopBot');
    $telegram->handle();
});
