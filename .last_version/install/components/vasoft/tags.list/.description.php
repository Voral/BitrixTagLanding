<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$arComponentDescription = array(
	"NAME" => Loc::getMessage('VASOFT_TAGS_LIST_NAME'),
	"DESCRIPTION" => Loc::getMessage('VASOFT_TAGS_LIST_DESCRIPTION'),
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => ""
	),
	"COMPLEX" => "N",
);
