<?php

namespace lib\Telegram\Dialogs;

use App\Models\Chat;
use Exception;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Update;

abstract class DialogHandler
{
	public Dialog $dialog;
	public Update $update;

	public function __construct(Dialog $dialog, Update $update)
	{
		$this->dialog = $dialog;
		$this->update = $update;
	}

	public function run(): void
	{
		Log::debug("Dialog handled.");

		// Получаем данные действий которые были в диалоге
		$actionsData = $this->getActionsData();
		// Получаем конфигурацию действий для диалога
		$actionMap = $this->getActionMap();
		$resultActions = [];

		$dialogEnd = true;

		/** @var Action $actionClass */
		foreach ($actionMap as $key => $actionClass) {
			$actionData = $actionsData[$key] ?? null;
			$chat = Chat::query()->firstWhere('id', $this->dialog->chatId);


			if ($actionData === null) {
				$action = $actionClass::makeNew($chat->telegramId);
				$action->firstLaunch();
			} else {
				$action = $actionClass::makeByConfig($chat->telegramId, $actionData);
				$action->handle($this->update);
			}
			array_push($resultActions, $action->toArray());


			if (!$action->isFinished()) {
				$dialogEnd = false;
				break;
			}
		}

		$this->dialog->actions = $resultActions;
		$this->dialog->save();

		if ($dialogEnd) {
			$this->sendResult();
			$this->dialog->done();
		}

		/*
				если же данные не пустые то:
				Проходим по конфигурации действий
				если в массиве данных диалога есть действие подходящее под конфигу, создаем
				Проверяем не обработано ли оно уже
					если да то:
					то переходим к следующему
					если следующего нет то нужно создать
					если нет то 
					обрабатываем по обновлению
					если обработка состоялась идем к следующему
				
				Если обработаны все действия:
				собираем данные с действий
				выдаем окончательный результат
				меняем статус диалога как done

			*/
	}

	public function getActionsData(): array
	{
		return json_decode($this->dialog->actions, true) ?? [];
	}

	abstract public function getActionMap(): array;

	abstract public function sendResult(): void;
}
