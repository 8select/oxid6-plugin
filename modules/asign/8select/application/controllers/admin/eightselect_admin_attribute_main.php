<?php

/**
 * Admin 8select configuration.
 */
class eightselect_admin_attribute_main extends oxAdminDetails
{
    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = "eightselect_admin_attribute_main";

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
        $oLang = oxRegistry::getLang();

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

        $sOptGroupAttribute = oxRegistry::getLang()->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ARTICLE');
        foreach ($aArticleFields as $aArticleField) {
            $aSelectAttributes[$sOptGroupAttribute][$aArticleField[0]] = $aArticleField[1];
        }

        // Dynamic attributes
        $sTableName = getViewName('oxattribute');
        $aAttributes = oxDb::getDb(oxDb::FETCH_MODE_NUM)->getAll("SELECT CONCAT('oxattributeid;', OXID), OXTITLE FROM {$sTableName}");
        $sOptGroupAttribute = $oLang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ATTRIBUTE');
        foreach ($aAttributes as $aAttribute) {
            $aSelectAttributes[$sOptGroupAttribute][$aAttribute[0]] = $aAttribute[1];
        }

        // Dynamic variant selections
        $sTableName = getViewName('oxarticles');
        $aVarSelect = oxDb::getDb(oxDb::FETCH_MODE_NUM)->getCol("SELECT DISTINCT OXVARNAME FROM {$sTableName} WHERE OXVARNAME != ''");
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
     * Return sorted 8select attributes
     *
     * @return object oxList
     * @throws oxSystemComponentException
     */
    private function _getAttributesFromEightselect()
    {
        /** @var eightselect_attribute $oAttribute */
        $oAttribute = oxNew('eightselect_attribute');
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

        $oAttr2OxidList = oxNew('oxList');
        $oAttr2OxidList->init('eightselect_attribute2oxid');

        foreach ($oAttr2OxidList->getList() as $oAttr2Oxid) {
            $aAttributes2Oxid[$oAttr2Oxid->eightselect_attribute2oxid__esattribute->value][] = $oAttr2Oxid->eightselect_attribute2oxid__oxtype->value . ';' . $oAttr2Oxid->eightselect_attribute2oxid__oxobject->value;
        }

        return $aAttributes2Oxid;
    }

    /**
     * Calls parent::save, save new associated attributes oder delete it
     *
     * @throws oxSystemComponentException
     */
    public function save()
    {
        parent::save();

        /** @var eightselect_attribute2oxid $oTmpAttribute2Oxid */
        $oTmpAttribute2Oxid = oxNew('eightselect_attribute2oxid');
        $oTmpAttribute2Oxid->init();

        $oConfig = $this->getConfig();
        $aAttributes = $oConfig->getRequestParameter("oxid2eightselect");

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
