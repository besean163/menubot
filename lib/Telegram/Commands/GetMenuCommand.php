<?php

namespace lib\Telegram\Commands;

use App\Models\Dish;
use App\Models\FoodSupplier;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Commands\UserCommand;
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


		$chatId = $this->getUpdate()->getMessage()->getChat()->getId();

		$foodSuppliers = FoodSupplier::all();
		$foodSuppliers = FoodSupplier::query()->where('id', 1)->get();
		$date = '2022-12-07';
		$msg = '';

		foreach ($foodSuppliers as $foodSupplier) {
			$msg .= $foodSupplier->name . "\n";
			$dishes = Dish::query()->where('date', $date)->where('foodSupplierId', $foodSupplier->id)->get();
			// Log::debug($dishes);
			// exit;

			/** @var Dish $dish */
			foreach ($dishes as $dish) {
				$msg .= $dish->getRow() . "\n";
			}
		}
		return Request::sendMessage([
			'chat_id' => $chatId,
			'text' => $msg
		]);
		// return Request::emptyResponse();
	}
}
