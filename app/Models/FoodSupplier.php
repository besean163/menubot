<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

class FoodSupplier extends Model
{
	// private int $id = 0;

	use HasFactory;

	protected $fillable = ['name', 'sourceId'];


	public static function getMenu(string $date, string $breakdown): string
	{
		$foodSuppliers = self::all();
		$foodSuppliersIds = $foodSuppliers->map(function (self $fs) {
			return $fs->id;
		});

		$dishes = Dish::query()->where('date', $date)->getQuery()->whereIn('foodSupplierId', $foodSuppliersIds)->get();
		$categories = FoodCategory::all()->keyBy(function (FoodCategory $fc) {
			return $fc->id;
		});

		if ($breakdown === 'supplier') {
			return self::getBySupplier($date);
		} elseif ($breakdown === 'category') {
			return self::getByCategories($date);
		} else {
			throw new Exception("Unknown breakdown: {$breakdown}.");
		}



		return '';
	}

	public static function getBySupplier(string $date): string
	{
		$foodSuppliers = self::all();

		$categories = FoodCategory::all()->keyBy(function (FoodCategory $fc) {
			return $fc->id;
		});
		$menu = 'Меню на ' . "\"{$date}\":\n";

		foreach ($foodSuppliers as $foodSupplier) {
			$menu .= sprintf("Подрядчик \"%s\":\n", $foodSupplier->name);
			$dishes = Dish::query()->where('date', $date)->where('foodSupplierId', $foodSupplier->id)->get();
			foreach ($categories as $category) {
				$categoryDishes = $dishes->filter(function (Dish $dish) use ($category) {
					if ($dish->categoryId === $category->id) {
						return true;
					}
					return false;
				});

				if ($categoryDishes->count() !== 0 && $category->sourceId !== 'category_800') {
					$menu .= sprintf("  Категория \"%s\":\n", $category->name);
					foreach ($categoryDishes as $categoryDish) {
						$menu .= sprintf("   -%s\n", $categoryDish->name);
					}
					$menu .= "\n";
				}
			}
			$menu .= "\n\n";
		}
		return $menu;
	}

	public static function getByCategories(string $date): string
	{
		$foodSuppliers = self::all()->keyBy(function (self $fs) {
			return $fs->id;
		});

		$categories = FoodCategory::all()->keyBy(function (FoodCategory $fc) {
			return $fc->id;
		});
		$dishes = Dish::query()->where('date', $date)->get();

		$menu = 'Меню на ' . "\"{$date}\":\n";

		foreach ($categories as $category) {
			if ($category->sourceId === 'category_800') {
				continue;
			}
			$dishNumber = 1;

			$menu .= sprintf("Категория \"%s\":\n", $category->name);
			$needDishes = null;
			foreach ($foodSuppliers as $foodSupplier) {
				$needDishes = $dishes->filter(function (Dish $d) use ($category, $foodSupplier) {
					if ($d->categoryId == $category->id && $d->foodSupplierId === $foodSupplier->id) {
						return true;
					}
					return false;
				});
				foreach ($needDishes as $needDish) {
					// $menu .= sprintf("  %s (%s)\n", $needDish->name, $foodSupplier->name);

					$numberPart = sprintf("%d. ", $dishNumber);
					$namePart = self::mb_str_pad($needDish->name, 25, ' ', STR_PAD_RIGHT);
					// $namePart = self::mb_str_pad(fake('us')->name(), 30, '.', STR_PAD_RIGHT);
					$pricePart = str_pad(sprintf("%.2f р.", $needDish->price), 10, ' ', STR_PAD_LEFT);
					$menu .= sprintf(
						"%s%s%s\n",
						$numberPart,
						$namePart,
						$pricePart
					);
					// $menu .= sprintf("%d. \n", $needDish->name, $foodSupplier->name);
					$dishNumber++;
				}
			}
			$menu .= "\n";
		}
		return '<code>' . $menu . '</code>';
	}

	private static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
	{
		$diff = strlen($input) - mb_strlen($input);
		return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
	}

	public static function getStringWithShift(string $text, int $maxSymbolsInRow = 10, $shift = true): string
	{
		// $text = 'ывафыапвап text';
		$result = '';
		if ($maxSymbolsInRow < 3) {
			throw new Exception("Max symbols count should be more then 2.");
		}

		// если меньше или равен оставляем все как есть без изменений
		if (mb_strlen($text) <= $maxSymbolsInRow) {
			$result = $text;
		}

		$sumText = '';
		$otherWords = [];
		$fill = false;
		// пробуем разбить по пробелам
		if ($result === '') {
			$words = preg_split('/(\s+)/', trim($text));
			foreach ($words as $key => $word) {
				if ($key === 0) {
					$sumText = $word;
					continue;
				}

				if ($sumText === '') {
					if (mb_strlen(sprintf("%s %s", $sumText, $word)) <= $maxSymbolsInRow && !$fill) {
						$sumText = sprintf("%s %s", $sumText, $word);
						continue;
					} else {
						$fill = true;
						array_push($otherWords, $word);
					}
				}
			}
		}
		$result = $sumText;
		echo $sumText . "\n";
		print_r($otherWords);

		if ($result === '' && $sumText !== '' && !empty($otherWords)) {
			$result = $sumText . "\n";
			// echo implode(' ', $otherWords) . "\n";
			$result .= self::getStringWithShift(implode(' ', $otherWords), $maxSymbolsInRow, $shift);
		}

		if ($result === '') {
			$chars = mb_str_split($result);
			$newWord = '';
			for ($i = 0; $i < $maxSymbolsInRow - 3; $i++) {
				$newWord .= $chars[$i];
			}
			$result = $newWord . '...';
		}


		return $result . "\n";
	}
}
