<?php

namespace Custom\Currency\Agent;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Custom\Currency;

class CurrencyAgent
{
	private const MODULE_NAME = 'custom.currency';

	/** @todo Make a list of currencies, remove static data later */
	private static array $codes = [
		'USD',
		'EUR'
	];

	public static function runAgent()
	{
		$xml = self::getDataCurrency();
		if ($xml === false)
		{
			AddMessage2Log('Service cbr.ru return null', self::MODULE_NAME);
		}
		elseif ($xml instanceof \SimpleXMLElement)
		{
			self::addData($xml);
		}

		return '\Custom\Currency\Agent\CurrencyAgent::runAgent();';
	}

	public static function getDataCurrency()
	{
		$httpClient = new HttpClient();
		$response = $httpClient->get('https://www.cbr.ru/scripts/XML_daily.asp');

		$xml = new \SimpleXMLElement($response);
		return $xml;
	}

	public static function addData(\SimpleXMLElement $xml) : void
	{
		$date = (string)$xml->attributes()->Date;
		$isExist = self::isExistDate($date);

		if ($isExist) { return; }

		foreach ($xml->Valute as $valute)
		{
			$codes = self::$codes;
			$charCode = (string)$valute->CharCode;
			if (!in_array($charCode, $codes)) { continue; }
			$fields = [
				'CODE' => $charCode,
				'DATE_TIME' => new Main\Type\DateTime($date, 'd.m.Y'),
				'COURSE' => (float)str_replace(',', '.', $valute->Value)
			];

			$rsData = Currency\DB\Entity\CustomCurrencyTable::add($fields);

			if (!$rsData->isSuccess())
			{
				$mess = sprintf('Error add data: %s', implode(';', $rsData->getErrorMessages()));
				AddMessage2Log($mess, self::MODULE_NAME);
			}
		}
	}

	public static function isExistDate(string $dateString) : bool
	{
		$date = new Main\Type\DateTime($dateString, 'd.m.Y');

		$rsData = Currency\DB\Entity\CustomCurrencyTable::getList([
			'filter' => [
				'DATE_TIME' => $date
			],
			'select' => [
				'CODE',
				'DATE_TIME'
			]
		])->fetchAll();

		if(empty($rsData))
		{
			return false;
		}

		$codes = self::$codes;

		$checked = array_combine($codes, (array)false);
		foreach ($rsData as $row)
		{
			if(in_array($row['CODE'], $codes))
			{
				$checked[$row['CODE']] = true;
			}
		}

		return count($codes) === count(array_filter($checked));
	}
}
