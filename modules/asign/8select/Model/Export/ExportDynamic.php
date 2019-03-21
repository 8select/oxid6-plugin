<?php

namespace ASign\EightSelect\Model\Export;

use ASign\EightSelect\Model\Attribute2Oxid;
use OxidEsales\Eshop\Core\Model\ListModel;

/**
 * Class ExportDynamic
 * @package ASign\EightSelect\Model\Export
 */
class ExportDynamic extends ExportAbstract
{
    /** @var array */
    protected $_convertHtml = [
        'beschreibung'  => '_removeNewLines',
        'beschreibung1' => '_removeNewLinesAndHtml',
    ];

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'ExportDynamic';

    /**
     * Runs export
     */
    public function run()
    {
        /** @var ListModel $attribute2oxidList */
        $attribute2oxidList = oxNew(ListModel::class);
        $attribute2oxidList->init(Attribute2Oxid::class);

        $attribute2oxidList = $attribute2oxidList->getList();
        $attribute2oxidList = $attribute2oxidList->getArray();

        /** @var Attribute2Oxid $attribute2oxid */
        foreach ($attribute2oxidList as $attribute2oxid) {
            $attribute = $attribute2oxid->getFieldData('esattribute');

            if (array_key_exists($attribute, $this->_aCsvAttributes)) {
                $type = $attribute2oxid->getFieldData('oxtype');

                if ($type === 'oxarticlesfield') {
                    $this->_processArticlesField($attribute2oxid);
                } elseif ($type === 'oxartextendsfield') {
                    $this->_processArtExtendsField($attribute2oxid);
                } elseif ($type === 'oxattributeid') {
                    $this->_processAttribute($attribute2oxid);
                } elseif ($type === 'oxvarselect') {
                    $this->_processVarSelect($attribute2oxid);
                }
            }
        }
    }

    /**
     * Processes article field
     *
     * @param Attribute2Oxid $attribute2oxid
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _processArticlesField(Attribute2Oxid $attribute2oxid)
    {
        $table = getViewName('oxarticles');
        $articleField = $attribute2oxid->getFieldData('oxobject');

        $query = "SELECT {$articleField} FROM {$table} WHERE OXID = ?";
        $value = (string) \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($query, [$this->_oArticle->getId()]);

        $name = $attribute2oxid->getFieldData('esattribute');
        $this->_aCsvAttributes[$name] = $this->_convertHtml($value, $name);
    }

    /**
     * Processes article extended field
     *
     * @param Attribute2Oxid $attribute2oxid
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _processArtExtendsField(Attribute2Oxid $attribute2oxid)
    {
        $table = getViewName('oxartextends');
        $artExtendsField = $attribute2oxid->getFieldData('oxobject');

        $query = "SELECT {$artExtendsField} FROM {$table} WHERE OXID = ?";
        $value = (string) \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($query, [$this->_oArticle->getId()]);

        $name = $attribute2oxid->getFieldData('esattribute');
        $this->_aCsvAttributes[$name] = $this->_convertHtml($value, $name);
    }

    /**
     * Processes attribute field
     *
     * @param Attribute2Oxid $attribute2oxid
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function _processAttribute(Attribute2Oxid $attribute2oxid)
    {
        $attributeTable = getViewName('oxattribute');
        $object2AttributeTable = getViewName('oxobject2attribute');
        $attributeId = $attribute2oxid->getFieldData('oxobject');

        $query = "SELECT o2a.OXVALUE
                  FROM {$attributeTable} AS oxattribute
                  JOIN {$object2AttributeTable} AS o2a ON oxattribute.OXID = o2a.OXATTRID
                  WHERE oxattribute.OXID = ?
                    AND o2a.OXOBJECTID = ?";
        $value = (string) \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($query, [$attributeId, $this->_oArticle->getId()]);

        $name = $attribute2oxid->getFieldData('esattribute');
        $this->_aCsvAttributes[$name] = $this->_convertHtml($value, $name);
    }

    /**
     * Processes varselect
     *
     * @param Attribute2Oxid $attribute2oxid
     */
    protected function _processVarSelect(Attribute2Oxid $attribute2oxid)
    {
        $attribute = $attribute2oxid->getFieldData('esattribute');
        $this->_aCsvAttributes[$attribute] = $this->getVariantSelection($attribute);
    }

    /**
     * Convert HTML content
     *
     * @param string $value
     * @param string $attributeName
     * @return string $sValue
     */
    protected function _convertHtml($value, $attributeName)
    {
        if (empty($value)) {
            return $value;
        }

        if (array_key_exists($attributeName, $this->_convertHtml) && method_exists($this, $function = $this->_convertHtml[$attributeName])) {
            $value = $this->$function($value);
        }

        return $value;
    }

    /**
     * Removes newlines from given text
     *
     * @param string $value
     * @return string
     */
    protected function _removeNewLines($value)
    {
        return str_replace(["\r\n", "\r", "\n"], ' ', $value);
    }

    /**
     * Removes newlines and HTML from given text
     *
     * @param string $value
     * @return string
     */
    protected function _removeNewLinesAndHtml($value)
    {
        $withOutNewLines = str_replace(["\r\n", "\r", "\n"], '<br>', $value);
        $withExtraSpaces = str_replace(">", '> ', $withOutNewLines);
        $withOutHtml = strip_tags($withExtraSpaces);
        $withOutHtmlEntities = html_entity_decode($withOutHtml);

        return trim(preg_replace('/[\h\xa0\xc2]+/', ' ', $withOutHtmlEntities));
    }
}
