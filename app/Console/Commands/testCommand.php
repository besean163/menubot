<?php

namespace App\Console\Commands;

use App\Models\FoodSupplier;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use lib\ObedApi;
use lib\Utils\Str;

class testCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'testCommand';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$api = new ObedApi(env("OBED_LOGIN"), env("OBED_PASS"));
		// $api = new ObedApi(env("OBED_LOGIN"), env("OBED_PASS"));

		if ($api->checkConnect()) {
			Log::debug('Set...');
		} else {
			Log::debug('Lose!');
		}

		// $api->getOrderIds('2022-12-23');
		// $api->getOrder('10641564');
		// $api->getOrder('10638352');
		// $api->getOrder('10641002');

		return Command::SUCCESS;
	}
}
