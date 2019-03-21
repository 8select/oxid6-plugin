<?php

namespace ASign\EightSelect\Controller\Admin;

use ASign\EightSelect\Model\Aws;
use ASign\EightSelect\Model\Export;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;

/**
 * Class AdminExportUpload
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminExportUpload extends \OxidEsales\Eshop\Application\Controller\Admin\DynamicExportBaseController
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassUpload = 'AdminExportUpload';

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
    protected $_sThisTemplate = 'eightselect_admin_export_upload.tpl';

    /**
     * Prepares Export
     */
    public function start()
    {
        $this->_aViewData['refresh'] = 0;

        $type = Registry::get(Request::class)->getRequestEscapedParameter('upload_full') ? 'upload_full' : 'upload_update';
        $this->_aViewData['sType'] = $type;
    }

    /**
     * Does upload
     */
    public function run()
    {
        $feedId = $this->getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$feedId) {
            $this->stop(Export::ERR_NOFEEDID);

            return;
        }

        $full = (bool) Registry::get(Request::class)->getRequestEscapedParameter('upload_full');
        $sourceFile = Registry::get(Export::class)->getExportLatestFile($full);

        // check if file is readable
        $this->fpFile = @fopen($sourceFile, "r");
        if (!isset($this->fpFile) || !$this->fpFile) {
            // we do have an error !
            $this->stop(ERR_FILEIO);
        } else {
            fclose($this->fpFile);

            try {
                Registry::get(Aws::class)->upload($sourceFile, $feedId, $full);
                $this->stop(ERR_SUCCESS);
            } catch (\Exception $exception) {
                Registry::getUtils()->writeToLog($exception, '8select.log');
                $this->stop(ERR_GENERAL);
            }
        }
    }
}
