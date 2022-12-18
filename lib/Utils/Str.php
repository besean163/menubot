<?php

namespace lib\Utils;

use Exception;
use LDAP\Result;

class Str
{
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
