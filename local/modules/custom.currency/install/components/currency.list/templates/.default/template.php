<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

/** @var array $arParams */
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

$pagesCount = [
	5, 10, 20, 50, 100
];

$sort = [
	'DATE_TIME/ASC' => 'По дате возрастанию',
	'DATE_TIME/DESC' => 'По дате убыванию',
	'COURSE/DESC' => 'По возрастанию цены',
	'COURSE/ASC' => 'По убыванию цены'
];

?>

<div id="<?=$arResult['ID_CONTAINER']?>" class="container message-container mt-5">
	<h1><?=Loc::getMessage('TITLE')?></h1>

	<div class="row">
		<div class="col-2 ml-auto form-group">
			<label for="nav_page_count"><?=Loc::getMessage('SORT_DATA')?></label>
			<select class="form-control" id="nav_page_count" name="page_count_currency" onchange="window.location.href = `${window.location.origin}${window.location.pathname}?sort_by=${this.value.split('/')[0]}&sort_order=${this.value.split('/')[1]}`">
				<?php
				foreach ($sort as $itemValue => $title)
				{
					?>
					<option <?=($itemValue == $arParams["SORT_BY1"] . '/' . $arParams['SORT_ORDER1']) ? 'selected' : ''?> value="<?=$itemValue?>"><?=$title?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="col-2 form-group">
			<label for="nav_page_count"><?=Loc::getMessage('TOTAL_COUNT')?></label>
			<select class="form-control" id="nav_page_count" name="page_count_currency" onchange="window.location.href = `${window.location.origin}${window.location.pathname}?page_count_currency=${this.value}`">
				<?php
				foreach ($pagesCount as $item)
				{
					?><option <?=$item == $arParams['ELEMENTS_COUNT'] ? 'selected' : ''?> value="<?=$item?>"><?=$item?></option><?php
				}
				?>
			</select>
		</div>
	</div>

	<?php
	if($arResult['HAS_ERRORS'])
	{
		$strError = implode('<br/>', $arResult['ERRORS']);
		ShowError($strError);
	}

	if(!empty($arResult['ITEMS']))
	{
		?>
		<table class="table">
			<thead>
			<tr>
				<th><?=Loc::getMessage('TITLE_CURRENCY')?></th>
				<th><?=Loc::getMessage('VALUE_RATE')?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($arResult['ITEMS'] as $dateGroup => $items)
			{
				?>
				<tr><td><?=$dateGroup?></td></tr>
				<?php
				foreach ($items as $item)
				{
					?>
					<tr>
						<td><?=$item['NAME']?></td>
						<td><?=$item['VALUE']?></td>
					</tr>
					<?php
				}

			}
			?>
			</tbody>
		</table>
		<?php
	}
	?>
</div>
