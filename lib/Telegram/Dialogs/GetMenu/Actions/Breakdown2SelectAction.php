<?php

namespace lib\Telegram\Dialogs\GetMenu\Actions;

use App\Models\Dish;
use Exception;
use Illuminate\Support\Facades\Log;
use lib\Date;
use lib\Telegram\Dialogs\Action;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

class Breakdown2SelectAction extends Action
{
	public static function type(): string
	{
		return 'breakdown_select_2';
	}

	protected function getValidValues(): array
	{
		$prevResults = $this->getPrevActionResults();
		$date = $prevResults[0];
		$breakdown = $prevResults[1];

		$result = [];
		switch ($breakdown) {
			case 'supplier':
				$foodSuppliers = Dish::getFoodSuppliers($date);
				foreach ($foodSuppliers as $foodSupplier) {
					$result[$foodSupplier->id] = $foodSupplier->name;
				}
				break;
			case 'category':
				$categories = Dish::getCategories($date);
				foreach ($categories as $category) {
					$result[$category->id] = $category->name;
				}
				break;
			default:
				throw new Exception("Unknown breakdown: {$breakdown}");
		}

		return $result;
	}

	protected function sendMessage(): void
	{
		$prevResult = $this->getPrevAction()->getResult();

		$question = match ($prevResult) {
			'supplier' => 'Выберите подрядчика:',
			'category' => 'Выберите категорию:',
			default => throw new Exception("Unknown result: {$prevResult}"),
		};

		$values = $this->getValidValues();
		$keyboard = [];
		foreach ($values as $data => $text) {
			$line = [];
			$button = [
				'text' => $text,
				'callback_data' => $data
			];
			array_push($line, $button);
			array_push($keyboard, $line);
		}

		$response =  Request::sendMessage([
			'chat_id' => $this->chatId,
			'text' => $question,
			'reply_markup' => [
				'inline_keyboard' => $keyboard
			],
		]);

		/** @var Message $result */
		$result = $response->getResult();
		$this->prev_message_id = $result->getMessageId();
	}
}
