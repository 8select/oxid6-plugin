<?php

namespace ASign\EightSelect\Core;

use ASign\EightSelect\Model\Attribute2Oxid;
use ASign\EightSelect\Model\Log;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;

/**
 * Class Events
 * @package ASign\EightSelect\Core
 */
class Events
{
    /** @var DbMetaDataHandler */
    static protected $metaDataHandler = null;
    static protected $logTable = null;
    static protected $attribute2OxidTable = null;

    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        self::_init();
        self::_addLogTable();
        self::_addAttribute2OxidTable();
        self::_addAttributes2Oxid();

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

        $attribute2Oxid = oxNew(Attribute2Oxid::class);
        self::$attribute2OxidTable = $attribute2Oxid->getCoreTableName();
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
     * Add attribute 2 Oxid table
     */
    protected static function _addAttribute2OxidTable()
    {
        $tableName = self::$attribute2OxidTable;

        if (!self::$metaDataHandler->tableExists($tableName)) {
            $query = "CREATE TABLE `{$tableName}` (
                        `OXID` VARCHAR(32) NOT NULL,
                        `OXSHOPID` INT(11) NOT NULL,
                        `ESATTRIBUTE` VARCHAR(32) NOT NULL,
                        `OXOBJECT` VARCHAR(32) NOT NULL,
                        `OXTYPE` VARCHAR(32) NOT NULL,
                        `OXTIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`OXID`)
                      ) CHARSET=utf8";

            DatabaseProvider::getDb()->execute($query);
        }
    }

    /**
     * Add attributes 2 oxid
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected static function _addAttributes2Oxid()
    {
        $shopId = Registry::getConfig()->getShopId();

        $attributes2Oxid = [
            [
                'eightselectAttribute' => 'sku',
                'oxidObject'           => 'OXARTNUM',
                'type'                 => 'oxarticlesfield',
            ],
            [
                'eightselectAttribute' => 'beschreibung',
                'oxidObject'           => 'OXLONGDESC',
                'type'                 => 'oxartextendsfield',
            ],
            [
                'eightselectAttribute' => 'beschreibung1',
                'oxidObject'           => 'OXLONGDESC',
                'type'                 => 'oxartextendsfield',
            ],
            [
                'eightselectAttribute' => 'ean',
                'oxidObject'           => 'OXEAN',
                'type'                 => 'oxarticlesfield',
            ],
            [
                'eightselectAttribute' => 'name2',
                'oxidObject'           => 'OXSHORTDESC',
                'type'                 => 'oxarticlesfield',
            ],
        ];

        $utilsObject = oxNew(UtilsObject::class);

        $checkQuery = 'SELECT 1 FROM `' . self::$attribute2OxidTable . '` WHERE `ESATTRIBUTE` = ? AND OXSHOPID = ?';
        $insertQuery = 'INSERT INTO `' . self::$attribute2OxidTable . '` (`OXID`, `OXSHOPID`, `ESATTRIBUTE`, `OXOBJECT`,  `OXTYPE`) VALUES (?, ?, ?, ?, ?)';

        foreach ($attributes2Oxid as $attribute2Oxid) {
            if (!DatabaseProvider::getDb()->getOne($checkQuery, [$attribute2Oxid['eightselectAttribute'], $shopId])) {
                DatabaseProvider::getDb()->execute($insertQuery, [$utilsObject->generateUId(), $shopId, $attribute2Oxid['eightselectAttribute'], $attribute2Oxid['oxidObject'], $attribute2Oxid['type']]);
            }
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
