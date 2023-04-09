<?php
/**
 * Страница настроек модуля vasoft.tags
 * @author Воробьев Александр
 * @see https://va-soft.ru/
 * @package vasoft.tags
 */
use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$module_id = "vasoft.tags";
$MODULE_RIGHT = CMain::GetGroupRight($module_id);
if ($MODULE_RIGHT >= "R" && Loader::includeModule($module_id)) {
	$arDisplayOptions = array(
		"PATH" => array(
			'TAB' => 'tags',
			'NAME' => Loc::getMessage("VASOFT_TAGS_PAGE_URL"),
			'DEFAULT' => Option::get($module_id, 'PATH'),
			'OPTIONS' => array(
				'TYPE' => 'string',
				'SIZE' => 50
			)
		),
		"PROTOCOL" => array(
			'TAB' => 'tags',
			'NAME' => Loc::getMessage("VASOFT_TAGS_PROTOKOL"),
			'DEFAULT' => Option::get($module_id, 'PROTOCOL'),
			'OPTIONS' => array(
				'TYPE' => 'select',
				'LIST' => array(
					'https' => 'https',
					'http' => 'http'
				)
			)
		),
	);
	$aTabs = array();
	$arSites = array();
	$rsSites = CSite::GetList($by = "sort", $order = "asc");
	while ($arSite = $rsSites->Fetch()) {
		$aTabs[] = array("DIV" => "tags_" . $arSite['LID'], "TAB" => Loc::getMessage("VASOFT_TAGS_SETTINGS") . $arSite['LID'], "TITLE" => Loc::getMessage("VASOFT_TAGS_SETTINGS") . $arSite['NAME']);
		$arSites[] = $arSite['LID'];
	}
	if ($REQUEST_METHOD == "GET" && $MODULE_RIGHT == "W" && strlen($RestoreDefaults) > 0 && check_bitrix_sessid()) {
		COption::RemoveOption($module_id);
		$z = CGroup::GetList($v1 = "id", $v2 = "asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while ($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}
	if ($REQUEST_METHOD == "POST" && strlen($Update) > 0 && $MODULE_RIGHT == "W" && check_bitrix_sessid()) {
		foreach ($arDisplayOptions as $key => $arOption) {
			foreach ($arSites as $site) {
				$sKey = $key . '-' . $site;
				if (!array_key_exists($sKey, $_POST))
					continue;
				Option::set($module_id, $key, ${$sKey}, $site);
				if ($key == 'PATH' && isset($_POST['URLREWRITE-' . $site]) && $_POST['URLREWRITE-' . $site] == 'Y') {
					\CUrlRewriter::Delete(array(
						'SITE_ID' => $site,
						'ID' => 'vasoft:tags.page'
					));
					\CUrlRewriter::Add(array(
						'SITE_ID' => $site,
						"CONDITION" => str_replace('//', '/', "#^/" . ${$sKey} . "/#"),
						"RULE" => "",
						"ID" => "vasoft:tags.page",
						"PATH" => str_replace('//', '/', "/" . ${$sKey} . "/index.php")
					));
				}
			}
			if (!array_key_exists($key, $_POST))
				continue;
			Option::set($module_id, $key, ${$key});
		}
	}

	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS"));

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	?>
	<form method="post"
		  action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>">
		<?= bitrix_sessid_post() ?>
		<? foreach ($arSites as $site): ?>
			<? $tabControl->BeginNextTab(); ?>
			<?
			if (is_array($arDisplayOptions)):
				foreach ($arDisplayOptions as $key => $arOption):
					if ($arOption['TAB'] != 'tags') continue;
					$sKey = $key . '-' . $site;
					$value = Option::get($module_id, $key, $arOption['DEFAULT'], $site);
					?>
					<tr>
						<td valign="top" width="50%"><?= $arOption['NAME']; ?></td>
						<td valign="top" width="50%">
							<? if ($arOption['OPTIONS']['TYPE'] == 'string'): ?>
								<input type="text" size="<?= $arOption['OPTION']['SIZE'] ?>" maxlength="255"
									   value="<?= $value ?>"
									   name="<?= $sKey ?>">
							<? elseif ($arOption['OPTIONS']['TYPE'] == 'select'): ?>
								<select name="<?= $sKey ?>">
									<? foreach ($arOption['OPTIONS']['LIST'] as $val => $name): ?>
										<option value="<?= $val ?>"<? if ($value == $val) echo ' selected'; ?>><?= $name ?></option>
									<? endforeach; ?>
								</select>
							<? endif ?>
						</td>
					</tr>
				<? endforeach; ?>
				<tr>
					<td valign="top" width="50%"><?= Loc::getMessage("VASOFT_TAGS_URL_REWRITE") ?></td>
					<td valign="top" width="50%"><input type="checkbox" name="URLREWRITE-<?= $site ?>" value="Y"></td>
				</tr>
			<? endif; ?>
		<? endforeach ?>
		<? $tabControl->BeginNextTab(); ?>
		<? require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php"); ?>
		<? $tabControl->Buttons(); ?>
		<script language="JavaScript">
			function RestoreDefaults() {
				if (confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
					window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
			}
		</script>
		<input <? if ($MODULE_RIGHT < "W") echo "disabled" ?> type="submit" name="Update"
															  value="<?= Loc::getMessage("VASOFT_TAGS_SAVE") ?>">
		<input type="hidden" name="Update" value="Y">
		<input <? if ($MODULE_RIGHT < "W") echo "disabled" ?> type="button"
															  title="<? echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
															  onclick="RestoreDefaults();"
															  value="<?= Loc::getMessage("VASOFT_TAGS_DEFAULT") ?>">
		<? $tabControl->End(); ?>
	</form>
<?
}


