<?php

namespace lib\Telegram\Dialogs;

use Exception;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

abstract class Action
{
	public DialogHandler $parentHandler;
	public int $id;
	public string $type;
	public string $status;
	public ?string $result = null;
	public array $sended_message_ids = [];
	protected string $chatId;

	const STATUS_WAIT = 'wait';
	const STATUS_FINISH = 'finish';

	const CLOSE_MARK = 'close';

	public function __construct(DialogHandler $handler, int $id, string $type, string $status, ?string $result = null, array $sended_message_ids = [])
	{
		$this->parentHandler = $handler;
		$this->id = $id;
		$this->chatId = $handler->getChat()->telegramId;
		$this->type = $type;
		$this->setStatus($status);
		$this->result = $result;
		$this->sended_message_ids = $sended_message_ids;
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
		$sended_message_ids = $config['sended_message_ids'] ?? [];

		if ($type === null || $status === null) {
			throw new Exception("Need type and status data.");
		}

		if ($type !== static::type()) {
			throw new Exception(sprintf("Type not match. (expect: %s, actual:%s).", static::type(), $type));
		}

		return new static($handler, $id, $type, $status, $result, $sended_message_ids);
	}

	public function firstLaunch(): void
	{
		$this->ask();
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

		$this->cleanSendedMessages();
		if (!$this->isCloseData($data) && (!in_array($data, array_keys($this->getValidValues())) || !$data)) {
			$this->sendWarningMessage();
			$this->ask();
		} else {
			$this->result = $data;

			if (($this->needClose() && $this->isCloseData($data)) || !$this->needClose()) {
				$this->finish();
			} else {
				$this->answer();
				$this->ask();
			}
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
			'sended_message_ids' => $this->sended_message_ids,
		];
	}

	protected function cleanSendedMessages(): void
	{
		foreach ($this->sended_message_ids as $sended_message_id) {
			Request::deleteMessage([
				'chat_id' => $this->chatId,
				'message_id' => $sended_message_id,
			]);
		}
		$this->sended_message_ids = [];
	}

	protected function sendWarningMessage(): void
	{
		$response = Request::sendMessage([
			'chat_id' => $this->chatId,
			'text' => "Пожалуйста выберите значение из предложенных.",
		]);

		/** @var Message $result */
		$result = $response->getResult();
		$this->sended_message_ids[] = $result->getMessageId();
	}


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

	public function isCloseData(?string $data): bool
	{
		return $data === self::CLOSE_MARK;
	}

	/** Метод для возврата пользователю результата его ответа */
	protected function answer(): void
	{
		Request::emptyResponse();
	}

	protected function needClose(): bool
	{
		return false;
	}

	// abstract protected function sendMessage(): void;
	abstract protected function getValidValues(): array;
	abstract protected function ask(): void;
}
