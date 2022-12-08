<?php

namespace lib\Telegram\Dialogs;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use lib\Telegram\Dialogs\GetMenu\GetMenuHandler;
use Longman\TelegramBot\Request;

class Dialog extends Model
{
	use HasFactory;

	const DIALOG_TYPE_GET_MENU = 'get_menu';

	const DIALOG_STATUS_WAIT = 'wait';

	protected $fillable = [
		'userId',
		'chatId',
		'type',
		'status'
	];

	protected $attributes = [
		'actions' => null,
	];

	protected function wait(): void
	{
		$this->status = self::DIALOG_STATUS_WAIT;
	}

	public function handle(): bool
	{
		$handler = match ($this->type) {
			self::DIALOG_TYPE_GET_MENU => new GetMenuHandler($this),
			default => throw new Exception("Unknown type: '{$this->type}'"),
		};
		$handler->run();
		return true;
	}
}
