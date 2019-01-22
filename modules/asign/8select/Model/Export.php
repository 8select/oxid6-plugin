<?php

namespace ASign\EightSelect\Model;

/**
 * Class Export
 * @package ASign\EightSelect\Model
 */
class Export extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    /** @var string */
    const EIGHTSELECT_CSV_DELIMITER = ';';

    /** @var string */
    const EIGHTSELECT_CSV_QUALIFIER = '"';

    /** @var string */
    const EIGHTSELECT_CSV_MULTI_DELIMITER = '|';

    /** @var string */
    const EIGHTSELECT_CATEGORY_DELIMITER = ' / ';

    /** @var int */
    public static $err_nofeedid = -99;

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'EightSelectExport';

    /**
     * @var array
     */
    protected $_aCsvAttributes = null;

    /**
     * @var oxArticle
     */
    protected $_oArticle = null;

    /**
     * @var oxArticle
     */
    protected $_oParent = null;

    /**
     * @var eightselect_export
     */
    protected $_oParentExport = null;

    /**
     * @var string
     */
    static protected $_sExportLocalPath = 'export/';

    /**
     * @var string
     */
    static protected $_sExportFileName = '#FEEDID#_#FEEDTYPE#_#TIMESTAMP#.csv';

    /**
     * EightSelectExport constructor.
     */
    public function __construct()
    {
        $oEightSelectAttribute = oxNew(\ASign\EightSelect\Model\Attribute::class);

        $sType = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('do_full') ? 'do_full' : 'do_update';

        if ($sType === 'do_full') {
            $aCsvFields = $oEightSelectAttribute->getAllFields();
        } else {
            $aCsvFields = $oEightSelectAttribute->getFieldsByType('forUpdate');
        }

        $this->_aCsvAttributes = array_fill_keys(array_keys($aCsvFields), '');
    }

    /**
     * @param \OxidEsales\Eshop\Application\Model\Article $oArticle
     */
    public function setArticle(\OxidEsales\Eshop\Application\Model\Article &$oArticle)
    {
        $this->_oArticle = $oArticle;
    }

    /**
     * @return \OxidEsales\Eshop\Application\Model\Article
     */
    public function getArticle()
    {
        return $this->_oArticle;
    }

    /**
     * @param \OxidEsales\Eshop\Application\Model\Article $oParent
     */
    public function setParent(\OxidEsales\Eshop\Application\Model\Article $oParent)
    {
        $this->_oParent = $oParent;
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function initData()
    {
        $oEightSelectExportStatic = oxNew(\ASign\EightSelect\Model\Export\ExportStatic::class);
        $oEightSelectExportStatic->setAttributes($this->_aCsvAttributes);
        $oEightSelectExportStatic->setArticle($this->_oArticle);
        $oEightSelectExportStatic->setParent($this->_oParent);
        $oEightSelectExportStatic->setParentExport($this->_oParentExport);
        $oEightSelectExportStatic->run();

        $oEightSelectExportDynamic = oxNew(\ASign\EightSelect\Model\Export\ExportDynamic::class);
        $oEightSelectExportDynamic->setAttributes($this->_aCsvAttributes);
        $oEightSelectExportDynamic->setArticle($this->_oArticle);
        $oEightSelectExportDynamic->setParent($this->_oParent);
        $oEightSelectExportDynamic->run();

        // special handling for main articles without variants
        if (!$this->_oArticle->isVariant()) {
            $this->_aCsvAttributes['groesse'] = 'onesize';
        }

        // copy empty variant infos from parent export
        if ($this->_oParentExport instanceof \ASign\EightSelect\Model\Export) {
            foreach ($this->_aCsvAttributes as $sAttrName => $sAttrValue) {
                if ($sAttrValue === '') {
                    $this->_aCsvAttributes[$sAttrName] = $this->_oParentExport->getAttributeValue($sAttrName);
                }
            }
        }
    }

    /**
     * Returns single line CSV header as string
     *
     * @return string
     */
    public function getCsvHeader()
    {
        $sType = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('do_full') ? 'do_full' : 'do_update';

        if ($sType === 'do_update') {
            $aCsvHeaderFields = [];
            $oEightSelectAttribute = oxNew(\ASign\EightSelect\Model\Attribute::class);
            $aCsvUpdateFields = $oEightSelectAttribute->getFieldsByType('forUpdate');

            foreach ($aCsvUpdateFields as $sKey => $aCsvField) {
                $aCsvHeaderFields[] = $aCsvField['propertyFeedName'];
            }
        } else {
            $aCsvHeaderFields = array_keys($this->_aCsvAttributes);
        }

        $sCsvHeader = self::EIGHTSELECT_CSV_QUALIFIER.implode(self::EIGHTSELECT_CSV_QUALIFIER . self::EIGHTSELECT_CSV_DELIMITER . self::EIGHTSELECT_CSV_QUALIFIER, $aCsvHeaderFields) . self::EIGHTSELECT_CSV_QUALIFIER;

        return $sCsvHeader . PHP_EOL;
    }

    /**
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getCsvLine()
    {
        $this->initData();
        $this->checkRequired();

        $sLine = $this->_getAttributesAsString();

        return $sLine . PHP_EOL;
    }

    /**
     * @return string
     */
    private function _getAttributesAsString()
    {
        $sDelimiter = self::EIGHTSELECT_CSV_DELIMITER;
        $sQualifier = self::EIGHTSELECT_CSV_QUALIFIER;

        $sLine = '';
        foreach ($this->_aCsvAttributes as $sFieldName => $sFieldValue) {
            // remove newlines
            $sFieldValue = preg_replace('/\s\s+/', ' ', $sFieldValue);

            // remove html (except "beschreibung")
            if ($sFieldName !== 'beschreibung') {
                $sFieldValue = strip_tags($sFieldValue);
            }

            // add slashes to ; in the value
            $sFieldValue = addcslashes($sFieldValue, $sDelimiter);

            // add extra double quote if double quote is in there
            $sFieldValue = str_replace('"', '""', $sFieldValue);

            // add delimiter and qualifier
            $sLine .= $sQualifier . $sFieldValue . $sQualifier . $sDelimiter;
        }

        return rtrim($sLine, $sDelimiter);
    }

    /**
     * @param $sAttributeName
     * @return mixed|string
     */
    public function getAttributeValue($sAttributeName)
    {
        return isset($this->_aCsvAttributes[$sAttributeName]) ? $this->_aCsvAttributes[$sAttributeName] : '';
    }

    /**
     * @param bool $blFull
     * @return mixed
     */
    public function getExportFileName($blFull = false)
    {
        $sFeedId = $this->getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$sFeedId) {
            throw new \UnexpectedValueException(\OxidEsales\Eshop\Core\Registry::getLang()->translateString('EIGHTSELECT_ADMIN_EXPORT_NOFEEDID'));
        }

        $aParams = [
            '#FEEDID#'    => $sFeedId,
            '#FEEDTYPE#'  => $blFull ? 'product_feed' : 'property_feed',
            '#TIMESTAMP#' => round(microtime(true) * 1000)
        ];

        $sFilename = str_replace(array_keys($aParams), $aParams, self::$_sExportFileName);

        return $sFilename;
    }

    /**
     * @param $blFull
     * @return array|false
     */
    private static function _getExportFiles($blFull)
    {
        $myConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $sExportLocalPath = $myConfig->getConfigParam('sShopDir') . self::$_sExportLocalPath;
        $sFeedId = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$sFeedId) {
            throw new \UnexpectedValueException(\OxidEsales\Eshop\Core\Registry::getLang()->translateString('EIGHTSELECT_ADMIN_EXPORT_NOFEEDID'));
        }

        $aParams = [
            '#FEEDID#'    => $myConfig->getConfigParam('sEightSelectFeedId'),
            '#FEEDTYPE#'  => $blFull ? 'product_feed' : 'property_feed',
            '#TIMESTAMP#' => '*',
        ];

        $sFilename = str_replace(array_keys($aParams), $aParams, self::$_sExportFileName);

        return glob($sExportLocalPath . $sFilename);
    }

    /**
     * Return the latest (newest) export feed
     *
     * @param bool $blFull
     * @return string
     * @throws UnexpectedValueException
     */
    public static function getExportLatestFile($blFull = false)
    {
        $aFiles = self::_getExportFiles($blFull);

        if (is_array($aFiles) && count($aFiles)) {
            return array_pop($aFiles);
        }

        return '';
    }

    /**
     * Remove unused export feeds. You can set the number of keeping files in module settings
     *
     * @param bool $blFull
     * @throws UnexpectedValueException
     */
    public static function clearExportLocalFolder($blFull = false)
    {
        $aFiles = self::_getExportFiles($blFull);

        if (is_array($aFiles) && count($aFiles)) {
            $iKeepFiles = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sEightSelectExportNrOfFeeds');
            $aFiles = array_reverse($aFiles);

            $i = 0;

            foreach ($aFiles as $sFile) {
                if ($iKeepFiles > $i++) {
                    continue;
                }

                unlink($sFile);
            }
        }
    }

    /**
     * @param EightSelectExport $oParentExport
     */
    public function setParentExport($oParentExport)
    {
        $this->_oParentExport = $oParentExport;
    }

    /**
     * Check if all required fields are not empty
     *
     * @return bool
     * @throws oxSystemComponentException
     */
    public function checkRequired()
    {
        static $aRequiredFields = null;
        if ($aRequiredFields === null) {
            $aRequiredFields = [];

            $oEightSelectAttribute = oxNew(\ASign\EightSelect\Model\Attribute::class);
            $aRequiredFields = array_keys($oEightSelectAttribute->getFieldsByType('required'));
        }

        foreach ($aRequiredFields as $sRequiredField ) {
            if (empty($this->_aCsvAttributes[$sRequiredField])) {
                return false;
            }
        }

        return true;
    }
}
