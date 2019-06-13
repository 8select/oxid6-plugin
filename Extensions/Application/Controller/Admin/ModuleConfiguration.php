<?php

namespace ASign\EightSelect\Extensions\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\SeoEncoder;

/**
 */
class ModuleConfiguration extends ModuleConfiguration_parent
{
    protected $_8selectUrl = 'https://sc-prod.staging.8select.io/';

    /**
     * connectToCSE
     * -----------------------------------------------------------------------------------------------------------------
     * Tries to connect to shop to CSE
     */
    public function connectToCSE()
    {
        $moduleId = $this->getEditObjectId();
        $lang = Registry::getLang();
        $module = oxNew(Module::class);

        if ($moduleId === 'asign_8select' && $module->load($moduleId)) {
            // Check if config is complete: don't register API if not
            if (!($apiId = $this->getConfig()->getConfigParam('sEightSelectApiId'))
                || !($feedId = $this->getConfig()->getConfigParam('sEightSelectFeedId'))
            ) {
                $this->_aViewData['_8select_connectError'] = $lang->translateString('mx_eightselect_connection_missing_config');

                return;
            }

            $baseUrl = $this->getConfig()->getShopUrl(0, false) . 'index.php?cl=EightSelectAPI&amp;';
            $seoEncoder = oxNew(SeoEncoder::class);

            $data = [
                'api'    => [
                    'attributes'        => $seoEncoder->getStaticUrl($baseUrl . 'fnc=renderAttributes', 0),
                    'products'          => $seoEncoder->getStaticUrl($baseUrl . 'fnc=render', 0),
                    'variantDimensions' => $seoEncoder->getStaticUrl($baseUrl . 'fnc=renderVariantDimensions', 0),
                ],
                'plugin' => ['version' => $module->getInfo('version')],
                'shop'   => [
                    'software' => 'OXID-' . $this->getShopEdition(),
                    'url'      => $this->getConfig()->getShopUrl(),
                    'version'  => $this->getShopVersion(),
                ],
            ];

            $curl = curl_init($this->_8selectUrl . "shops/$apiId/$feedId");
            curl_setopt_array($curl, [
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json',
                                           "8select-com-fid: $feedId",
                                           "8select-com-tid: $apiId",],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => json_encode($data),
                CURLOPT_CUSTOMREQUEST  => 'PUT',
            ]);
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            if ($info['http_code'] === 200) {
                $this->_aViewData['_8select_connectSuccess'] = $lang->translateString('mx_eightselect_connection_success');
            } else {
                $this->_aViewData['_8select_connectError'] = $lang->translateString('mx_eightselect_connection_curl_error') . $response;
            }
        }
    }

    /**
     * getEightSelectSkuFields
     * -----------------------------------------------------------------------------------------------------------------
     * Returns all possible SKU fields
     *
     * @return array
     */
    public function getEightSelectSkuFields()
    {
        $lang = Registry::getLang();

        return [
            'OXID'      => $lang->translateString('GENERAL_ARTICLE_OXID'),
            'OXARTNUM'  => $lang->translateString('GENERAL_ARTICLE_OXARTNUM'),
            'OXEAN'     => $lang->translateString('GENERAL_ARTICLE_OXEAN'),
            'OXMPN'     => $lang->translateString('GENERAL_ARTICLE_OXMPN'),
            'OXDISTEAN' => $lang->translateString('GENERAL_ARTICLE_OXDISTEAN'),
        ];
    }
}
