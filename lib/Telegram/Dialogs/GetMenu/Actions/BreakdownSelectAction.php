<?php

namespace lib\Telegram\Dialogs\GetMenu\Actions;

use Exception;
use Illuminate\Support\Facades\Log;
use lib\Date;
use lib\Telegram\Dialogs\Action;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

class BreakdownSelectAction extends Action
{
	public static function type(): string
	{
		return 'breakdown_select';
	}

	public function firstLaunch(): void
	{
		$this->sendMessage();
	}

	protected function getValidValues(): array
	{
		return [
			'supplier' => 'По ресторанам',
			'category' => 'По категориям',
		];
	}

	protected function sendMessage(): void
	{
		$values = $this->getValidValues();
		$keyboard = [];
		$line = [];
		foreach ($values as $data => $text) {
			$button = [
				'text' => $text,
				'callback_data' => $data
			];
			array_push($line, $button);
		}
		array_push($keyboard, $line);

		$response =  Request::sendMessage([
			'chat_id' => $this->chatId,
			'text' => 'Как рабить данные?',
			'reply_markup' => [
				'inline_keyboard' => $keyboard
			],
		]);

		/** @var Message $result */
		$result = $response->getResult();
		$this->prev_message_id = $result->getMessageId();
	}
}
