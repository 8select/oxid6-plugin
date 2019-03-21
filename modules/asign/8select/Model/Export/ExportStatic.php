<?php

namespace ASign\EightSelect\Model\Export;

use ASign\EightSelect\Model\Export;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Core\Price;

/**
 * Class ExportStatic
 * @package ASign\EightSelect\Model\Export
 */
class ExportStatic extends ExportAbstract
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'EightSelectExportStatic';

    /** @var string */
    protected $_virtualMasterSku = null;

    /**
     * Set static fields (not configurable ones)
     */
    public function run()
    {
        if ($this->_oParent) {
            $model = $this->_oParent->getFieldData('oxartnum');
        } else {
            $model = $this->_oArticle->getFieldData('oxartnum');
        }

        if ($this->_oArticle->getFieldData('oxtitle')) {
            $title = $this->_oArticle->oxarticles__oxtitle->rawValue;
        } elseif ($this->_oParent) {
            $title = $this->_oParent->oxarticles__oxtitle->rawValue;
        } else {
            $title = '';
        }

        !isset($this->_aCsvAttributes['mastersku']) ? null : $this->_aCsvAttributes['mastersku'] = $this->_getVirtualMasterSku();
        !isset($this->_aCsvAttributes['model']) ? null : $this->_aCsvAttributes['model'] = $model;
        !isset($this->_aCsvAttributes['status']) ? null : $this->_aCsvAttributes['status'] = $this->getArticleStatus($this->_oArticle);
        !isset($this->_aCsvAttributes['name1']) ? null : $this->_aCsvAttributes['name1'] = html_entity_decode($title, ENT_QUOTES | ENT_HTML401);
        !isset($this->_aCsvAttributes['produkt_url']) ? null : $this->_aCsvAttributes['produkt_url'] = $this->_oArticle->getLink();
        !isset($this->_aCsvAttributes['bilder']) ? null : $this->_aCsvAttributes['bilder'] = $this->_getPictures();

        /** @var Manufacturer $manufacturer */
        $manufacturer = $this->_oArticle->getManufacturer();

        if ($manufacturer) {
            !isset($this->_aCsvAttributes['marke']) ? null : $this->_aCsvAttributes['marke'] = $manufacturer->oxmanufacturers__oxtitle->rawValue;
        }

        if (isset($this->_aCsvAttributes['angebots_preis'])) {
            /** @var Price $price */
            $price = $this->_oArticle->getPrice();

            if ($price) {
                $this->_aCsvAttributes['angebots_preis'] = $price->getPrice();
            }
        }

        if (isset($this->_aCsvAttributes['streich_preis'])) {
            /** @var Price $tPrice */
            $tPrice = $this->_oArticle->getTPrice();

            if ($tPrice) {
                $fPrice = $tPrice->getPrice();
            } else {
                $fPrice = $this->_aCsvAttributes['angebots_preis'];
            }

            $this->_aCsvAttributes['streich_preis'] = $fPrice;
        }

        $this->_setCategories();
    }

    /**
     * @return string
     */
    protected function _getPictures()
    {
        $pictureUrls = [];
        $picCount = $this->getConfig()->getConfigParam('iPicCount');

        for ($i = 1; $i <= $picCount; $i++) {
            $picUrl = $this->_oArticle->getPictureUrl($i);

            if (strpos($picUrl, 'nopic.jpg') === false) {
                $pictureUrls[] = $picUrl;
            }
        }

        return implode(Export::CSV_MULTI_DELIMITER, $pictureUrls);
    }

    /**
     * @return string
     */
    protected function _getVirtualMasterSku()
    {
        if (!$this->_oParent) {
            return $this->_oArticle->getFieldData('oxartnum');
        }

        if ($this->_virtualMasterSku !== null) {
            return $this->_virtualMasterSku;
        }

        $this->_virtualMasterSku = '';

        /** @var ExportDynamic $exportDynamic */
        $exportDynamic = oxNew(ExportDynamic::class);
        $exportDynamic->setArticle($this->_oArticle);
        $exportDynamic->setParent($this->_oParent);
        $fieldValue = strtolower($exportDynamic->getVariantSelection('farbe'));

        if ($fieldValue) {
            $virtualMasterSku = $this->_oParent->getFieldData('oxartnum') . '-' . str_replace(' ', '', $fieldValue);
            $this->_virtualMasterSku = $virtualMasterSku;
        }

        return $this->_virtualMasterSku;
    }

    /**
     * Set categories
     */
    public function _setCategories()
    {
        if ($this->_oParentExport) {
            !isset($this->_aCsvAttributes['kategorie1']) ? null : $this->_aCsvAttributes['kategorie1'] = $this->_oParentExport->getAttributeValue('kategorie1');
            !isset($this->_aCsvAttributes['kategorie2']) ? null : $this->_aCsvAttributes['kategorie2'] = $this->_oParentExport->getAttributeValue('kategorie2');
            !isset($this->_aCsvAttributes['kategorie3']) ? null : $this->_aCsvAttributes['kategorie3'] = $this->_oParentExport->getAttributeValue('kategorie3');

            return;
        } elseif ($this->_oParent) {
            $catIds = $this->_oParent->getCategoryIds();
        } else {
            $catIds = $this->_oArticle->getCategoryIds();
        }

        $categories = array_slice($this->_getCategoryPaths($catIds), 0, 3);

        if (count($categories)) {
            $i = 1;
            foreach ($categories as $categoryPath) {
                !isset($this->_aCsvAttributes['kategorie' . $i]) ? null : $this->_aCsvAttributes['kategorie' . $i] = $categoryPath;
                $i++;
            }
        }
    }

    /**
     * @param array $catIds
     * @return array $aCatPaths
     */
    protected function _getCategoryPaths($catIds)
    {
        static $tmpCat = null;

        if ($tmpCat === null) {
            /** @var Category $tmpCat */
            $tmpCat = oxNew(Category::class);
        }

        static $categoryPath = [];

        $catPaths = [];
        $categories = [];
        foreach ($catIds as $cat) {
            $tmp = explode('=', $cat);
            $catId = isset($tmp[0]) ? $tmp[0] : null;
            $time = isset($tmp[1]) ? (int) $tmp[1] : null;

            if (!isset($categoryPath[$catId])) {
                /** @var Category $category */
                $category = clone $tmpCat;
                $category->load($catId);
                $categories[$catId] = $category;
                $catPath = str_replace('/', '%2F', html_entity_decode($category->oxcategories__oxtitle->rawValue, ENT_QUOTES | ENT_HTML401));

                while ($category->getId() != $category->getFieldData('oxrootid')) {
                    $parentCatId = $category->getFieldData('oxparentid');
                    $category = clone $tmpCat;
                    $category->load($parentCatId);
                    $catPath = str_replace('/', '%2F', html_entity_decode($category->oxcategories__oxtitle->rawValue, ENT_QUOTES | ENT_HTML401)) . Export::CATEGORY_DELIMITER . $catPath;
                }

                $categoryPath[$catId] = $catPath;
            }

            if ($time == 1) {
                array_unshift($catPaths, $categoryPath[$catId]);
            } else {
                array_push($catPaths, $categoryPath[$catId]);
            }
        }

        return array_filter(array_unique($catPaths));
    }

    /**
     * Return article status
     *
     * @param Article $article
     * @return int
     */
    protected function getArticleStatus($article)
    {
        if (!$article->isVisible()) {
            return 0;
        }

        if ($article->getFieldData('oxstockflag') == 3 && $article->isNotBuyable()) {
            return 0;
        }

        return 1;
    }
}
