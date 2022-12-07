<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use lib\Telegram\Telegram;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\TelegramLog;


Route::post('/', function (Request $request) {
    Log::error('sdf');
    $telegram = new Telegram(env('BOT_TOKEN'), 'DevelopBot');
    TelegramLog::initialize(Log::stack(['single']), Log::stack(['single']));
    TelegramLog::notice('here');

    // Log::alert($telegram->getUpdate());

    if ($telegram->haveDialog()) {
        $telegram->handleDialog();
    } else {
        $telegram->handle();
    }
});
