<?php

namespace lib\Telegram\Commands;

use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Commands\UserCommands\StartCommand as UserCommandsStartCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class StartCommand extends UserCommandsStartCommand
{
	public function execute(): ServerResponse
	{
		Log::info($this->getUpdate()->getMessage()->getChat()->getId());
		return Request::sendMessage([
			'chat_id' => '275665865',
			'text' => 'work'
		]);
	}
}
