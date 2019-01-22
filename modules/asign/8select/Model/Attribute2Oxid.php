<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Class Attribute2Oxid
 * @package ASign\EightSelect\Model
 */
class Attribute2Oxid extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'Attribute2Oxid';

    /**
     * Core database table name. $sCoreTable could be only original data table name and not view name.
     *
     * @var string
     */
    protected $_sCoreTable = 'eightselect_attribute2oxid';

    /**
     * Delete attributes to oxid.
     *
     * @param $s8selectAttributeName
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function deleteAttributes2Oxid($s8selectAttributeName)
    {
        $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $sCoreTable = $this->getCoreTableName();

        $sDelete = "DELETE FROM {$sCoreTable} WHERE OXSHOPID = ? AND ESATTRIBUTE = ?";

        return (bool) $oDB->execute($sDelete, [$this->getShopId(), $s8selectAttributeName]);
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

            $this->eightselect_attribute2oxid__oxtype = new Field($sType);
            $this->eightselect_attribute2oxid__oxobject = new Field($sObject);
        }

        $this->eightselect_attribute2oxid__esattribute = new Field($s8selectAttributeName);
    }
}
