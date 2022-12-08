<?php

namespace lib\Telegram\Dialogs;

abstract class DialogHandler
{
	public function __construct(Dialog $dialog)
	{
	}

	abstract public function run(): void;
}
