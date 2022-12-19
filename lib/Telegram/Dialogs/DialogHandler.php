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

	public function getChat(): Chat
	{
		return Chat::query()->firstWhere('id', $this->dialog->chatId);
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


			if ($actionData === null) {
				$action = $actionClass::makeNew($this, $key);
				$action->firstLaunch();
			} else {
				$action = $actionClass::makeByConfig($this, $actionData);
				$action->handle($this->update);
			}
			array_push($resultActions, $action->toArray());

			$this->dialog->actions = json_encode($resultActions);
			$this->dialog->save();

			if (!$action->isFinished()) {
				$dialogEnd = false;
				break;
			}
		}


		if ($dialogEnd) {
			// $this->sendResult();
			$this->dialog->done();
		}
	}

	public function getActionsData(): array
	{
		Log::debug($this->dialog->actions);
		return json_decode($this->dialog->actions, true) ?? [];
	}

	abstract public function getActionMap(): array;

	abstract public function sendResult(): void;
}
