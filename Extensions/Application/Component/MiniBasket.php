<?php

namespace ASign\EightSelect\Extensions\Application\Component;

use OxidEsales\Eshop\Core\Registry;

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
        $basket = Registry::getSession()->getBasket();

        Registry::getUtils()->showMessageAndExit($basket->getItemsCount());
    }
}
