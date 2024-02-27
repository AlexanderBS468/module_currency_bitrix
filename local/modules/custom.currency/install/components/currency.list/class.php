<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;
use Custom\Currency\DB;

Loc::loadMessages(__FILE__);

class CurrencyList extends CBitrixComponent
{
	private const ACTION_FROM_VALUE = 'ACTION_FROM';
	protected array $arErrors = [];
	protected array $modules = [
		'custom.currency'
	];

	/**
	 * @description prepare params
	 * @param $arParams
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function onPrepareComponentParams($arParams) : array
	{
		$arParams = parent::onPrepareComponentParams($arParams);

		$arParams["SORT_BY1"] = $arParams["SORT_BY1"] ?: "DATE_TIME";
		$arParams["SORT_ORDER1"] = $arParams["SORT_ORDER1"] ?: "DESC";
		$arParams["ELEMENTS_COUNT"] = $arParams["ELEMENTS_COUNT"] ?: 5;

		$requestList = $this->request->getValues();
		if ($elementCount = $requestList['page_count_currency'])
		{
			$arParams["ELEMENTS_COUNT"] = $elementCount;
		}

		if ($requestList['sort_by'] && $requestList['sort_order'])
		{
			$arParams["SORT_BY1"] = $requestList['sort_by'];
			$arParams["SORT_ORDER1"] = $requestList['sort_order'];
		}

		if (!isset($arParams["CACHE_TIME"]))
		{
			$arParams["CACHE_TIME"] = 86400;
		}

		$arParams['ACTION'] = $arParams['ACTION'] ?: self::ACTION_FROM_VALUE;

		$arParams['TEMPLATE_NAME'] = trim($this->getTemplateName()) !== '' ? $this->getTemplateName() : '.default' ;
		$arParams['SALT_SIGN'] =  'currency.list';
		$signedParams = ( new Main\Security\Sign\Signer )->sign(base64_encode(serialize($arParams)), $arParams['SALT_SIGN']);
		$arParams['SIGNED_PARAMS'] = CUtil::JSEscape($signedParams);

		$this->arResult = [
			'AJAX_PATH' => $this->getPath() . '/ajax.php',
			'ID_CONTAINER' => 'block_data',
			'ERRORS' => [],
		];

		$this->arErrors =& $this->arResult['ERRORS'];

		return $arParams;
	}

	/**
	 * @description default start method component
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function executeComponent()
	{
		if($this->startResultCache(false, array($this->arParams['SORT_BY1'], $this->arParams['SORT_ORDER1'])))
		{
			if ($this->includeModules())
			{
				$this->abortResultCache();
			}

			$this->makeResult();

			if (empty($this->arResult))
			{
				$this->abortResultCache();
			}
		}

		$this->arResult['HAS_ERRORS'] = ($this->hasError());
		$this->includeComponentTemplate();
	}

	protected function makeResult() : void
	{
		$groupedData = $filter = [];

		$nav = new Main\UI\PageNavigation("nav-more");
		$nav->allowAllRecords(true)
		    ->setPageSize($this->arParams["ELEMENTS_COUNT"])
		    ->initFromUri();

		if ($GLOBALS['FILTER_CURRENCY'])
		{
			$filter = $GLOBALS['FILTER_CURRENCY'];
		}

		$rsDb = DB\Entity\CustomCurrencyTable::getList([
			"filter" => $filter,
			"order" => [
				$this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1']
			],
			"count_total" => true,
			"offset" => $nav->getOffset(),
			"limit" => $nav->getLimit(),
		]);

		$nav->setRecordCount($rsDb->getCount());

		$rsDbRows = $rsDb->fetchAll();

		foreach ($rsDbRows as $item)
		{
			$date = date('Y-m-d', strtotime($item['DATE_TIME']));

			if (!array_key_exists($date, $groupedData)) {
				$groupedData[$date] = [];
			}

			$groupedData[$date][] = [
				'NAME' => $item['CODE'],
				'VALUE' => $item['COURSE'],
			];
		}

		$this->arResult["ITEMS"] = $groupedData;

		$GLOBALS["NAV_CURRENCY"] = $nav;
	}

	protected function includeModules() : bool
	{
		foreach ($this->modules as $module)
		{
			if (!Loader::includeModule($module))
			{
				$mess = sprintf('CUSTOM_C_ERROR_MODULE_%s_NOT_INSTALL', strtoupper($module));
				$this->setError(Loc::getMessage($mess));
			}
		}

		return !$this->hasError();
	}

	protected function isAjax() : bool
	{
		return ($this->request->get('via_ajax') === 'Y');
	}

	public function hasError() : bool
	{
		return !empty($this->arErrors);
	}

	public function setError($message) : void
	{
		if($message instanceof Main\Result)
		{
			$errors = $message->getErrorMessages();
		}
		else
		{
			$errors = array($message);
		}

		foreach($errors as $error)
		{
			if(!in_array($error, $this->arErrors, true))
			{
				$this->arErrors[] = $error;
			}
		}
	}
}