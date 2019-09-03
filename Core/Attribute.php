<?php

namespace ASign\EightSelect\Core;

use OxidEsales\Eshop\Core\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 * Attributes manager
 */
class Attribute extends Base
{
    /**
     * All fields with additional data
     *
     * @var array
     */
    protected $_aEightselectFields = [];

    protected $_aVarNames = [];

    /**
     * Collects all possible fields
     */
    public function __construct()
    {
        parent::__construct();

        $defaultLang = (int) Registry::getConfig()->getConfigParam('sDefaultLang');

        $articleView = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles', $defaultLang);
        $articleColumns = DatabaseProvider::getDb()->getCol("SHOW COLUMNS FROM $articleView");
        foreach ($articleColumns as $column) {
            if (strpos($column, 'OXPIC') === 0) {
                continue;
            }
            $fields[] = ['name' => 'oxarticles.' . $column, 'label' => Registry::getLang()->translateString($column)];
        }

        $attributesView = Registry::get(TableViewNameGenerator::class)->getViewName('oxattribute', $defaultLang);
        $attributes = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll("SELECT OXID, OXTITLE FROM $attributesView");
        foreach ($attributes as $attribute) {
            $fields[] = ['name' => 'oxattribute.id=' . $attribute['OXID'], 'label' => $attribute['OXTITLE']];
        }

        $maxCategoriesQuery = 'SELECT MAX(COUNTER) FROM (selEct COUNT(1) COUNTER FROM oxobject2category o2c JOIN oxarticles a ON o2c.OXOBJECTID = a.OXID JOIN oxcategories c ON c.OXID = o2c.OXCATNID  GROUP BY OXOBJECTID) tmp';
        $maxCategories = DatabaseProvider::getDb()->getOne($maxCategoriesQuery);
        for ($i = 0; $i < $maxCategories; $i++) {
            $fields[] = ['name' => 'oxcategory.' . $i, 'label' => 'Category ' . $i];
        }

        $fields[] = ['name' => 'oxartextends.OXLONGDESC', 'label' => 'Article long description',];
        $fields[] = ['name' => 'oxvendor.OXTITLE', 'label' => 'Vendor title',];
        $fields[] = ['name' => 'oxmanufacturers.OXTITLE', 'label' => 'Manufacturer title',];
        $fields[] = ['name' => 'oxseo.URL', 'label' => 'Article URL',];
        $fields[] = ['name' => 'product.PICTURES', 'label' => 'Article pictures',];
        $fields[] = ['name' => 'product.BUYABLE', 'label' => 'Variant can be ordered',];
        $fields[] = ['name' => 'product.SKU', 'label' => 'Variant SKU',];

        $varNamesQuery = "SELECT DISTINCT OXVARNAME FROM $articleView WHERE OXVARNAME != ''";
        $varNamesResult = DatabaseProvider::getDb()->getCol($varNamesQuery);
        $varNames = [];
        foreach ($varNamesResult as $value) {
            $splitNames = explode(' | ', $value);
            foreach ($splitNames as $name) {
                if (!in_array($name, $varNames, true)) {
                    $varNames[] = $name;

                    $fields[] = ['name' => 'oxvarname.' . $name, 'label' => $name];
                    $this->_aVarNames[] = ['name' => 'oxvarname.' . $name, 'label' => $name];
                }
            }
        }

        $this->_aEightselectFields = $fields;
    }

    /**
     * Return all CSV field names in correct order
     *
     * @param bool get fields as sorted array (first: required; second; name)
     * @return array
     */
    public function getAllFields()
    {
        return $this->_aEightselectFields;
    }

    /**
     * Returns list of attributes relevant for variant building
     *
     * @return array
     */
    public function getVarNames()
    {
        return $this->_aVarNames;
    }
}