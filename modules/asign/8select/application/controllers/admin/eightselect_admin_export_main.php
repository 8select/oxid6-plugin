<?php

/**
 * Admin 8select export manager.
 */
class eightselect_admin_export_main extends DynExportBase
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = "eightselect_admin_export_do";

    /**
     * Upload class name
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
    protected $_sThisTemplate = "eightselect_admin_export_main.tpl";

}
