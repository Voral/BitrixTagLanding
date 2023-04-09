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

/*
CACHE_TIME
SET_TITLE  Устанавливать заголовок страницы [Y|N] При отмеченной опции в качестве заголовка страницы будет установлено Профиль пользователя.
SET_BROWSER_TITLE Y/N Устанавливать мета-тег 'Description' [Y|N] При отмеченной опции в коде страницы будет дописываться мета-тег 'Description' с содержанием первого сообщения темы.
SET_META_KEYWORDS Y/N
SET_META_DESCRIPTION Y/N

PAGER_ID
PAGER_ALLOW_ALL Показывать ссылку "Все"  [Y|N] При отмеченной опции в постраничную навигацию будет добавлена ссылка Все, с помощью которой можно отобразить все элементы каталога.
PAGER_SIZE
PAGER_TITLE Название категорий Задается название категорий, по которым происходит перемещение по элементам.
PAGER_TEMPLATE Шаблон постраничной навигации  Указывается название шаблона постраничной навигации. Если поле пусто, то выбирается шаблон по умолчанию (.default).

FILTER
	Массив, содержащий условия поиска в виде наборов "название поля"=>"значение".

	Название поля может принимать значение:
	QUERY - Строка запроса. Обязательный элемент. Должна быть сформирована в соответствии с правилами языка запросов.
	TAGS - Список тегов. В результате поиска будут возвращены все элементы имеющие данные теги.
	SITE_ID - Сайт, в информации которого производится поиск. Не обязательный параметр, по умолчанию равен текущему.
	MODULE_ID - Код модуля, данными которого ограничивается поиск. Если этот параметр равен false, то поиск производится по всем модулям. Не обязательный параметр, по умолчанию равен false.
	ITEM_ID - Код проиндексированного элемента. Используется для ограничения области поиска по коду элемента. Если параметр имеет значение false, то ограничение не производится. Не обязательный параметр, по умолчанию равен false.
	PARAM1 - Первый параметр элемента или массив первых параметров. Используется для ограничения области поиска по произвольному параметру. Если параметр имеет значение false, то ограничение не производится. Не обязательный параметр, по умолчанию равен false.
	PARAM2 - Второй параметр элемента или массив вторых параметров. Используется для ограничения области поиска по произвольному параметру. Если параметр имеет значение false, то ограничение не производится. Не обязательный параметр, по умолчанию равен false.
	URL - маска адреса относительно корня сайта, по которому доступен данный элемент или массив масок адресов;
	DATE_CHANGE - время изменения элемента в формате сайта (включает время);
	CHECK_DATES - если задан и равен Y, то найдены будут только активные элементы;


QUERY  трока запроса. Обязательный элемент. Должна быть сформирована в соответствии с правилами языка запросов.
SORT
	Массив, содержащий признак сортировки в виде наборов "название поля"=>"направление".

	Название поля может принимать значение:
	ID - идентификатор в поисковом индексе;
	MODULE_ID - идентификатор модуля;
	ITEM_ID - идентификатор элемента поискового индекса (например для форума это идентификатор сообщения);
	TITLE - заголовок;
	PARAM1 - Первый параметр элемента или массив первых параметров. Используется для ограничения области поиска по произвольному параметру. Если параметр имеет значение false, то ограничение не производится. Не обязательный параметр, по умолчанию равен false.
	PARAM2 - Второй параметр элемента или массив вторых параметров. Используется для ограничения области поиска по произвольному параметру. Если параметр имеет значение false, то ограничение не производится. Не обязательный параметр, по умолчанию равен false.
	DATE_FROM - дата начала активности элемента;
	DATE_TO - дата окончания активности элемента;
	RANK - вычисленное значение релевантности;
	TITLE_RANK - количество вхождений подстрок запроса в заголовок. Позволяет повысить значимость заголовка в результатах поиска;
	CUSTOM_RANK - заданное значение релевантности;
	DATE_CHANGE - время изменения элемента;
	Направление сортировки может принимать значение:
	ASC - по возрастанию;
	DESC - по убыванию.
	Пример (именно это значение считается по умолчанию):
	array("CUSTOM_RANK"=>"DESC", "RANK"=>"DESC", "DATE_CHANGE"=>"DESC")
	В случае когда параметр bTagsCloud равен true, допустимыми полями являются:
	DATE_CHANGE - время изменения элемента;
	NAME - значение тег;
	CNT - частота тега;


PERIOD  Период выборки тегов (дней)  Параметр определяет период выборки тегов (дней).
CHECK_DATES - Искать только в активных по дате документах  [Y|N] При отмеченной опции поиск будет осуществляться только в активных по дате документах.
MAX
MIN
*/

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
