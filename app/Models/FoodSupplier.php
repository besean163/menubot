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
						$menu .= sprintf("   -%s\"\n", $categoryDish->name);
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
					$menu .= sprintf("  %s (%s)\n", $needDish->name, $foodSupplier->name);
				}
			}
			$menu .= "\n";
		}
		return $menu;
	}
}
