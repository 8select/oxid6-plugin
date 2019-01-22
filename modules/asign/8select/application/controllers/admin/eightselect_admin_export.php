<?php

/**
 * Admin 8select export manager.
 */
class eightselect_admin_export extends DynExportBase
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = 'eightselect_admin_export_do';

    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = 'eightselect_admin_export_main';

    /**
     * View template name
     *
     * @var string
     */
    protected $_sThisTemplate = "eightselect_admin_export.tpl";
}
