<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Core\Field;

/**
 * Class Log
 * @package ASign\EightSelect\Model
 */
class Log extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    /** @var string */
    public static $ACTION_EXPORT_FULL = 'Export Full';

    /** @var string */
    public static $ACTION_EXPORT_UPD = 'Export Update';

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'Log';

    /**
     * Core database table name. $sCoreTable could be only original data table name and not view name.
     *
     * @var string
     */
    protected $_sCoreTable = 'eightselect_log';

    /**
     * @var bool
     */
    protected $_blFullExport = null;

    /**
     * @param $sAction
     * @param $sMessage
     * @throws \Exception
     */
    public function addLog($sAction, $sMessage)
    {
        $this->init();
        $this->_setAction($sAction);
        $this->_setMessage($sMessage);
        $this->_setDate();
        $this->save();
    }

    /**
     * @param $blFull
     * @throws \Exception
     */
    public function startExport($blFull)
    {
        $this->init();
        $this->_blFullExport = (bool)$blFull;
        $this->_setAction($blFull ? self::$ACTION_EXPORT_FULL : self::$ACTION_EXPORT_UPD);
        $this->_setMessage('Start export');
        $this->save();
    }

    /**
     * @throws \Exception
     */
    public function successExport()
    {
        $this->_setMessage('Export successfully');
        $this->_setDate();
        $this->save();
    }

    /**
     * @param $sMessage
     * @throws \Exception
     */
    public function errorExport($sMessage)
    {
        $this->_setMessage("Export error\n{$sMessage}");
        $this->save();
    }

    /**
     * @param bool $blFull
     * @return mixed
     */
    public function getLastSuccessExportDate($blFull)
    {
        $iShopId = $this->getConfig()->getShopId();
        $sVarName = 'sExportDate' . ($blFull ? 'Full' : 'Update');

        return $this->getConfig()->getShopConfVar($sVarName, $iShopId, 'module:asign_8select');
    }

    /**
     * @param bool $blFull
     * @param string $sDateTime
     */
    public function setLastSuccessExportDate($blFull, $sDateTime = null)
    {
        if ($sDateTime === null) {
            $sDateTime = date('Y-m-d H:i:s');
        }

        $iShopId = $this->getConfig()->getShopId();
        $sVarName = 'sExportDate' . ($blFull ? 'Full' : 'Update');

        $this->getConfig()->saveShopConfVar('str', $sVarName, $sDateTime, $iShopId, 'module:asign_8select');
    }

    /**
     * Set an action name
     *
     * @param string $sActionName
     */
    private function _setAction($sActionName)
    {
        $this->eightselect_log__eightselect_action = new Field($sActionName);
    }

    /**
     * Set a message
     *
     * @param string $sMessage
     */
    private function _setMessage($sMessage)
    {
        $this->eightselect_log__eightselect_message = new Field($sMessage);
    }

    /**
     * Set to current datetime
     */
    private function _setDate()
    {
        $this->eightselect_log__eightselect_date = new Field(date('Y-m-d H:i:s'));
    }
}
