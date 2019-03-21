<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\BaseModel;

/**
 * Class Attribute2Oxid
 * @package ASign\EightSelect\Model
 */
class Attribute2Oxid extends BaseModel
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
     * @param string $attributeName
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function deleteAttributes2Oxid($attributeName)
    {
        $db = DatabaseProvider::getDb();
        $coreTable = $this->getCoreTableName();

        $delete = "DELETE FROM {$coreTable} WHERE OXSHOPID = ? AND ESATTRIBUTE = ?";

        return (bool) $db->execute($delete, [$this->getShopId(), $attributeName]);
    }

    /**
     * @param string $attributeName
     * @param string $oxidAttribute
     */
    public function setAttributeData($attributeName, $oxidAttribute)
    {
        $oxidParams = explode(';', $oxidAttribute);

        if (count($oxidParams) === 2) {
            $this->assign([
                'oxtype'   => $oxidParams[0],
                'oxobject' => $oxidParams[1],
            ]);
        }

        $this->assign(['esattribute' => $attributeName]);
    }
}
