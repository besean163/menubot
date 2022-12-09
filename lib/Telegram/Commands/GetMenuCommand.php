<?php

namespace lib\Telegram\Commands;

use App\Models\Dish;
use App\Models\FoodSupplier;
use Illuminate\Support\Facades\Log;
use lib\Telegram\Dialogs\Dialog;
use lib\Telegram\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class GetMenuCommand extends UserCommand
{
	/**
	 * @var string
	 */
	protected $name = 'get_menu';

	/**
	 * @var string
	 */
	protected $description = 'Get menu command';

	/**
	 * @var string
	 */
	protected $usage = '/get_menu';

	/**
	 * @var string
	 */
	protected $version = '1.2.0';

	/**
	 * Command execute method
	 *
	 * @return ServerResponse
	 */
	public function execute(): ServerResponse
	{
		// получить пользователя
		// получить чат
		// создать диалог (про статус не забыть)
		// создать действие
		// сформировать сообщение для пользователя отправить
		// Log::notice('here');

		$newDialog = Dialog::query()->create([
			'userId' => $this->getUser()->id,
			'chatId' => $this->getChat()->id,
			'type' => Dialog::DIALOG_TYPE_GET_MENU,
			'status' => Dialog::DIALOG_STATUS_WAIT
		]);

		/** @var Dialog $newDialog */
		$newDialog->handle($this->getUpdate());



		return Request::emptyResponse();
	}
}
