<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Bitrix компонент вывода списка тегов
 *
 * @version 1.0.0
 * @author Alexander Vorobyev
 * @see https://va-soft.ru/
 * @package vasoft.tags
 */
use \Bitrix\Main\Loader;

class VasoftTagsListComponent extends CBitrixComponent
{
	/**
	 * Обработка входных параметров компонента
	 * @param array $arParams параметры
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams['ELEMENT_ID'] = (empty($arParams['ELEMENT_ID'])) ? 0 : intval($arParams['ELEMENT_ID']);
		$arParams['TAGS'] = (empty($arParams['TAGS'])) ? '' : trim($arParams['TAGS']);
		$arParams["CACHE_TIME"] = (empty($arParams["CACHE_TIME"])) ? 86400 : intval($arParams["CACHE_TIME"]);
		return $arParams;
	}

	private function generateResult()
	{
		$this->arResult = array();
		if (Loader::includeModule('vasoft.tags')) {
			$tags = $this->arParams['TAGS'];
			if ($this->arParams['ELEMENT_ID'] > 0 && Loader::includeModule('iblock')) {
				$arElement = \Bitrix\Iblock\ElementTable::getList(array(
					'filter' => array('ID' => $this->arParams['ELEMENT_ID']),
					'select' => array('TAGS')
				))->fetch();
				if ($arElement) {
					$tags = $arElement['TAGS'];
				}
			}
			$this->arResult = \Vasoft\Tags\TagsTable::getTagsArray($tags, $this->arParams['PATH']);
		}
	}

	/**
	 * Выполнение компонента
	 */
	public function executeComponent()
	{
		if ($this->StartResultCache()) {
			$this->generateResult();
			$this->setResultCacheKeys(array());
			$this->includeComponentTemplate();
		}
	}
}
