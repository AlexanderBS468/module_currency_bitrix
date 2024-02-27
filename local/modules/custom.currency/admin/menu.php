<?php

use Bitrix\Main\Localization\Loc;

global $USER;

Loc::loadMessages(__FILE__);

return [
	'parent_menu' => 'global_menu_services',
	'section' => 'customcurrency',
	'sort' => 1000,
	'text' => Loc::getMessage('MENU_CONTROL'),
	'title' => Loc::getMessage('MENU_TITLE'),
	'items_id' => 'menu_custom_currency',
	'items' => array_filter([
		[
			'text' => Loc::getMessage('MENU_CURRENCY_LIST'),
			'title' => Loc::getMessage('MENU_CURRENCY_LIST'),
			'url' => 'custom_currency_list.php?lang=' . LANGUAGE_ID,
		],
		[
			'text' => Loc::getMessage('MENU_SETTINGS'),
			'title' => Loc::getMessage('MENU_SETTINGS'),
			'url' => 'settings.php?mid=custom.currency&lang=' . LANGUAGE_ID,
			'hidden' => !($USER instanceof CUser && $USER->IsAdmin()),
		],
	], static function(array $item) {
		return empty($item['hidden']);
	}),
];
