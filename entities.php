<?php

class User
{
	private int $name;
	private string $telegramId;
	private string $login;
	private string $password;
}

class FoodSupplier
{
	private int $id;
	private string $name;
	private string $sourceId;
}

class DayMenu
{
	private int $id;
	private int $foodSupplierId;
	private string $date;

	/*
		[
			[
				"name" => "Каша",
				"weight" => "100",
				"weightUnit" => "г",
				"price" => "20.00",
				"priceUnit => "руб."
			],
			[
				"name" => "Каша",
				"weight" => "100",
				"weightUnit" => "г",
				"price" => "20.00",
				"priceUnit => "руб."
			]
		]
	*/
	private array $list;
}

class FoodSupplierApi
{
	private array $cookies;
	private string $ligin;
	private string $password;
}
