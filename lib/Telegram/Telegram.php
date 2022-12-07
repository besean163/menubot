<?php

namespace lib\Telegram;

use lib\Telegram\Commands\GetMenuCommand;
use lib\Telegram\Commands\StartCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram as BaseTelegram;

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
	}
}
