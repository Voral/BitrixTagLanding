<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$arComponentParameters = array(
	"PARAMETERS" => array(
		"CACHE_TIME" => array(
			'DEFAULT' => 86400,
		),
		"TAGS" => array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_TAGS"),
			"TYPE" => "STRING",
			'VALUE' => '',
			"PARENT" => "BASE",
		),
		"ELEMENT_ID" => array(
			"NAME" => Loc::getMessage("VASOFT_TAGS_ELEMENT_ID"),
			"TYPE" => "STRING",
			'VALUE' => '',
			"PARENT" => "BASE",
		)
	)
);
