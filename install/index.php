<?php

use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class base_module extends CModule
{

    var $MODULE_ID = "base.module";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    /**
     * Путь к папке с компонентами внутри модуля
     * @var string
     */
    var string $COMPONENTS_FROM_PATH;
    /**
     * Путь установки компонентов модуля
     * @var string
     */
    var string $COMPONENTS_TO_PATH;
    /**
     * Путь к папке с расширениями внутри модуля
     * @var string
     */
    var string $EXTENSIONS_FROM_PATH;
    /**
     * Путь установки расширений модуля
     * @var string
     */
    var string $EXTENSIONS_TO_PATH;
    /**
     * Путь к папке с публичными страницами внутри модуля
     * @var string
     */
    var string $PUBLIC_FROM_PATH;
    /**
     * Путь установки публичных страниц модуля
     * @var string
     */
    var string $PUBLIC_TO_PATH;

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("BASE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("BASE_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("BASE_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("BASE_MODULE_PARTNER_URI");

        $this->COMPONENTS_FROM_PATH = __DIR__ . "/components/";
        $this->COMPONENTS_TO_PATH = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/ognevoydev/";

        $this->EXTENSIONS_FROM_PATH = __DIR__ . "/js/";
        $this->EXTENSIONS_TO_PATH = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/ognevoydev/";

        $this->PUBLIC_FROM_PATH = __DIR__ . "/public/";
        $this->PUBLIC_TO_PATH = $_SERVER["DOCUMENT_ROOT"] . "/";
    }

    private function getEntities()
    {
        return [
//            \Base\Module\Internal\SomeTable::class,
        ];
    }

    public function isVersionD7(): ?bool
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);

            $this->installFiles();
            $this->installDB();
            $this->installEvents();
            $this->installOptions();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("BASE_MODULE_INSTALL_ERROR_VERSION"));
        }
    }


    public function DoUninstall()
    {
        $this->unInstallFiles();
        $this->unInstallDB();
        $this->unInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Установка файлов модуля
     * @return void
     */
    public function installFiles()
    {
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($this->COMPONENTS_FROM_PATH)) {
            CopyDirFiles($this->COMPONENTS_FROM_PATH, $this->COMPONENTS_TO_PATH, true, true);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($this->EXTENSIONS_FROM_PATH)) {
            CopyDirFiles($this->EXTENSIONS_FROM_PATH, $this->EXTENSIONS_TO_PATH, true, true);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($this->PUBLIC_FROM_PATH)) {
            CopyDirFiles($this->PUBLIC_FROM_PATH, $this->PUBLIC_TO_PATH, true, true);
        }
    }

    /**
     * Создание таблиц
     * @return void
     */
    public function installDB()
    {
        Loader::includeModule($this->MODULE_ID);

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (!Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                Base::getInstance($entity)->createDbTable();
            }
        }
    }

    /**
     * Регистрация обработчиков событий
     * @return void
     */
    public function installEvents(): void
    {
        RegisterModuleDependences('main', 'OnEpilog', $this->MODULE_ID, '\\Base\\Module\\EventHandler', 'bar');
    }

    /**
     * Установка параметров модуля
     * @return void
     */
    public function installOptions(): void
    {
        if (empty(Option::get($this->MODULE_ID, "base_module_option"))) {
            Option::set($this->MODULE_ID, "base_module_option", "Y");
        }
    }

    /**
     * Удаление файлов модуля
     * @return void
     */
    public function unInstallFiles()
    {
        $this->deleteFiles($this->COMPONENTS_FROM_PATH, $this->COMPONENTS_TO_PATH);
        $this->deleteFiles($this->EXTENSIONS_FROM_PATH, $this->EXTENSIONS_TO_PATH);
        $this->deleteFiles($this->PUBLIC_FROM_PATH, $this->PUBLIC_TO_PATH);
    }

    /**
     * Удаление таблиц
     * @return void
     */
    public function unInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        $connection = \Bitrix\Main\Application::getConnection();

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                $connection->dropTable($entity::getTableName());
            }
        }
    }

    /**
     * Удаление обработчиков событий
     * @return void
     */
    public function unInstallEvents(): void
    {
        UnRegisterModuleDependences('main', 'OnEpilog', $this->MODULE_ID, '\\Ognevoydev\\BaseModule\\EventHandler', 'handleTabsInitialize');
    }

    private function deleteFiles(string $installedFromPath, string $installedToPath)
    {
        $files = array_diff(scandir($installedFromPath), ['..', '.']);

        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($installedFromPath . $file)) {
                \Bitrix\Main\IO\File::deleteFile($installedToPath . $file);
            }
            if (is_dir($installedFromPath . $file)) {
                \Bitrix\Main\IO\Directory::deleteDirectory($installedToPath . $file);
            }
        }
    }
}
