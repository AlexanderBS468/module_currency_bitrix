<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Main\Localization\Loc::loadMessages(__DIR__ . '/index.php');

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('CUSTOM_INSTALLED_TITLE', [ "#MODULE_NAME#" => Loc::getMessage('CUSTOM_MODULE_NAME') ]));

$message = new CAdminMessage([
	'TYPE' => 'OK',
	'MESSAGE' => Loc::getMessage("CUSTOM_INSTALLED_INTRO", [
		"#MODULE_NAME#" => Loc::getMessage('CUSTOM_MODULE_NAME'),
	]),
]);
echo $message->Show();

echo Loc::getMessage("CUSTOM_INSTALLED_TEXT", [
	'#LANGUAGE#' => LANGUAGE_ID,
]);

?>
<form action="<?= htmlspecialcharsbx($APPLICATION->GetCurPage()) ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" value="<?= Loc::getMessage('CUSTOM_INSTALLED_CLOSE') ?>">
</form>