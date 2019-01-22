<?php

class eightselect_oxcmp_basket extends eightselect_oxcmp_basket_parent
{

    /**
     * @param null $sProductId
     * @param null $dAmount
     * @param null $aSel
     * @param null $aPersParam
     * @param bool $blOverride
     * @return mixed
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function tobasket($sProductId = null, $dAmount = null, $aSel = null, $aPersParam = null, $blOverride = false)
    {
        $sSKU = oxRegistry::getConfig()->getRequestParameter('sku');

        if ( $sSKU ) {
            $oArticle = $this->_loadArticleWithSKU( $sSKU );

            if ($oArticle->exists()) {
                $sProductId = $oArticle->getId();
            }
        }

        return parent::tobasket($sProductId, $dAmount, $aSel, $aPersParam, $blOverride);
    }

    /**
     * @param $sSKU
     * @return object
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _loadArticleWithSKU( $sSKU )
    {

        $oAttr2oxid = oxNew('eightselect_attribute2oxid');

        $sViewName = $oAttr2oxid->getViewName();
        $sSql = "SELECT * FROM {$sViewName} WHERE {$sViewName}.ESATTRIBUTE = 'sku'";
        $blLoaded = $oAttr2oxid->assignRecord($sSql);

        $oArticle = oxNew("oxArticle");

        if ($blLoaded) {

            $sType = $oAttr2oxid->eightselect_attribute2oxid__oxtype->value;
            if ($sType === 'oxarticlesfield') {
                $oArticle->load( $this->_loadByArticlesField($oAttr2oxid, $sSKU) );
            } elseif($sType === 'oxartextendsfield') {
                $oArticle->load( $this->_loadByArtExtendsField($oAttr2oxid, $sSKU) );
            } elseif($sType === 'oxattributeid') {
                $oArticle->load( $this->_loadByAttribute($oAttr2oxid, $sSKU) );
            } elseif($sType === 'oxvarselect') {
                $oArticle->load( $this->_loadByVarSelect($oAttr2oxid, $sSKU) );
            }

        }

        return $oArticle;
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws oxConnectionException
     */
    private function _loadByArticlesField(eightselect_attribute2oxid $oAttr2oxid, $sSKU)
    {
        $sTable = getViewName('oxarticles');
        $sArticleField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT OXID FROM {$sTable} WHERE {$sArticleField} = ?";
        return oxDb::getDb()->getOne($sSql, [$sSKU]);
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws oxConnectionException
     */
    private function _loadByArtExtendsField(eightselect_attribute2oxid $oAttr2oxid, $sSKU)
    {
        $sTable = getViewName('oxartextends');
        $sArtExtendsField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT OXID FROM {$sTable} WHERE {$sArtExtendsField} = ?";
        return oxDb::getDb()->getOne($sSql, [$sSKU]);
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws oxConnectionException
     */
    private function _loadByAttribute(eightselect_attribute2oxid $oAttr2oxid, $sSKU)
    {
        $sAttributeTable = getViewName('oxattribute');
        $sO2ATable = getViewName('oxobject2attribute');
        $sAttributeId = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT o2a.OXOBJECTID
                  FROM {$sAttributeTable} AS oxattribute
                  JOIN {$sO2ATable} AS o2a ON oxattribute.OXID = o2a.OXATTRID
                  WHERE oxattribute.OXID = ?
                    AND o2a.OXVALUE = ?";
        return oxDb::getDb()->getOne($sSql, [$sAttributeId, $sSKU]);
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws oxConnectionException
     */
    private function _loadByVarSelect(eightselect_attribute2oxid $oAttr2oxid, $sSKU)
    {
        $sTable = getViewName('oxarticles');
        $sArticleField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT OXID FROM {$sTable} WHERE OXVARSELECT LIKE ?";
        return oxDb::getDb()->getOne($sSql, ['%' . $sSKU . '%']);
    }

}