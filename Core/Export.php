<?php

namespace ASign\EightSelect\Core;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\SeoEncoderArticle;
use OxidEsales\Eshop\Core\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 * 8select export
 */
class Export extends Base
{
    /** @var array */
    protected $data = [];

    /** @var array */
    protected $priceFields = [
        'oxprice', 'oxbprice', 'oxtprice', 'oxvarminprice', 'oxvarmaxprice',
        'oxpricea', 'oxpriceb', 'oxpricec',
    ];

    /**
     * Main export method: Calls sub methods to collect all required data
     *
     * @param array $fields         Fields which should be exported
     * @param array $articleData    Article data (directly from oxarticles table)
     * @param array $requiredFields Required fields which may not be filtered out
     * @return array
     */
    public function getExportData($fields, $articleData, $requiredFields)
    {
        $groupedFields = [];
        foreach ($fields as $fieldData) {
            list($table) = explode('.', $fieldData['name']);
            if (!isset($groupedFields[$table])) {
                $groupedFields[$table] = [];
            }

            $groupedFields[$table][] = $fieldData;
        }

        /** @var Article $article */
        $article = oxNew(Article::class);
        $article->load($articleData['OXID']);

        $id = $articleData['OXPARENTID'] ? $articleData['OXPARENTID'] : $articleData['OXID'];

        foreach ($groupedFields as $table => $tableFields) {
            if ($table === 'oxarticles') {
                $this->_buildArticleFields($articleData, $tableFields, $article);
            } elseif ($table === 'oxcategory') {
                $this->_buildCategoryFields($id, $tableFields);
            } elseif ($table === 'oxattribute') {
                $this->_buildAttributeFields($id, $tableFields);
            } elseif ($table === 'oxvendor') {
                $this->_buildVendorField($articleData['OXVENDORID'], $tableFields);
            } elseif ($table === 'oxmanufacturers') {
                $this->_buildManufacturerField($articleData['OXMANUFACTURERID'], $tableFields);
            } elseif ($table === 'oxseo') {
                $this->_buildSeoField($article, $tableFields);
            } elseif ($table === 'oxvarname') {
                $this->_buildVarNameFields($articleData, $tableFields);
            } elseif ($table === 'oxartextends') {
                $this->_buildArtExtendsAttribute($article, $tableFields);
            } elseif ($table === 'product') {
                $this->_buildProductAttributes($articleData, $tableFields, $article);
            }
        }
        $this->data = array_filter($this->data, function ($field, $key) use ($requiredFields) {
            $isEmpty = $field['value'] === '' || $field['value'] === null;
            $isRequired = in_array($key, $requiredFields, true);

            return !$isEmpty || $isRequired;
        }, ARRAY_FILTER_USE_BOTH);

        return $this->data;
    }

    /**
     * Builds attributes from oxarticles table
     * Special case for pictures: OXID has only the picture name but we need a full URL
     *
     * @param array   $articleData Article data (directly from oxarticles table)
     * @param array   $tableFields Article fields which should be exported
     * @param Article $article     Loaded article
     */
    protected function _buildArticleFields($articleData, $tableFields, $article)
    {
        foreach ($tableFields as $fieldData) {
            list(, $field) = explode('.', $fieldData['name']);
            if (in_array(strtolower($field), $this->priceFields, true)) {
                if ($articleData[$field]) {
                    $decimal = pow(10, $this->getConfig()->getActShopCurrencyObject()->decimal);
                    $articleData[$field] *= $decimal;
                } else {
                    // Value = 0 or empty: Set to null
                    $articleData[$field] = null;
                }
            }

            // OXPARENTID may never be empty, says 8select
            if ($field === 'OXPARENTID' && !$articleData[$field]) {
                $articleData[$field] = $articleData['OXID'];
            }

            $this->data[$fieldData['name']] = [
                'label' => $fieldData['label'],
                'value' => $articleData[$field],
            ];
        }
    }

