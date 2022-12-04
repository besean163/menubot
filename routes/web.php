<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;

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

    Log::info(Request::method());
    Log::info(Request::url());
    // return view('welcome');
    Log::info('in web.');
    Log::info(Request::all());
    // return [];
    // $client = new Client();
    // $params =
    //     [
    //         RequestOptions::HEADERS => [
    //             "Content-Type" => "application/json"
    //         ],
    //         RequestOptions::BODY => json_encode([
    //             "chat_id" => "275665865",
    //             "text" => "work"
    //         ]),

    //     ];
    // $client->post("https://api.telegram.org/bot1858930058:AAFRaVAE3XyxFsiREylp9WKP-BXDiuZ5cms/sendMessage", $params);
    // return [
    //     "chat_id" => "275665865",
    //     "text" => "work"
    // ];
    // exit;
});
