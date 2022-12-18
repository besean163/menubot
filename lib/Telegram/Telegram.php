<?php

namespace lib\Telegram;

use App\Models\Chat;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use lib\Telegram\Commands\SystemCommands\GenericmessageCommand;
use lib\Telegram\Commands\GetMenuCommand;
use lib\Telegram\Commands\StartCommand;
use lib\Telegram\Commands\SystemCommand;
use lib\Telegram\Commands\SystemCommands\GenericCommand;
use lib\Telegram\Dialogs\Dialog;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram as BaseTelegram;
use Longman\TelegramBot\TelegramLog;

class Telegram extends BaseTelegram
{
	public ?User $user = null;
	public ?Chat $chat = null;
	public ?Dialog $dialog = null;

	protected $command_classes = [
		Command::AUTH_USER   => [
			'start' => StartCommand::class,
			'get_menu' => GetMenuCommand::class
		],
		Command::AUTH_ADMIN  => [],
		Command::AUTH_SYSTEM => [
			self::GENERIC_COMMAND => GenericCommand::class,
			self::GENERIC_MESSAGE_COMMAND => GenericmessageCommand::class
		],
	];

	public function getUpdate(): Update
	{
		return $this->update;
	}

	public function process(): void
	{
		$this->setUpdate();
		if (!$this->isAvalibleUpdateType()) {
			return;
		}

		// устанавливаем пользователя
		$user = $this->getUser();
		if (!$user) {
			return;
		}
		$this->setUser($user);

		// устанавливаем чат
		$chat = $this->getChat();
		if (!$chat) {
			return;
		}
		$this->setChat($chat);

		// Проверяем наличие диалога, если есть то обрабатываем его
		$dialog = $this->getDialog();
		if ($dialog) {
			$dialog->handle($this->update);
		} else {
			$this->simpleCommandHandle();
		}
	}

	private function setUpdate(): void
	{
		if ($this->update && $this->last_update_id) {
			return;
		}
		$update = $this->getInputUpdate();

		$this->update         = $update;
		$this->last_update_id = $update->getUpdateId();
	}

	public function getInputUpdate(): Update
	{
		if ($this->bot_username === '') {
			throw new TelegramException('Bot Username is not defined!');
		}

		$input = Request::getInput();
		if (empty($input)) {
			throw new TelegramException('Input is empty! The webhook must not be called manually, only by Telegram.');
		}

		// Log update.
		TelegramLog::update($input);

		$post = json_decode($input, true);
		if (empty($post)) {
			throw new TelegramException('Invalid input JSON! The webhook must not be called manually, only by Telegram.');
		}

		return new Update($post, $this->bot_username);
	}


	public function getUser(): ?User
	{
		if ($this->user) {
			return $this->user;
		}

		$message  = $this->update->getMessage() ? $this->update->getMessage() : $this->update->getCallbackQuery();

		$from = $message->getFrom();
		// с ботами не работаем, не создаем юзера
		if ($from->getIsBot()) {
			return null;
		}

		$userId = $from->getId();
		$userName = $from->getUsername();
		$userFirstName = $from->getFirstName();
		$userLastName  = $from->getLastName();

		return User::query()->firstOrCreate([
			'telegramId' => $userId,
			'telegramName' => $userName,
			'firstName' => $userFirstName,
			'lastName' => $userLastName
		]);
	}

	protected function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function getChat(): ?Chat
	{
		if ($this->chat) {
			return $this->chat;
		}

		$message  = $this->update->getMessage() ? $this->update->getMessage() : $this->update->getCallbackQuery()->getMessage();
		if (!$message) {
			return null;
		}

		$telChat = $message->getChat();

		// работаем только в приватных чатах
		if (!$telChat->isPrivateChat()) {
			return null;
		}

		$chatId = $telChat->getId();
		$chatType = $telChat->getType();
		$chatName = $telChat->getUsername() ? $telChat->getUsername() : $telChat->getTitle();

		return Chat::query()->firstOrCreate([
			'telegramId' => $chatId,
			'type' => $chatType,
			'name' => $chatName,
		]);
	}

