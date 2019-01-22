<?php

/**
 * 8select export class.
 */
class eightselect_admin_export_upload extends DynExportBase
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassUpload = "eightselect_admin_export_upload";

    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = "eightselect_admin_export_main";

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = "eightselect_admin_export_upload.tpl";

    /**
     * Prepares Export
     */
    public function start()
    {
        $this->_aViewData['refresh'] = 0;

        $sType = oxRegistry::getConfig()->getRequestParameter("upload_full") ? 'upload_full' : 'upload_update';
        $this->_aViewData['sType'] = $sType;
    }

    /**
     * Does upload
     */
    public function run()
    {
        $sFeedId = $this->getConfig()->getConfigParam('sEightSelectFeedId');

        if (!$sFeedId) {
            $this->stop(eightselect_export::$err_nofeedid);
            return;
        }

        $blFull = (bool)oxRegistry::getConfig()->getRequestParameter('upload_full');
        $sSourceFile = eightselect_export::getExportLatestFile($blFull);

        // check if file is readable
        $this->fpFile = @fopen($sSourceFile, "r");
        if (!isset($this->fpFile) || !$this->fpFile) {
            // we do have an error !
            $this->stop(ERR_FILEIO);
        } else {
            fclose($this->fpFile);

            try {
                eightselect_aws::upload($sSourceFile, $sFeedId, $blFull);
                $this->stop(ERR_SUCCESS);
            } catch (Exception $oEx) {
                $this->stop(ERR_GENERAL);
            }
        }
    }
}
