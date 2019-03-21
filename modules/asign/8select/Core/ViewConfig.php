<?php

namespace ASign\EightSelect\Core;

use OxidEsales\Eshop\Core\Registry;
use \OxidEsales\EshopCommunity\Core\Request;

/**
 * Class ViewConfig
 * @package ASign\EightSelect\Core
 */
class ViewConfig extends ViewConfig_parent
{
    protected $_is8SelectActive = null;

    /**
     * @return bool
     */
    public function isEightSelectActive()
    {
        if ($this->_is8SelectActive !== null) {
            return $this->_is8SelectActive;
        }

        $this->_is8SelectActive = (bool) $this->getConfig()->getConfigParam('blEightSelectActive');

        if (!$this->getEightSelectApiId()) {
            $this->_is8SelectActive = false;
        }

        return $this->_is8SelectActive;
    }

    /**
     * @return string
     */
    public function getEightSelectApiId()
    {
        return $this->getConfig()->getConfigParam('sEightSelectApiId');
    }

    /**
     * @param string $widgetType
     * @return bool
     */
    public function showEightSelectWidget($widgetType)
    {
        if ($this->getConfig()->getConfigParam('blEightSelectPreview') && !Registry::get(Request::class)->getRequestEscapedParameter('8s_preview')) {
            return false;
        }

        $widgetType = ucwords($widgetType, '-');
        $widgetType = str_replace('-', '', $widgetType);

        return (bool) $this->getConfig()->getConfigParam('blEightSelectWidget' . $widgetType);
    }
}
