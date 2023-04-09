<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$arComponentParameters = array(
	"PARAMETERS" => array(
		'PAGER_ID' => array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_PAGER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "tags_pager",
			"PARENT" => "PAGER"
		),
		"PAGER_ALLOW_ALL" => array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_PAGER_ALLOW_ALL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			'VALUE' => 'Y',
			"PARENT" => "PAGER",
		),
		"PAGER_SIZE" =>  array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_PAGER_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
			"PARENT" => "PAGER"
		),
		"PAGER_TITLE" =>  array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_PAGER_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => Loc::getMessage("VASOFT_TAGS_PAGER_TITLE_DEFAULT"),
			"PARENT" => "PAGER"
		),
		"PAGER_TEMPLATE" => array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_PAGER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ".default",
			"PARENT" => "PAGER"
		),
		"SET_TITLE" => array(
			'DEFAULT' => 'N'
		),
		"CACHE_TIME" => array(
			'DEFAULT' => 86400,
		),
		"SET_BROWSER_TITLE" => array(
			"NAME" => Loc::getMessage('VASOFT_TAGS_PAGE_SET_BROWSER_TITLE'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			'VALUE' => 'Y',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_META_KEYWORDS" => array(
			"NAME" => Loc::getMessage('VASOFT_TAGS_PAGE_SET_META_KEYWORDS'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			'VALUE' => 'Y',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_META_DESCRIPTION" => array(
			"NAME" => Loc::getMessage('VASOFT_TAGS_PAGE_SET_META_DESCRIPTION'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			'VALUE' => 'Y',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		'PERIOD' => array(
			'NAME' => Loc::getMessage("VASOFT_TAGS_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"CHECK_DATES" => array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			'VALUE' => 'Y',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		'PERIOD' => array(
			'NAME' => Loc::getMessage("VASOFT_TAGS_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	)
);
