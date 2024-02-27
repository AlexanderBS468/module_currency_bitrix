<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION;

if (!$USER->IsAdmin())
{
	ShowError(Loc::getMessage('SETTINGS_ACCESS_DENIED'));
	return;
}

$moduleID = 'custom.currency';

$fields = [
	'CURRENCY_LIST' => [
		'TITLE' => Loc::getMessage('CURRENCY_LIST'),
		'TYPE' => 'select_multiple',
		'VALUES' => [
			'EUR',
			'USD'
		]
	]
];

if ($_POST['update_settings'] == "Y" && check_bitrix_sessid())
{
	$fieldRequest = '';

	foreach($fields as $fieldKey => $fieldName)
	{
		if (array_key_exists($fieldKey, $_POST))
		{
			if (is_string($_POST[$fieldKey]))
			{
				$fieldRequest = trim($_POST[$fieldKey]);
			}

			if (is_array($_POST[$fieldKey]))
			{
				$fieldsRequest = [];
				foreach ($_POST[$fieldKey] as $value)
				{
					$fieldsRequest[] = trim($value);
				}

				$fieldRequest = serialize($fieldsRequest);
			}

			if (mb_strlen($fieldRequest))
			{
				\COption::SetOptionString($moduleID, $fieldKey, $fieldRequest);
			}
			else
			{
				\COption::RemoveOption($moduleID, $fieldKey);
			}
		}
	}

	LocalRedirect($APPLICATION->GetCurPageParam());
}

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage('OPTION_TAB_LABEL'),
		"TITLE" => GetMessage('OPTION_TAB_LABEL')
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();
?>
	<form method="POST" action="<?= $APPLICATION->GetCurPageParam()?>">
		<?php
		echo bitrix_sessid_post();

		$tabControl->BeginNextTab();

		foreach ($fields as $fieldKey => $field)
		{
			$fieldValue = \COption::GetOptionString($moduleID, $fieldKey);

			$controlId = $fieldKey;
			?>
			<tr>
				<td width="40%" class="adm-detail-valign-top">
					<label><?= $field['TITLE']; ?></label>
				</td>
				<td width="60%">
				<?php
				switch ($field['TYPE'])
				{
					case "select_multiple":
						$fieldValue = unserialize($fieldValue);
						?>
							<select id="<?=$controlId; ?>" name="<?= $controlId ?>[]" multiple>
								<?php
								foreach ($field['VALUES'] as $index => $value)
								{
									?><option value="<?=$value ?>"<?=(in_array($value, $field['VALUES']) ? ' selected' : '');?>><?=htmlspecialcharsbx($value); ?></option><?php
								}
								?>
							</select>
						<?php
						break;
					case "text":
						?><input type="text" id="<?= $controlId ?>" name="<?= $controlId ?>" value="<?=htmlspecialcharsbx($fieldValue); ?>" size="<?=$field['SIZE']; ?>" maxlength="255"><?php
						break;
				}
				?>
				</td>
			</tr>
			<?php
		}

		$tabControl->Buttons();
		?>
		<input type="submit" value="<?= GetMessage('OPTION_APPLY') ?>">
		<input type="hidden" name="update_settings" value="Y">

	</form>
<?php
$tabControl->End();