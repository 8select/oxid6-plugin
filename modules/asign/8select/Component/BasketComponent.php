<?php

namespace ASign\EightSelect\Component;

use ASign\EightSelect\Model\Attribute2Oxid;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;

/**
 * Class BasketComponent
 * @package ASign\EightSelect\Component
 */
class BasketComponent extends BasketComponent_parent
{
    /**
     * To basket
     *
     * @param string $productId
     * @param int    $amount
     * @param array  $sel
     * @param array  $persParam
     * @param bool   $override
     * @return mixed
     */
    public function toBasket($productId = null, $amount = null, $sel = null, $persParam = null, $override = false)
    {
        $sku = Registry::get(Request::class)->getRequestEscapedParameter('sku');

        if ($sku) {
            $article = $this->_loadArticleWithSKU($sku);

            if ($article->exists()) {
                $productId = $article->getId();
            }
        }

        return parent::toBasket($productId, $amount, $sel, $persParam, $override);
    }

    /**
     * Load article with SKU
     *
     * @param string $sku
     * @return Article
     */
    protected function _loadArticleWithSKU($sku)
    {
        /** @var Attribute2Oxid $attribute2oxid */
        $attribute2oxid = oxNew(Attribute2Oxid::class);

        $viewName = $attribute2oxid->getViewName();
        $query = "SELECT * FROM {$viewName} WHERE {$viewName}.ESATTRIBUTE = 'sku'";
        $loaded = $attribute2oxid->assignRecord($query);

        $article = oxNew(Article::class);

        if ($loaded) {
            $type = $attribute2oxid->getFieldData('oxtype');

            if ($type === 'oxarticlesfield') {
                $article->load($this->_loadByArticlesField($attribute2oxid, $sku));
            } elseif ($type === 'oxartextendsfield') {
                $article->load($this->_loadByArtExtendsField($attribute2oxid, $sku));
            } elseif ($type === 'oxattributeid') {
                $article->load($this->_loadByAttribute($attribute2oxid, $sku));
            } elseif ($type === 'oxvarselect') {
                $article->load($this->_loadByVarSelect($sku));
            }
        }

        return $article;
    }

    /**
     * Load by articles field
     *
     * @param Attribute2Oxid $attribute2oxid
     * @param string         $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByArticlesField(Attribute2Oxid $attribute2oxid, $sku)
    {
        $view = getViewName('oxarticles');
        $articleField = $attribute2oxid->getFieldData('oxobject');

        $query = "SELECT OXID FROM {$view} WHERE {$articleField} = ?";

        return DatabaseProvider::getDb()->getOne($query, [$sku]);
    }

    /**
     * Load by art extends field
     *
     * @param Attribute2Oxid $attribute2oxid
     * @param string         $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByArtExtendsField(Attribute2Oxid $attribute2oxid, $sku)
    {
        $view = getViewName('oxartextends');
        $artExtendsField = $attribute2oxid->getFieldData('oxobject');

        $query = "SELECT OXID FROM {$view} WHERE {$artExtendsField} = ?";

        return DatabaseProvider::getDb()->getOne($query, [$sku]);
    }

    /**
     * Load by attribute
     *
     * @param Attribute2Oxid $attribute2oxid
     * @param string         $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByAttribute(Attribute2Oxid $attribute2oxid, $sku)
    {
        $attributeTable = getViewName('oxattribute');
        $object2AttributeTable = getViewName('oxobject2attribute');
        $attributeId = $attribute2oxid->getFieldData('oxobject');

        $query = "SELECT o2a.OXOBJECTID
                  FROM {$attributeTable} AS oxattribute
                  JOIN {$object2AttributeTable} AS o2a ON oxattribute.OXID = o2a.OXATTRID
                  WHERE oxattribute.OXID = ?
                    AND o2a.OXVALUE = ?";

        return DatabaseProvider::getDb()->getOne($query, [$attributeId, $sku]);
    }

    /**
     * Load by var select
     *
     * @param string $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByVarSelect($sku)
    {
        $view = getViewName('oxarticles');

        $query = "SELECT OXID FROM {$view} WHERE OXVARSELECT LIKE ?";

        return DatabaseProvider::getDb()->getOne($query, ['%' . $sku . '%']);
    }
}
