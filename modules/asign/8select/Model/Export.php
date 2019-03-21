<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;

/**
 * Class Export
 * @package ASign\EightSelect\Model
 */
class Export extends BaseModel
{
    /** @var string */
    const CSV_DELIMITER = ';';

    /** @var string */
    const CSV_QUALIFIER = '"';

    /** @var string */
    const CSV_MULTI_DELIMITER = '|';

    /** @var string */
    const CATEGORY_DELIMITER = ' / ';

    /** @var int */
    const ERR_NOFEEDID = -99;

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
     * @var Article
     */
    protected $_oArticle = null;

    /**
     * @var Article
     */
    protected $_oParent = null;

    /**
     * @var Export
     */
    protected $_oParentExport = null;

    /**
     * @var string
     */
    protected $_sExportLocalPath = 'export/';

    /**
     * @var string
     */
    protected $_sExportFileName = '#FEEDID#_#FEEDTYPE#_#TIMESTAMP#.csv';

    /**
     * EightSelectExport constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $attribute = oxNew(Attribute::class);

        $type = Registry::get(Request::class)->getRequestEscapedParameter('do_full') ? 'do_full' : 'do_update';

        if ($type === 'do_full') {
            $csvFields = $attribute->getAllFields();
        } else {
            $csvFields = $attribute->getFieldsByType('forUpdate');
        }

        $this->_aCsvAttributes = array_fill_keys(array_keys($csvFields), '');
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article &$article)
    {
        $this->_oArticle = $article;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->_oArticle;
    }

    /**
     * @param Article $parent
     */
    public function setParent(Article $parent)
    {
        $this->_oParent = $parent;
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function initData()
    {
        $exportStatic = oxNew(Export\ExportStatic::class);
        $exportStatic->setAttributes($this->_aCsvAttributes);
        $exportStatic->setArticle($this->_oArticle);
        $exportStatic->setParent($this->_oParent);
        $exportStatic->setParentExport($this->_oParentExport);
        $exportStatic->run();

        $exportDynamic = oxNew(Export\ExportDynamic::class);
        $exportDynamic->setAttributes($this->_aCsvAttributes);
        $exportDynamic->setArticle($this->_oArticle);
        $exportDynamic->setParent($this->_oParent);
        $exportDynamic->run();

        // special handling for main articles without variants
        if (!$this->_oArticle->isVariant()) {
            $this->_aCsvAttributes['groesse'] = 'onesize';
        }

        // copy empty variant infos from parent export
        if ($this->_oParentExport instanceof Export) {
            foreach ($this->_aCsvAttributes as $attributeName => $attributeValue) {
                if ($attributeValue === '') {
                    $this->_aCsvAttributes[$attributeName] = $this->_oParentExport->getAttributeValue($attributeName);
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
        $type = Registry::get(Request::class)->getRequestEscapedParameter('do_full') ? 'do_full' : 'do_update';

        if ($type === 'do_update') {
            $csvHeaderFields = [];
            $attribute = oxNew(Attribute::class);
            $csvUpdateFields = $attribute->getFieldsByType('forUpdate');

            foreach ($csvUpdateFields as $key => $csvField) {
                $csvHeaderFields[] = $csvField['propertyFeedName'];
            }
        } else {
            $csvHeaderFields = array_keys($this->_aCsvAttributes);
        }

        $csvHeader = self::CSV_QUALIFIER . implode(self::CSV_QUALIFIER . self::CSV_DELIMITER . self::CSV_QUALIFIER, $csvHeaderFields) . self::CSV_QUALIFIER;

        return $csvHeader . PHP_EOL;
    }

    /**
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getCsvLine()
    {
        $this->initData();
        $this->checkRequired();

        $line = $this->_getAttributesAsString();

        return $line . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function _getAttributesAsString()
    {
        $delimiter = self::CSV_DELIMITER;
        $qualifier = self::CSV_QUALIFIER;

        $line = '';
        foreach ($this->_aCsvAttributes as $fieldName => $fieldValue) {
            // remove newlines
            $fieldValue = preg_replace('/\s\s+/', ' ', $fieldValue);

            // remove html (except "beschreibung")
            if ($fieldName !== 'beschreibung') {
                $fieldValue = strip_tags($fieldValue);
            }

            // Don't add slashes to ; in the value: They are already in quotes, escaping them only breaks HTML entities

            // add extra double quote if double quote is in there
            $fieldValue = str_replace('"', '""', $fieldValue);

            // add delimiter and qualifier
            $line .= $qualifier . $fieldValue . $qualifier . $delimiter;
        }

        return rtrim($line, $delimiter);
    }

    /**
     * @param string $attributeName
     * @return mixed|string
     */
    public function getAttributeValue($attributeName)
    {
        return isset($this->_aCsvAttributes[$attributeName]) ? $this->_aCsvAttributes[$attributeName] : '';
    }

    /**
     * @param bool $full
     * @return mixed
     */
    public function getExportFileName($full = false)
    {
        $feedId = $this->getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$feedId) {
            throw new \UnexpectedValueException(Registry::getLang()->translateString('EIGHTSELECT_ADMIN_EXPORT_NOFEEDID'));
        }

        $params = [
            '#FEEDID#'    => $feedId,
            '#FEEDTYPE#'  => $full ? 'product_feed' : 'property_feed',
            '#TIMESTAMP#' => round(microtime(true) * 1000)
        ];

        return str_replace(array_keys($params), $params, $this->_sExportFileName);
    }

    /**
     * @param bool $full
     * @return array|false
     */
    protected function _getExportFiles($full)
    {
        $config = Registry::getConfig();
        $exportLocalPath = $config->getConfigParam('sShopDir') . $this->_sExportLocalPath;
        $feedId = Registry::getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$feedId) {
            throw new \UnexpectedValueException(Registry::getLang()->translateString('EIGHTSELECT_ADMIN_EXPORT_NOFEEDID'));
        }

        $params = [
            '#FEEDID#'    => $config->getConfigParam('sEightSelectFeedId'),
            '#FEEDTYPE#'  => $full ? 'product_feed' : 'property_feed',
            '#TIMESTAMP#' => '*',
        ];

        $fileName = str_replace(array_keys($params), $params, $this->_sExportFileName);

        return glob($exportLocalPath . $fileName);
    }

    /**
     * Return the latest (newest) export feed
     *
     * @param bool $full
     * @return string
     */
    public function getExportLatestFile($full = false)
    {
        $files = $this->_getExportFiles($full);

        if (is_array($files) && count($files)) {
            return array_pop($files);
        }

        return '';
    }

    /**
     * Remove unused export feeds. You can set the number of keeping files in module settings
     *
     * @param bool $full
     */
    public function clearExportLocalFolder($full = false)
    {
        $files = $this->_getExportFiles($full);

        if (is_array($files) && count($files)) {
            $keepFiles = Registry::getConfig()->getConfigParam('sEightSelectExportNrOfFeeds');
            $files = array_reverse($files);

            $i = 0;

            foreach ($files as $file) {
                if ($keepFiles > $i++) {
                    continue;
                }

                unlink($file);
            }
        }
    }

    /**
     * @param Export $parentExport
     */
    public function setParentExport($parentExport)
    {
        $this->_oParentExport = $parentExport;
    }

    /**
     * Check if all required fields are not empty
     *
     * @return bool
     */
    public function checkRequired()
    {
        static $requiredFields = null;
        if ($requiredFields === null) {
            $requiredFields = [];

            $attribute = oxNew(Attribute::class);
            $requiredFields = array_keys($attribute->getFieldsByType('required'));
        }

        foreach ($requiredFields as $requiredField) {
            if (empty($this->_aCsvAttributes[$requiredField])) {
                return false;
            }
        }

        return true;
    }
}
