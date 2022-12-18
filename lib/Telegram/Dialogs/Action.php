<?php

namespace lib\Telegram\Dialogs;

use Exception;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

abstract class Action
{
	public DialogHandler $parentHandler;
	public int $id;
	public string $type;
	public string $status;
	public ?string $result = null;
	public ?string $prev_message_id = null;
	public array $service_message_ids = [];
	protected string $chatId;

	const STATUS_WAIT = 'wait';
	const STATUS_FINISH = 'finish';

	public function __construct(DialogHandler $handler, int $id, string $type, string $status, ?string $result = null, ?string $prev_message_id = null, array $service_message_ids = [])
	{
		$this->parentHandler = $handler;
		$this->id = $id;
		$this->chatId = $handler->getChat()->telegramId;
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

	public static function makeNew(DialogHandler $handler, int $id): static
	{
		return new static($handler, $id, static::type(), self::STATUS_WAIT);
	}

	public static function makeByConfig(DialogHandler $handler, array $config): static
	{
		$id = $config['id'] ?? null;
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

		return new static($handler, $id, $type, $status, $result, $prev_message_id, $service_message_ids);
	}

	public function firstLaunch(): void
	{
		$this->sendMessage();
	}

	public function handle(Update $update): void
	{
		if ($this->isFinished()) {
			return;
		}

		$callback = $update->getCallbackQuery();
		$data = null;
		if ($callback) {
			$data = $callback->getData();
		}

		if (!in_array($data, array_keys($this->getValidValues())) || !$data) {
			$this->cleanServiceMessages();
			$this->deletePrevMessage();
			$this->sendWarningMessage();
			$this->sendMessage();
		} else {
			$this->deletePrevMessage();
			$this->result = $data;
			$this->prev_message_id = null;
			$this->finish();
			$this->cleanServiceMessages();
		}
	}

	public function getResult(): string
	{
		return $this->result;
	}

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
			'id' => $this->id,
			'type' => $this->type,
			'status' => $this->status,
			'result' => $this->result,
			'prev_message_id' => $this->prev_message_id,
			'service_message_ids' => $this->service_message_ids,
		];
	}

	protected function cleanServiceMessages(): void
	{
		foreach ($this->service_message_ids as $serviceMessageId) {
			Request::deleteMessage([
				'chat_id' => $this->chatId,
				'message_id' => $serviceMessageId,
			]);
		}
		$this->service_message_ids = [];
	}

	protected function sendWarningMessage(): void
	{
		$response = Request::sendMessage([
			'chat_id' => $this->chatId,
			'text' => "Пожалуйста выберите значение из предложенных.",
		]);

		/** @var Message $result */
		$result = $response->getResult();
		$this->service_message_ids[] = $result->getMessageId();
	}

	abstract protected function getValidValues(): array;

	protected function deletePrevMessage(): void
	{
		Request::deleteMessage([
			'chat_id' => $this->chatId,
			'message_id' => $this->prev_message_id,
		]);
	}
	abstract protected function sendMessage(): void;

	protected function getPrevAction(): Action
	{
		if ($this->isFirstAction()) {
			throw new Exception("Can't get previous action. It action is first.");
		}
		$prevActionId = $this->id - 1;
		$actionClasses = $this->parentHandler->getActionMap();

		/** @var static $prevActionClass */
		$prevActionClass = $actionClasses[$prevActionId] ?? null;
		if (!$prevActionClass) {
			throw new Exception("Action class doesn't exist.");
		}

		$actionsData = $this->parentHandler->getActionsData();

		$prevActionData = $actionsData[$prevActionId] ?? null;
		if (!$prevActionData) {
			throw new Exception("Action doesn't exist yet.");
		}

		return $prevActionClass::makeByConfig($this->parentHandler, $prevActionData);
	}

	protected function getPrevActionResults(): array
	{
		if ($this->isFirstAction()) {
			throw new Exception("Can't get previous action. It action is first.");
		}
		$results = [];
		// $prevActionId = $this->id - 1;
		$actionClasses = $this->parentHandler->getActionMap();
		$actionsData = $this->parentHandler->getActionsData();

		for ($prevActionId = 0; $prevActionId < $this->id; $prevActionId++) {
			$prevActionData = $actionsData[$prevActionId] ?? null;
			if (!$prevActionData) {
				throw new Exception("Action doesn't exist yet.");
			}

			/** @var static $prevActionClass */
			$prevActionClass = $actionClasses[$prevActionId] ?? null;
			if (!$prevActionClass) {
				throw new Exception("Action class doesn't exist.");
			}
			$prevAction = $prevActionClass::makeByConfig($this->parentHandler, $prevActionData);
			$results[] = $prevAction->getResult();
		}

		return $results;
	}

	public function isFirstAction(): bool
	{
		return $this->id === 0;
	}
}
