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
	public User $user;
	public Chat $chat;
	public ?Dialog $dialog = null;

	protected $command_classes = [
		Command::AUTH_USER   => [
			'start' => StartCommand::class,
			'get_menu' => GetMenuCommand::class
		],
		Command::AUTH_ADMIN  => [],
		Command::AUTH_SYSTEM => [
			self::GENERIC_MESSAGE_COMMAND => GenericmessageCommand::class
		],
	];

	public function getUpdate(): Update
	{
		return $this->update;
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getChat(): Chat
	{
		return $this->chat;
	}

	public function handle(): bool
	{
		if (!$this->setSourceData()) {
			return false;
		}

		$channelPost = $this->update->getChannelPost();
		if ($channelPost) {
			return false;
		}

		if ($this->dialog) {
			return $this->dialog->handle($this->getUpdate());
		} else {
			return $this->simpleCommandHandle();
		}
	}

	public function setSourceData(): bool
	{
		$this->setUpdate();
		if (!$this->setUser()) {
			return false;
		}
		$this->setChat();
		$this->setDialog();
		return true;
	}

	private function setUpdate(): void
	{
		if ($this->update && $this->last_update_id) {
			return;
		}

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

		$update = new Update($post, $this->bot_username);
		$this->update         = $update;
		$this->last_update_id = $update->getUpdateId();
	}

	protected function setUser(): bool
	{
		$message  = $this->update->getMessage() ? $this->update->getMessage() : $this->update->getCallbackQuery();

		$from = $message->getFrom();
		if ($from->getIsBot()) {
			return false;
		}

		$userId = $from->getId();
		$userName = $from->getUsername();
		$userFirstName = $from->getFirstName();
		$userLastName  = $from->getLastName();

		$this->user = User::query()->firstOrCreate([
			'telegramId' => $userId,
			'telegramName' => $userName,
			'firstName' => $userFirstName,
			'lastName' => $userLastName
		]);

		return true;
	}

	protected function setChat(): void
	{
		$message  = $this->update->getMessage() ? $this->update->getMessage() : $this->update->getCallbackQuery()->getMessage();

		$chat = $message->getChat();
		$chatId = $chat->getId();
		$chatType = $chat->getType();
		$chatName = $chat->getUsername() ? $chat->getUsername() : $chat->getTitle();

		$this->chat = Chat::query()->firstOrCreate([
			'telegramId' => $chatId,
			'type' => $chatType,
			'name' => $chatName,
		]);
	}

	protected function setDialog(): void
	{
		$this->dialog = Dialog::query()
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
}
