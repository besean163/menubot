<?php

namespace lib\Telegram;

use App\Models\Chat;
use App\Models\User;
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
		return $this->telegram->getUser();
	}

	public function getChat(): Chat
	{
		return $this->telegram->getChat();
	}
}
