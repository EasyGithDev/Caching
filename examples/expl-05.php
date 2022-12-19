<?php

use Caching\Cache;

require __DIR__ . '/../classes/autoload.php';

$key = 'MyKey5';
$lifeTime = 60;
$now = time();

if (Cache::put($key, "my content", $now + $lifeTime)) {
    echo 'Cache is stored<br>';
}

echo 'Cache content is :', Cache::get($key), '<br>';

if (Cache::forget($key)) {
    echo 'Cache is removed';
} else {
    echo 'No way !!!!';
}
