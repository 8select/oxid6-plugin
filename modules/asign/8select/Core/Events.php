<?php

namespace ASign\EightSelect\Core;

use ASign\EightSelect\Model\Log;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\SeoEncoder;

/**
 * Class Events
 * @package ASign\EightSelect\Core
 */
class Events
{
    /** @var DbMetaDataHandler */
    static protected $metaDataHandler = null;
    static protected $logTable = null;

    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        self::_init();
        self::_addLogTable();
        self::_addEndpointsToSeo();

        try {
            /** @var Module $module */
            $module = oxNew(Module::class);
            $module->load('asign_8select');

            self::_clearSmartyCache();

            /** @var Log $log */
            $log = oxNew(Log::class);
            $log->addLog('Module onActivate', 'Version: ' . $module->getInfo('version') . ' success');
        } catch (\Exception $exception) {
            Registry::getUtils()->writeToLog($exception, '8select.log');
        }
    }

    /**
     * Execute action on deactivate event
     */
    public static function onDeactivate()
    {
        try {
            /** @var Module $module */
            $module = oxNew(Module::class);
            $module->load('asign_8select');

            self::_clearSmartyCache();

            /** @var Log $log */
            $log = oxNew(Log::class);
            $log->addLog('Module onDeactivate', 'Version: ' . $module->getInfo('version') . ' success');
        } catch (\Exception $exception) {
            Registry::getUtils()->writeToLog($exception, '8select.log');
        }
    }

    /**
     * Init
     */
    protected static function _init()
    {
        self::$metaDataHandler = oxNew(DbMetaDataHandler::class);

        $log = oxNew(Log::class);
        self::$logTable = $log->getCoreTableName();
    }

    /**
     * Add logging table
     */
    protected static function _addLogTable()
    {
        $tableName = self::$logTable;

        if (!self::$metaDataHandler->tableExists($tableName)) {
            $query = "CREATE TABLE `{$tableName}` (
                        `OXID` VARCHAR(32) NOT NULL,
                        `EIGHTSELECT_ACTION` VARCHAR(255),
                        `EIGHTSELECT_MESSAGE` TEXT,
                        `EIGHTSELECT_DATE` DATETIME not null,
                        `OXTIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`OXID`)
                      ) CHARSET=utf8";

            DatabaseProvider::getDb()->execute($query);
        }
    }

    /**
     * _addEndpointsToSeo
     * -----------------------------------------------------------------------------------------------------------------
     * Add API endpoints to as oxSEO entries
     */
    private static function _addEndpointsToSeo()
    {
        $baseDir = 'cse-api/';
        $urls = [
            'products'           => 'render',
            'attributes'         => 'renderAttributes',
            'variant-dimensions' => 'renderVariantDimensions',
        ];

        $shopID = Registry::getConfig()->getShopId();
        $defaultLang = (int) Registry::getConfig()->getConfigParam('sDefaultLang');
        $seoEncoder = Registry::get(SeoEncoder::class);

        foreach ($urls as $endpoint => $renderFunction) {
            $stdUrl = "index.php?cl=EightSelectAPI&fnc=$renderFunction";
            $seoUrl = $baseDir . $endpoint;
            $oxID = $seoEncoder->getDynamicObjectId($shopID, $stdUrl);

            $seoEncoder->addSeoEntry($oxID, $shopID, $defaultLang, $stdUrl, $seoUrl, 'static', 0);
        }
    }

    /**
     * Clear smarty cache
     */
    protected static function _clearSmartyCache()
    {
        $utilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $smartyDir = $utilsView->getSmartyDir();

        if ($smartyDir && is_readable($smartyDir)) {
            foreach (glob($smartyDir . '*') as $file) {
                if (!is_dir($file)) {
                    @unlink($file);
                }
            }
        }

        Registry::getUtils()->oxResetFileCache();
    }
}
