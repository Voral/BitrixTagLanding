<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix компонент для отображения изображений товара
 *
 * @version 1.0.0
 * @author Alexander Vorobyev
 * @see https://va-soft.ru/
 * @package vasoft.tags
 */
use Vasoft\Tags\TagsTable,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class VasoftDetailimgComponent extends CBitrixComponent
{
	/**
	 * Обработка входных параметров компонента
	 * @param array $arParams параметры
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams["CACHE_TIME"] = (empty($arParams["CACHE_TIME"])) ? 86400 : intval($arParams["CACHE_TIME"]);
		$arParams["SET_TITLE"] = (isset($arParams["SET_TITLE"]) && $arParams["SET_TITLE"] == 'Y');
		$arParams["SET_BROWSER_TITLE"] = (isset($arParams["SET_BROWSER_TITLE"]) && $arParams["SET_BROWSER_TITLE"] == 'Y');
		$arParams["SET_META_KEYWORDS"] = (isset($arParams["SET_META_KEYWORDS"]) && $arParams["SET_META_KEYWORDS"] == 'Y');
		$arParams["SET_META_DESCRIPTION"] = (isset($arParams["SET_META_DESCRIPTION"]) && $arParams["SET_META_DESCRIPTION"] == 'Y');

		$arParams['PAGER_ID'] = (empty($arParams['PAGER_ID'])) ? 'tags_pager' : trim($arParams['PAGER_ID']);
		$arParams["PAGER_ALLOW_ALL"] = (isset($arParams["PAGER_ALLOW_ALL"]) && $arParams["PAGER_ALLOW_ALL"] == 'Y');
		$arParams["PAGER_SIZE"] = (empty($arParams["PAGER_SIZE"])) ? 20 : intval($arParams["PAGER_SIZE"]);
		$arParams["PAGER_TITLE"] = (empty($arParams['PAGER_TITLE'])) ? Loc::getMessage("VASOFT_TAGS_PAGER_TITLE") : trim($arParams['PAGER_TITLE']);
		$arParams["PAGER_TEMPLATE"] = (empty($arParams['PAGER_TEMPLATE'])) ? 'arrows' : trim($arParams['PAGER_TEMPLATE']);

		if (!isset($arParams['SITE_ID'])) {
			$arParams['SITE_ID'] = LANG;
		}

		return $arParams;
	}

	private function generateResult()
	{
		$this->arResult = array(
			'ALL_CNT' => 0,
			'ITEMS' => array(),
			'TEXT' => '',
			'TAG' => '',
			'PHRASE' => '',
			'CLOUD' => [],
			'SEO' => array(
				'TITLE' => '',
				'KEYWORDS' => '',
				'BROWSER_TITLE' => '',
				'DESCRIPTION' => ''
			)
		);
		if (Loader::includeModule('vasoft.tags')) {
			$tag = trim(str_replace(TagsTable::getSection(LANG), '', $GLOBALS['APPLICATION']->GetCurDir()), '/');
			$this->arResult = TagsTable::getSearchResult($tag, $this->arParams);
			if ($this->arResult['SEO']['TITLE'] === '') {
				$this->arResult['SEO']['TITLE'] = sprintf(Loc::getMessage('VASOFT_TAGS_PAGE_TITLE'), $this->arResult['PHRASE']);
			}
			if ($this->arResult['SEO']['BROWSER_TITLE'] === '') {
				$this->arResult['SEO']['BROWSER_TITLE'] = $this->arResult['SEO']['TITLE'];
			}
		}
	}

	/**
	 * Выполнение компонента
	 */
	public function executeComponent()
	{
		$cacheDirMain = '/vasoft_tags/';
		$cacheDir = $cacheDirMain . md5($GLOBALS['APPLICATION']->GetCurDir());
		if ($this->StartResultCache(false, $GLOBALS['APPLICATION']->GetCurDir(), $cacheDir)) {
			$this->generateResult();
			if (defined('BX_COMP_MANAGED_CACHE') && !empty($this->arResult['TAG'])) {
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache($cacheDirMain);
				$CACHE_MANAGER->RegisterTag("vasoft_tags");
				$CACHE_MANAGER->EndTagCache();
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("vasoft_tags_" . $this->arResult['TAG']);
				$CACHE_MANAGER->EndTagCache();
			}
			$this->setResultCacheKeys(array('SEO', 'ALL_CNT'));
			$this->includeComponentTemplate();
		}
		if ($this->arParams['SET_TITLE']) {
			$GLOBALS['APPLICATION']->SetTitle($this->arResult['SEO']['TITLE']);
		}
		if ($this->arParams['SET_BROWSER_TITLE']) {
			$GLOBALS['APPLICATION']->SetPageProperty('title', $this->arResult['SEO']['BROWSER_TITLE']);
		}
		if ($this->arParams['SET_META_KEYWORDS'] && $this->arResult['SEO']['KEYWORDS'] !== '') {
			$GLOBALS['APPLICATION']->SetPageProperty('keywords', $this->arResult['SEO']['KEYWORDS']);
		}
		if ($this->arParams['SET_META_DESCRIPTION'] && $this->arResult['SEO']['DESCRIPTION'] !== '') {
			$GLOBALS['APPLICATION']->SetPageProperty('description', $this->arResult['SEO']['DESCRIPTION']);
		}
	}
}
