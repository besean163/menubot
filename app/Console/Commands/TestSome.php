<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use lib\Date;
use lib\ObedApi;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class TestSome extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'testsome';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Скрипт для тестирования чего нибудь.';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		return Command::SUCCESS;
	}
}
