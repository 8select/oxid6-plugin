<?php

/**
 * 8select export class.
 */
class eightselect_admin_export_do extends DynExportBase
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = "eightselect_admin_export_do";

    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = "eightselect_admin_export_main";

    /**
     * define max. number of records to export in one go
     */
    public $_iExportPerTick = 1000000;

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = "eightselect_admin_export_do.tpl";

    /** @var array */
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
        oxRegistry::getSession()->setVariable("iEnd", $iEnd);
        $this->_aViewData['iEnd'] = $iEnd;

        $sType = oxRegistry::getConfig()->getRequestParameter("do_full") ? 'do_full' : 'do_update';
        $this->_aViewData['sType'] = $sType;
    }

    /**
     * Does export
     */
    public function run()
    {
        $blFull = (bool)oxRegistry::getConfig()->getRequestParameter('do_full');

        /** @var eightselect_export $oEightSelectExport */
        $oEightSelectExport = oxNew('eightselect_export');

        /** @var eightselect_log $oEightSelectLog */
        $oEightSelectLog = oxNew('eightselect_log');
        $oEightSelectLog->startExport($blFull);

        $mDateTime = $oEightSelectLog->getLastSuccessExportDate($blFull);
        $oEightSelectLog->setLastSuccessExportDate($blFull);

        try {
            $this->sExportFileName = $oEightSelectExport->getExportFileName($blFull);
            $this->_sFilePath = $this->getConfig()->getConfigParam('sShopDir') . "/" . $this->sExportPath . $this->sExportFileName;
            parent::run();
            $oEightSelectLog->successExport();
        } catch (UnexpectedValueException $oEx) {
            $this->stop(eightselect_export::$err_nofeedid);
            $oEightSelectLog->errorExport($oEx->getMessage());
            $oEightSelectLog->setLastSuccessExportDate($blFull, $mDateTime);
        }
    }

    /**
     * Does export line by line on position iCnt
     *
     * @param integer $iCnt export position
     *
     * @return bool
     */
    public function nextTick($iCnt)
    {
        $iExportedItems = $iCnt;
        $blContinue = false;

        static $oEightSelectTmpExport = null;
        if ($oEightSelectTmpExport === null) {
            $oEightSelectTmpExport = oxNew('eightselect_export');
        }

        /** @var oxArticle $oArticle */
        if ($oArticle = $this->getOneArticle($iCnt, $blContinue)) {

            $sParentId = $oArticle->oxarticles__oxparentid->value;

            // set parent article (performance loading)
            if ($oArticle->isVariant() && !isset($this->_aParent[$sParentId])) {
                // clear parent from other variant
                $this->_aParent = [];
                $oParent = $oArticle->getParentArticle();
                $this->_aParent[$sParentId]['article_parent'] = $oParent;

                /** @var eightselect_export $oEightSelectParentExport */
                $oEightSelectParentExport = clone $oEightSelectTmpExport;
                $oEightSelectParentExport->setArticle($oParent);
                $oEightSelectParentExport->initData();
                $this->_aParent[$sParentId]['export_parent'] = $oEightSelectParentExport;
            }

            /** @var eightselect_export $oEightSelectExport */
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
     * inserts articles into heaptable
     *
     * @param string $sHeapTable heap table name
     * @param string $sCatAdd category id filter (part of sql)
     *
     * @return bool
     */
    protected function _insertArticles($sHeapTable, $sCatAdd)
    {
        $oDB = oxDb::getDb();

        $iExpLang = oxRegistry::getConfig()->getRequestParameter("iExportLanguage");
        if (!isset($iExpLang)) {
            $iExpLang = oxRegistry::getSession()->getVariable("iExportLanguage");
        }

        /** @var oxArticle $oArticle */
        $oArticle = oxNew('oxarticle');
        $oArticle->setLanguage($iExpLang);

        $sArticleTable = getViewName("oxarticles", $iExpLang);

        $sSelect = "INSERT INTO {$sHeapTable} ";
        $sSelect .= "SELECT oxarticles.OXID FROM {$sArticleTable} as oxarticles ";
        $sSelect .= "LEFT JOIN {$sArticleTable} AS mainart ON mainart.OXID = oxarticles.OXPARENTID ";
        $sSelect .= "WHERE (oxarticles.OXPARENTID != '' OR (oxarticles.OXPARENTID = '' AND oxarticles.OXVARCOUNT = 0)) ";

        if ($sCatAdd) {
            $sSelect .= $sCatAdd;
        }


        $blFull = (bool)oxRegistry::getConfig()->getRequestParameter('do_full');
        if ($blFull) {
            // export only active articles in product_feed
            $sSelect .= "AND " . $oArticle->getSqlActiveSnippet(true) . " ";
        } else {
            // get only last changed articles
            /** @var eightselect_log $oEightSelectLog */
            $oEightSelectLog = oxNew('eightselect_log');
            $mDateTime = $oEightSelectLog->getLastSuccessExportDate($blFull);
            if ($mDateTime) {
                $sSelect .= "AND (oxarticles.OXTIMESTAMP >= " . $oDB->quote($mDateTime) . " OR mainart.OXTIMESTAMP >= " . $oDB->quote($mDateTime) . ") ";
            }
        }

        $sSelect .= "GROUP BY oxarticles.OXID ORDER BY oxarticles.OXARTNUM ASC";

        return $oDB->execute($sSelect) ? true : false;
    }

    /**
     * removes parent articles so that we only have variants itself
     *
     * @param string $sHeapTable table name
     */
    protected function _removeParentArticles($sHeapTable)
    {
        /* we don't have parent articles in heap-table, so we can skip that */
    }

    /**
     * @param integer $iShopId
     * @param string $sType
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    private function _exportCron($iShopId, $sType)
    {
        $oConfig = oxRegistry::getConfig();
        $oConfig->setShopId($iShopId);
        $oConfig->init();
        $this->setConfig($oConfig);

        $_GET[$sType] = true;
        $_GET['iStart'] = 0;
        $_GET['refresh'] = 0;

        $this->start();
        $this->run();
        $this->stop();
    }

    /**
     * @param integer $iShopId
     * @param string $sType
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    private function _uploadCron($iShopId, $sType)
    {
        $oConfig = oxRegistry::getConfig();
        $oConfig->setShopId($iShopId);
        $oConfig->init();
        $this->setConfig($oConfig);

        $_GET[$sType] = true;

        /** @var eightselect_admin_export_upload $oUpload */
        $oUpload = oxNew('eightselect_admin_export_upload');
        $oUpload->run();
    }

    /**
     * @param integer $iShopId
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function export_full($iShopId)
    {
        $this->_exportCron($iShopId, 'do_full');
    }

    /**
     * @param integer $iShopId
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function export_update($iShopId)
    {
        $this->_exportCron($iShopId, 'do_update');
    }

    /**
     * @param integer $iShopId
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function upload_full($iShopId)
    {
        $this->_uploadCron($iShopId, 'upload_full');
    }

    /**
     * @param integer $iShopId
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function upload_update($iShopId)
    {
        $this->_uploadCron($iShopId, 'upload_update');
    }

    /**
     * @param integer $iShopId
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function export_upload_full($iShopId)
    {
        $this->export_full($iShopId);
        $this->upload_full($iShopId);
    }

    /**
     * @param integer $iShopId
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function export_upload_update($iShopId)
    {
        $this->export_update($iShopId);
        $this->upload_update($iShopId);
    }
}
