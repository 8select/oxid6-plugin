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
    'version'     => '__VERSION__',
    'author'      => '8select Software GmbH',
    'url'         => 'https://www.8select.com/',
    'email'       => 'service@8select.de',
    'extend'      => [
        \OxidEsales\Eshop\Core\ViewConfig::class                                  => ASign\EightSelect\Extensions\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Model\Article::class                        => ASign\EightSelect\Extensions\Application\Model\Article::class,
        \OxidEsales\Eshop\Application\Component\BasketComponent::class            => ASign\EightSelect\Extensions\Application\Component\BasketComponent::class,
        \OxidEsales\Eshop\Application\Component\Widget\MiniBasket::class          => ASign\EightSelect\Extensions\Application\Component\MiniBasket::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => ASign\EightSelect\Extensions\Application\Controller\Admin\ModuleConfiguration::class,
    ],
    'controllers' => [
        'EightSelectAPI' => \ASign\EightSelect\Application\Controller\EightSelectAPI::class,
    ],
    'events'      => [
        'onActivate'   => 'ASign\EightSelect\Core\Events::onActivate',
        'onDeactivate' => 'ASign\EightSelect\Core\Events::onDeactivate'
    ],
    'templates'   => [
    ],
    'blocks'      => [
        [
            'template' => 'layout/base.tpl',
            'block'    => 'base_style',
            'file'     => '/Application/blocks/base_style.tpl',
        ],
        [
            'template' => 'page/details/inc/related_products.tpl',
            'block'    => 'details_relatedproducts_similarproducts',
            'file'     => '/Application/blocks/page/details/inc/eightselect_sys-psv.tpl',
        ],
        [
            'template' => 'page/checkout/thankyou.tpl',
            'block'    => 'checkout_thankyou_main',
            'file'     => '/Application/blocks/page/checkout/eightselect_performance-tracking.tpl',
        ],
        [
            'template' => 'module_config.tpl',
            'block'    => 'admin_module_config_var_type_select',
            'file'     => '/Application/blocks/eightselect_admin_module_config_var_type_select.tpl',
        ],
        [
            'template' => 'module_config.tpl',
            'block'    => 'admin_module_config_form',
            'file'     => '/Application/blocks/eightselect_admin_module_config_form.tpl',
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
            'group' => 'eightselect_widget',
            'name'  => 'sArticleSkuField',
            'type'  => 'select',
            'value' => 'OXARTNUM',
        ],
        [
            'group' => 'eightselect_feed',
            'name'  => 'sEightSelectExportNrOfFeeds',
            'type'  => 'str',
            'value' => '3',
        ],
    ],
];
