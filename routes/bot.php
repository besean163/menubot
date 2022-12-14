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
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\TelegramLog;


Route::post('/', function (Request $request) {
	/*
		Кто может пользоваться:
		 - не бот
		 - запрос из приватного чата
	 */
	Log::error('WORK!');
	// Log::error('Работает!');
	// Log::alert($request::post());

	$telegram = new Telegram(env('BOT_TOKEN'), 'DevelopBot');
	TelegramLog::initialize(null, Log::stack(['single']));
	$telegram->process();
});
