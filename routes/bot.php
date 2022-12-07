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
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\TelegramLog;


Route::post('/', function (Request $request) {
    Log::error('sdf');
    $telegram = new Telegram(env('BOT_TOKEN'), 'DevelopBot');
    TelegramLog::initialize(Log::stack(['single']), Log::stack(['single']));
    TelegramLog::notice('here');

    $telegram->setUpdateData();
    $update = $telegram->getUpdate();

    $channelPost = $update->getChannelPost();
    if ($channelPost) {
        return;
    }

    $chat = $update->getMessage()->getChat();
    $from = $update->getMessage()->getFrom();
    $userId = $from->getId();
    $userName = $from->getUsername();
    $userFirstName = $from->getFirstName();
    $userLastName  = $from->getLastName();

    $chatId = $chat->getId();
    $chatType = $chat->getType();
    $chatName = $chat->getUsername() ? $chat->getUsername() : $chat->getTitle();

    $user = User::query()->firstOrCreate([
        'telegramId' => $userId,
        'telegramName' => $userName,
        'firstName' => $userFirstName,
        'lastName' => $userLastName
    ]);

    $chat = Chat::query()->firstOrCreate([
        'telegramId' => $chatId,
        'type' => $chatType,
        'name' => $chatName,
    ]);

    Log::info($user);
    Log::info($chat);

    // Получить юзера

    if ($telegram->haveDialog()) {
        $telegram->handleDialog();
    } else {
        $telegram->handle();
    }
});
