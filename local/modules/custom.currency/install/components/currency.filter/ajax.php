<?php
const STOP_STATISTICS = true;
const NO_AGENT_CHECK = true;
const NOT_CHECK_PERMISSIONS = true;

use Bitrix\Main;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

Main\Localization\Loc::loadMessages(dirname(__FILE__).'/class.php');

$request = Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new Main\Web\PostDecodeFilter);
$template = $request->getQuery('template') ?: '.default';
$componentName = 'currency.filter';
$componentGroup = 'custom';

try
{
	$signedParamsString = $request->getPost('signedParamsString') ?: '';
	$salt = $request->getQuery('salt_sign_add') ?: $componentName;
	$signer = new Main\Security\Sign\Signer;
	$paramsUnsign = $signer->unsign($signedParamsString, $salt);
	$params = unserialize(base64_decode($paramsUnsign));
}
catch(Main\Security\Sign\BadSignatureException $e)
{
	die('Bad signature.');
}
catch (Main\ArgumentTypeException $e)
{
	die('Argument Exception' . $e->getMessage());
}

$GLOBALS['APPLICATION']->IncludeComponent(
	implode(':', [$componentGroup, $componentName]),
	$template,
	$params
);
?>