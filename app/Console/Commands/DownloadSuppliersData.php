<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
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

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		/*
		1. Скачиваем данные доступных ресторанов (имя, id для взаимодействия)
		*/

		$api = new ObedApi(env("OBED_LOGIN"), env("OBED_PASS"));
		$cafesData = $api->getCafeData();

		// $foodSuppliers = new Collection();
		// foreach ($cafesData as $cafeData) {
		// 	$name = $cafeData['name'];
		// 	$sourceId = $cafeData['id'];
		// 	$foodSupplier = FoodSupplier::where('sourceId', $sourceId)->first();
		// 	// Log::info(get_class($foodSupplier));
		// 	if (!$foodSupplier) {
		// 		Log::alert('here');
		// 		$foodSupplier = FoodSupplier::create([
		// 			'name' => $name,
		// 			'sourceId' => $sourceId,
		// 		]);
		// 	}

		// 	$foodSuppliers->push($foodSupplier);
		// }

		// Log::info($foodSuppliers);

		$foodSupplier = FoodSupplier::first();

		// $api->getMenuList($foodSupplier->sourceId, '2022-12-05');
		// Log::info($api->getMenuList($foodSupplier->sourceId, '2022-12-05'));
		$fs = FoodSupplier::query()->getQuery()->whereIn('id', [13, 14])->get(['name']);

		Log::info($fs);

		return Command::SUCCESS;
	}
}
