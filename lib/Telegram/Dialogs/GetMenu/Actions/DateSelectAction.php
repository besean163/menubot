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

class DateSelectAction extends Action
{
	public static function type(): string
	{
		return 'date_select';
	}

	private function needThisWeek(): bool
	{
		$todayWeekDay = Date::today()->getWeekDay();
		if ($todayWeekDay == 7) {
			return false;
		}
		return true;
	}

	protected function getValidValues(): array
	{
		$dates = $this->needThisWeek() ? Date::getThisWeekWorkDays() : Date::getNextWeekWorkDays();
		$result = [];
		foreach ($dates as $date) {
			$cyrWeekDay = (new Date($date))->getCyrillicWeekDay();
			$result[$date] = sprintf("%s (%s)", $cyrWeekDay, $date);
		}
		return $result;
	}

	protected function sendMessage(): void
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
		$this->prev_message_id = $result->getMessageId();
	}
}
