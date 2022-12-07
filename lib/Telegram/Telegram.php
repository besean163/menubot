<?php

namespace lib\Telegram;

use App\Models\User;
use Exception;
use lib\Telegram\Commands\GetMenuCommand;
use lib\Telegram\Commands\StartCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram as BaseTelegram;
use Longman\TelegramBot\TelegramLog;

class Telegram extends BaseTelegram
{
	protected $command_classes = [
		Command::AUTH_USER   => [
			'start' => StartCommand::class,
			'get_menu' => GetMenuCommand::class
		],
		Command::AUTH_ADMIN  => [],
		Command::AUTH_SYSTEM => [],
	];

	public function getUpdate(): Update
	{
		return $this->update;
	}

	public function haveDialog(): bool
	{
		return true;
	}

	public function handleDialog(): void
	{
		// получить диалог
		// получить последнее не законченное действие
		// проверить подходит ли ответ пользователя действию
		// передать действию ответ пользователя
	}

	public function getUser(): User
	{
		return new User();
	}

	/**
	 * Handle bot request from webhook
	 *
	 * @return bool
	 *
	 * @throws TelegramException
	 */
	public function handle(): bool
	{
		if ($response = $this->processUpdateCustom()) {
			return $response->isOk();
		}

		return false;
	}

	public function setUpdateData(): void
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

	public function processUpdateCustom(): ServerResponse
	{
		$this->setUpdateData();

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
}
