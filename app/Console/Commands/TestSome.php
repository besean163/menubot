<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use Illuminate\Console\Command;

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
		$foodSupplier = FoodSupplier::create([
			'name' => 'testFoodSupplier',
			'sourceId' => 'someId',
		]);
		$foodSupplier->save();
		return Command::SUCCESS;
	}
}
