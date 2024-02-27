<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CurrencyFilter extends CBitrixComponent
{
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

		$arParams['TEMPLATE_NAME'] = trim($this->getTemplateName()) !== '' ? $this->getTemplateName() : '.default' ;
		$arParams['SALT_SIGN'] =  'currency.list';
		$signedParams = ( new Main\Security\Sign\Signer )->sign(base64_encode(serialize($arParams)), $arParams['SALT_SIGN']);
		$arParams['SIGNED_PARAMS'] = CUtil::JSEscape($signedParams);

		$this->arResult = [
			'AJAX_PATH' => $this->getPath() . '/ajax.php',
			'FORM_ID' => 'form_filter',
			'ID_CONTAINER' => 'currencyList',
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
		$this->includeModules();

		$this->arResult['FIELDS'] = [
			[
				'FIELD_TYPE' => 'date',
				'FIELD_NAME' => 'dateFrom',
				'FILTER_FIELD' => '>=DATE_TIME',
				'FIELD_TITLE' => 'Дата от'
			],
			[
				'FIELD_TYPE' => 'date',
				'FIELD_NAME' => 'dateTo',
				'FILTER_FIELD' => '<=DATE_TIME',
				'FIELD_TITLE' => 'Дата до'
			],
			[
				'FIELD_TYPE' => 'number',
				'FIELD_NAME' => 'rateFrom',
				'FILTER_FIELD' => '>=COURSE',
				'FIELD_TITLE' => 'Курс от'
			],
			[
				'FIELD_TYPE' => 'number',
				'FIELD_NAME' => 'rateTo',
				'FILTER_FIELD' => '<=COURSE',
				'FIELD_TITLE' => 'Курс до'
			],
			[
				'FIELD_TYPE' => 'text',
				'FIELD_NAME' => 'currencyCode',
				'FILTER_FIELD' => '=CODE',
				'FIELD_TITLE' => 'Валюта'
			],
		];

		if ($this->request->isPost())
		{
			$filter = [];

			$requestList = $this->request->getValues();

			$apply = $requestList['apply'] === 'Y';
			foreach ($this->arResult['FIELDS'] as $fieldFilter)
			{
				if ($requestList[$fieldFilter['FIELD_NAME']] && $apply)
				{
					$valueFieldFilter = $requestList[$fieldFilter['FIELD_NAME']];

					if ($fieldFilter['FIELD_TYPE'] === 'date')
					{
						$valueFieldFilter = new Main\Type\DateTime($valueFieldFilter, "Y-m-d");
					}
					$filter[$fieldFilter['FILTER_FIELD']] = $valueFieldFilter;
				}
			}

			if ($apply)
			{
				$this->arResult['REQUEST'] = $this->request->getValues();
			}
			$GLOBALS['FILTER_CURRENCY'] = $this->arResult['FILTER'] = $filter;
		}

		$this->arResult['HAS_ERRORS'] = ($this->hasError());
		$this->includeComponentTemplate();
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