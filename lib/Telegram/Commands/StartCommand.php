<?php

namespace lib\Telegram\Commands;

use Illuminate\Support\Facades\Log;
use lib\Telegram\UserCommand;
use Longman\TelegramBot\Commands\UserCommands\StartCommand as UserCommandsStartCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class StartCommand extends UserCommand
{
	/**
	 * @var string
	 */
	protected $name = 'start';

	/**
	 * @var string
	 */
	protected $description = 'Start command';

	/**
	 * @var string
	 */
	protected $usage = '/start';

	/**
	 * @var string
	 */
	protected $version = '1.2.0';

	public function execute(): ServerResponse
	{
		$message = $this->update->getMessage();
		$userName = $message->getFrom()->getUsername();
		$chatId = $this->getUpdate()->getMessage()->getChat()->getId();
		Log::info("User '{$userName}' start.");
		return Request::sendMessage([
			'chat_id' => $chatId,
			'text' => 'Привет. Вы добавлены.'
		]);
	}
}
