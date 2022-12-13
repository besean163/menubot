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

	public static function getStringWithShift(string $text, int $maxSymbolsInRow = 10, $needCut = false): string
	{
		/*
			Это функция переноса:
			- длина строки ограничивается переданным числом
			- часть строки больше числа либо обрезается либо рекурсивно передается этой же функции, это решается относительно 3го параметра

			Последовательность:
			1. Проверяем строку по длине, если меньше указаного числа, значит возвращаем эту же строку
			2. Если нет, пытаемся разбить на слова по пробелам
			3. Проверяем первое слово. 
				а. Если оно длинее лимита: 
					- Обрезаем его или делим для переноса
				б. Если короче:
					- Сохраняем в результирующщую строку
					- Идем дальше по массиву слов
					- Проверяем если сумма результирующей строки и нового слова
						1. если меньше лимита
							- идем дальше по массиву
						2. если больше 
							- сохраняем результирующую строку без нового слова в массиве результата
							- остальные слова строки сохраняем в массив остатков
							- объединяем массив остатков в новую строку и передаем в функцию рекурсивно
			4. Соединяем массив результата переносом строки в строку
			5. возвращаем строку
		*/

		$cutSymbol = '...';
		$cutSymbolLength = mb_strlen($cutSymbol);

		if ($maxSymbolsInRow < $cutSymbolLength && $needCut) {
			throw new Exception(sprintf("Max symbols count should be more then %d.", $cutSymbolLength));
		}

		// если меньше или равен оставляем все как есть без изменений
		if (mb_strlen($text) <= $maxSymbolsInRow || trim($text) === '') {
			return $text;
		}

		$resultRows = [];
		$words = preg_split('/(\s+)/', trim($text));

		$filled = false;
		$row = '';
		$remainWords = [];
		foreach ($words as $word) {
			if ($filled) {
				$remainWords[] = $word;
				continue;
			}

			if (mb_strlen($word) > $maxSymbolsInRow) {
				$chars = mb_str_split($word);
				$row = implode('', array_slice($chars, 0, $maxSymbolsInRow));
				$remnant = implode('', array_slice($chars, $maxSymbolsInRow + 1));
				$remainWords[] = $remnant;
				$filled = true;
				continue;
			}

			$checkRow = $row . '_' . $word;
			if (mb_strlen($checkRow) <= $maxSymbolsInRow) {
				$row = $checkRow;
				continue;
			}
		}

		for ($i = 1; $i < count($words); $i++) {
			$sum = $row . '_' . $words[$i];
			if (mb_strlen($sum) <= $maxSymbolsInRow) {
				$row = $sum;
			}
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

	public function newFunc($text, $limit): string
	{
		// если меньше или равен оставляем все как есть без изменений
		if (mb_strlen($text) <= $limit || trim($text) === '') {
			return $text;
		}

		$words = preg_split('/(\s+)/', trim($text));
		$word = $words[0];

		if (mb_strlen($word) > $limit) {
			$chars = mb_str_split($word);
			$row = implode('', array_slice($chars, 0, $limit));
			$remnant = implode('', array_slice($chars, $limit + 1));
			$remainWords[] = $remnant;
			$filled = true;
			continue;
		}





		return '';
	}
}
