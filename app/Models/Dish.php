<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

		Log::notice('here');
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

	public function getRow(): string
	{
		return $this->name;
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
}
