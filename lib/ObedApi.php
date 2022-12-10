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

	/* function getMenuList(string $date): string
	{
		$cafesData = $this->getCafeData();


		$params = [
			RequestOptions::QUERY => [
				'date' => $date
			],
			RequestOptions::COOKIES => $this->cookies
		];

		$dishNamePattern = '/class="ob-supplier-complex-tile__title\s\C+?>(\C+?)</';
		$weightPattern = '/<span class="ob-supplier-complex-tile__grams">\((.+?)\)/';
		$costPattern = '/<input type="hidden" class="price_.+?value="(.+?)"/';
		$menu = '';
		foreach ($cafesData as $cafeData) {
			$url = self::BASE_URL . 'suppliers/' . $cafeData['id'] . '/menu';
			$response = $this->client->get($url, $params);
			$page = $response->getBody()->getContents();
			preg_match_all($dishNamePattern, $page, $dishNameMatches);
			preg_match_all($weightPattern, $page, $weightMatches);
			preg_match_all($costPattern, $page, $costMatches);
			$menu .= $cafeData['name'] . ':' . "\n";
			$dishes = $dishNameMatches[1];
			$weights = $weightMatches[1];
			$costs = $costMatches[1];
			for ($i = 0; $i < count($dishes); $i++) {
				$numberPart = str_pad(sprintf("  %d.", $i + 1), 5);
				$namePart = self::mb_str_pad(sprintf("%s", $dishes[$i]), 55);
				$weightPart = self::mb_str_pad(sprintf("(%s)", $weights[$i]), 15);
				$costPart = self::mb_str_pad(sprintf("- %.2f руб.", floatval($costs[$i])), 10);
				$menu .= sprintf(
					"%s%s%s%s\n",
					$numberPart,
					$namePart,
					$weightPart,
					$costPart
				);
				// $menu .= sprintf(
				// 	"\t%d. %s (%s) - %.2f руб.\n",
				// 	$i + 1,
				// 	$dishes[$i],
				// 	$weights[$i],
				// 	floatval($costs[$i])
				// );
			}
			if (next($cafeData) !== false) {
				$menu .= "\n";
			}
		}

		return $menu;
	} */

	public function syncDishes(FoodSupplier $foodSupplier, string $date): void
	{
		$params = [
			RequestOptions::QUERY => [
				'date' => $date
			],
			RequestOptions::COOKIES => $this->cookies
		];

		$categoriesPattern = '/<p class="ob-supplier-complex__complex-title js-categories-title" id="(category_\d+)">(.+?)</';
		$idCaloriesNamePattern = '/dish_name_(\d+).+?data-calorie="(\d+)">(.+?)</';
		$weightDataPattern = '/<span class="ob-supplier-complex-tile__grams">\((.+?)\)/';
		$costPattern = '/<input type="hidden" class="price_.+?value="(.+?)"/';
		$ingredientPattern = '/id="dish_description_\d+">(.+?)</';

		$url = self::BASE_URL . 'suppliers/' . $foodSupplier->sourceId . '/menu';
		$response = $this->client->get($url, $params);
		$page = $response->getBody()->getContents();

		$categoriesParts = explode('<!-- Название категории -->', $page);
		foreach ($categoriesParts as $categoriesPart) {
			if (strpos($categoriesPart, '<!-- Наименование блюда -->') === false) {
				continue;
			}
			// Log::notice('here');

			preg_match($categoriesPattern, $categoriesPart, $categoryMatches);

			// Log::notice($categoryMatches);
			// exit;

			$categoryId = $categoryMatches[1];
			$categoryName = $categoryMatches[2];

			Log::info($categoryId);
			$category = FoodCategory::query()->firstOrCreate([
				'name' => $categoryName,
				'sourceId' => $categoryId,
			]);

			preg_match_all($idCaloriesNamePattern, $categoriesPart, $idCaloriesNameMatches);
			preg_match_all($weightDataPattern, $categoriesPart, $weightDataMatches);
			preg_match_all($costPattern, $categoriesPart, $costMatches);
			preg_match_all($ingredientPattern, $categoriesPart, $ingredientMatches);

			$dishIds = $idCaloriesNameMatches[1];
			$dishesCalories = $idCaloriesNameMatches[2];
			$dishNames = $idCaloriesNameMatches[3];
			$weightSets = $weightDataMatches[1];
			$costs = $costMatches[1];
			$ingredientSets = $ingredientMatches[1];
			for ($i = 0; $i < count($dishIds); $i++) {
				$sourceId = trim($dishIds[$i]);
				$name = trim($dishNames[$i]);
				$price = (float) (trim($costs[$i]));
				$calories = (float) (trim($dishesCalories[$i]));

				Log::alert($weightSets[$i]);
				$weightData = explode(' ', $weightSets[$i]);
				$weight = (float) (trim($weightData[0]));
				$weightDimension = trim($weightData[1] ?? '');
				$ingredients = array_map('trim', explode(',', trim($ingredientSets[$i] ?? '')));

				Dish::make($foodSupplier->id, $date, $category->id, $sourceId, $name, $weight, $weightDimension, $price, $calories, $ingredients);
			}
		}
	}

	private static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
	{
		$diff = strlen($input) - mb_strlen($input);
		return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
	}
}
