<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use lib\Utils\Str;

class Dish extends Model
{
	use HasFactory;

	const CATEGORY_ID = 'cid';
	const SOURCE_ID = 'sid';
	const NAME = 'n';
	const WEIGHT = 'w';
	const WEIGHT_DIMENSION = 'wd';
	const PRICE = 'p';
	const CALORIES = 'c';
	const INGREDIENTS = 'i';

	// public string $date;
	// public string $foodSupplierId;
	// public int $categoryId;
	// public string $sourceId;
	// public string $name;
	// public float $weight;
	// public string $weightDimension;
	// public float $price;
	// public float $calories;
	// public array $ingredients;

	protected $table = 'dishes';

	protected $fillable = [
		'foodSupplierId',
		'date',
		'categoryId',
		'sourceId',
		'name',
		'weight',
		'weightDimension',
		'price',
		'calories',
		'ingredients'
	];

	public static function make(int $foodSupplierId, string $date, int $categoryId, string $sourceId, string $name, float $weight, string $weightDimension, float $price, float $calories, array $ingredients): void
	{
		$dish = self::query()
			->where('foodSupplierId', $foodSupplierId)
			->where('date', $date)
			->where('categoryId', $categoryId)
			->where('sourceId', $sourceId)
			->first();

		if (!$dish) {
			$dish = self::query()->create([
				'foodSupplierId' => $foodSupplierId,
				'date' => $date,
				'categoryId' => $categoryId,
				'sourceId' => $sourceId,
				'name' => $name,
				'weight' => $weight,
				'weightDimension' => $weightDimension,
				'price' => $price,
				'calories' => $calories,
				'ingredients' => json_encode($ingredients)
			]);
		}
	}

	public function getRow(int $rowNumber, bool $needMark = false): string
	{
		// в общем 40 должно быть
		// лимит сиволов на строку номера
		$numberLimit = 3;
		// лимит сиволов на строку имени
		$nameLimit = 15;
		// лимит сиволов на строку веса
		$weightLimit = 6;
		// лимит сиволов на строку разделителя данных и цены
		$dataPriceSeparatorLimit = 3;
		// лимит сиволов на строку цены
		$priceLimit = 6;
		// лимит сиволов на строку разделителя цены и валюты этой цены
		$priceValuteSeparatorLimit = 1;
		// лимит сиволов на строку валюты цены
		$valuteLimit = 2;

		$supplierMarkLimit = 5;

		$result =  '';
		$numberText = $rowNumber . '.';
		$weightText = $this->weight . $this->weightDimension;
		$priceText = sprintf('%.2f', $this->price);
		$supplierMarkText = sprintf('(%s)', $this->getFoodSupplier()->getShortName());

		$numberRow = str_pad($numberText, $numberLimit, ' ', STR_PAD_LEFT);
		$weightRow = Str::mb_str_pad($weightText, $weightLimit, ' ', STR_PAD_LEFT);
		$dataPriceSeparatorRow = str_pad('-', $dataPriceSeparatorLimit, ' ', STR_PAD_BOTH);
		$priceRow = str_pad($priceText, $priceLimit, ' ', STR_PAD_LEFT);
		$priceValuteSeparatorRow = str_pad('', $priceValuteSeparatorLimit);
		$valuteRow = str_pad('р.', $valuteLimit);
		$supplierMarkRow = str_pad($supplierMarkText, $supplierMarkLimit);

		$nameRows = Str::explodeStringByLimit($this->name, $nameLimit);
		if (!empty($nameRows)) {
			foreach ($nameRows as $key => $nameRow) {
				$numberPart = str_pad('', strlen($numberRow));
				$namePart = Str::mb_str_pad($nameRow, $nameLimit);
				$weightPart = Str::mb_str_pad('', mb_strlen($weightRow));
				$dataPriceSeparatorPart = str_pad('', strlen($dataPriceSeparatorRow));
				$pricePart = str_pad('', strlen($priceRow));
				$priceValuteSeparatorPart = str_pad('', strlen($priceValuteSeparatorRow));
				$valutePart = str_pad('', strlen($valuteRow));
				$supplierMarkPart = str_pad('', strlen($supplierMarkRow));

				$row = '';
				if ($key === 0) {
					$numberPart = $numberRow;
				}

				if (next($nameRows) === false) {
					$weightPart = $weightRow;
					$dataPriceSeparatorPart = $dataPriceSeparatorRow;
					$pricePart = $priceRow;
					$priceValuteSeparatorPart = $priceValuteSeparatorRow;
					$valutePart = $valuteRow;
					$supplierMarkPart = $supplierMarkRow;
				}

				// $row = sprintf(
				// 	"%s%s%s%s%s%s%s\n",
				// 	$numberPart,
				// 	$namePart,
				// 	$weightPart,
				// 	$dataPriceSeparatorPart,
				// 	$pricePart,
				// 	$priceValuteSeparatorPart,
				// 	$valutePart
				// );

				$row .= $numberPart;
				$row .= $namePart;
				if (trim($weightPart) != '') {
					$row .= $weightPart;
				}
				if (trim($dataPriceSeparatorPart) != '') {
					$row .= $dataPriceSeparatorPart;
				}

				if (trim($pricePart) != '') {
					$row .= $pricePart;
				}

				if (trim($priceValuteSeparatorPart) != '') {
					$row .= $priceValuteSeparatorPart;
				}

				if (trim($valutePart) != '') {
					$row .= $valutePart;
				}
				if ($needMark) {
					$row .= $supplierMarkPart;
				}
				$row .= "\n";


				$result .= $row;
			}
		}

		return $result;
	}

	// public function toArray(): array
	// {
	// 	return [
	// 		self::CATEGORY_ID => $this->categoryId,
	// 		self::SOURCE_ID => $this->sourceId,
	// 		self::NAME => $this->name,
	// 		self::WEIGHT => $this->weight,
	// 		self::WEIGHT_DIMENSION => $this->weightDimension,
	// 		self::PRICE => $this->price,
	// 		self::CALORIES => $this->calories,
	// 		self::INGREDIENTS => $this->ingredients
	// 	];
	// }

	public static function getFoodSuppliers(string $date): Collection
	{
		$dishes = self::query()->where('date', $date)->get();

		$fooSupplierIds = $dishes->groupBy(function (self $d) {
			return $d->foodSupplierId;
		})->keys()->toArray();

		return FoodSupplier::query()->getQuery()->whereIn('id', $fooSupplierIds)->get();
	}

	public static function getCategories(string $date): Collection
	{
		$dishes = self::query()->where('date', $date)->get();

		$categoryIds = $dishes->groupBy(function (self $d) {
			return $d->categoryId;
		})->keys()->toArray();

		return FoodCategory::query()->getQuery()->whereIn('id', $categoryIds)->get();
	}

	public function getFoodSupplier(): FoodSupplier
	{
		return FoodSupplier::query()->where('id', $this->foodSupplierId)->first();
	}
}