    /**
     * Builds category assign fields
     *
     * @param string $articleId   Article ID
     * @param array  $tableFields Category fields which should be exported
     */
    protected function _buildCategoryFields($articleId, $tableFields)
    {
        $categoryAssignView = Registry::get(TableViewNameGenerator::class)->getViewName('oxobject2category');
        $maxCategories = count($tableFields);
        $categoryIdQuery = "SELECT OXCATNID FROM $categoryAssignView WHERE OXOBJECTID = ? ORDER BY OXTIME LIMIT $maxCategories";
        $categoryIds = DatabaseProvider::getDb()->getCol($categoryIdQuery, [$articleId]);
        $categoryPaths = $this->_getCategoryPaths($categoryIds);
        foreach ($categoryPaths as $i => $categoryPath) {
            $this->data['oxcategory.' . $i] = ['label' => 'Category ' . $i, 'value' => $categoryPath];
        }
    }

    /**
     * Builds attribute assign fields
     *
     * @param string $articleId   Article ID
     * @param array  $tableFields Attributes which should be exported
     */
    protected function _buildAttributeFields($articleId, $tableFields)
    {
        $attributeAssignView = Registry::get(TableViewNameGenerator::class)->getViewName('oxobject2attribute');
        $attributeQuery = "SELECT OXVALUE FROM $attributeAssignView WHERE OXATTRID = ? AND OXOBJECTID = ?";
        foreach ($tableFields as $fieldData) {
            list(, $attributeId) = explode('=', $fieldData['name']);
            $attributeValue = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getOne($attributeQuery, [$attributeId, $articleId]);
            $this->data[$fieldData['name']] = [
                'label' => $fieldData['label'],
                'value' => $attributeValue ? $attributeValue : '',
            ];
        }
    }

    /**
     * Builds vendor title field
     *
     * @param string $vendorId    Vendor ID
     * @param array  $tableFields Fields which should be exported
     */
    protected function _buildVendorField($vendorId, $tableFields)
    {
        $fieldData = array_shift($tableFields);
        $this->data[$fieldData['name']] = [
            'label' => $fieldData['label'],
            'value' => '',
        ];
        if ($vendorId) {
            $vendorView = Registry::get(TableViewNameGenerator::class)->getViewName('oxvendor');
            $vendorQuery = "SELECT OXTITLE FROM $vendorView WHERE OXID = ?";
            $vendorTitle = DatabaseProvider::getDb()->getOne($vendorQuery, [$vendorId]);
            if ($vendorTitle) {
                $this->data[$fieldData['name']]['value'] = $vendorTitle;
            }
        }
    }

    /**
     * Builds manufacturer title field
     *
     * @param string $manufacturerId Manufacturer ID
     * @param array  $tableFields    Fields which should be exported
     */
    protected function _buildManufacturerField($manufacturerId, $tableFields)
    {
        $fieldData = array_shift($tableFields);
        $this->data[$fieldData['name']] = [
            'label' => $fieldData['label'],
            'value' => '',
        ];
        if ($manufacturerId) {
            $manufacturerView = Registry::get(TableViewNameGenerator::class)->getViewName('oxmanufacturers');
            $manufacturerQuery = "SELECT OXTITLE FROM $manufacturerView WHERE OXID = ?";
            $manufacturerTitle = DatabaseProvider::getDb()->getOne($manufacturerQuery, [$manufacturerId]);
            if ($manufacturerTitle) {
                $this->data[$fieldData['name']]['value'] = $manufacturerTitle;
            }
        }
    }

    /**
     * Builds article SEO URL field
     *
     * @param Article $article     Loaded article
     * @param array   $tableFields Fields which should be exported
     */
    protected function _buildSeoField($article, $tableFields)
    {
        $fieldData = array_shift($tableFields);
        $this->data[$fieldData['name']] = [
            'label' => $fieldData['label'],
            'value' => Registry::get(SeoEncoderArticle::class)->getArticleUrl($article),
        ];
    }

    /**
     * Builds variant name fields
     *
     * @param array $articleData Article data (directly from oxarticles table)
     * @param array $tableFields Variant names which should be exported
     */
    protected function _buildVarNameFields($articleData, $tableFields)
    {
        $varName = explode('|', str_replace([' | ', ' |', '| '], '|', $articleData['OXVARNAME']));
        $varSelect = explode('|', str_replace([' | ', ' |', '| '], '|', $articleData['OXVARSELECT']));
        $fullVarSelect = array_combine($varName, $varSelect);
        foreach ($tableFields as $fieldData) {
            $this->data[$fieldData['name']] = [
                'label' => $fieldData['label'],
                'value' => isset($fullVarSelect[$fieldData['label']]) ? $fullVarSelect[$fieldData['label']] : '',
                'isVariantDetail' => true,
            ];
        }
    }

