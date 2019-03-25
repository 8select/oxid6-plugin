<?php

namespace ASign\EightSelect\Model\Export;

use ASign\EightSelect\Model\Attribute2Oxid;
use ASign\EightSelect\Model\Export;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 * Class ExportAbstract
 * @package ASign\EightSelect\Model\Export
 */
abstract class ExportAbstract extends BaseModel
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'ExportAbstract';

    /**
     * @var Article
     */
    protected $_oArticle = null;

    /**
     * @var Article
     */
    protected $_oParent = null;

    /**
     * @var Export
     */
    protected $_oParentExport = null;

    /**
     * @var array
     */
    protected $_aCsvAttributes = [];

    /**
     * @param array $csvAttributes
     */
    public function setAttributes(array &$csvAttributes)
    {
        $this->_aCsvAttributes = &$csvAttributes;
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article &$article)
    {
        $this->_oArticle = $article;
    }

    /**
     * @param Article $parent
     */
    public function setParent(&$parent)
    {
        $this->_oParent = $parent;
    }

    /**
     * @param Export $parentExport
     */
    public function setParentExport(&$parentExport)
    {
        $this->_oParentExport = $parentExport;
    }

    /**
     * Set data to fields
     */
    abstract public function run();

    /**
     * @param string $attributeName
     * @return string
     */
    public function getVariantSelection($attributeName)
    {
        if ($this->_oParent === null) {
            return '';
        }

        $table = Registry::get(TableViewNameGenerator::class)->getViewName('eightselect_attribute2oxid');

        $list = oxNew(ListModel::class);
        $list->init(Attribute2Oxid::class);
        $list->selectString("SELECT OXOBJECT FROM {$table} WHERE ESATTRIBUTE = '{$attributeName}'");

        /** @var Attribute2Oxid $attr2Oxid */
        foreach ($list->getArray() as $attr2Oxid) {
            $selection = $attr2Oxid->getFieldData('oxobject');

            if (strpos($this->_oParent->getFieldData('oxvarname'), $selection) !== false) {
                $selectionNames = explode(' | ', $this->_oParent->getFieldData('oxvarname'));
                $selectionNames = array_map('trim', $selectionNames);
                $selectionValues = explode(' | ', $this->_oArticle->getFieldData('oxvarselect'));
                $selectionValues = array_map('trim', $selectionValues);

                $sizePos = array_search($selection, $selectionNames);
                if ($sizePos !== false && isset($selectionValues[$sizePos])) {
                    return $selectionValues[$sizePos];
                }
            }
        }

        return '';
    }
}
