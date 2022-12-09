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

	public function firstLaunch(): void
	{
		$this->sendMessage();
	}

	public function handle(Update $update): void
	{
		$callback = $update->getCallbackQuery();
		$data = null;
		if ($callback) {
			$data = $callback->getData();
		}

		if (!in_array($data, $this->getValidValues()) || !$data) {
			$this->cleanServiceMessages();
			$this->deletePrevMessage();
			$this->sendWarningMessage();
			$this->sendMessage();
		} else {
			$this->deletePrevMessage();
			$this->result = $data;
			$this->prev_message_id = null;
			$this->finish();
			$this->cleanServiceMessages();
			// $this->sendSuccessMessage();
		}
	}

	private function needThisWeek(): bool
	{
		$todayWeekDay = Date::today()->getWeekDay();
		if ($todayWeekDay == 7) {
			return false;
		}
		return true;
	}

	private function sendWarningMessage(): void
	{
		$response = Request::sendMessage([
			'chat_id' => $this->chatId,
			'text' => "Пожалуйста выберите значение из предложенных.",
		]);

		/** @var Message $result */
		$result = $response->getResult();
		$this->service_message_ids[] = $result->getMessageId();
	}

	private function sendSuccessMessage(): void
	{
		Request::sendMessage([
			'chat_id' => $this->chatId,
			'text' => "Значение принято.",
		]);
	}

	private function deletePrevMessage(): void
	{
		Request::deleteMessage([
			'chat_id' => $this->chatId,
			'message_id' => $this->prev_message_id,
		]);
	}

	private function cleanServiceMessages(): void
	{
		foreach ($this->service_message_ids as $serviceMessageId) {
			Request::deleteMessage([
				'chat_id' => $this->chatId,
				'message_id' => $serviceMessageId,
			]);
		}
	}

	private function getValidValues(): array
	{
		return $this->needThisWeek() ? Date::getThisWeekWorkDays() : Date::getNextWeekWorkDays();
	}

	private function sendMessage(): void
	{
		$values = $this->getValidValues();
		$keyboard = [];
		foreach ($values as $value) {
			$line = [];
			$button = [
				'text' => $value,
				'callback_data' => $value
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
