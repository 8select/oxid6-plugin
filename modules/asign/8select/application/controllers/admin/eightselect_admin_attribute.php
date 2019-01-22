<?php

/**
 * Admin 8select configuration.
 */
class eightselect_admin_attribute extends oxAdminDetails
{
    /**
     * Export class name
     *
     * @var string
     */
    public $sClassDo = 'eightselect_admin_attribute_do';

    /**
     * Export ui class name
     *
     * @var string
     */
    public $sClassMain = 'eightselect_admin_attribute_main';

    /**
     * View template name
     *
     * @var string
     */
    protected $_sThisTemplate = "eightselect_admin_attribute.tpl";
}
