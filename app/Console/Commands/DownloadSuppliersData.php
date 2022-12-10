<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use lib\Date;
use lib\ObedApi;


/**
 * 	/opt/menubot/artisan downloadSupplierData
 */
class DownloadSuppliersData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'downloadSupplierData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Скачивает данные поставщиков еды.';

	private ObedApi $api;
	private Collection $foodSuppliers;

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$this->setApi();
		$this->syncFoodSuppliers();
		$this->syncDishes();


		return Command::SUCCESS;
	}

	private function setApi(): void
	{
		$this->api = new ObedApi(env("OBED_LOGIN"), env("OBED_PASS"));
	}

	private function syncFoodSuppliers(): void
	{
		$cafesData = $this->api->getCafeData();

		$foodSuppliers = new Collection();
		foreach ($cafesData as $cafeData) {
			$name = $cafeData['name'];
			$sourceId = $cafeData['id'];
			$foodSupplier = FoodSupplier::query()->firstOrCreate([
				'sourceId' => $sourceId,
				'name' => $name
			]);
			// Log::info(get_class($foodSupplier));
			if (!$foodSupplier) {
				$foodSupplier = FoodSupplier::create([
					'name' => $name,
					'sourceId' => $sourceId,
				]);
			}

			$foodSuppliers->push($foodSupplier);
		}
		$this->foodSuppliers = $foodSuppliers;
	}

	private function syncDishes(): void
	{
		$dates = $this->needThisWeek() ? Date::getThisWeekWorkDays() : Date::getNextWeekWorkDays();

		/** @var FoodSupplier $foodSupplier*/
		foreach ($this->foodSuppliers as $foodSupplier) {
			foreach ($dates as $date) {
				$this->api->syncDishes($foodSupplier, $date);
			}
		}
	}

	private function needThisWeek(): bool
	{
		$todayWeekDay = Date::today()->getWeekDay();
		if ($todayWeekDay == 7) {
			return false;
		}
		return true;
	}
}
