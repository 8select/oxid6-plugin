<?php

/**
 * oxViewConfig class wrapper for 8Select module.
 */
class eightselect_oxviewconfig extends eightselect_oxviewconfig_parent
{
    private $_blEightSelectActive = null;

    /**
     * @return bool
     */
    public function isEightSelectActive()
    {
        if ($this->_blEightSelectActive !== null) {
            return $this->_blEightSelectActive;
        }

        $this->_blEightSelectActive = (bool)$this->getConfig()->getConfigParam('blEightSelectActive');

        if (!$this->getEightSelectApiId()) {
            $this->_blEightSelectActive = false;
        }

        return $this->_blEightSelectActive;
    }

    /**
     * @return mixed
     */
    public function getEightSelectApiId()
    {
        return $this->getConfig()->getConfigParam('sEightSelectApiId');
    }

    /**
     * @param string $sWidgetType
     * @return bool
     */
    public function showEightSelectWidget($sWidgetType)
    {
        if ($this->getConfig()->getConfigParam('blEightSelectPreview') && !$this->getConfig()->getRequestParameter("8s_preview")) {
            return false;
        }

        $sWidgetType = ucwords($sWidgetType, "-");
        $sWidgetType = str_replace('-', '', $sWidgetType);
        return (bool) $this->getConfig()->getConfigParam('blEightSelectWidget'.$sWidgetType);
    }
}
