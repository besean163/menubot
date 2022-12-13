<?php

namespace lib\Telegram\Dialogs\GetMenu;

use App\Models\FoodSupplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use lib\Telegram\Dialogs\DialogHandler;
use lib\Telegram\Dialogs\GetMenu\Actions\BreakdownSelectAction;
use lib\Telegram\Dialogs\GetMenu\Actions\DateSelectAction;
use Longman\TelegramBot\Request;

class GetMenuHandler extends DialogHandler
{
	use HasFactory;

	public function getActionMap(): array
	{
		return [
			DateSelectAction::class,
			BreakdownSelectAction::class,
		];
	}

	public function sendResult(): void
	{
		$chat = $this->getChat();
		$actionsData = $this->getActionsData();
		$results = [];
		foreach ($this->getActionMap() as $key => $actionClass) {
			$actionData = $actionsData[$key];
			$action = $actionClass::makeByConfig($chat->telegramId, $actionData);
			$results[] = $action->getResult();
		}


		$date = $results[0];
		$breakdown = $results[1];
		$menu = FoodSupplier::getMenu($date, $breakdown);
		Request::sendMessage([
			'chat_id' => $chat->telegramId,
			'text' => $menu,
			'parse_mode' => 'html',
		]);
	}
}
