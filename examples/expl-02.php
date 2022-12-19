<?php

use Caching\Cache;

require __DIR__ . '/../classes/autoload.php';

$host = '127.0.0.1';
$dbname = 'blog';
$user = 'root';
$pass = 'root';

try {
    $dbh = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}

$lifeTime = 60;
$key = 'MyKey2';

$content = Cache::getInstance()->storeCache($key, $lifeTime, function () use ($dbh) {
    $sql = 'SELECT * from authors';
    return $dbh->query($sql)->fetchAll();
});

var_dump( $content );