	protected function setChat(Chat $chat): void
	{
		$this->chat = $chat;
	}

	protected function getDialog(): ?Dialog
	{
		return Dialog::query()
			->where('userId', $this->user->id)
			->where('chatId', $this->chat->id)
			->where('status', '!=', Dialog::DIALOG_STATUS_DONE)
			->first();
	}

	private function simpleCommandHandle(): bool
	{
		if ($response = $this->processUpdateCustom()) {
			return $response->isOk();
		}

		return false;
	}

	public function processUpdateCustom(): ServerResponse
	{
		if (is_callable($this->update_filter)) {
			$reason = 'Update denied by update_filter';
			try {
				$allowed = (bool) call_user_func_array($this->update_filter, [$this->update, $this, &$reason]);
			} catch (Exception $e) {
				$allowed = false;
			}

			if (!$allowed) {
				TelegramLog::debug($reason);
				return new ServerResponse(['ok' => false, 'description' => 'denied']);
			}
		}

		//Load admin commands
		if ($this->isAdmin()) {
			$this->addCommandsPath(TB_BASE_COMMANDS_PATH . '/AdminCommands', false);
		}

		//Make sure we have an up-to-date command list
		//This is necessary to "require" all the necessary command files!
		$this->commands_objects = $this->getCommandsList();

		//If all else fails, it's a generic message.
		$command = self::GENERIC_MESSAGE_COMMAND;

		$update_type = $this->update->getUpdateType();
		if ($update_type === 'message') {
			$message = $this->update->getMessage();
			$type    = $message->getType();

			// Let's check if the message object has the type field we're looking for...
			$command_tmp = $type === 'command' ? $message->getCommand() : $this->getCommandFromType($type);
			// ...and if a fitting command class is available.
			$command_obj = $command_tmp ? $this->getCommandObject($command_tmp) : null;

			// Empty usage string denotes a non-executable command.
			// @see https://github.com/php-telegram-bot/core/issues/772#issuecomment-388616072
			if (
				($command_obj === null && $type === 'command')
				|| ($command_obj !== null && $command_obj->getUsage() !== '')
			) {
				$command = $command_tmp;
			}
		} elseif ($update_type !== null) {
			$command = $this->getCommandFromType($update_type);
		}

		//Make sure we don't try to process update that was already processed
		$last_id = DB::selectTelegramUpdate(1, $this->update->getUpdateId());
		if ($last_id && count($last_id) === 1) {
			TelegramLog::debug('Duplicate update received, processing aborted!');
			return Request::emptyResponse();
		}

		DB::insertRequest($this->update);
		return $this->executeCommand($command);
	}

	public function getCommandObject(string $command, string $filepath = ''): ?Command
	{
		if (isset($this->commands_objects[$command])) {
			return $this->commands_objects[$command];
		}

		$which = [Command::AUTH_SYSTEM];
		// $this->isAdmin() && $which[] = Command::AUTH_ADMIN;
		$which[] = Command::AUTH_USER;

		foreach ($which as $auth) {
			$command_class = $this->getCommandClassName($auth, $command, $filepath);

			if ($command_class) {
				$command_obj = new $command_class($this, $this->update);

				if ($auth === Command::AUTH_SYSTEM && $command_obj instanceof SystemCommand) {
					return $command_obj;
				}
				// if ($auth === Command::AUTH_ADMIN && $command_obj instanceof AdminCommand) {
				// 	return $command_obj;
				// }
				if ($auth === Command::AUTH_USER && $command_obj instanceof UserCommand) {
					return $command_obj;
				}
			}
		}
		return null;
	}

	private function isAvalibleUpdateType(): bool
	{
		$avalibleTypes = [
			Update::TYPE_MESSAGE,
			Update::TYPE_CALLBACK_QUERY
		];

		$updateType = $this->update->getUpdateType();
		if ($updateType && in_array($updateType, $avalibleTypes)) {
			return true;
		}
		return false;
	}
}
