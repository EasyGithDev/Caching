<?php

use Caching\Cache;
use Caching\Debug;

require __DIR__ . '/../classes/autoload.php';

$lifeTime = 60;
$key = 'MyKey1';
$debug =  new class implements Debug
{
    function msg(string $msg): void
    {
        echo nl2br($msg);
    }
};

$content = Cache::getInstance()
    ->setDebug($debug)
    ->storeCache($key, $lifeTime, function () {
        return 'Groovy baby !!!!!!!!!!!!!!!!!';
    });

var_dump($content);
