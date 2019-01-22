<?php

class eightselect_oxwminibasket extends eightselect_oxwminibasket_parent
{

    public function getBasketItemsCount()
    {
        $oSession = oxRegistry::getSession();
        $oBasket = $oSession->getBasket();

        oxRegistry::getUtils()->showMessageAndExit($oBasket->getItemsCount());
    }

}