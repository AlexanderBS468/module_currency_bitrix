<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

/** @var array $arResult$arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Main\UI\Extension::load("ui.bootstrap4");

?>

<div id="<?=$arResult['ID_CONTAINER']?>" class="container message-container mt-5">
	<?php
	if($arResult['HAS_ERRORS'])
	{
		$strError = implode('<br/>', $arResult['ERRORS']);
		ShowError($strError);
	}

	if(!empty($arResult['FIELDS']))
	{
		?>
		<form id="<?=$arResult['FORM_ID']?>" action="<?=POST_FORM_ACTION_URI?>" method="post" id="filters" name="filter" enctype="multipart/form-data">
			<?php
			echo bitrix_sessid_post();
			foreach ($arResult['FIELDS'] as $field)
			{
				?>
				<div class="form-group">
					<label for="<?=$field['FIELD_NAME']?>"><?=$field['FIELD_TITLE']?></label>
					<input class="form-control" type="<?=$field['FIELD_TYPE']?>" id="<?=$field['FIELD_NAME']?>" name="<?=$field['FIELD_NAME']?>" value="<?=$arResult['REQUEST'][$field['FIELD_NAME']]?>">
				</div>
				<?php
			}
			?>
			<button type="submit" class="btn btn-success btn-default btn-submit mt-3" name="apply" value="Y"><?=Loc::getMessage('SUBMIT')?></button>
			<button type="submit" class="btn btn-primary btn-default btn-submit mt-3" name="reset" value="Y"><?=Loc::getMessage('RESET')?></button>
		</form>
		<?php
	}
	?>
</div>
