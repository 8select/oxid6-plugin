<?php

namespace ASign\EightSelect\Core;

use ASign\EightSelect\Controller\Admin\AdminExportDo;
use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Class Dispatcher
 * @package ASign\EightSelect\Core
 */
class Dispatcher
{
    protected $request = null;

    /**
     * @param Request $request
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \UnexpectedValueException
     */
    public function dispatch($request)
    {
        $this->request = $request;

        $method = $this->request->getArgument(Request::ARGUMENT_METHOD);
        $shopId = (int) $this->request->getArgument(Request::ARGUMENT_SHOP_ID);

        if (!DatabaseProvider::getDb()->getOne('SELECT 1 FROM oxshops WHERE OXID = ?', [$shopId])) {
            throw new \UnexpectedValueException("Can't find ShopID {$shopId}");
        }

        $exportDo = oxNew(AdminExportDo::class);

        if (method_exists($exportDo, $method)) {
            $exportDo->$method($shopId);
        } else {
            throw new \UnexpectedValueException('Command name not found');
        }

        return true;
    }
}
