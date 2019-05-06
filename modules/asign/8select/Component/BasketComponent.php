<?php

namespace ASign\EightSelect\Component;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 * Class BasketComponent
 *
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
        $skuField = Registry::getConfig()->getConfigParam('sArticleSkuField');
        list($type, $field) = explode(';', $skuField);

        $article = oxNew(Article::class);

        if ($type && $field) {
            if ($type === 'oxarticles') {
                $article->load($this->_loadByArticlesField($field, $sku));
            } elseif ($type === 'oxartextends') {
                $article->load($this->_loadByArtExtendsField($field, $sku));
            } elseif ($type === 'oxattribute') {
                $article->load($this->_loadByAttribute($field, $sku));
            } elseif ($type === 'oxvarselect') {
                $article->load($this->_loadByVarSelect($sku));
            }
        }

        return $article;
    }

    /**
     * Load by articles field
     *
     * @param        $articleField
     * @param string $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByArticlesField($articleField, $sku)
    {
        $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');
        $query = "SELECT OXID FROM {$view} WHERE {$articleField} = ?";

        return DatabaseProvider::getDb()->getOne($query, [$sku]);
    }

    /**
     * Load by art extends field
     *
     * @param        $artExtendsField
     * @param string $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByArtExtendsField($artExtendsField, $sku)
    {
        $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxartextends');
        $query = "SELECT OXID FROM {$view} WHERE {$artExtendsField} = ?";

        return DatabaseProvider::getDb()->getOne($query, [$sku]);
    }

    /**
     * Load by attribute
     *
     * @param        $attributeId
     * @param string $sku
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _loadByAttribute($attributeId, $sku)
    {
        $attributeTable = Registry::get(TableViewNameGenerator::class)->getViewName('oxattribute');
        $object2AttributeTable = Registry::get(TableViewNameGenerator::class)->getViewName('oxobject2attribute');

        $query = "SELECT o2a.OXOBJECTID
                  FROM {$attributeTable} AS oxattribute
                  JOIN {$object2AttributeTable} AS o2a ON oxattribute.OXID = o2a.OXATTRID
                  WHERE oxattribute.OXID = ? AND o2a.OXVALUE = ?";

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
        $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');
        $query = "SELECT OXID FROM {$view} WHERE OXVARSELECT LIKE ?";

        return DatabaseProvider::getDb()->getOne($query, ['%' . $sku . '%']);
    }
}
