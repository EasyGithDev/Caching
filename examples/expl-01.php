<?php

use Caching\Cache;

require __DIR__ . '/../classes/autoload.php';

$lifeTime = 60;
$key = 'MyKey';

$content = (new Cache)->store($key, $lifeTime, function () {
    return 'Groovy baby !!!!!!!!!!!!!!!!!';
});

var_dump($content);
