<?php
/**
 * Административное меню модуля vasoft.tags
 * @author Воробьев Александр
 * @see https://va-soft.ru/
 * @package vasoft.tags
 */
use Bitrix\Main\Localization\Loc;

$POST_RIGHT = $GLOBALS['APPLICATION']->GetGroupRight("vasoft.tags");
$menu = array();
if ($POST_RIGHT != "D") {

	Loc::loadMessages(__FILE__);

	$menu = array(
		array(
			'parent_menu' => 'global_menu_content',
			'sort' => 800,
			'text' => Loc::getMessage("VASOFT_TAGS_MENU_ITEM"),
			'title' => Loc::getMessage("VASOFT_TAGS_MENU_ITEM"),
			'url' => 'vasoft_tags_tags.php',
			'more_url' => array('vasoft_tags_tag_edit.php'),
			'module_id' => 'vasoft.tags',
			'items_id' => 'vasoft_tags',
			'items' => array(),
		),
	);
}
return $menu;
