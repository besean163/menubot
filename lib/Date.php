<?php

namespace lib;

use DateInterval;
use DateTimeImmutable;
use Exception;

class Date extends DateTimeImmutable
{
	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	const DATE_FORMAT = 'Y-m-d';


	public static function getThisWeekWorkDays(): array
	{
		$today = self::today();
		$firstWeekDate = $today->getFirstWeekDayDate();
		$days = self::dates($firstWeekDate, $firstWeekDate->addDays(4));

		return $days;
	}

	public static function getNextWeekWorkDays(): array
	{
		$today = self::today();
		$firstWeekDate = $today->getFirstWeekDayDate()->addDays(7);
		$days = self::dates($firstWeekDate, $firstWeekDate->addDays(4));

		return $days;
	}

	public static function dates(self $start, self $end): array
	{
		$interval = $start->diff($end);
		if ($interval->invert == 1) {
			throw new Exception("Start date must be earlier then end date.");
		}

		$days = [];
		$addDate = $start;
		for ($day = 0; $day <= $interval->days; $day++) {
			array_push($days, $addDate->getDateISO());
			$addDate = $addDate->addDays(1);
		}

		return $days;
	}


	public static function today(): self
	{
		return new self('today');
	}

	public static function now(): self
	{
		return new self('now');
	}

	public function addDays(int $days): self
	{
		$inteval = new DateInterval("P" . abs($days) . "D");
		if ($days < 0) {
			$inteval->invert = 1;
		}
		return $this->add($inteval);
	}

	public function getDateTimeISO(): string
	{
		return $this->format(self::DATE_TIME_FORMAT);
	}

	public function getDateISO(): string
	{
		return $this->format(self::DATE_FORMAT);
	}

	public function getWeekDay(): string
	{
		return $this->format('N');
	}

	public function getCyrillicWeekDay(): string
	{
		$cyrillicWeekDays = [
			1 => 'Понедельник',
			2 => 'Вторник',
			3 => 'Среда',
			4 => 'Четверг',
			5 => 'Пятница',
			6 => 'Суббота',
			7 => 'Воскресенье',
		];

		return $cyrillicWeekDays[$this->getWeekDay()];
	}

	public function getFirstWeekDayDate(): self
	{
		return $this->getWeekDay() === 1 ? $this : $this->addDays(1 - $this->getWeekDay());
	}

	public static function parseFromHumanReadFromObed(string $humanReadDate): self
	{
		// $monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
		$monthNamesShort = [
			"янв" => '01',
			"фев" => '02',
			"мар" => '03',
			"апр" => '04',
			"май" => '05',
			"июн" => '06',
			"июл" => '07',
			"авг" => '08',
			"сен" => '09',
			"окт" => '10',
			"ноя" => '11',
			"дек" => '12'
		];

		$pattern = '/(\d+).+?(\W+?)[,\s]+(\W+)/';
		preg_match($pattern, $humanReadDate, $matches);
		$dayNumber = (int) $matches[1];
		$monthShort = mb_strtolower($matches[2]);
		// $dayShortName = $matches[3];

		$nowYear = self::today()->format('Y');
		$monthNumber = $monthNamesShort[$monthShort];
		$detectDate = $nowYear . '-' . $monthNumber . '-' . $dayNumber;

		return new self($detectDate);
	}
}
