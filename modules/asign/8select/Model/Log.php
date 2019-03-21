<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

/**
 * Class Log
 * @package ASign\EightSelect\Model
 */
class Log extends BaseModel
{
    /** @var string */
    const ACTION_EXPORT_FULL = 'Export Full';

    /** @var string */
    const ACTION_EXPORT_UPD = 'Export Update';

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
     * @param string $action
     * @param string $message
     * @throws \Exception
     */
    public function addLog($action, $message)
    {
        $this->init();
        $this->_setAction($action);
        $this->_setMessage($message);
        $this->_setDate();
        $this->save();
    }

    /**
     * @param bool $full
     * @throws \Exception
     */
    public function startExport($full)
    {
        $this->init();
        $this->_blFullExport = (bool) $full;
        $this->_setAction($full ? self::ACTION_EXPORT_FULL : self::ACTION_EXPORT_UPD);
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
     * @param string $message
     * @throws \Exception
     */
    public function errorExport($message)
    {
        $this->_setMessage("Export error\n{$message}");
        $this->save();
    }

    /**
     * @param bool $full
     * @return mixed
     */
    public function getLastSuccessExportDate($full)
    {
        $shopId = $this->getConfig()->getShopId();
        $varName = 'sExportDate' . ($full ? 'Full' : 'Update');

        return $this->getConfig()->getShopConfVar($varName, $shopId, 'module:asign_8select');
    }

    /**
     * @param bool   $full
     * @param string $dateTime
     */
    public function setLastSuccessExportDate($full, $dateTime = null)
    {
        if ($dateTime === null) {
            $dateTime = date('Y-m-d H:i:s');
        }

        $shopId = $this->getConfig()->getShopId();
        $varName = 'sExportDate' . ($full ? 'Full' : 'Update');

        $this->getConfig()->saveShopConfVar('str', $varName, $dateTime, $shopId, 'module:asign_8select');
    }

    /**
     * Set an action name
     *
     * @param string $actionName
     */
    protected function _setAction($actionName)
    {
        $this->assign(['eightselect_action' => $actionName]);
    }

    /**
     * Set a message
     *
     * @param string $message
     */
    protected function _setMessage($message)
    {
        $this->assign(['eightselect_message' => $message]);
    }

    /**
     * Set to current datetime
     */
    protected function _setDate()
    {
        $this->assign(['eightselect_date' => date('Y-m-d H:i:s')]);
    }
}
