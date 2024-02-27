<?php

namespace Custom\Currency\DB\Entity;

use Bitrix\Main\ORM;
use Bitrix\Main\Type;
use Custom\Currency\DB;

class CustomCurrencyTable extends DB\DataManager
{
	public static function getTableName()
	{
		return 'custom_currency';
	}

	public static function getMap()
	{
		return array(
			new ORM\Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true
			]),
			new ORM\Fields\StringField('CODE', [
				'required' => true,
			]),
			new ORM\Fields\DatetimeField('DATE_TIME', [
				'default_value' => function() { return new Type\DateTime(); },
			]),
			new ORM\Fields\FloatField('COURSE', [
				'required' => true,
			])
		);
	}
}
