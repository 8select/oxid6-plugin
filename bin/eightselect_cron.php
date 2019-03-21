<?php

require_once __DIR__ . '/../bootstrap.php';

try {
    $dispatcher = \OxidEsales\Eshop\Core\Registry::get(\ASign\EightSelect\Core\Dispatcher::class);
    $dispatcher->dispatch(new \ASign\EightSelect\Core\Request());
} catch (Exception $oEx) {
    echo $oEx->getMessage();
}
