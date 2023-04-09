<?php
/**
 * Установочный скрипт vasoft.tags
 * @author Воробьев Александр
 * @see https://va-soft.ru/
 * @package vasoft.tags
 */
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class vasoft_tags extends CModule
{
	var $MODULE_ID = 'vasoft.tags';
	private $arTables = array(
		'\Vasoft\Tags\TagsTable'
	);
	private $execlusionAdminFiles;

	function __construct()
	{
		$this->execlusionAdminFiles = array(
			'.',
			'..',
			'menu.php'
		);
		$arModuleVersion = array();
		include(__DIR__ . '/version.php');
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage("MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("MODULE_DESCRIPTION");
		$this->PARTNER_NAME = 'VASoft';
		$this->PARTNER_URI = 'https://va-soft.ru/';

		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
		$this->MODULE_GROUP_RIGHTS = 'Y';
	}

	function DoInstall()
	{
		if (!$this->isVersionD7()) {
			throw new SystemException(Loc::getMessage("ERROR_NEED_D7"));
		} elseif (!\Bitrix\Main\Loader::includeModule('search')) {
			throw new SystemException(Loc::getMessage("ERROR_NEED_SEARCH_MODULE"));
		} else {
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
			$this->installFiles();
			$this->installDB();
		}
	}

	function DoUninstall()
	{
		global $APPLICATION;
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
		if ($request['step'] < 2) {
			$APPLICATION->IncludeAdminFile(Loc::getMessage("VASOFT_TAGS_MODULE_REMOVE"), $this->GetPath() . '/install/unstep1.php');
		} elseif ($request['step'] == 2) {
			$this->unInstallFiles();
			if ($request['savedata'] != 'Y') {
				$this->unInstallDB();
			}
			\Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
			$APPLICATION->IncludeAdminFile(Loc::getMessage("VASOFT_TAGS_MODULE_REMOVE"), $this->GetPath() . '/install/unstep2.php');
		}
	}

	function isVersionD7()
	{
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	function installFiles()
	{
		CopyDirFiles($this->GetPath() . '/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
		if (Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
			CopyDirFiles($this->GetPath() . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, $this->execlusionAdminFiles)) {
						continue;
					}
					$subName = str_replace('.', '_', $this->MODULE_ID);
					file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $subName . '_' . $item, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>');
				}
				closedir($dir);
			}
		}
	}

	function unInstallFiles()
	{
		DeleteDirFiles(dirname(__FILE__) . "/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components");
		if (Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
			\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . $this->GetPath() . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/');
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, $this->execlusionAdminFiles)) {
						continue;
					}
					$subName = str_replace('.', '_', $this->MODULE_ID);
					\Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $subName . '_' . $item);
				}
				closedir($dir);
			}
		}
	}

	function GetPath($notDocumentRoot = false)
	{
		return ($notDocumentRoot)
			? preg_replace('#^(.*)\/(local|bitrix)\/modules#', '/$2/modules', dirname(__DIR__))
			: dirname(__DIR__);
	}

	function installDB()
	{
		$includeResult = Loader::includeSharewareModule($this->MODULE_ID);
		if ($includeResult == Loader::MODULE_INSTALLED) {
			foreach ($this->arTables as $tableClass) {
				if (!Application::getConnection($tableClass::getConnectionName())->isTableExists(Base::getInstance($tableClass)->getDBTableName())) {
					Base::getInstance($tableClass)->createDbTable();
				}
			}
		}
	}

	function unInstallDB()
	{
		if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID)) {
			foreach ($this->arTables as $tableClass) {
				Bitrix\Main\Application::getConnection($tableClass::getConnectionName())->queryExecute('drop table if exists ' . Base::getInstance($tableClass)->getDBTableName());
			}
			\Bitrix\Main\Config\Option::delete($this->MODULE_ID);
		}
	}
}

?>