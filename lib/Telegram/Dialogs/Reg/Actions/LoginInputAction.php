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

class LoginInputAction extends Action
{
	public static function type(): string
	{
		return 'login_input';
	}

	protected function getValidValues(): array
	{
		// $weekDates = $this->needThisWeek() ? Date::getThisWeekWorkDays() : Date::getNextWeekWorkDays();

		// $today = Date::today();
		// $result = [];
		// foreach ($weekDates as $date) {
		// 	$isToday = $date === $today->getDateISO();
		// 	if ($isToday) {
		// 		$cyrWeekDay = "Сегодня";
		// 	} else {
		// 		$cyrWeekDay = (new Date($date))->getCyrillicWeekDay();
		// 	}
		// 	$text = sprintf("%s (%s)", $cyrWeekDay, $date);
		// 	if ($isToday) {
		// 		$text = sprintf("\u{2755} %s \u{2755}", $text);
		// 	}
		// 	$result[$date] = $text;
		// }
		// return $result;
		return [];
	}

	protected function ask(): void
	{
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
			'text' => 'Выберите дату:',
			'reply_markup' => [
				'inline_keyboard' => $keyboard
			],
		]);

		/** @var Message $result */
		$result = $response->getResult();
		$this->sended_message_ids[] = $result->getMessageId();
	}
}
