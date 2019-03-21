<?php

namespace ASign\EightSelect\Controller\Admin;

use ASign\EightSelect\Model\Export;
use ASign\EightSelect\Model\Log;
use OxidEsales\Eshop\Application\Controller\Admin\DynamicExportBaseController;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;

/**
 * Class AdminExportDo
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminExportDo extends DynamicExportBaseController
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
    protected $_aParent = [];

    /**
     * Prepares export
     */
    public function start()
    {
        $this->_aViewData['refresh'] = 0;
        $this->_aViewData['iStart'] = 0;

        // prepare it
        $end = $this->prepareExport();

        Registry::getSession()->setVariable("iEnd", $end);
        $this->_aViewData['iEnd'] = $end;

        $type = Registry::get(Request::class)->getRequestEscapedParameter("do_full") ? 'do_full' : 'do_update';
        $this->_aViewData['sType'] = $type;
    }

    /**
     * Does export
     * @throws \Exception
     */
    public function run()
    {
        $full = (bool) Registry::get(Request::class)->getRequestEscapedParameter('do_full');

        /** @var Export $eightSelectExport */
        $eightSelectExport = oxNew(Export::class);

        /** @var Log $eightSelectLog */
        $eightSelectLog = oxNew(Log::class);
        $eightSelectLog->startExport($full);

        $dateTime = $eightSelectLog->getLastSuccessExportDate($full);
        $eightSelectLog->setLastSuccessExportDate($full);

        try {
            $this->sExportFileName = $eightSelectExport->getExportFileName($full);
            $this->_sFilePath = $this->getConfig()->getConfigParam('sShopDir') . "/" . $this->sExportPath . $this->sExportFileName;

            parent::run();

            $eightSelectLog->successExport();
        } catch (\Throwable $exception) {
            $this->stop(Export::ERR_NOFEEDID);
            $eightSelectLog->errorExport($exception->getMessage());
            $eightSelectLog->setLastSuccessExportDate($full, $dateTime);
        }
    }

    /**
     * @param int $count
     * @return bool|int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function nextTick($count)
    {
        $exportedItems = $count;
        $continue = false;

        /** @var Export $eightSelectTmpExport */
        static $eightSelectTmpExport = null;

        if ($eightSelectTmpExport === null) {
            $eightSelectTmpExport = oxNew(Export::class);
        }

        /** @var Article $article */
        if ($article = $this->getOneArticle($count, $continue)) {
            $parentId = $article->getParentId();

            $export = clone $eightSelectTmpExport;
            $export->setArticle($article);

            // set parent article (performance loading)
            if ($article->isVariant() && !isset($this->_aParent[$parentId])) {
                // clear parent from other variant
                $this->_aParent = [];
                // $oParent can be false here: Check for this possibility
                if ($parent = $article->getParentArticle()) {
                    $this->_aParent[$parentId]['article_parent'] = $parent;

                    /** @var Export $parentExport */
                    $parentExport = clone $eightSelectTmpExport;
                    $parentExport->setArticle($parent);
                    $parentExport->initData();
                    $this->_aParent[$parentId]['export_parent'] = $parentExport;
                }
            }

            if ($article->isVariant() && isset($this->_aParent[$parentId])) {
                $export->setParent($this->_aParent[$parentId]['article_parent']);
                $export->setParentExport($this->_aParent[$parentId]['export_parent']);
            }

            // set header if it's the first article
            if ((int) $count === 0) {
                fwrite($this->fpFile, $export->getCsvHeader());
            }

            // write variant to CSV
            fwrite($this->fpFile, $export->getCsvLine());

            return ++$exportedItems;
        }

        return $continue;
    }

    /**
     * Inserts articles into heaptable
     *
     * @param string $heapTable
     * @param string $catAdd
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function _insertArticles($heapTable, $catAdd)
    {
        $db = DatabaseProvider::getDb();
        $exportLang = Registry::get(Request::class)->getRequestEscapedParameter("iExportLanguage");

        if (!isset($exportLang)) {
            $exportLang = Registry::getSession()->getVariable("iExportLanguage");
        }

        $article = oxNew(Article::class);
        $article->setLanguage($exportLang);

        $articleTable = getViewName("oxarticles", $exportLang);

        $select = "INSERT INTO {$heapTable} ";
        $select .= "SELECT oxarticles.OXID FROM {$articleTable} as oxarticles ";
        $select .= "LEFT JOIN {$articleTable} AS mainart ON mainart.OXID = oxarticles.OXPARENTID ";
        $select .= "WHERE (oxarticles.OXPARENTID != '' OR (oxarticles.OXPARENTID = '' AND oxarticles.OXVARCOUNT = 0)) ";

        if ($catAdd) {
            $select .= $catAdd;
        }

        $full = (bool) Registry::get(Request::class)->getRequestEscapedParameter('do_full');

        if ($full) {
            $select .= "AND " . $article->getSqlActiveSnippet(true) . " ";
        } else {
            $log = oxNew(Log::class);
            $dateTime = $log->getLastSuccessExportDate($full);

            if ($dateTime) {
                $select .= "AND (oxarticles.OXTIMESTAMP >= " . $db->quote($dateTime) . " OR mainart.OXTIMESTAMP >= " . $db->quote($dateTime) . ") ";
            }
        }

        $select .= "GROUP BY oxarticles.OXID ORDER BY oxarticles.OXARTNUM ASC";
        file_put_contents(OX_BASE_PATH . 'log/0mzwack.log', date('[Y-m-d H:i:s] ') . __METHOD__ . ' '.$select . PHP_EOL, 8);

        return $db->execute($select) ? true : false;
    }

    /**
     * @param string $heapTable
     */
    protected function _removeParentArticles($heapTable)
    {
        /* we don't have parent articles in heap-table, so we can skip that */
    }

    /**
     * @param int    $shopId
     * @param string $type
     * @throws \Exception
     */
    protected function _exportCron($shopId, $type)
    {
        $config = Registry::getConfig();
        $config->setShopId($shopId);
        $config->reinitialize();

        $_GET[$type] = true;
        $_GET['iStart'] = 0;
        $_GET['refresh'] = 0;

        $this->_iExportPerTick = 1000000;

        $this->start();
        $this->run();
        $this->stop();
    }

    /**
     * @param int    $shopId
     * @param string $type
     */
    protected function _uploadCron($shopId, $type)
    {
        $config = Registry::getConfig();
        $config->setShopId($shopId);
        $config->reinitialize();

        $_GET[$type] = true;

        $upload = oxNew(AdminExportUpload::class);
        $upload->run();
    }

    /**
     * @param int $shopId
     * @throws \Exception
     */
    public function export_full($shopId)
    {
        $this->_exportCron($shopId, 'do_full');
    }

    /**
     * @param int $shopId
     * @throws \Exception
     */
    public function export_update($shopId)
    {
        $this->_exportCron($shopId, 'do_update');
    }

    /**
     * @param int $shopId
     */
    public function upload_full($shopId)
    {
        $this->_uploadCron($shopId, 'upload_full');
    }

    /**
     * @param int $shopId
     */
    public function upload_update($shopId)
    {
        $this->_uploadCron($shopId, 'upload_update');
    }

    /**
     * @param int $shopId
     * @throws \Exception
     */
    public function export_upload_full($shopId)
    {
        $this->export_full($shopId);
        $this->upload_full($shopId);
    }

    /**
     * @param int $shopId
     * @throws \Exception
     */
    public function export_upload_update($shopId)
    {
        $this->export_update($shopId);
        $this->upload_update($shopId);
    }
}
