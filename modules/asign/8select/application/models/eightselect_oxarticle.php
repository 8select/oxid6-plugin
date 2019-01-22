<?php

/**
 * oxArticle class wrapper for 8Select module.
 */
class eightselect_oxarticle extends eightselect_oxarticle_parent
{
    /** @var array */
    private $_aEightSelectColorLabels = null;

    /** @var string */
    private $_sVirtualMasterSku = null;

    /**
     * Return virtual master SKU (with color value suffix if color-variant is selected)
     *
     * @return string
     * @throws oxConnectionException
     */
    public function getEightSelectVirtualSku()
    {
        if ($this->_sVirtualMasterSku !== null) {
            return $this->_sVirtualMasterSku;
        }

        $this->_sVirtualMasterSku = $this->oxarticles__oxartnum->value;

        $oView = $this->getConfig()->getTopActiveView();
        if ($oView instanceof Details) {
            $aVarSelections = $oView->getVariantSelections();

            if ($aVarSelections && $aVarSelections['blPerfectFit'] && $aVarSelections['oActiveVariant']) {
                $oVariant = $aVarSelections['oActiveVariant'];
                $this->_sVirtualMasterSku = $oVariant->oxarticles__oxartnum->value;
            } elseif (isset($aVarSelections['selections']) && count($aVarSelections['selections'])) {
                $aEightSelectColorLabels = $this->getEightSelectColorLabels();

                foreach ($aVarSelections['selections'] as $oVarSelectList) {
                    if (in_array($oVarSelectList->getLabel(), $aEightSelectColorLabels) && $oVarSelectList->getActiveSelection()) {
                        $oSelection = $oVarSelectList->getActiveSelection();
                        $sFieldValue = strtolower($oSelection->getName());
                        $this->_sVirtualMasterSku .= '-' . str_replace(' ', '', $sFieldValue);
                        break;
                    }
                }
            }
        }

        return $this->_sVirtualMasterSku;
    }

    /**
     * @return array
     * @throws oxConnectionException
     */
    private function getEightSelectColorLabels()
    {
        if ($this->_aEightSelectColorLabels === null) {
            $sSql = "SELECT OXOBJECT FROM eightselect_attribute2oxid WHERE ESATTRIBUTE = 'farbe'";
            $this->_aEightSelectColorLabels = (array) oxDb::getDb()->getCol($sSql);
        }

        return $this->_aEightSelectColorLabels;
    }
}
