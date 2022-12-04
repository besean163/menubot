<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
		$botToken = '1858930058:AAFRaVAE3XyxFsiREylp9WKP-BXDiuZ5cms';
		$telegram = new Telegram($botToken);
		Request::initialize($telegram);
		$response = Request::sendMessage([
			'chat_id' => '275665865',
			'text' => 'lib work'
		]);
		Log::info($response);
		// Log::info('here');
		// $foodSupplier = FoodSupplier::create([
		// 	'name' => 'testFoodSupplier',
		// 	'sourceId' => 'someId',
		// ]);
		// $foodSupplier->save();
		return Command::SUCCESS;
	}
}
