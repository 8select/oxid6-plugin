<?php

namespace ASign\EightSelect\Controller\Admin;

/**
 * Class AdminExportDo
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminExportDo extends \OxidEsales\Eshop\Application\Controller\Admin\DynamicExportBaseController
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = 'AdminExportDo';

    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = 'AdminExportMain';

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'eightselect_admin_export_do.tpl';

    /**
     * @var array
     */
    private $_aParent = [];

    /**
     * Prepares export
     */
    public function start()
    {
        $this->_aViewData['refresh'] = 0;
        $this->_aViewData['iStart'] = 0;

        // prepare it
        $iEnd = $this->prepareExport();

        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable("iEnd", $iEnd);
        $this->_aViewData['iEnd'] = $iEnd;

        $sType = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("do_full") ? 'do_full' : 'do_update';
        $this->_aViewData['sType'] = $sType;
    }

    /**
     * Does export
     */
    public function run()
    {
        $blFull = (bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('do_full');

        /** @var EightSelectExport $oEightSelectExport */
        $oEightSelectExport = oxNew(\ASign\EightSelect\Model\Export::class);

        /** @var eightselect_log $oEightSelectLog */
        $oEightSelectLog = oxNew(\ASign\EightSelect\Model\Log::class);
        $oEightSelectLog->startExport($blFull);

        $mDateTime = $oEightSelectLog->getLastSuccessExportDate($blFull);
        $oEightSelectLog->setLastSuccessExportDate($blFull);

        try {
            $this->sExportFileName = $oEightSelectExport->getExportFileName($blFull);
            $this->_sFilePath = $this->getConfig()->getConfigParam('sShopDir') . "/" . $this->sExportPath . $this->sExportFileName;

            parent::run();

            $oEightSelectLog->successExport();
        } catch (\Throwable $oEx) {
            $this->stop(\ASign\EightSelect\Model\Export::$err_nofeedid);
            $oEightSelectLog->errorExport($oEx->getMessage());
            $oEightSelectLog->setLastSuccessExportDate($blFull, $mDateTime);
        }
    }

    /**
     * @param int $iCnt
     * @return bool|int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function nextTick($iCnt)
    {
        $iExportedItems = $iCnt;
        $blContinue = false;

        static $oEightSelectTmpExport = null;

        if ($oEightSelectTmpExport === null) {
            $oEightSelectTmpExport = oxNew(\ASign\EightSelect\Model\Export::class);
        }

        if ($oArticle = $this->getOneArticle($iCnt, $blContinue)) {

            $sParentId = $oArticle->oxarticles__oxparentid->value;

            // set parent article (performance loading)
            if ($oArticle->isVariant() && !isset($this->_aParent[$sParentId])) {
                // clear parent from other variant
                $this->_aParent = [];
                $oParent = $oArticle->getParentArticle();
                $this->_aParent[$sParentId]['article_parent'] = $oParent;

                $oEightSelectParentExport = clone $oEightSelectTmpExport;
                $oEightSelectParentExport->setArticle($oParent);
                $oEightSelectParentExport->initData();
                $this->_aParent[$sParentId]['export_parent'] = $oEightSelectParentExport;
            }

            $oEightSelectExport = clone $oEightSelectTmpExport;
            $oEightSelectExport->setArticle($oArticle);

            if ($oArticle->isVariant()) {
                $oEightSelectExport->setParent($this->_aParent[$oArticle->oxarticles__oxparentid->value]['article_parent']);
                $oEightSelectExport->setParentExport($this->_aParent[$oArticle->oxarticles__oxparentid->value]['export_parent']);
            }

            // set header if it's the first article
            if ((int)$iCnt === 0) {
                fwrite($this->fpFile, $oEightSelectExport->getCsvHeader());
            }

            // write variant to CSV
            fwrite($this->fpFile, $oEightSelectExport->getCsvLine());

            return ++$iExportedItems;
        }

        return $blContinue;
    }

    /**
     * Inserts articles into heaptable
     *
     * @param string $sHeapTable
     * @param string $sCatAdd
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function _insertArticles($sHeapTable, $sCatAdd)
    {
        $oDB = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        $iExpLang = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("iExportLanguage");

        if (!isset($iExpLang)) {
            $iExpLang = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("iExportLanguage");
        }

        $oArticle = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
        $oArticle->setLanguage($iExpLang);

        $sArticleTable = getViewName("oxarticles", $iExpLang);

        $sSelect = "INSERT INTO {$sHeapTable} ";
        $sSelect .= "SELECT oxarticles.OXID FROM {$sArticleTable} as oxarticles ";
        $sSelect .= "LEFT JOIN {$sArticleTable} AS mainart ON mainart.OXID = oxarticles.OXPARENTID ";
        $sSelect .= "WHERE (oxarticles.OXPARENTID != '' OR (oxarticles.OXPARENTID = '' AND oxarticles.OXVARCOUNT = 0)) ";

        if ($sCatAdd) {
            $sSelect .= $sCatAdd;
        }

        $blFull = (bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('do_full');

        if ($blFull) {
            $sSelect .= "AND " . $oArticle->getSqlActiveSnippet(true) . " ";
        } else {
            $oEightSelectLog = oxNew(\ASign\EightSelect\Model\Log::class);
            $mDateTime = $oEightSelectLog->getLastSuccessExportDate($blFull);

            if ($mDateTime) {
                $sSelect .= "AND (oxarticles.OXTIMESTAMP >= " . $oDB->quote($mDateTime) . " OR mainart.OXTIMESTAMP >= " . $oDB->quote($mDateTime) . ") ";
            }
        }

        $sSelect .= "GROUP BY oxarticles.OXID ORDER BY oxarticles.OXARTNUM ASC";

        return $oDB->execute($sSelect) ? true : false;
    }

    /**
     * @param string $sHeapTable
     */
    protected function _removeParentArticles($sHeapTable)
    {
        /* we don't have parent articles in heap-table, so we can skip that */
    }

    /**
     * @param $iShopId
     * @param $sType
     * @throws \oxConnectionException
     */
    private function _exportCron($iShopId, $sType)
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $oConfig->setShopId($iShopId);
        $oConfig->init();
        $this->setConfig($oConfig);

        $_GET[$sType] = true;
        $_GET['iStart'] = 0;
        $_GET['refresh'] = 0;

        $this->_iExportPerTick = 1000000;

        $this->start();
        $this->run();
        $this->stop();
    }

    /**
     * @param $iShopId
     * @param $sType
     * @throws \oxConnectionException
     */
    private function _uploadCron($iShopId, $sType)
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $oConfig->setShopId($iShopId);
        $oConfig->init();
        $this->setConfig($oConfig);

        $_GET[$sType] = true;

        $oUpload = oxNew(\ASign\EightSelect\Controller\Admin\AdminExportUpload::class);
        $oUpload->run();
    }

    /**
     * @param $iShopId
     * @throws \oxConnectionException
     */
    public function export_full($iShopId)
    {
        $this->_exportCron($iShopId, 'do_full');
    }

    /**
     * @param $iShopId
     * @throws \oxConnectionException
     */
    public function export_update($iShopId)
    {
        $this->_exportCron($iShopId, 'do_update');
    }

    /**
     * @param $iShopId
     * @throws \oxConnectionException
     */
    public function upload_full($iShopId)
    {
        $this->_uploadCron($iShopId, 'upload_full');
    }

    /**
     * @param $iShopId
     * @throws \oxConnectionException
     */
    public function upload_update($iShopId)
    {
        $this->_uploadCron($iShopId, 'upload_update');
    }

    /**
     * @param $iShopId
     * @throws \oxConnectionException
     */
    public function export_upload_full($iShopId)
    {
        $this->export_full($iShopId);
        $this->upload_full($iShopId);
    }

    /**
     * @param $iShopId
     * @throws \oxConnectionException
     */
    public function export_upload_update($iShopId)
    {
        $this->export_update($iShopId);
        $this->upload_update($iShopId);
    }
}
