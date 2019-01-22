<?php

namespace ASign\EightSelect\Core;

/**
 * Class Events
 * @package ASign\EightSelect\Core
 */
class Events
{
    static private $oMetaDataHandler = null;
    static private $sLogTable = null;
    static private $sAttribute2OxidTable = null;

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
            $oEightSelectModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
            $oEightSelectModule->load('asign_8select');

            self::_clearSmartyCache();

            $oEightSelectLog = oxNew(\ASign\EightSelect\Model\Log::class);
            $oEightSelectLog->addLog('Module onActivate', 'Version: ' . $oEightSelectModule->getInfo('version') . ' success');
        } catch (\Exception $oEx) {
        }
    }

    /**
     * Execute action on deactivate event
     *
     * @return null
     */
    public static function onDeactivate()
    {
        try {
            /** @var oxModule $oEightSelectModule */
            $oEightSelectModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
            $oEightSelectModule->load('asign_8select');

            self::_clearSmartyCache();

            /** @var eightselect_log $oEightSelectLog */
            $oEightSelectLog = oxNew(\ASign\EightSelect\Model\Log::class);
            $oEightSelectLog->addLog('Module onDeactivate', 'Version: ' . $oEightSelectModule->getInfo('version') . ' success');
        } catch (\Exception $oEx) {
        }
    }

    /**
     * Init
     */
    private static function _init()
    {
        self::$oMetaDataHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);

        $o8SelectLog = oxNew(\ASign\EightSelect\Model\Log::class);
        self::$sLogTable = $o8SelectLog->getCoreTableName();

        $o8SelectAttribute2Oxid = oxNew(\ASign\EightSelect\Model\Attribute2Oxid::class);
        self::$sAttribute2OxidTable = $o8SelectAttribute2Oxid->getCoreTableName();
    }

    /**
     * Add logging table
     */
    private static function _addLogTable()
    {
        $sTableName = self::$sLogTable;

        if (!self::$oMetaDataHandler->tableExists($sTableName)) {
            $sSql = "CREATE TABLE `{$sTableName}` (
                        `OXID` VARCHAR(32) NOT NULL,
                        `EIGHTSELECT_ACTION` VARCHAR(255),
                        `EIGHTSELECT_MESSAGE` TEXT,
                        `EIGHTSELECT_DATE` DATETIME not null,
                        `OXTIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`OXID`)
                      ) CHARSET=utf8";

            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sSql);
        }
    }

    /**
     * Add attribute 2 Oxid table
     */
    private static function _addAttribute2OxidTable()
    {
        $sTableName = self::$sAttribute2OxidTable;

        if (!self::$oMetaDataHandler->tableExists($sTableName)) {
            $sSql = "CREATE TABLE `{$sTableName}` (
                        `OXID` VARCHAR(32) NOT NULL,
                        `OXSHOPID` INT(11) NOT NULL,
                        `ESATTRIBUTE` VARCHAR(32) NOT NULL,
                        `OXOBJECT` VARCHAR(32) NOT NULL,
                        `OXTYPE` VARCHAR(32) NOT NULL,
                        `OXTIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`OXID`)
                      ) CHARSET=utf8";

            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sSql);
        }
    }

    /**
     * Add attributes 2 oxid
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    private static function _addAttributes2Oxid()
    {
        $aAttributes2Oxid = [
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

        $oUtils = oxNew(\OxidEsales\Eshop\Core\UtilsObject::class);

        $sSqlCheck = 'SELECT 1 FROM `' . self::$sAttribute2OxidTable . '` WHERE `ESATTRIBUTE` = ? AND OXSHOPID = ?';
        $sSqlInsert = 'INSERT INTO `' . self::$sAttribute2OxidTable . '` (`OXID`, `OXSHOPID`, `ESATTRIBUTE`, `OXOBJECT`,  `OXTYPE`) VALUES (?, ?, ?, ?, ?)';

        foreach ($aAttributes2Oxid as $aAttribute2Oxid) {
            if (!\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSqlCheck, [$aAttribute2Oxid['eightselectAttribute'], $oUtils->getShopId()])) {
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sSqlInsert, [$oUtils->generateUId(), $oUtils->getShopId(), $aAttribute2Oxid['eightselectAttribute'], $aAttribute2Oxid['oxidObject'], $aAttribute2Oxid['type']]);
            }
        }
    }

    /**
     * Clear smarty cache
     */
    private static function _clearSmartyCache()
    {
        $oUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $sSmartyDir = $oUtilsView->getSmartyDir();

        if ($sSmartyDir && is_readable($sSmartyDir)) {
            foreach (glob($sSmartyDir . '*') as $sFile) {
                if (!is_dir($sFile)) {
                    @unlink($sFile);
                }
            }
        }

        \OxidEsales\Eshop\Core\Registry::getUtils()->oxResetFileCache();
    }
}
