<?php

namespace ASign\EightSelect\Controller\Admin;

/**
 * Class AdminAttributeMain
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminAttributeMain extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = "AdminAttributeMain";

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = "eightselect_admin_attribute_main.tpl";

    /**
     * Storage for saved associated attributes
     *
     * @var array
     */
    protected $_aAttrEightselect2Oxid = [];

    /**
     * Calls parent::render, sets template data
     *
     * @return string
     */
    public function render()
    {
        $sReturn = parent::render();

        $this->_aViewData['aAttributesEightselect'] = $this->_getAttributesFromEightselect();
        $this->_aViewData['aAttributesOxid'] = $this->_getAttributesFromOxid();

        $this->_aAttrEightselect2Oxid = $this->_getEightselect2Oxid();

        return $sReturn;
    }

    /**
     * Collect Oxid possible values to match with 8select
     *
     * @return array $aSelectAttributes
     */
    private function _getAttributesFromOxid()
    {
        $aSelectAttributes = [];
        $oLang = \OxidEsales\Eshop\Core\Registry::getLang();

        // Default static Oxid fields
        $aArticleFields = [
            ['oxarticlesfield;OXARTNUM', $oLang->translateString('ARTICLE_MAIN_ARTNUM')],
            ['oxarticlesfield;OXTITLE', $oLang->translateString('ARTICLE_MAIN_TITLE')],
            ['oxarticlesfield;OXSHORTDESC', $oLang->translateString('GENERAL_ARTICLE_OXSHORTDESC')],
            ['oxartextendsfield;OXLONGDESC', $oLang->translateString('GENERAL_ARTICLE_OXLONGDESC')],
            ['oxarticlesfield;OXEAN', $oLang->translateString('ARTICLE_MAIN_EAN')],
            ['oxarticlesfield;OXWIDTH', $oLang->translateString('GENERAL_ARTICLE_OXWIDTH')],
            ['oxarticlesfield;OXHEIGHT', $oLang->translateString('GENERAL_ARTICLE_OXHEIGHT')],
            ['oxarticlesfield;OXHLENGTH', $oLang->translateString('GENERAL_ARTICLE_OXLENGTH')],
        ];

        $sOptGroupAttribute = \OxidEsales\Eshop\Core\Registry::getLang()->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ARTICLE');

        foreach ($aArticleFields as $aArticleField) {
            $aSelectAttributes[$sOptGroupAttribute][$aArticleField[0]] = $aArticleField[1];
        }

        // Dynamic attributes
        $sTableName = getViewName('oxattribute');
        $aAttributes = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_NUM)->getAll("SELECT CONCAT('oxattributeid;', OXID), OXTITLE FROM {$sTableName}");
        $sOptGroupAttribute = $oLang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ATTRIBUTE');

        foreach ($aAttributes as $aAttribute) {
            $aSelectAttributes[$sOptGroupAttribute][$aAttribute[0]] = $aAttribute[1];
        }

        // Dynamic variant selections
        $sTableName = getViewName('oxarticles');
        $aVarSelect = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_NUM)->getCol("SELECT DISTINCT OXVARNAME FROM {$sTableName} WHERE OXVARNAME != ''");
        $sOptGroupVarSelect = $oLang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_VARSELECT');

        foreach ($aVarSelect as $sVarSelect) {
            $aDiffVarSelect = explode(' | ', $sVarSelect);

            foreach ($aDiffVarSelect as $sDiffVarSelect) {
                $sDiffVarSelect = trim($sDiffVarSelect);
                $aSelectAttributes[$sOptGroupVarSelect]['oxvarselect;' . $sDiffVarSelect] = $sDiffVarSelect;
            }
        }

        return $aSelectAttributes;
    }

    /**
     * Get attribute from eightselect table
     *
     * @return mixed
     */
    private function _getAttributesFromEightselect()
    {
        /** @var eightselect_attribute $oAttribute */
        $oAttribute = oxNew(\ASign\EightSelect\Model\Attribute::class);

        return $oAttribute->getFieldsByType('configurable', true);
    }

    /**
     * Return associated 8select to oxid attributes
     *
     * @return array $aAttributes2Oxid
     * @throws oxSystemComponentException
     */
    private function _getEightselect2Oxid()
    {
        $aAttributes2Oxid = [];

        $oAttr2OxidList = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oAttr2OxidList->init(\ASign\EightSelect\Model\Attribute2Oxid::class);

        foreach ($oAttr2OxidList->getList() as $oAttr2Oxid) {
            $aAttributes2Oxid[$oAttr2Oxid->eightselect_attribute2oxid__esattribute->value][] = $oAttr2Oxid->eightselect_attribute2oxid__oxtype->value . ';' . $oAttr2Oxid->eightselect_attribute2oxid__oxobject->value;
        }

        return $aAttributes2Oxid;
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function save()
    {
        parent::save();

        $oTmpAttribute2Oxid = oxNew(\ASign\EightSelect\Model\Attribute2Oxid::class);
        $oTmpAttribute2Oxid->init();

        $oConfig = $this->getConfig();
        $aAttributes = $oConfig->getRequestParameter('oxid2eightselect');

        foreach ($aAttributes as $s8selectAttributeName => $aOxidAttribute) {
            $oTmpAttribute2Oxid->deleteAttributes2Oxid($s8selectAttributeName);

            foreach ($aOxidAttribute as $sOxidAttribute) {
                $oAttribute2Oxid = clone $oTmpAttribute2Oxid;

                if ($sOxidAttribute !== '-') {
                    $oAttribute2Oxid->setAttributeData($s8selectAttributeName, $sOxidAttribute);
                    $oAttribute2Oxid->save();
                }
            }
        }
    }

    /**
     * Check if attribute is set
     *
     * @param string $sEightselectAttr
     * @param string $sObject
     * @return bool
     */
    public function isAttributeSelected($sEightselectAttr, $sObject)
    {
        if (isset($this->_aAttrEightselect2Oxid[$sEightselectAttr]) && in_array($sObject, $this->_aAttrEightselect2Oxid[$sEightselectAttr])) {
            return true;
        }

        return false;
    }
}
