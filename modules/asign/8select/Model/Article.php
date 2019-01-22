<?php

namespace ASign\EightSelect\Model;

/**
 * Class Article
 * @package ASign\EightSelect\Model
 */
class Article extends Article_parent
{
    /** @var array */
    private $_aEightSelectColorLabels = null;

    /** @var string */
    private $_sVirtualMasterSku = null;

    /**
     * Get EightSelect virtual sku
     *
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function getEightSelectVirtualSku()
    {
        if ($this->_sVirtualMasterSku !== null) {
            return $this->_sVirtualMasterSku;
        }

        $this->_sVirtualMasterSku = $this->oxarticles__oxartnum->value;

        $oView = $this->getConfig()->getTopActiveView();
        if ($oView instanceof \OxidEsales\Eshop\Application\Controller\ArticleDetailsController) {
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
     * Get EightSelect color labels
     *
     * @return array|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    private function getEightSelectColorLabels()
    {
        if ($this->_aEightSelectColorLabels === null) {
            $sSql = "SELECT OXOBJECT FROM eightselect_attribute2oxid WHERE ESATTRIBUTE = 'farbe'";
            $this->_aEightSelectColorLabels = (array) \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getCol($sSql);
        }

        return $this->_aEightSelectColorLabels;
    }
}
