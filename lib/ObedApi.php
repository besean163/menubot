<?php

namespace lib;

use App\Models\Dish;
use App\Models\FoodCategory;
use App\Models\FoodSupplier;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class ObedApi
{
	private string $login;
	private string $password;
	private CookieJar $cookies;
	private Client $client;

	private static array $needCookies = [
		'PHPSESSID',
		'uid',
		's_id',
		's_pass'
	];

	private const BASE_URL = 'https://www.obed.ru/';

	public function __construct(string $login, string $password)
	{
		$this->login = $login;
		$this->password = $password;
		$this->client = new Client();
		$this->setCookies();
	}

	private function setCookies(): void
	{
		$params = [
			RequestOptions::FORM_PARAMS => [
				'f_login' => $this->login,
				'f_password' => $this->password
			],
			RequestOptions::ALLOW_REDIRECTS => false
		];
		$response = $this->client->post(self::BASE_URL, $params);
		$headers = $response->getHeaders();

		$cookies = [];
		foreach ($headers['Set-Cookie'] as $cookieString) {
			$cookieData = explode('; ', $cookieString);
			$cookie = [];
			foreach ($cookieData as $key => $cookieDataString) {
				$cookieParams = explode('=', $cookieDataString);
				$paramName = $cookieParams[0];
				$paramValue = $cookieParams[1] ?? 'delete';
				if ($key == 0) {
					if (!in_array($paramName, self::$needCookies) || strstr($paramValue, 'delete')) {
						break;
					}
					$cookie['Name'] = $paramName;
					$cookie['Value'] = $paramValue;
				} else {
					$paramName = ucfirst($paramName);
					$cookie[$paramName] = $paramValue;
				}
			}

			if (!empty($cookie)) {
				array_push($cookies, $cookie);
			}
		}
		$this->cookies = new CookieJar(false, $cookies);
	}

	public function getCafeData(): array
	{
		$data = [];
		$url = self::BASE_URL . 'obed';

		$params = [
			RequestOptions::COOKIES => $this->cookies
		];
		$response = $this->client->get($url, $params);
		$page = $response->getBody()->getContents();

		$namePattern = '/<div class="ob-h3 text-no-wrap">(.+?)<\/div>/';
		$pathPattern = '/<a class="item-card" href="(.+)">/';
		preg_match_all($namePattern, $page, $names);
		preg_match_all($pathPattern, $page, $paths);

		$names = $names[1];
		$paths = $paths[1];
		for ($i = 0; $i < count($names); $i++) {
			$idPattern = "/\/suppliers\/(.+)\/menu/";
			preg_match($idPattern, $paths[$i], $idMatch);
			$id = $idMatch[1];
			$orderIdPattern = "/order_id=(.+)/";
			preg_match($orderIdPattern, $paths[$i], $orderIdMatch);
			// $orderId = $orderIdMatch[1];

			array_push($data, [
				'name' => $names[$i],
				'id' => $id,
				// 'orderId' => $orderId,
				// 'path' => $paths[$i],
			]);
		}
		// print_r($data);
		return $data;
	}

	public function syncDishes(FoodSupplier $foodSupplier, string $date): void
	{
		Log::info(sprintf("Date '%s'. Foodsupplier '%s'. Start download dishes...", $date, $foodSupplier->name));

		$params = [
			RequestOptions::QUERY => [
				'date' => $date
			],
			RequestOptions::COOKIES => $this->cookies
		];

		$categoriesPattern = '/<p class="ob-supplier-complex__complex-title js-categories-title" id="(category_\d+)">(.+?)</';
		$idNamePattern = '/dish_name_(\d+).+?>(.+?)</';
		$caloriePattern = '/data-calorie="(\d+)"/';
		// ???? ???????????????? ?? ????????????????????
		// $idCaloriesNamePattern = '/dish_name_(\d+).+?data-calorie="(\d+)">(.+?)</';
		$weightDataPattern = '/<span class="ob-supplier-complex-tile__grams">\((.+?)\)/';
		$costPattern = '/<input type="hidden" class="price_.+?value="(.+?)"/';
		$ingredientPattern = '/div class="ob-supplier-complex-tile__order-compos ob-supplier-complex-tile__order-compos_mobile.+?>(.+?)</';



		$url = self::BASE_URL . 'suppliers/' . $foodSupplier->sourceId . '/menu';
		$response = $this->client->get($url, $params);
		$page = $response->getBody()->getContents();

		// if ($date == '2022-12-12') {
		// 	Log::debug('save');
		// 	file_put_contents('/home/besean/page' . $foodSupplier->id . '.txt', $page);
		// }

		$categoriesParts = explode('<!-- ???????????????? ?????????????????? -->', $page);
		// ?????????????????????? ???????????? ??????????, ?????? ?????? ???? ??????????????????
		unset($categoriesParts[0]);
		$categoriesCount = count($categoriesParts);
		Log::info(sprintf("Found %d categories.", $categoriesCount));
		foreach ($categoriesParts as $categoriesPart) {
			$dishCount = 0;

			preg_match($categoriesPattern, $categoriesPart, $categoryMatches);

			$categoryId = trim($categoryMatches[1]);
			$categoryName = trim($categoryMatches[2]);

			// Log::info($categoryId);
			$category = FoodCategory::query()->firstOrCreate([
				'name' => $categoryName,
				'sourceId' => $categoryId,
			]);

			preg_match_all($idNamePattern, $categoriesPart, $idNameMatches);
			preg_match_all($caloriePattern, $categoriesPart, $calorieMatches);
			preg_match_all($weightDataPattern, $categoriesPart, $weightDataMatches);
			preg_match_all($costPattern, $categoriesPart, $costMatches);
			preg_match_all($ingredientPattern, $categoriesPart, $ingredientMatches);

			$dishIds = $idNameMatches[1];
			$dishNames = $idNameMatches[2];
			$dishesCalories = $calorieMatches[1];
			$weightSets = $weightDataMatches[1];
			$costs = $costMatches[1];
			$ingredientSets = $ingredientMatches[1];
			for ($i = 0; $i < count($dishIds); $i++) {
				$sourceId = trim($dishIds[$i]);
				$name = trim($dishNames[$i]);
				$price = (float) (trim($costs[$i]));
				$calories = (float) (trim($dishesCalories[$i]));

				$weightData = explode(' ', $weightSets[$i]);
				$weight = (float) (trim($weightData[0]));
				$weightDimension = trim($weightData[1] ?? '');
				$ingredients = array_map('trim', explode(',', trim($ingredientSets[$i] ?? '')));

				Dish::make($foodSupplier->id, $date, $category->id, $sourceId, $name, $weight, $weightDimension, $price, $calories, $ingredients);
				$dishCount += 1;
			}
			Log::info(sprintf("'%s' category. %d dishes downloaded.", $category->name, $dishCount));
		}

		Log::info(sprintf("%s - end download dishes.", $foodSupplier->name));
	}
}
