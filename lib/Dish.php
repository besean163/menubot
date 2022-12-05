<?php

namespace lib;

class Dish
{
	public int $categoryId;
	public int $sourceId;
	public string $name;
	public float $weight;
	public string $weightDimension;
	public float $price;
	public float $calories;
	public string $ingredients;

	// 'categoryId' => $category->id,
	// 				'name' => trim($dishNames[$i]),
	// 				'id' => $dishIds[$i],
	// 				'weight' => $weight,
	// 				'weightDimension' => $weightDimension,
	// 				'price' => (float) $costs[$i],
	// 				'calories' => $dishesCalories[$i],
	// 				'ingredients' => trim($ingredientSets[$i])
	public function __construct(array $config = [])
	{
		$this->categoryId = $config['categotyId'];
		$this->sourceId = intval($config['sourceId']);
		$this->name = trim($config['name']);
		$this->weight = floatval($config['weight']);
		$this->weightDimension = trim($config['weightDimension']);
		$this->price = floatval($config['price']);
		$this->calories = floatval($config['calories']);
		$this->
	}
}
