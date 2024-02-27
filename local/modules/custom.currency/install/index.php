<?php

use Bitrix\Main;
use Bitrix\Main\ModuleManager;
use Custom\Currency;

Main\Localization\Loc::loadMessages(__FILE__);

class custom_currency extends CModule
{
	public $MODULE_ID = 'custom.currency';
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $PARTNER_NAME;
	public $PARTNER_URI;

	public function __construct()
	{
		$arModuleVersion = null;

		include __DIR__ . '/version.php';

		if (isset($arModuleVersion) && is_array($arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = GetMessage('CUSTOM_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('CUSTOM_MODULE_DESCRIPTION');

		$this->PARTNER_NAME = GetMessage('CUSTOM_PARTNER_NAME');
		$this->PARTNER_URI = GetMessage('CUSTOM_PARTNER_URI');
	}

	public function DoInstall()
	{
		global $APPLICATION;

		$result = true;

		try
		{
			$this->checkRequirements();

			Main\ModuleManager::registerModule($this->MODULE_ID);

			if (Main\Loader::includeModule($this->MODULE_ID))
			{
				$this->InstallDB();
				$this->InstallEvents();
				$this->InstallAgents();
				$this->InstallFiles();

				$APPLICATION->IncludeAdminFile('', __DIR__ . '/step1.php');
			}
			else
			{
				throw new Main\SystemException(GetMessage('CUSTOM_MODULE_NOT_REGISTERED'));
			}
		}
		catch (Exception $exception)
		{
			$result = false;
			$APPLICATION->ThrowException($exception->getMessage());
		}

		return $result;
	}

	protected function checkRequirements()
	{
		// require php version

		$requirePhp = '7.4.0';

		if (CheckVersion(PHP_VERSION, $requirePhp) === false)
		{
			throw new Main\SystemException(GetMessage('CUSTOM_INSTALL_REQUIRE_PHP', ['#VERSION#' => $requirePhp]));
		}

		// required modules

		$requireModules = [
			'main' => '22.375.100',
			'iblock' => '22.100.100',
		];

		if (class_exists(ModuleManager::class))
		{
			foreach ($requireModules as $moduleName => $moduleVersion)
			{
				$currentVersion = Main\ModuleManager::getVersion($moduleName);

				if ($currentVersion !== false && CheckVersion($currentVersion, $moduleVersion))
				{
					unset($requireModules[$moduleName]);
				}
			}
		}

		if (!empty($requireModules))
		{
			$moduleVersion = reset($requireModules);
			$moduleName = key($requireModules);

			throw new Main\SystemException(GetMessage('CUSTOM_INSTALL_REQUIRE_MODULE', [
				'#MODULE#' => $moduleName,
				'#VERSION#' => $moduleVersion,
			]));
		}
	}

	/**
	 * @return string[]
	 */
	public function getClassListDB() : array
	{
		return [
			Currency\DB\Entity\CustomCurrencyTable::class
		];
	}

	public function InstallDB()
	{
		/**
		 * @var Bitrix\Main\Entity\DataManager $className
		 */
		foreach ($this->getClassListDB() as $className)
		{
			$dataManager = new Currency\DB\DataManager($className::getEntity());
			$dataManager->install();
		}
	}

	public function InstallEvents()
	{

	}

	public function InstallAgents()
	{
		CAgent::AddAgent(
			"\Custom\Currency\Agent\CurrencyAgent::runAgent();",
			$this->MODULE_ID,
			'Y'
		);
	}

	public function InstallFiles()
	{
		CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/admin', true, true);
		CopyDirFiles(__DIR__ . '/components', $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/components/' . $this->MODULE_ID, true, true);
	}

	/** @noinspection SpellCheckingInspection */
	public function DoUninstall()
	{
		global $APPLICATION, $step;

		$step = (int)$step;

		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage('CUSTOM_UNINSTALL_MODULE'), __DIR__ . '/unstep1.php');
		}
		elseif ($step === 2)
		{
			if (Main\Loader::includeModule($this->MODULE_ID))
			{
				$request = Main\Application::getInstance()->getContext()->getRequest();
				$isSaveData = $request->get('savedata') === 'Y';

				if (!$isSaveData)
				{
					$this->UnInstallDB();
				}

				$this->UnInstallEvents();
				$this->UnInstallAgents();
				$this->UnInstallFiles();
			}

			Main\ModuleManager::unRegisterModule($this->MODULE_ID);
		}
	}

	public function UnInstallDB()
	{
		/**
		 * @var Bitrix\Main\Entity\DataManager $className
		 */
		foreach ($this->getClassListDB() as $className)
		{
			$dataManager = new Currency\DB\DataManager($className::getEntity());
			$dataManager->uninstall();
		}
	}

	public function UnInstallEvents()
	{

	}

	public function UnInstallAgents()
	{
		$res = CAgent::GetList([], [
			'MODULE_ID' => $this->MODULE_ID
		]);

		while ($agent = $res->Fetch())
		{
			$result = CAgent::Delete($agent["ID"]);
		}

	}

	public function UnInstallFiles()
	{
		DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFilesEx(BX_ROOT . sprintf('/components/%s/', $this->MODULE_ID));
	}
}
