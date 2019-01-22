<?php

namespace ASign\EightSelect\Controller\Admin;

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

        $sType = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('upload_full') ? 'upload_full' : 'upload_update';
        $this->_aViewData['sType'] = $sType;
    }

    /**
     * Does upload
     */
    public function run()
    {
        $sFeedId = $this->getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$sFeedId) {
            $this->stop(\ASign\EightSelect\Model\Export::$err_nofeedid);
            return;
        }

        $blFull = (bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('upload_full');
        $sSourceFile = \ASign\EightSelect\Model\Export::getExportLatestFile($blFull);

        // check if file is readable
        $this->fpFile = @fopen($sSourceFile, "r");
        if (!isset($this->fpFile) || !$this->fpFile) {
            // we do have an error !
            $this->stop(ERR_FILEIO);
        } else {
            fclose($this->fpFile);

            try {
                \ASign\EightSelect\Model\Aws::upload($sSourceFile, $sFeedId, $blFull);
                $this->stop(ERR_SUCCESS);
            } catch (\Exception $oEx) {
                $this->stop(ERR_GENERAL);
            }
        }
    }
}
