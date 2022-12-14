<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Log;
use lib\Utils\Str;

class FoodSupplier extends Model
{
	// private int $id = 0;

	use HasFactory;

	protected $fillable = ['name', 'sourceId'];


	public static function getMenu(string $date, string $breakdown, int $breakdownId): string
	{
		$result = '';
		if ($breakdown === 'supplier') {
			$result = self::getBySupplier($date, $breakdownId);
		} elseif ($breakdown === 'category') {
			$result = self::getByCategories($date, $breakdownId);
		} else {
			throw new Exception("Unknown breakdown: {$breakdown}.");
		}

		$result = sprintf("<code>%s</code>", $result);
		return $result;
	}

	public static function getBySupplier(string $date, int $foodSupplierId): string
	{
		$foodSupplier = FoodSupplier::query()->where('id', $foodSupplierId)->first();
		$dishes = Dish::query()->where('date', $date)->where('foodSupplierId', $foodSupplierId)->get();
		$categories = FoodCategory::all()->keyBy(function (FoodCategory $fc) {
			return $fc->id;
		});
		$menu = 'Меню на ' . "\"{$date}\":\n";
		$menu .= sprintf("Подрядчик \"%s\":\n", $foodSupplier->name);
		$dishes = Dish::query()->where('date', $date)->where('foodSupplierId', $foodSupplier->id)->get();
		foreach ($categories as $category) {
			// костыль, это категория полуфабрикатов
			if ($category->sourceId === 'category_800') {
				continue;
			}

			$categoryDishes = $dishes->filter(function (Dish $dish) use ($category) {
				return $dish->categoryId === $category->id;
			});

			if ($categoryDishes->count() === 0) {
				continue;
			}

			$menu .= sprintf("Категория \"%s\":\n", $category->name);
			$dishNumber = 1;
			/** @var Dish $categoryDish */
			foreach ($categoryDishes as $categoryDish) {
				$menu .= $categoryDish->getRow($dishNumber);
				$dishNumber++;
			}
			$menu .= "\n";
		}

		return $menu;
	}

	public static function getByCategories(string $date, int $categoryId): string
	{
		$category = FoodCategory::query()->where('id', $categoryId)->first();
		$dishes = Dish::query()->where('date', $date)->where('categoryId', $categoryId)->get();

		$menu = 'Меню на ' . "\"{$date}\":\n";
		$menu .= sprintf("Категория \"%s\":\n", $category->name);
		$needDishes = $dishes->filter(function (Dish $d) use ($category) {
			return $d->categoryId == $category->id;
		});
		$dishNumber = 1;
		foreach ($needDishes as $needDish) {
			$menu .= $needDish->getRow($dishNumber, true);
			$dishNumber++;
		}
		return $menu;
	}


	public function getShortName(): string
	{
		$shortName = '';
		$name = preg_replace('/(\s+)/', ' ', trim($this->name));
		$words = explode(' ', $name);
		foreach ($words as $word) {
			$firstChar = mb_strtoupper(mb_substr($word, 0, 1));
			$shortName .= $firstChar;
		}
		return $shortName;
	}
}
