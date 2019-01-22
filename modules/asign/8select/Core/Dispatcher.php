<?php

namespace ASign\EightSelect\Core;

/**
 * Class Dispatcher
 * @package ASign\EightSelect\Core
 */
class Dispatcher
{
    protected static $oInstance = null;
    protected $oRequest = null;
    protected $aCommands = [];

    /**
     * @return Dispatcher
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = new self;
        }

        return self::$oInstance;
    }

    /**
     * @param $request
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function dispatch($request)
    {
        $this->oRequest = $request;

        $sMethod = $this->oRequest->getArgument(\ASign\EightSelect\Core\Request::ARGUMENT_METHOD);
        $iShopId = (int)$this->oRequest->getArgument(\ASign\EightSelect\Core\Request::ARGUMENT_SHOP_ID);

        if (!\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne('SELECT 1 FROM oxshops WHERE OXID = ?', [$iShopId])) {
            throw new \UnexpectedValueException("Can't find ShopID {$iShopId}");
        }

        $oEightSelectExportDo = oxNew(\ASign\EightSelect\Controller\Admin\AdminExportDo::class);

        if (method_exists($oEightSelectExportDo, $sMethod)) {
            $oEightSelectExportDo->$sMethod($iShopId);
        } else {
            throw new \UnexpectedValueException('Command name not found');
        }

        return true;
    }
}
