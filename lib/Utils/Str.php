<?php

namespace lib\Utils;

use Exception;
use LDAP\Result;

class Str
{
	// public static function getStringWithShift(string $text, int $limit = 10, $needCut = false): string
	// {
	// 	/*
	// 		Это функция переноса:
	// 		- длина строки ограничивается переданным числом
	// 		- часть строки больше числа либо обрезается либо рекурсивно передается этой же функции, это решается относительно 3го параметра

	// 		Последовательность:
	// 		1. Проверяем строку по длине, если меньше указаного числа, значит возвращаем эту же строку
	// 		2. Если нет, пытаемся разбить на слова по пробелам
	// 		3. Проверяем первое слово. 
	// 			а. Если оно длинее лимита: 
	// 				- Обрезаем его или делим для переноса
	// 			б. Если короче:
	// 				- Сохраняем в результирующщую строку
	// 				- Идем дальше по массиву слов
	// 				- Проверяем если сумма результирующей строки и нового слова
	// 					1. если меньше лимита
	// 						- идем дальше по массиву
	// 					2. если больше 
	// 						- сохраняем результирующую строку без нового слова в массиве результата
	// 						- остальные слова строки сохраняем в массив остатков
	// 						- объединяем массив остатков в новую строку и передаем в функцию рекурсивно
	// 		4. Соединяем массив результата переносом строки в строку
	// 		5. возвращаем строку
	// 	*/

	// 	$cutSymbol = '...';
	// 	$cutSymbolLength = mb_strlen($cutSymbol);

	// 	if ($limit < $cutSymbolLength && $needCut) {
	// 		throw new Exception(sprintf("Max symbols count should be more then %d.", $cutSymbolLength));
	// 	}

	// 	// если меньше или равен оставляем все как есть без изменений
	// 	if (mb_strlen($text) <= $limit || trim($text) === '') {
	// 		return $text;
	// 	}

	// 	$words = preg_split('/(\s+)/', trim($text));
	// 	$filled = false;
	// 	$row = '';
	// 	$remainWords = [];
	// 	foreach ($words as $word) {
	// 		if ($filled) {
	// 			$remainWords[] = $word;
	// 			continue;
	// 		}

	// 		$needDivideWord = mb_strlen($word) > $limit;

	// 		if ($needDivideWord && !$filled) {
	// 			$rowLength = mb_strlen($row);
	// 			$chars = mb_str_split($word);
	// 			$emptyLength = $limit - $rowLength;
	// 			$row = implode('', array_slice($chars, 0, $emptyLength));
	// 			$remnant = implode('', array_slice($chars, $emptyLength));
	// 			$remainWords[] = $remnant;
	// 			$filled = true;
	// 			continue;
	// 		}

	// 		if ($row == '') {
	// 			$checkRow = $word;
	// 		} else {
	// 			$checkRow = $row . ' ' . $word;
	// 		}

	// 		if ((mb_strlen($checkRow) <= $limit) && !$filled) {
	// 			$row = $checkRow;
	// 		} else {
	// 			$filled = true;
	// 			$remainWords[] = $word;
	// 			continue;
	// 		}

	// 		if (mb_strlen($row) === $limit) {
	// 			$filled = true;
	// 		}
	// 	}
	// 	if (!empty($remainWords)) {
	// 		$row .= "\n";
	// 		$row .= self::explodeStringByLimit(implode(' ', $remainWords), $limit, $needCut);
	// 	}

	// 	return $row;
	// }

	public static function explodeStringByLimit(string $text, int $limit = 10): array
	{
		$text = preg_replace('/(\s+)/', ' ', trim($text));
		$loopMaxAttempts = 100;
		$loopAttempt = 1;
		$result = [];
		do {
			$row = self::getFirstRowByLimit($text, $limit);
			if ($row !== '') {
				$result[] = $row;

				$start = mb_strpos($text, $row);
				if ($start !== false) {
					$text = trim(mb_substr($text, mb_strlen($row)));
				}
			}
			$loopAttempt++;
		} while ($row !== '' && $loopAttempt < $loopMaxAttempts);
		return $result;
	}

	public static function getFirstRowByLimit(string $text, int $limit = 10): string
	{
		// echo 'text - ' . $text . "\n";
		$text = preg_replace('/(\s+)/', ' ', trim($text));
		$result = '';
		// если меньше или равен оставляем все как есть без изменений
		if (mb_strlen($text) <= $limit || $text === '') {
			$result = $text;
		} else {
			$words = explode(' ', $text);
			foreach ($words as $word) {
				// echo 'word - ' . $word . "\n";
				$needDivideWord = mb_strlen($word) > $limit;

				if (!$needDivideWord) {
					if ($result === '') {
						$result = $word;
						// echo 'here----------' . "\n";
					} else {
						$checkRow = $result . ' ' . $word;
						if ((mb_strlen($checkRow) <= $limit)) {
							$result = $checkRow;
						} else {
							break;
						}
					}
				} else {
					// $chars = mb_str_split($word);
					if ($result === '') {
						$result = mb_substr($word, 0, $limit);
						// $result = implode('', array_slice($chars, 0, $limit));
					} else {
						$resultLength = mb_strlen($result . ' ');
						if ($resultLength <= $limit) {
							$needFillLength = $limit - $resultLength;
							$result .= ' ' . mb_substr($word, 0, $needFillLength);
						}
						// $result = implode('', array_slice($chars, 0, $needFillLength));
					}
					break;
				}
			}
		}

		// echo 'row - ' . $result . "\n";

		return $result;
	}

	public static function arrayStrPad(array $rows, int $length, string $pad_string = " ", int $pad_type = STR_PAD_RIGHT): string
	{
		$result = '';
		foreach ($rows as $row) {
			// if (next($rows) === false) {;
			// 	$result .= self::mb_str_pad($row, $length, $pad_string, $pad_type);
			// } else {
			// 	$result .= "\n";
			// }

			$result .= self::mb_str_pad($row, $length, $pad_string, $pad_type);
			if (next($rows) !== false) {;
				$result .= "\n";
			}
		}
		return $result;
	}

	public static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
	{
		$diff = strlen($input) - mb_strlen($input);
		return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
	}
}
