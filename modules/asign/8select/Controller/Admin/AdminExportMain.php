<?php

namespace ASign\EightSelect\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\DynamicExportBaseController;

/**
 * Class AdminExportMain
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminExportMain extends DynamicExportBaseController
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = 'AdminExportDo';

    /**
     * Upload class name
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
    protected $_sThisTemplate = 'eightselect_admin_export_main.tpl';
}
