<?php

namespace lib\Telegram\Dialogs;

use Exception;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Longman\TelegramBot\Entities\Update;

abstract class Action
{
	public string $type;
	public string $status;
	public ?string $result = null;
	public ?string $prev_message_id = null;
	public array $service_message_ids = [];
	protected string $chatId;

	const STATUS_WAIT = 'wait';
	const STATUS_FINISH = 'finish';

	public function __construct(string $chatId, string $type, string $status, ?string $result = null, ?string $prev_message_id = null, array $service_message_ids = [])
	{
		$this->chatId = $chatId;
		$this->type = $type;
		$this->setStatus($status);
		$this->result = $result;
		$this->prev_message_id = $prev_message_id;
		$this->service_message_ids = $service_message_ids;
	}

	public function setStatus(string $status): void
	{
		if (
			$status !== self::STATUS_WAIT
			&& $status !== self::STATUS_FINISH
		) {
			throw new Exception("Unknown status: {$status}.");
		}
		$this->status =  $status;
	}

	abstract public static function type(): string;

	public static function makeNew(string $chatId): static
	{
		return new static($chatId, static::type(), self::STATUS_WAIT);
	}

	public static function makeByConfig(string $chatId, array $config): static
	{
		$type = $config['type'] ?? null;
		$status = $config['status'] ?? null;
		$result = $config['result'] ?? null;
		$prev_message_id = $config['prev_message_id'] ?? null;
		$service_message_ids = $config['service_message_ids'] ?? [];

		if ($type === null || $status === null) {
			throw new Exception("Need type and status data.");
		}

		if ($type !== static::type()) {
			throw new Exception(sprintf("Type not match. (expect: %s, actual:%s).", static::type(), $type));
		}

		return new static($chatId, $type, $status, $result, $prev_message_id, $service_message_ids);
	}

	abstract public function firstLaunch(): void;

	abstract public function handle(Update $update): void;

	public function isFinished(): bool
	{
		return $this->status === self::STATUS_FINISH;
	}

	public function finish(): void
	{
		$this->status = self::STATUS_FINISH;
	}

	public function isWaiting(): bool
	{
		return $this->status === self::STATUS_WAIT;
	}

	public function toArray(): array
	{
		return [
			'type' => $this->type,
			'status' => $this->status,
			'result' => $this->result,
			'prev_message_id' => $this->prev_message_id,
			'service_message_ids' => $this->service_message_ids,
		];
	}
}
