<?php

namespace lib\Telegram\Dialogs;

use Illuminate\Database\Console\Migrations\StatusCommand;

abstract class Action
{
	public string $type;
	public string $status;
	public ?string $result = null;
	public ?string $message_id = null;

	const STATUS_WAIT = 'wait';
	const STATUS_FINISH = 'finish';

	public function __construct(string $type, string $status, ?string $result = null, ?string $message_id = null)
	{
		$this->type = $type;
		$this->status = $status;
		$this->result = $result;
		$this->message_id = $message_id;
	}

	abstract public static function type(): string;

	public static function new(): self
	{
		return new static(
			static::type(),
			static::STATUS_WAIT
		);
	}

	public function toArray(): array
	{
		return [
			'type' => $this->type,
			'status' => $this->status,
			'result' => $this->result,
			'message_id' => $this->message_id,
		];
	}
}
