<?php

namespace lib\Telegram;

use App\Models\Chat;
use App\Models\User;
use Exception;
use lib\Telegram\Telegram;
use Longman\TelegramBot\Commands\Command as CommandsCommand;
use Longman\TelegramBot\Entities\Update;

abstract class Command extends CommandsCommand
{
	/**
	 * Telegram object
	 *
	 * @var Telegram
	 */
	protected $telegram;

	public function __construct(Telegram $telegram, ?Update $update = null)
	{
		parent::__construct($telegram, $update);
	}

	public function getUser(): User
	{
		$user = $this->telegram->getUser();
		if (!$user) {
			throw new Exception("User wasn't set.");
		}
		return $user;
	}

	public function getChat(): Chat
	{
		$chat = $this->telegram->getChat();
		if (!$chat) {
			throw new Exception("Chat wasn't set.");
		}
		return $chat;
	}
}
