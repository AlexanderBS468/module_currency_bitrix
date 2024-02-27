<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('ADMIN_DOCUMENTS_PAGE_TITLE'));

?>
<div class="row">
	<div class="col-2">
		<?php
		$APPLICATION->IncludeComponent(
			'custom.currency:currency.filter',
			'.default',
			array(
			),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		?>
	</div>
	<div class="col-9">
		<?php
		$APPLICATION->IncludeComponent(
			'custom.currency:currency.list',
			'.default',
			array(
				"ELEMENTS_COUNT" => 5,
			),
			false,
			array('HIDE_ICONS' => 'Y')
		);

		$APPLICATION->IncludeComponent(
			"bitrix:main.pagenavigation",
			"",
			array(
				"NAV_OBJECT" => $GLOBALS['NAV_CURRENCY'],
				"SEF_MODE" => "N",
			),
			false
		);
		?>
	</div>
</div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
