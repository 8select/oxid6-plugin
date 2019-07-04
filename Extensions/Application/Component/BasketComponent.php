<?php

namespace ASign\EightSelect\Extensions\Application\Component;

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
     * @param string $sku
     * @return Article
     */
    protected function _loadArticleWithSKU($sku)
    {
        $skuField = Registry::getConfig()->getConfigParam('sArticleSkuField');
        $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');
        $query = "SELECT OXID FROM {$view} WHERE {$skuField} = ?";

        $article = oxNew(Article::class);
        $article->load(DatabaseProvider::getDb()->getOne($query, [$sku]));

        return $article;
    }
}
