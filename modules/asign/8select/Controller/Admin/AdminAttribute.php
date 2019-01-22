<?php

namespace ASign\EightSelect\Controller\Admin;

/**
 * Class AdminAttribute
 * @package ASign\EightSelect\Controller\Admin
 */
class AdminAttribute extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = 'AdminAttributeDo';

    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = 'AdminAttributeMain';

    /**
     * View template name
     *
     * @var string
     */
    protected $_sThisTemplate = "eightselect_admin_attribute.tpl";
}
