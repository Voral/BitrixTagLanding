<?php
/**
 * Список тегов для административной части vasoft.tags
 *
 * @autor Александр Воробьев
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

$sTableID = TagsTable::getTableName();
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);


// ******************************************************************** //
//                           ФИЛЬТР                                     //
// ******************************************************************** //

// *********************** CheckFilter ******************************** //
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	// В данном случае проверять нечего.
	// В общем случае нужно проверять значения переменных $find_имя
	// и в случае возниконовения ошибки передавать ее обработчику
	// посредством $lAdmin->AddFilterError('текст_ошибки').
	return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// *********************** /CheckFilter ******************************* //

// опишем элементы фильтра
$FilterArr = Array(
	"find_phrase",
	"find_code",
	"find_title",
	'find_lid'
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);
$arFilter = array();
if (CheckFilter()) {
	if ($find_code != '') $arFilter['CODE'] = $find_code;
	if ($find_phrase != '') $arFilter['PHRASE'] = $find_phrase;
	if ($find_title != '') $arFilter['TITLE'] = $find_title;
	if ($find_lid != '') $arFilter['LID'] = $find_lid;
}


// ******************************************************************** //
//                ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ СПИСКА              //
// ******************************************************************** //
if (isset($_REQUEST['map']) && $POST_RIGHT == "W") {
	$cite = ($_REQUEST['map'] == 'all' || trim($_REQUEST['map']) == '') ? '' : $_REQUEST['map'];
	TagsTable::generateMap($cite);
}
// сохранение отредактированных элементов
if ($lAdmin->EditAction() && $POST_RIGHT == "W") {
	// пройдем по списку переданных элементов
	foreach ($FIELDS as $ID => $arFields) {
		if (!$lAdmin->IsUpdated($ID))
			continue;

		// сохраним изменения каждого элемента
		$DB->StartTransaction();
		$ID = IntVal($ID);
		$cData = new TagsTable();
		if (($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch())) {
			foreach ($arFields as $key => $value)
				$arData[$key] = $value;
			if (!$cData->Update($ID, $arData)) {
				$lAdmin->AddGroupError(Loc::getMessage("VASOFT_TAGS_ERROR_SAVE"), $ID);
				$DB->Rollback();
			}
		} else {
			$lAdmin->AddGroupError(Loc::getMessage("VASOFT_TAGS_ERROR_SAVE") . Loc::getMessage("VASOFT_TAGS_ERROR_NOT_EXISTS"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

// обработка одиночных и групповых действий
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
	// если выбрано "Для всех элементов"
	if ($_REQUEST['action_target'] == 'selected') {
		$cData = new TagsTable();
		$rsData = $cData->getList(array($by => $order), $arFilter);
		while ($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	// пройдем по списку элементов
	foreach ($arID as $ID) {
		if (strlen($ID) <= 0)
			continue;
		$ID = IntVal($ID);

		// для каждого элемента совершим требуемое действие
		switch ($_REQUEST['action']) {
			// удаление
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!TagsTable::delete($ID)) {
					$DB->Rollback();
					$lAdmin->AddGroupError(Loc::getMessage("VASOFT_TAGS_ERROR_DELETE"), $ID);
				}
				$DB->Commit();
				break;

			// активация/деактивация
			case "activate":
			case "deactivate":
				$cData = new TagsTable();
				if (($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch())) {
					$arFields["ACTIVE"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
					if (!$cData->Update($ID, $arFields))
						$lAdmin->AddGroupError(Loc::getMessage("VASOFT_TAGS_ERROR_SAVE"), $ID);
				} else
					$lAdmin->AddGroupError(Loc::getMessage("VASOFT_TAGS_ERROR_SAVE") . Loc::getMessage("VASOFT_TAGS_ERROR_NOT_EXISTS"), $ID);
				break;
		}
	}
}

// ******************************************************************** //
//                ВЫБОРКА ЭЛЕМЕНТОВ СПИСКА                              //
// ******************************************************************** //

$arSites = array();
$rsSites = CSite::GetList($by1 = "sort", $order1 = "asc");
$currentCite = '';
while ($arSite = $rsSites->Fetch()) {
	$arSites[$arSite['LID']] = $arSite;
	if ($_SERVER['DOCUMENT_ROOT'] == $arSite['ABS_DOC_ROOT']) {
		$currentCite = $arSite['LID'];
	}
}

// выберем список рассылок
$cData = new TagsTable();

$arOptions = array(
	'order' => array($by => $order),
);
if (!empty($arFilter)) {
	$arOptions['filter'] = $arFilter;
}
$rsData = $cData->getList($arOptions);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("VASOFT_TAGS_NAV_TITLE")));

// ******************************************************************** //
//                ПОДГОТОВКА СПИСКА К ВЫВОДУ                            //
// ******************************************************************** //

$lAdmin->AddHeaders(array(
	array("id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"align" => "right",
		"default" => true,
	),
	array("id" => "PHRASE",
		"content" => Loc::getMessage("VASOFT_TAGS_FIELD_PHRASE"),
		"sort" => "PHRASE",
		"default" => true,
	),
	array("id" => "CODE",
		"content" => Loc::getMessage("VASOFT_TAGS_FIELD_CODE"),
		"sort" => "CODE",
		"default" => true,
	),
	array("id" => "TITLE",
		"content" => Loc::getMessage("VASOFT_TAGS_FIELD_TITLE"),
		"sort" => "TITLE",
		"default" => true,
	),
	array("id" => "LID",
		"content" => Loc::getMessage("VASOFT_TAGS_FIELD_LID"),
		"sort" => "LID",
		"default" => true,
	),
	array("id" => "BROWSER_TITLE",
		"content" => Loc::getMessage("VASOFT_TAGS_FIELD_BROWSER_TITLE"),
		"sort" => "BROWSER_TITLE",
		"default" => false,
	),
	array("id" => "KEYWORDS",
		"content" => Loc::getMessage("VASOFT_TAGS_FIELD_KEYWORDS"),
		"sort" => "KEYWORDS",
		"default" => false,
	),
));

while ($arRes = $rsData->NavNext(true, "f_")):

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("PHRASE", array("size" => 20));
	$row->AddInputField("CODE", array("size" => 20));
	$row->AddInputField("TITLE", array("size" => 20));
	$row->AddInputField("BROWSER_TITLE", array("size" => 20));
	$row->AddViewField("PHRASE", '<a href="vasoft_tags_tag_edit.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_PHRASE . '</a>');
	$row->AddViewField("LID", '[' . $f_LID . '] ' . $arSites[$f_LID]['NAME']);

	$arActions = Array();

	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => Loc::getMessage(" VASOFT_TAGS_EDIT"),
		"ACTION" => $lAdmin->ActionRedirect("vasoft_tags_tag_edit.php?ID=" . $f_ID)
	);

	// удаление элемента
	if ($POST_RIGHT >= "W")
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("VASOFT_TAGS_DELETE"),
			"ACTION" => "if(confirm('" . Loc::getMessage("VASOFT_TAGS_DELETE_CONFIRM") . "')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
		);

	$row->AddActions($arActions);

endwhile;

// резюме таблицы
$lAdmin->AddFooter(
	array(
		array("title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()), // кол-во элементов
		array("counter" => true, "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"), // счетчик выбранных элементов
	)
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
	"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
));

// ******************************************************************** //
//                АДМИНИСТРАТИВНОЕ МЕНЮ                                 //
// ******************************************************************** //

// сформируем меню из одного пункта - добавление рассылки
$aContext = array(
	array(
		"TEXT" => Loc::getMessage("VASOFT_TAGS_ADD"),
		"LINK" => "vasoft_tags_tag_edit.php?lang=" . LANG,
		"TITLE" => Loc::getMessage("VASOFT_TAGS_ADD"),
		"ICON" => "btn_new",
	),
	array(
		"TEXT" => Loc::getMessage("VASOFT_TAGS_GENMAP_SITE") . $currentCite,
		"LINK" => "vasoft_tags_tags.php?lang=" . LANG . '&map=' . $currentCite,
		"TITLE" => Loc::getMessage("VASOFT_TAGS_GENMAP_SITE") . $currentCite,
	),
);
if (count($arSites) > 1) {
	$aContext[] = array(
		"TEXT" => Loc::getMessage("VASOFT_TAGS_GENMAP_SITE_ALL"),
		"LINK" => "vasoft_tags_tags.php?lang=" . LANG . '&map=all',
		"TITLE" => Loc::getMessage("VASOFT_TAGS_GENMAP_SITE_ALL"),
	);
}

// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);

// ******************************************************************** //
//                ВЫВОД                                                 //
// ******************************************************************** //

// альтернативный вывод
$lAdmin->CheckListMode();

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage("VASOFT_TAGS_TITLE"));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// ******************************************************************** //
//                ВЫВОД ФИЛЬТРА                                         //
// ******************************************************************** //

// создадим объект фильтра
$oFilter = new CAdminFilter(
	$sTableID . "_filter",
	array(
		Loc::getMessage('VASOFT_TAGS_FIELD_PHRASE'),
		Loc::getMessage('VASOFT_TAGS_FIELD_CODE'),
		Loc::getMessage('VASOFT_TAGS_FIELD_TITLE'),
		Loc::getMessage('VASOFT_TAGS_FIELD_LID')
	)
);
?>
	<form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
		<? $oFilter->Begin(); ?>
		<tr>
			<td><b><?= Loc::getMessage('VASOFT_TAGS_FIELD_PHRASE') ?>:</b></td>
			<td><input type="text" name="find_phrase" size="47" value="<? echo htmlspecialchars($find_phrase) ?>"></td>
		</tr>
		<tr>
			<td><b><?= Loc::getMessage('VASOFT_TAGS_FIELD_CODE') ?>:</b></td>
			<td><input type="text" name="find_code" size="47" value="<? echo htmlspecialchars($find_code) ?>"></td>
		</tr>
		<tr>
			<td><b><?= Loc::getMessage('VASOFT_TAGS_FIELD_TITLE') ?>:</b></td>
			<td><input type="text" name="find_title" size="47" value="<? echo htmlspecialchars($find_title) ?>"></td>
		</tr>
		<tr>
			<td><b><?= Loc::getMessage('VASOFT_TAGS_FIELD_LID') ?>:</b></td>
			<td><?= CLang::SelectBox("LID", $find_lid); ?></td>
		</tr>
		<?
		$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
		$oFilter->End();
		?>
	</form>

<?
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