    /**
     * Builds article long description field
     *
     * @param Article $article     Loaded article
     * @param array   $tableFields Fields which should be exported
     */
    protected function _buildArtExtendsAttribute($article, $tableFields)
    {
        $fieldData = array_shift($tableFields);
        $this->data[$fieldData['name']] = [
            'label' => $fieldData['label'],
            'value' => $article->getLongDesc(),
        ];
    }

    /**
     * _buildProductAttributes
     * -----------------------------------------------------------------------------------------------------------------
     * Builds special attributes which didn't really fit elsewhere
     * BUYABLE and PICTURES are not singular database fields
     *
     * @param array   $articleData Article data (directly from oxarticles table)
     * @param array   $tableFields Article fields which should be exported
     * @param Article $article     Loaded article
     */
    protected function _buildProductAttributes($articleData, $tableFields, $article)
    {
        foreach ($tableFields as $fieldData) {
            list(, $field) = explode('.', $fieldData['name']);

            $this->data[$fieldData['name']] = [
                'label' => $fieldData['label'],
                'value' => '',
            ];

            if ($field === 'PICTURES') {
                $this->data[$fieldData['name']]['value'] = $this->_getArticlePictures($article);
            } elseif ($field === 'BUYABLE') {
                $this->data[$fieldData['name']]['value'] = $article->isBuyable() ? 1 : 0;
            } elseif ($field === 'SKU') {
                $articleSkuField = $this->getConfig()->getConfigParam('sArticleSkuField');
                $this->data[$fieldData['name']]['value'] = $article->getFieldData($articleSkuField);
            }
        }
    }

    /**
     * _getArticlePictures
     * -----------------------------------------------------------------------------------------------------------------
     *
     * @param Article $article
     * @return array
     */
    protected function _getArticlePictures($article)
    {
        $pictures = [];

        $hasVariantPictures = false;
        for ($i = 1; $i <= 12; $i++) {
            $pictureUrl = $article->getPictureUrl($i);
            if (!is_null($pictureUrl) && strpos($pictureUrl, 'nopic.jpg') === false) {
                $hasVariantPictures = true;

                $pictures[] = $pictureUrl;
            }
        }

        if (!$hasVariantPictures && ($parent = $article->getParentArticle())) {
            for ($i = 1; $i <= 12; $i++) {
                $pictureUrl = $parent->getPictureUrl($i);
                if (!is_null($pictureUrl) && strpos($pictureUrl, 'nopic.jpg') === false) {
                    $pictures[] = $pictureUrl;
                }
            }
        }

        return $pictures;
    }

    /**
     * Builds category paths for the given category IDs
     *
     * @param array $categoryIds
     * @return array
     */
    protected function _getCategoryPaths($categoryIds)
    {
        $oTmpCat = oxNew(Category::class);

        $aCategoryPath = [];

        $aCatPaths = [];
        foreach ($categoryIds as $sCatId) {
            if (!$aCategoryPath[$sCatId]) {
                $oCat = clone $oTmpCat;
                $oCat->load($sCatId);
                $aCategories[$sCatId] = $oCat;
                $sCatPath = str_replace('/', '%2F', html_entity_decode($oCat->oxcategories__oxtitle->rawValue, ENT_QUOTES | ENT_HTML401));
                while ($oCat->oxcategories__oxid->value != $oCat->oxcategories__oxrootid->value) {
                    $sParentCatId = $oCat->oxcategories__oxparentid->value;
                    $oCat = clone $oTmpCat;
                    $oCat->load($sParentCatId);
                    $sCatPath = str_replace('/', '%2F', html_entity_decode($oCat->oxcategories__oxtitle->rawValue, ENT_QUOTES | ENT_HTML401)) . ' / ' . $sCatPath;
                }
                $aCategoryPath[$sCatId] = $sCatPath;
            }
            array_push($aCatPaths, $aCategoryPath[$sCatId]);
        }

        return array_filter(array_unique($aCatPaths));
    }
}
