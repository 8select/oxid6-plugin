<?php

include(dirname(__FILE__).'/../bootstrap.php');

$oDispatcher = \ASign\EightSelect\Core\Dispatcher::getInstance();

try {
    $oDispatcher->dispatch(new \ASign\EightSelect\Core\Request());
} catch (Exception $oEx) {
    echo <<<EOT
{$oEx->getMessage()}
EOT;
}
