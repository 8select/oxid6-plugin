<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = [
    'id'          => 'asign_8select',
    'title'       => '8select CSE',
    'description' => [
        'de' => '<p>Hier finden Sie die <b>Installationsanleitung für das Plugin: <a href="https://www.8select.com/8select-cse-installationsanleitung-oxid" target="_blank">8select CSE Installationsanleitung</a></b></p>
                 <p>Gerne begleiten wir Sie bei der Installation und stehen auch sonst für alle Fragen rund um die Installation zur Verfügung. Sie erreichen uns bei Fragen unter <b>+49 (0)941 20 609 6-10</b> und per E-Mail unter <b><a href="mailto:onboarding@8select.de">onboarding@8select.de</a></b></p>
                 <p>Ihr 8select-Team</p>',
        'en' => '<p>Here you will find the <b>Installation instructions for the plugin: <a href="https://www.8select.com/8select-cse-installationsanleitung-oxid" target="_blank">8select CSE Installation Guide</a></b></p>
                 <p>We are happy to accompany you during the installation and are otherwise available for all questions concerning the installation. You can reach us with questions below <b>+49 (0)941 20 609 6-10</b> and by e-mail <b><a href="mailto:onboarding@8select.de">onboarding@8select.de</a></b></p>
                 <p>Your 8select team</p>',
    ],
    'thumbnail'   => '8selectLogo.jpeg',
    'version'     => '1.0.0',
    'author'      => 'A-SIGN GmbH',
    'url'         => 'https://www.a-sign.ch',
    'email'       => 'info@a-sign.ch',
    'extend'      => [
        \OxidEsales\Eshop\Core\ViewConfig::class  => ASign\EightSelect\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Model\Article::class => ASign\EightSelect\Model\Article::class,
        \OxidEsales\Eshop\Application\Component\BasketComponent::class => ASign\EightSelect\Component\BasketComponent::class,
        \OxidEsales\Eshop\Application\Component\Widget\MiniBasket::class => ASign\EightSelect\Component\MiniBasket::class
    ],
    'controllers'  => [
        'AdminAttribute'     => ASign\EightSelect\Controller\Admin\AdminAttribute::class,
        'AdminAttributeMain' => ASign\EightSelect\Controller\Admin\AdminAttributeMain::class,
        'AdminExport'        => ASign\EightSelect\Controller\Admin\AdminExport::class,
        'AdminExportDo'      => ASign\EightSelect\Controller\Admin\AdminExportDo::class,
        'AdminExportMain'    => ASign\EightSelect\Controller\Admin\AdminExportMain::class,
        'AdminExportUpload'  => ASign\EightSelect\Controller\Admin\AdminExportUpload::class
    ],
    'files'       => [
        // Core
        'Dispatcher' => ASign\EightSelect\Core\Dispatcher::class,
        'Request'    => ASign\EightSelect\Core\Request::class,

        // Models
        'Attribute'      => ASign\EightSelect\Model\Attribute::class,
        'Attribute2Oxid' => ASign\EightSelect\Model\Attribute2Oxid::class,
        'Aws'            => ASign\EightSelect\Model\Aws::class,
        'Export'         => ASign\EightSelect\Model\Export::class,
        'Log'            => ASign\EightSelect\Model\SelectLog::class,
        'ExportAbstract' => ASign\EightSelect\Model\Export\ExportAbstract::class,
        'ExportDynamic'  => ASign\EightSelect\Model\Export\ExportDynamic::class,
        'ExportStatic'   => ASign\EightSelect\Model\Export\ExportStatic::class,
    ],
    'events'      => [
        'onActivate'   => 'ASign\EightSelect\Core\Events::onActivate',
        'onDeactivate' => 'ASign\EightSelect\Core\Events::onDeactivate'
    ],
    'templates'   => [
        'eightselect_admin_attribute.tpl'      => 'asign/8select/views/admin/tpl/eightselect_admin_attribute.tpl',
        'eightselect_admin_attribute_main.tpl' => 'asign/8select/views/admin/tpl/eightselect_admin_attribute_main.tpl',
        'eightselect_admin_export.tpl'         => 'asign/8select/views/admin/tpl/eightselect_admin_export.tpl',
        'eightselect_admin_export_do.tpl'      => 'asign/8select/views/admin/tpl/eightselect_admin_export_do.tpl',
        'eightselect_admin_export_main.tpl'    => 'asign/8select/views/admin/tpl/eightselect_admin_export_main.tpl',
        'eightselect_admin_export_upload.tpl'  => 'asign/8select/views/admin/tpl/eightselect_admin_export_upload.tpl',
    ],
    'blocks'      => [
        [
            'template' => 'layout/base.tpl',
            'block'    => 'base_style',
            'file'     => '/views/blocks/base_style.tpl',
        ],
        [
            'template' => 'page/details/inc/related_products.tpl',
            'block'    => 'details_relatedproducts_similarproducts',
            'file'     => '/views/blocks/page/details/inc/eightselect_sys-psv.tpl',
        ],
        [
            'template' => 'page/checkout/thankyou.tpl',
            'block'    => 'checkout_thankyou_main',
            'file'     => '/views/blocks/page/checkout/eightselect_performance-tracking.tpl',
        ],
    ],
    'settings'    => [
        [
            'group' => 'eightselect_main',
            'name'  => 'blEightSelectActive',
            'type'  => 'bool',
            'value' => 'false',
        ],
        [
            'group' => 'eightselect_main',
            'name'  => 'blEightSelectPreview',
            'type'  => 'bool',
            'value' => 'false',
        ],
        [
            'group' => 'eightselect_main',
            'name'  => 'sEightSelectApiId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'eightselect_main',
            'name'  => 'sEightSelectFeedId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'eightselect_widget',
            'name'  => 'blEightSelectWidgetSysPsv',
            'type'  => 'bool',
            'value' => 'true',
        ],
        [
            'group' => 'eightselect_feed',
            'name'  => 'sEightSelectExportNrOfFeeds',
            'type'  => 'str',
            'value' => '3',
        ],
    ],
];
