<?php

namespace App\Console\Commands;

use App\Models\Dish;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use lib\Date;

/**
 * 	/opt/menubot/artisan cleanDishes
 */
class CleanDishes extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'cleanDishes';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Для очистки таблицы блюд';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		Log::info('Start clean dishes...');
		$date = Date::today()->addDays(-7)->getDateISO();
		$deleteCount = Dish::query()->where('date', '<', $date)->delete();
		Log::info(sprintf('Cleaning finished. %d rows deleted.', $deleteCount));

		return Command::SUCCESS;
	}
}
