<?php
/**
 * Страница редактирования тега модуля vasoft.tags
 * @author Воробьев Александр
 * @see https://va-soft.ru/
 * @package vasoft.tags
 */
use Bitrix\Main\Localization\Loc,
	Vasoft\Tags\TagsTable;

global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

Loc::loadLanguageFile(__FILE__);

$POST_RIGHT = $GLOBALS['APPLICATION']->GetGroupRight("vasoft.tags");
if ($POST_RIGHT == "D" || !\Bitrix\Main\Loader::includeModule('vasoft.tags')) {
	$GLOBALS['APPLICATION']->AuthForm(GetMessage("ACCESS_DENIED"));
}

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => Loc::getMessage("VASOFT_TAGS_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => Loc::getMessage("VASOFT_TAGS_TAB_TITLE")
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);        // идентификатор редактируемой записи
$message = null;        // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //
$lastError = '';

if (
	$REQUEST_METHOD == "POST" // проверка метода вызова страницы
	&& ($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
	&& $POST_RIGHT == "W"          // проверка наличия прав на запись для модуля
	&& check_bitrix_sessid()     // проверка идентификатора сессии
) {
	$tag = new TagsTable();
	$arFields = Array(
		'LID' => $LID,
		"PHRASE" => $PHRASE,
		"CODE" => $CODE,
		"TITLE" => $TITLE,
		"KEYWORDS" => $KEYWORDS,
		"BROWSER_TITLE" => $BROWSER_TITLE,
		"DESCRIPTION" => $DESCRIPTION,
		"TEXT" => $TEXT,
		'TEXT_TYPE' => $TEXT_TYPE
	);
	$res = false;
	if ($ID > 0) {
		$result = $tag->Update($ID, $arFields);
		if ($result->isSuccess()) {
			$res = true;
		} else {
			$lastError = implode('<br>', $result->getErrorMessages());
		}
	} else {
		$result = $tag->Add($arFields);
		if ($result->isSuccess()) {
			$ID = $result->getId();
			$res = true;
		} else {
			$lastError = implode('<br>', $result->getErrorMessages());
		}
	}

	if ($res) {
		if ($apply != "")
			LocalRedirect("/bitrix/admin/vasoft_tags_tag_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
		else
			// если была нажата кнопка "Сохранить" - отправляем к списку элементов.
			LocalRedirect("/bitrix/admin/vasoft_tags_tags.php?lang=" . LANG);
	} else {
		// если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(Loc::getMessage("VASOFT_TAGS_ERROR_SAVE"), $e);
		$bVarsFromForm = true;
	}
}

// ******************************************************************** //
//                ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     //
// ******************************************************************** //

// значения по умолчанию
$str_LID = '';
$str_PHRASE = '';
$str_CODE = "";
$str_TITLE = "";
$str_BROWSER_TITLE = "";
$str_KEYWORDS = "";
$str_DESCRIPTION = "";
$str_TEXT = "";
$str_TEXT_TYPE = 'html';

// выборка данных
if ($ID > 0) {
	$item = TagsTable::getById($ID);
	$arItem = $item->fetch();
	if ($arItem) {
		foreach ($arItem as $code => $value) {
			${'str_' . $code} = $value;
		}
	} else {
		$ID = 0;
	}
}

// если данные переданы из формы, инициализируем их
if ($bVarsFromForm)
	$DB->InitTableVarsForEdit(TagsTable::getTableName(), "", "str_");

// ******************************************************************** //
//                ВЫВОД ФОРМЫ                                           //
// ******************************************************************** //

// установим заголовок страницы
$APPLICATION->SetTitle(($ID > 0 ? Loc::getMessage("VASOFT_TAGS_TITLE_EDIT") . $ID : Loc::getMessage("VASOFT_TAGS_TITLE_CREATE")));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// конфигурация административного меню
$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("VASOFT_TAGS_LIST"),
		"TITLE" => Loc::getMessage("VASOFT_TAGS_LIST"),
		"LINK" => "vasoft_tags_tags.php?lang=" . LANG,
		"ICON" => "btn_list",
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if ($_REQUEST["mess"] == "ok" && $ID > 0) {
	CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("VASOFT_TAGS_SAVED"), "TYPE" => "OK"));
}

if ($message) {
	echo $message->Show();
} elseif ($lastError != "") {
	CAdminMessage::ShowMessage($lastError);
}
?>
<form method="POST" Action="<? echo $APPLICATION->GetCurPage() ?>" ENCTYPE="multipart/form-data" name="post_form">
	<?php
	echo bitrix_sessid_post();
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><?= Loc::getMessage("VASOFT_TAGS_FIELD_SITE"); ?><span class="required">*</span></td>
		<td width="60%"><?= CLang::SelectBox("LID", $str_LID); ?></td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage("VASOFT_TAGS_FIELD_PHRASE"); ?><span class="required">*</span></td>
		<td width="60%"><input type="text" name="PHRASE" value="<? echo $str_PHRASE; ?>" size="30" maxlength="100"
							   required></td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage("VASOFT_TAGS_FIELD_CODE"); ?><span class="required">*</span></td>
		<td width="60%"><input type="text" name="CODE" value="<? echo $str_CODE; ?>" size="30" maxlength="100" required>
		</td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage("VASOFT_TAGS_FIELD_TITLE"); ?></td>
		<td width="60%"><input type="text" name="TITLE" value="<? echo $str_TITLE; ?>" size="30" maxlength="250"></td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage("VASOFT_TAGS_FIELD_BROWSER_TITLE"); ?></td>
		<td width="60%"><input type="text" name="BROWSER_TITLE" value="<? echo $str_BROWSER_TITLE; ?>" size="30"
							   maxlength="255"></td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage("VASOFT_TAGS_FIELD_EYWORDS"); ?></td>
		<td width="60%"><input type="text" name="KEYWORDS" value="<? echo $str_KEYWORDS; ?>" size="30" maxlength="255">
		</td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage("VASOFT_TAGS_FIELD_DESCRIPTION"); ?></td>
		<td width="60%"><textarea name="DESCRIPTION" cols="60" row="10"><? echo $str_DESCRIPTION; ?></textarea></td>
	</tr>
	<tr>
		<td colspan="2">
			<? echo Loc::getMessage("VASOFT_TAGS_FIELD_TEXT"); ?>
			<?CFileMan::AddHTMLEditorFrame(
				"TEXT",
				$str_TEXT,
				"TEXT_TYPE",
				$str_TEXT_TYPE,
				array(
					'height' => 450,
					'width' => '100%'
				),
				"N",
				0,
				"",
				"",
				$str_LID,
				true,
				false,
				array(
					'hideTypeSelector' => false,
				)
			);?></td>
	</tr>
	<?
	// завершение формы - вывод кнопок сохранения изменений
	$tabControl->Buttons(
		array(
			"disabled" => ($POST_RIGHT < "W"),
			"back_url" => "vasoft_tags_tags.php?lang=" . LANG,
		)
	);
	?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<? if ($ID > 0 && !$bCopy): ?>
		<input type="hidden" name="ID" value="<?= $ID ?>">
	<? endif; ?>
	<?
	$tabControl->End();
	$tabControl->ShowWarnings("post_form", $message);
	?>
	<? echo BeginNote(); ?>
	<span class="required">*</span><? echo GetMessage("REQUIRED_FIELDS") ?>
	<? echo EndNote(); ?>
</form>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
