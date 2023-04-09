<?php /** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection AutoloadingIssuesInspection */

namespace Vasoft\Tags;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CDatabase;
use CLang;
use CSearch;
use CSite;
use CTimeZone;
use Exception;


class TagsTable extends Entity\DataManager
{
    private static $arPaths = array();

    /** @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function getTableName()
    {
        return 'vasoft_tags';
    }

    /**
     * @return array
     * @throws SystemException
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            new Entity\StringField('LID', array(
                'required' => true,
            )),
            new Entity\DatetimeField('DATEUPDATE', array(
                'required' => true
            )),
            new Entity\StringField('CODE', array(
                'required' => true,
                'unique' => true
            )),
            new Entity\StringField('PHRASE', array(
                'required' => true,
            )),
            new Entity\StringField('TITLE', array()),
            new Entity\StringField('BROWSER_TITLE', array()),
            new Entity\StringField('KEYWORDS', array()),
            new Entity\StringField('DESCRIPTION', array()),
            new Entity\TextField('TEXT', array()),
            new Entity\StringField('TEXT_TYPE', array(
                'default' => 'html',
                'reuired' => true
            ))

        );
    }

    /**
     * Преобразует строку или массив тегов в массив ссылок на страницы тегов
     * @param mixed $param строка (разделитель запятая) или массив тегов
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function getTagsArray($param)
    {
        $arResult = array();
        $arTags = array();
        if (!empty($param)) {
            $arParam = is_array($param) ? $param : explode(',', trim($param));
            $path = self::getSection(LANG);
            foreach ($arParam as $tag) {
                $tag = trim($tag);
                if ($tag !== '') {
                    $arTags[] = $tag;
                    $arResult[$tag] = [
                        'NAME' => $tag,
                        'DETAIL_PAGE_URL' => $path . urlencode($tag) . '/'
                    ];
                }
            }
            if (!empty($arTags)) {
                $iterator = self::getList([
                    'filter' => ['PHRASE' => $arTags],
                    'select' => ['CODE', 'PHRASE']
                ]);
                while ($arPhrase = $iterator->fetch()) {
                    $arResult[$arPhrase['PHRASE']]['DETAIL_PAGE_URL'] = $path . urlencode($arPhrase['CODE']) . '/';
                }
            }
        }
        return $arResult;
    }

    /**
     * Возвращает результат поиска по тегу
     *
     * Внутри функции кеширования не производится.
     * @param string $tag тег
     * @param array $arOptions массив параметров поиска
     * - FILTER - фильтр для дополнительной фильтрации выборки. Обязательно дополняется TAGS и SITE_ID
     * - SORT - Массив, содержащий признак сортировки в виде наборов "название поля"=>"направление".
     * - MIN
     * -MAX
     * @return array
     * @throws SystemException В случае отсутствия модуля поиска
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @depricated Будет удален в будущих версиях
     * @noinspection PhpIssetCanBeReplacedWithCoalesceInspection
     * @noinspection NullCoalescingOperatorCanBeUsedInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUndefinedClassInspection
     */
    public static function getSearchResult($tag, $arOptions = array())
    {
        $arResult = array(
            'ALL_CNT' => 0,
            'ITEMS' => array(),
            'TEXT' => '',
            'TAG' => $tag,
            'PHRASE' => $tag,
            'CLOUD' => [],
            'SEO' => array(
                'TITLE' => '',
                'KEYWORDS' => '',
                'BROWSER_TITLE' => '',
                'DESCRIPTION' => ''
            )
        );
        if (Loader::includeModule('search')) {
            $arFilter = isset($arOptions['FILTER']) ? $arOptions['FILTER'] : array();
            if (isset($arOptions['QUERY'])) {
                $arFilter['QUERY'] = trim($arOptions['QUERY']);
            }
            if (!array_key_exists('SITE_ID', $arFilter)) {
                $arFilter['SITE_ID'] = LANG;
            }
            if ($tag !== '') {
                $arSEO = self::getList([
                    'filter' => ['CODE' => $tag],
                    'select' => ['PHRASE', 'TITLE', 'BROWSER_TITLE', 'KEYWORDS', 'DESCRIPTION', 'TEXT', 'TEXT_TYPE']
                ])->fetch();
                if (!$arSEO) {
                    $arSEO = self::getList([
                        'filter' => ['PHRASE' => $tag],
                        'select' => ['CODE']
                    ])->fetch();
                    if ($arSEO) {
                        $url = preg_replace("|/" . $tag . "/|ui", "/" . $arSEO['CODE'] . "/", $GLOBALS['APPLICATION']->GetCurDir());
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Location: ' . $url);
                        die();
                    }
                }
                if ($arSEO) {
                    $arResult['PHRASE'] = $arSEO['PHRASE'];
                    $arResult['SEO'] = array(
                        'TITLE' => trim($arSEO['TITLE']),
                        'KEYWORDS' => trim($arSEO['KEYWORDS']),
                        'BROWSER_TITLE' => trim($arSEO['BROWSER_TITLE']),
                        'DESCRIPTION' => trim($arSEO['DESCRIPTION'])
                    );
                    $arResult['TEXT'] = trim($arSEO['TEXT']);
                } else {
                    $arResult['PHRASE'] = $tag;
                }

                $arFilter['TAGS'] = $arResult['PHRASE'];
                if (!array_key_exists('PAGER_ID', $arOptions)) {
                    $arOptions['PAGER_ID'] = 'tags_pager';
                }
                if (!array_key_exists('PAGER_ALLOW_ALL', $arOptions)) {
                    $arOptions['PAGER_ALLOW_ALL'] = true;
                }
                if (!array_key_exists('PAGER_SIZE', $arOptions)) {
                    $arOptions['PAGER_SIZE'] = 20;
                }
                if (!array_key_exists('PAGER_TITLE', $arOptions)) {
                    $arOptions['PAGER_TITLE'] = Loc::getMessage('VASOFT_TAGS_PAGER_TITLE');
                }
                if (!array_key_exists('PAGER_TEMPLATE', $arOptions)) {
                    $arOptions['PAGER_TEMPLATE'] = 'arrows';
                }

                $arSort = (array_key_exists('SORT', $arOptions)
                    ? $arOptions['SORT']
                    : ["CUSTOM_RANK" => "DESC", "RANK" => "DESC", "DATE_CHANGE" => "DESC"]);

                $obSearch = new CSearch;
                $obSearch->Search($arFilter, $arSort);
                $obSearch->NavStart($arOptions['PAGER_SIZE'], false);
                while ($arItem = $obSearch->NavNext()) {
                    $arResult['ITEMS'][] = $arItem;
                }
                $navComponentObject = null;
                $arResult["NAV_STRING"] = $obSearch->GetPageNavStringEx(
                    $navComponentObject,
                    $arOptions['PAGER_TITLE'],
                    $arOptions["PAGER_TEMPLATE"],
                    $arOptions['PAGER_ALLOW_ALL']
                );
                $arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
                $arResult["NAV_RESULT"] = $obSearch;
                if (count($arResult['ITEMS']) === 0) {
                    $arResult['SEO'] = array(
                        'TITLE' => '',
                        'KEYWORDS' => '',
                        'BROWSER_TITLE' => '',
                        'DESCRIPTION' => ''
                    );
                }
            } else {
                $arFilter["!TAGS"] = false;
                if (isset($arOptions['PERIOD']) && 0 < (int)$arOptions['PERIOD']) {
                    $arFilter['DATE_CHANGE'] = Date(
                        CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)),
                        time() - ($arOptions["PERIOD"] * 24 * 3600) + CTimeZone::GetOffset()
                    );
                }
                if (isset($arOptions["CHECK_DATES"]) && $arOptions["CHECK_DATES"]) {
                    $arFilter["CHECK_DATES"] = "Y";
                }
                $obSearch = new CSearch();
                $obSearch->Search($arFilter, array("CNT" => "DESC"), array(), true);
                $max = 0;
                $arCount = array();
                $arTags = array();

                while ($arStat = $obSearch->Fetch()) {
                    $arResult['CLOUD'][] = $arStat;
                    if ($arStat['CNT'] > $max) {
                        $max = $arStat['CNT'];
                    }
                    $arCount[$arStat['CNT']] = 0;
                    $arTags[] = $arStat['NAME'];
                }
                $rsCodes = self::getList(array(
                    'filter' => array('PHRASE' => $arTags),
                    'select' => array('PHRASE', 'CODE')
                ));
                $arTags = array();
                while ($arCode = $rsCodes->fetch()) {
                    $arTags[$arCode['PHRASE']] = $arCode['CODE'];
                }
                $sum = 0;
                foreach ($arCount as $cnt => $val) {
                    $sum += $cnt;
                }
                if (empty($arCount)) {
                    $medium = 0;
                    $maxMedium = 0;
                } else {
                    $medium = $sum / count($arCount);
                    $maxMedium = $max / $medium;
                }
                /**
                 * $top - это максимальны уровень,
                 * обеспечиваем так, чтоб строилось относительно середины диапазона
                 */
                $max = isset($arOptions['MAX']) ? $arOptions['MAX'] : 130;
                $min = isset($arOptions['MIN']) ? $arOptions['MIN'] : 70;
                /**
                 * @var $arResult array[]
                 * @var int $i
                 * @var array $arStat
                 */
                foreach ($arResult['CLOUD'] as &$arStat) {
                    $code = isset($arTags[$arStat['NAME']]) ? $arTags[$arStat['NAME']] : $arStat['NAME'];
                    $arStat['URL'] = str_replace('//', '/', self::getSection(LANG) . $code . '/');
                    $arStat['KOEF'] = ($arStat['CNT'] * ($max - $min) / ($medium * $maxMedium)) + $min;
                }
                unset($arStat);
            }
        } else {
            throw new \SystemException(Loc::getMessage("ERROR_NEED_SEARCH_MODULE"));
        }
        return $arResult;
    }

    /**
     * @param string $siteId
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection HttpUrlsUsage
     */
    public static function generateMap($siteId = '')
    {
        $rsSites = CSite::GetList();
        $defaultPath = Option::get('vasoft.tags', 'PATH');
        $defaultProtocol = Option::get('vasoft.tags', 'PROTOCOL');
        $arSites = [];
        while ($arSite = $rsSites->Fetch()) {
            if ($siteId === '' || $siteId === $arSite['LID']) {
                $arSite['TAG_PATH'] = (Option::get('vasoft.tags', 'PROTOCOL', $defaultProtocol, $arSite['LID']) . '://');
                $arSite['TAG_PATH'] .= ('/' . $arSite['SERVER_NAME'] . '/');
                $arSite['TAG_PATH'] .= (Option::get('vasoft.tags', 'PATH', $defaultPath, $arSite['LID']));
                $arSite['TAG_PATH'] = str_replace('//', '/', $arSite['TAG_PATH']);
                $arSites[] = $arSite;
            }
        }

        foreach ($arSites as $arSite) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $rsLinks = self::getList([
                'filter' => ['!TITLE' => false, 'LID' => $arSite['LID']],
                'select' => ['CODE', 'DATEUPDATE']
            ]);
            while ($arLink = $rsLinks->fetch()) {
                $xml .= ('<url><loc>' . $arSite['TAG_PATH'] . $arLink['CODE'] . '/</loc></url>');
            }
            $xml .= '</urlset>';
            file_put_contents($arSite['ABS_DOC_ROOT'] . '/sitemap_tags_' . $arSite['LID'] . '.xml', $xml, LOCK_EX);
        }
    }

    /**
     * @param string $lid
     * @return mixed
     */
    public static function getSection($lid = '')
    {
        if ($lid === '') {
            $lid = LANG;
        }
        if (!isset(self::$arPaths[$lid])) {
            $defaultPath = Option::get('vasoft.tags', 'PATH');
            self::$arPaths[$lid] = '/' . trim(Option::get('vasoft.tags', 'PATH', $defaultPath, $lid), '/') . '/';
        }
        return self::$arPaths[$lid];
    }

    /**
     * @param mixed $id
     * @param array $arFields
     * @return UpdateResult
     * @throws ObjectException
     * @throws Exception
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function update($id, array $arFields)
    {
        $arFields['DATEUPDATE'] = new DateTime();
        return parent::update($id, $arFields);
    }

    /**
     * @param array $arFields
     * @return AddResult
     * @throws ObjectException
     * @throws Exception
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function add(array $arFields)
    {
        $arFields['DATEUPDATE'] = new DateTime();
        return parent::add($arFields);
    }
}
