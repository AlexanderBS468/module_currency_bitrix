<?php
$path = sprintf('/%s/modules/custom.currency/admin/currency_list.php', 'bitrix');

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path))
{
	$path = sprintf('/%s/modules/custom.currency/admin/currency_list.php', 'local');
}

require_once $_SERVER['DOCUMENT_ROOT'] . $path;
