<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Application\Controller\ArticleDetailsController;
use OxidEsales\Eshop\Application\Model\SelectList;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Selection;

/**
 * Class Article
 * @package ASign\EightSelect\Model
 */
class Article extends Article_parent
{
    /** @var array */
    protected $_colorLabels = null;

    /** @var string */
    protected $_virtualMasterSku = null;

    /**
     * Get EightSelect virtual sku
     *
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function getEightSelectVirtualSku()
    {
        if ($this->_virtualMasterSku !== null) {
            return $this->_virtualMasterSku;
        }

        $this->_virtualMasterSku = $this->getFieldData('oxartnum');

        $view = $this->getConfig()->getTopActiveView();
        if ($view instanceof ArticleDetailsController) {
            $varSelections = $view->getVariantSelections();

            if ($varSelections && $varSelections['blPerfectFit'] && $varSelections['oActiveVariant']) {
                $variant = $varSelections['oActiveVariant'];
                $this->_virtualMasterSku = $variant->oxarticles__oxartnum->value;
            } elseif (isset($varSelections['selections']) && count($varSelections['selections'])) {
                $colorLabels = $this->getEightSelectColorLabels();

                /** @var SelectList $varSelectList */
                foreach ($varSelections['selections'] as $varSelectList) {
                    if (in_array($varSelectList->getLabel(), $colorLabels) && $varSelectList->getActiveSelection()) {
                        /** @var Selection $selection */
                        $selection = $varSelectList->getActiveSelection();
                        $fieldValue = strtolower($selection->getName());
                        $this->_virtualMasterSku .= '-' . str_replace(' ', '', $fieldValue);
                        break;
                    }
                }
            }
        }

        return $this->_virtualMasterSku;
    }

    /**
     * Get EightSelect color labels
     *
     * @return array|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function getEightSelectColorLabels()
    {
        if ($this->_colorLabels === null) {
            $colorField = Registry::getConfig()->getConfigParam('SHOP_MODULE_sArticleColorField');
            list(, $colorLabel) = explode(';', $colorField);

            $this->_colorLabels = [$colorLabel];
        }

        return $this->_colorLabels;
    }
}
