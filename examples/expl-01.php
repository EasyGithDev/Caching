<?php

use Caching\Cache;

require __DIR__ . '/../classes/autoload.php';

$lifeTime = 60;
$key = 'MyKey1';

$content = Cache::getInstance()->storeCache($key, $lifeTime, function () {
    return 'Groovy baby !!!!!!!!!!!!!!!!!';
});

var_dump($content);
