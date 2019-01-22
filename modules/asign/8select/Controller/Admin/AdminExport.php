<?php

namespace ASign\EightSelect\Controller\Admin;

/**
 * Class AdminExport
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminExport extends \OxidEsales\Eshop\Application\Controller\Admin\DynamicExportBaseController
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
     * View template name
     *
     * @var string
     */
    protected $_sThisTemplate = 'eightselect_admin_export.tpl';
}
