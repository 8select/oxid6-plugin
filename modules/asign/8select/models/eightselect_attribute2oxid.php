<?php

/**
 * Attributes manager
 *
 */
class eightselect_attribute2oxid extends oxBase
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'eightselect_attribute2oxid';

    /**
     * Core database table name. $sCoreTable could be only original data table name and not view name.
     *
     * @var string
     */
    protected $_sCoreTable = 'eightselect_attribute2oxid';

    public function deleteAttributes2Oxid($s8selectAttributeName)
    {
        $oDB = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sCoreTable = $this->getCoreTableName();
        $sDelete = "DELETE FROM {$sCoreTable} WHERE OXSHOPID = ? AND ESATTRIBUTE = ?";
        $oDB->execute($sDelete, [$this->getShopId(), $s8selectAttributeName]);

        return (bool) $oDB->affected_Rows();
    }

    /**
     * @param string $s8selectAttributeName
     * @param string $sOxidAttribute
     */
    public function setAttributeData($s8selectAttributeName, $sOxidAttribute)
    {
        $aOxidParams = explode(';', $sOxidAttribute);

        if (count($aOxidParams) === 2) {
            $sType = $aOxidParams[0];
            $sObject = $aOxidParams[1];

            $this->eightselect_attribute2oxid__oxtype = new oxField($sType);
            $this->eightselect_attribute2oxid__oxobject = new oxField($sObject);
        }

        $this->eightselect_attribute2oxid__esattribute = new oxField($s8selectAttributeName);
    }
}