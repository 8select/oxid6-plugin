<?php

/**
 * 8select export
 *
 */
class eightselect_export_dynamic extends eightselect_export_abstract
{
    private $_aConvertHtml = [
        'beschreibung'  => '_removeNewLines',
        'beschreibung1' => '_removeNewLinesAndHtml',
    ];

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'eightselect_export_dynamic';

    /**
     * Set dynamic fields (configurable ones)
     */
    public function run()
    {
        /** @var oxList $oEightSelectAttr2oxidList */
        $oEightSelectAttr2oxidList = oxNew('oxList');
        $oEightSelectAttr2oxidList->init('eightselect_attribute2oxid');

        /** @var oxList $oAttr2oxidList */
        $oAttr2oxidList = $oEightSelectAttr2oxidList->getList();
        $aAttr2oxidList = $oAttr2oxidList->getArray();

        /** @var eightselect_attribute2oxid $oAttr2oxid */
        foreach ($aAttr2oxidList as $oAttr2oxid) {
            $sEightSelectAttribute = $oAttr2oxid->eightselect_attribute2oxid__esattribute->value;
            if (array_key_exists($sEightSelectAttribute, $this->_aCsvAttributes)) {
                $sType = $oAttr2oxid->eightselect_attribute2oxid__oxtype->value;
                if ($sType === 'oxarticlesfield') {
                    $this->_processArticlesField($oAttr2oxid);
                } elseif ($sType === 'oxartextendsfield') {
                    $this->_processArtExtendsField($oAttr2oxid);
                } elseif ($sType === 'oxattributeid') {
                    $this->_processAttribute($oAttr2oxid);
                } elseif ($sType === 'oxvarselect') {
                    $this->_processVarSelect($oAttr2oxid);
                }
            }
        }
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     */
    private function _processArticlesField(eightselect_attribute2oxid $oAttr2oxid)
    {
        $sTable = getViewName('oxarticles');
        $sArticleField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT {$sArticleField} FROM {$sTable} WHERE OXID = ?";
        $sValue = (string)oxDb::getDb()->getOne($sSql, [$this->_oArticle->getId()]);

        $sValue = $this->_convertHtml($sValue, $oAttr2oxid->eightselect_attribute2oxid__esattribute->value);
        $this->_aCsvAttributes[$oAttr2oxid->eightselect_attribute2oxid__esattribute->value] = $sValue;
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     */
    private function _processArtExtendsField(eightselect_attribute2oxid $oAttr2oxid)
    {
        $sTable = getViewName('oxartextends');
        $sArtExtendsField = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT {$sArtExtendsField} FROM {$sTable} WHERE OXID = ?";
        $sValue = (string)oxDb::getDb()->getOne($sSql, [$this->_oArticle->getId()]);

        $sValue = $this->_convertHtml($sValue, $oAttr2oxid->eightselect_attribute2oxid__esattribute->value);
        $this->_aCsvAttributes[$oAttr2oxid->eightselect_attribute2oxid__esattribute->value] = $sValue;
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     */
    private function _processAttribute(eightselect_attribute2oxid $oAttr2oxid)
    {
        $sAttributeTable = getViewName('oxattribute');
        $sO2ATable = getViewName('oxobject2attribute');
        $sAttributeId = $oAttr2oxid->eightselect_attribute2oxid__oxobject->value;

        $sSql = "SELECT o2a.OXVALUE
                  FROM {$sAttributeTable} AS oxattribute
                  JOIN {$sO2ATable} AS o2a ON oxattribute.OXID = o2a.OXATTRID
                  WHERE oxattribute.OXID = ?
                    AND o2a.OXOBJECTID = ?";
        $sValue = (string)oxDb::getDb()->getOne($sSql, [$sAttributeId, $this->_oArticle->getId()]);

        $sValue = $this->_convertHtml($sValue, $oAttr2oxid->eightselect_attribute2oxid__esattribute->value);
        $this->_aCsvAttributes[$oAttr2oxid->eightselect_attribute2oxid__esattribute->value] = $sValue;
    }

    /**
     * @param eightselect_attribute2oxid $oAttr2oxid
     */
    private function _processVarSelect(eightselect_attribute2oxid $oAttr2oxid)
    {
        $sEightSelectAttribute = $oAttr2oxid->eightselect_attribute2oxid__esattribute->value;
        $this->_aCsvAttributes[$sEightSelectAttribute] = $this->getVariantSelection($sEightSelectAttribute);
    }

    /**
     * Convert HTML content
     *
     * @param string $sValue
     * @param string $sEightSelectAttributeName
     * @return string $sValue
     */
    private function _convertHtml($sValue, $sEightSelectAttributeName)
    {
        if (empty($sValue)) {
            return $sValue;
        }

        if (array_key_exists($sEightSelectAttributeName, $this->_aConvertHtml) && method_exists($this, $sFunc = $this->_aConvertHtml[$sEightSelectAttributeName])) {
            $sValue = $this->$sFunc($sValue);
        }

        return $sValue;
    }

    /**
     * Remove newlines
     *
     * @param string $sValue
     * @return string
     */
    private function _removeNewLines($sValue)
    {
        return str_replace(["\r\n", "\r", "\n"], ' ', $sValue);
    }

    /**
     * Remove newlines and HTML tags
     *
     * @param string $sValue
     * @return string
     */
    private function _removeNewLinesAndHtml($sValue)
    {
        $sWithOutNewLines = str_replace(["\r\n", "\r", "\n"], '<br>', $sValue);
        $sWithExtraSpaces = str_replace(">", '> ', $sWithOutNewLines);
        $sWithOutHtml = strip_tags($sWithExtraSpaces);
        $sWithOutHtmlEntities = html_entity_decode($sWithOutHtml);
        return trim(preg_replace('/[\h\xa0\xc2]+/', ' ', $sWithOutHtmlEntities));
    }
}