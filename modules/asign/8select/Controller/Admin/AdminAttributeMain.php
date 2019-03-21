<?php

namespace ASign\EightSelect\Controller\Admin;

use ASign\EightSelect\Model\Attribute;
use ASign\EightSelect\Model\Attribute2Oxid;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;

/**
 * Class AdminAttributeMain
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminAttributeMain extends AdminDetailsController
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
    protected $_attribute2Oxid = [];

    /**
     * Calls parent::render, sets template data
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();

        $this->_aViewData['aAttributesEightselect'] = $this->_getAttributesFromEightselect();
        $this->_aViewData['aAttributesOxid'] = $this->_getAttributesFromOxid();

        $this->_attribute2Oxid = $this->_getEightselect2Oxid();

        return $template;
    }

    /**
     * Collect Oxid possible values to match with 8select
     *
     * @return array $aSelectAttributes
     */
    protected function _getAttributesFromOxid()
    {
        $selectAttributes = [];
        $lang = Registry::getLang();

        // Default static Oxid fields
        $articleFields = [
            ['oxarticlesfield;OXARTNUM', $lang->translateString('ARTICLE_MAIN_ARTNUM')],
            ['oxarticlesfield;OXTITLE', $lang->translateString('ARTICLE_MAIN_TITLE')],
            ['oxarticlesfield;OXSHORTDESC', $lang->translateString('GENERAL_ARTICLE_OXSHORTDESC')],
            ['oxartextendsfield;OXLONGDESC', $lang->translateString('GENERAL_ARTICLE_OXLONGDESC')],
            ['oxarticlesfield;OXEAN', $lang->translateString('ARTICLE_MAIN_EAN')],
            ['oxarticlesfield;OXWIDTH', $lang->translateString('GENERAL_ARTICLE_OXWIDTH')],
            ['oxarticlesfield;OXHEIGHT', $lang->translateString('GENERAL_ARTICLE_OXHEIGHT')],
            ['oxarticlesfield;OXHLENGTH', $lang->translateString('GENERAL_ARTICLE_OXLENGTH')],
        ];

        $optGroupAttribute = Registry::getLang()->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ARTICLE');

        foreach ($articleFields as $articleField) {
            $selectAttributes[$optGroupAttribute][$articleField[0]] = $articleField[1];
        }

        // Dynamic attributes
        $tableName = getViewName('oxattribute');
        $attributes = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getAll("SELECT CONCAT('oxattributeid;', OXID), OXTITLE FROM {$tableName}");
        $optGroupAttribute = $lang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ATTRIBUTE');

        foreach ($attributes as $attribute) {
            $selectAttributes[$optGroupAttribute][$attribute[0]] = $attribute[1];
        }

        // Dynamic variant selections
        $tableName = getViewName('oxarticles');
        $varSelects = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getCol("SELECT DISTINCT OXVARNAME FROM {$tableName} WHERE OXVARNAME != ''");
        $optGroupVarSelect = $lang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_VARSELECT');

        foreach ($varSelects as $varSelect) {
            $diffVarSelects = explode(' | ', $varSelect);

            foreach ($diffVarSelects as $diffVarSelect) {
                $diffVarSelect = trim($diffVarSelect);
                $selectAttributes[$optGroupVarSelect]['oxvarselect;' . $diffVarSelect] = $diffVarSelect;
            }
        }

        return $selectAttributes;
    }

    /**
     * Get attribute from eightselect table
     *
     * @return mixed
     */
    protected function _getAttributesFromEightselect()
    {
        /** @var Attribute $attribute */
        $attribute = oxNew(Attribute::class);

        return $attribute->getFieldsByType('configurable', true);
    }

    /**
     * Return associated 8select to oxid attributes
     *
     * @return array $aAttributes2Oxid
     */
    protected function _getEightselect2Oxid()
    {
        $attributes2Oxid = [];

        $attr2OxidList = oxNew(ListModel::class);
        $attr2OxidList->init(Attribute2Oxid::class);

        foreach ($attr2OxidList->getList() as $attr2Oxid) {
            $attributes2Oxid[$attr2Oxid->eightselect_attribute2oxid__esattribute->value][] = $attr2Oxid->eightselect_attribute2oxid__oxtype->value . ';' . $attr2Oxid->eightselect_attribute2oxid__oxobject->value;
        }

        return $attributes2Oxid;
    }

    /**
     * save
     *
     * @throws \Exception
     */
    public function save()
    {
        parent::save();

        $tmpAttribute2Oxid = oxNew(Attribute2Oxid::class);
        $tmpAttribute2Oxid->init();

        $attributes = Registry::get(Request::class)->getRequestEscapedParameter('oxid2eightselect');

        foreach ($attributes as $selectAttributeName => $oxidAttributes) {
            $tmpAttribute2Oxid->deleteAttributes2Oxid($selectAttributeName);

            foreach ($oxidAttributes as $oxidAttribute) {
                $attribute2Oxid = clone $tmpAttribute2Oxid;

                if ($oxidAttribute !== '-') {
                    $attribute2Oxid->setAttributeData($selectAttributeName, $oxidAttribute);
                    $attribute2Oxid->save();
                }
            }
        }
    }

    /**
     * Check if attribute is set
     *
     * @param string $eightselectAttr
     * @param string $object
     * @return bool
     */
    public function isAttributeSelected($eightselectAttr, $object)
    {
        if (isset($this->_attribute2Oxid[$eightselectAttr]) && in_array($object, $this->_attribute2Oxid[$eightselectAttr])) {
            return true;
        }

        return false;
    }
}
