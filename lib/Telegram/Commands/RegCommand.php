<?php

namespace lib\Telegram\Commands;

use App\Models\ObedUser;
use Illuminate\Support\Facades\Log;
use lib\Telegram\Dialogs\Dialog;
use lib\Telegram\UserCommand;
use Longman\TelegramBot\Commands\UserCommands\StartCommand as UserCommandsStartCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class RegCommand extends UserCommand
{
	/**
	 * @var string
	 */
	protected $name = 'reg';

	/**
	 * @var string
	 */
	protected $description = 'Registration command';

	/**
	 * @var string
	 */
	protected $usage = '/reg';

	/**
	 * @var string
	 */
	protected $version = '1.2.0';

	public function execute(): ServerResponse
	{
		$user = $this->getUser();
		$obedUser = ObedUser::query()->where('userId', $user->id)->get();

		if (!$obedUser) {
			$newDialog = Dialog::query()->create([
				'userId' => $this->getUser()->id,
				'chatId' => $this->getChat()->id,
				'type' => Dialog::DIALOG_TYPE_GET_MENU,
				'status' => Dialog::DIALOG_STATUS_WAIT
			]);

			/** @var Dialog $newDialog */
			$newDialog->handle($this->getUpdate());
		}







		$message = $this->update->getMessage();
		$userName = $message->getFrom()->getUsername();
		$chatId = $this->getUpdate()->getMessage()->getChat()->getId();
		Log::info("User '{$userName}' start.");
		return Request::sendMessage([
			'chat_id' => $chatId,
			'text' => 'Привет. Вы добавлены.'
		]);
	}
}
