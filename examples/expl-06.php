<?php

use Caching\Cache;

require __DIR__ . '/../classes/autoload.php';

class JsonCache extends Cache
{
    protected static $_instance = null;
    public static function getInstance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    protected function serialize(mixed $content): string
    {
        return json_encode($content);
    }

    protected function unserialize(string $data): mixed
    {
        return json_decode($data);
    }
};

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

$key = 'MyKey6';
$lifeTime = 60;

$content = JsonCache::store($key, $lifeTime, function () use ($dbh) {
    $sql = 'SELECT * from authors LIMIT 10';
    return $dbh->query($sql)->fetchAll();
});

var_dump($content);
