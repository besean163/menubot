<?php

namespace lib;

use App\Models\FoodCategory;

class Dish
{
	const CATEGORY_ID = 'cid';
	const SOURCE_ID = 'sid';
	const NAME = 'n';
	const WEIGHT = 'w';
	const WEIGHT_DIMENSION = 'wd';
	const PRICE = 'p';
	const CALORIES = 'c';
	const INGREDIENTS = 'i';

	public int $categoryId;
	public string $sourceId;
	public string $name;
	public float $weight;
	public string $weightDimension;
	public float $price;
	public float $calories;
	public array $ingredients;

	public function __construct(int $categoryId, string $sourceId, string $name, float $weight, string $weightDimension, float $price, float $calories, array $ingredients)
	{
		$this->categoryId = $categoryId;
		$this->sourceId = $sourceId;
		$this->name = trim($name);
		$this->weight = $weight;
		$this->weightDimension = $weightDimension;
		$this->price = $price;
		$this->calories = $calories;
		$this->ingredients = $ingredients;
	}

	public function toArray(): array
	{
		return [
			self::CATEGORY_ID => $this->categoryId,
			self::SOURCE_ID => $this->sourceId,
			self::NAME => $this->name,
			self::WEIGHT => $this->weight,
			self::WEIGHT_DIMENSION => $this->weightDimension,
			self::PRICE => $this->price,
			self::CALORIES => $this->calories,
			self::INGREDIENTS => $this->ingredients
		];
	}
}
