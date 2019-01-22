<?php

namespace ASign\EightSelect\Model\Export;

/**
 * Class ExportAbstract
 * @package ASign\EightSelect\Model\Export
 */
abstract class ExportAbstract extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'ExportAbstract';

    /**
     * @var oxArticle
     */
    protected $_oArticle = null;

    /**
     * @var oxArticle
     */
    protected $_oParent = null;

    /**
     * @var export
     */
    protected $_oParentExport = null;

    /**
     * @var array
     */
    protected $_aCsvAttributes = [];

    /**
     * @param array $aCsvAttributes
     */
    public function setAttributes(array &$aCsvAttributes)
    {
        $this->_aCsvAttributes = &$aCsvAttributes;
    }

    /**
     * @param \OxidEsales\Eshop\Application\Model\Article $oArticle
     */
    public function setArticle(\OxidEsales\Eshop\Application\Model\Article &$oArticle)
    {
        $this->_oArticle = $oArticle;
    }

    /**
     * @param oxArticle|null $oParent
     */
    public function setParent(&$oParent)
    {
        $this->_oParent = $oParent;
    }

    /**
     * @param eightselect_export|null $oParentExport
     */
    public function setParentExport(&$oParentExport)
    {
        $this->_oParentExport = $oParentExport;
    }

    /**
     * Set data to fields
     */
    abstract public function run();

    /**
     * @param string $sAttributeName
     * @return string
     */
    public function getVariantSelection($sAttributeName)
    {
        if ($this->_oParent === null) {
            return '';
        }

        $sTable = getViewName('eightselect_attribute2oxid');

        $oList = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);

        $oList->init(\ASign\EightSelect\Model\Attribute2Oxid::class);
        $oList->selectString("SELECT OXOBJECT FROM {$sTable} WHERE ESATTRIBUTE = '{$sAttributeName}'");

        foreach ($oList->getArray() as $oAttr2Oxid) {
            $sSelection = $oAttr2Oxid->eightselect_attribute2oxid__oxobject->value;

            if (strpos($this->_oParent->oxarticles__oxvarname->value, $sSelection) !== false) {
                $aSelectionNames = explode(' | ', $this->_oParent->oxarticles__oxvarname->value);
                $aSelectionNames = array_map('trim', $aSelectionNames);
                $aSelectionValues = explode(' | ', $this->_oArticle->oxarticles__oxvarselect->value);
                $aSelectionValues = array_map('trim', $aSelectionValues);
                $iSizePos = array_search($sSelection, $aSelectionNames);

                if ($iSizePos !== false && isset($aSelectionValues[$iSizePos])) {
                    return $aSelectionValues[$iSizePos];
                }
            }
        }

        return '';
    }
}
