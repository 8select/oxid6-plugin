<?php

namespace ASign\EightSelect\Component;

/**
 * Class BasketComponent
 * @package ASign\EightSelect\Component
 */
class BasketComponent extends BasketComponent_parent
{
    /**
     * To basket
     *
     * @param null $sProductId
     * @param null $dAmount
     * @param null $aSel
     * @param null $aPersParam
     * @param bool $blOverride
     * @return mixed
     */
    public function toBasket($sProductId = null, $dAmount = null, $aSel = null, $aPersParam = null, $blOverride = false)
    {
        $sSKU = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('sku');

        if ($sSKU) {
            $oArticle = $this->_loadArticleWithSKU($sSKU);

            if ($oArticle->exists()) {
                $sProductId = $oArticle->getId();
            }
        }

        return parent::toBasket($sProductId, $dAmount, $aSel, $aPersParam, $blOverride);
    }

    /**
     * Load article with SKU
     *
     * @param $sSKU
     * @return object|\OxidEsales\Eshop\Application\Model\Article
     */
    protected function _loadArticleWithSKU($sSKU)
    {
        $oAttr2oxid = oxNew(\ASign\EightSelect\Model\Attribute2Oxid::class);

        $sViewName = $oAttr2oxid->getViewName();
        $sSql = "SELECT * FROM {$sViewName} WHERE {$sViewName}.ESATTRIBUTE = 'sku'";
        $blLoaded = $oAttr2oxid->assignRecord($sSql);

        $oArticle = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);

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
     * Load by articles field
     *
     * @param \ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    private function _loadByArticlesField(\ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid, $sSKU)
    {
        $sTable = getViewName('oxarticles');
        $sArticleField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT OXID FROM {$sTable} WHERE {$sArticleField} = ?";

        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSql, [$sSKU]);
    }

    /**
     * Load by art extends field
     *
     * @param \ASign\EightSelect\Model\EightSelectAttribute2Oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    private function _loadByArtExtendsField(\ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid, $sSKU)
    {
        $sTable = getViewName('oxartextends');
        $sArtExtendsField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT OXID FROM {$sTable} WHERE {$sArtExtendsField} = ?";

        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSql, [$sSKU]);
    }

    /**
     * Load by attribute
     *
     * @param \ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    private function _loadByAttribute(\ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid, $sSKU)
    {
        $sAttributeTable = getViewName('oxattribute');
        $sO2ATable = getViewName('oxobject2attribute');
        $sAttributeId = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT o2a.OXOBJECTID
                  FROM {$sAttributeTable} AS oxattribute
                  JOIN {$sO2ATable} AS o2a ON oxattribute.OXID = o2a.OXATTRID
                  WHERE oxattribute.OXID = ?
                    AND o2a.OXVALUE = ?";

        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSql, [$sAttributeId, $sSKU]);
    }

    /**
     * Load by var select
     *
     * @param \ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid
     * @param $sSKU
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    private function _loadByVarSelect(\ASign\EightSelect\Model\Attribute2Oxid $oAttr2oxid, $sSKU)
    {
        $sTable = getViewName('oxarticles');
        $sArticleField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT OXID FROM {$sTable} WHERE OXVARSELECT LIKE ?";

        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSql, ['%' . $sSKU . '%']);
    }
}
