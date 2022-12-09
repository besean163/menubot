<?php

namespace lib\Telegram\Dialogs;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use lib\Telegram\Dialogs\GetMenu\GetMenuHandler;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

class Dialog extends Model
{
	use HasFactory;

	const DIALOG_TYPE_GET_MENU = 'get_menu';

	const DIALOG_STATUS_WAIT = 'wait';
	const DIALOG_STATUS_DONE = 'done';

	protected $fillable = [
		'userId',
		'chatId',
		'type',
		'status'
	];

	protected $attributes = [
		'actions' => null,
	];

	public function wait(): void
	{
		$this->status = self::DIALOG_STATUS_WAIT;
		$this->save();
	}


	public function done(): void
	{
		$this->status = self::DIALOG_STATUS_DONE;
		$this->save();
	}

	public function handle(Update $update): bool
	{
		$handler = match ($this->type) {
			self::DIALOG_TYPE_GET_MENU => new GetMenuHandler($this, $update),
			default => throw new Exception("Unknown type: '{$this->type}'"),
		};

		/** @var DialogHandler $handler */
		$handler->run();
		return true;
	}
}
