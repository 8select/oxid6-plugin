<?php

namespace ASign\EightSelect\Component;

/**
 * Class MiniBasket
 * @package ASign\EightSelect\Component
 */
class MiniBasket extends MiniBasket_parent
{
    /**
     * Get basket items count
     */
    public function getBasketItemsCount()
    {
        $oSession = \OxidEsales\Eshop\Core\Registry::getSession();
        $oBasket = $oSession->getBasket();

        \OxidEsales\Eshop\Core\Registry::getUtils()->showMessageAndExit($oBasket->getItemsCount());
    }
}
