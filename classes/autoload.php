<?php

namespace Caching;

spl_autoload_register(function ($class_name) {

    $class_name =    str_replace(__NAMESPACE__, '', $class_name);
    $class_name =    str_replace('\\', '', $class_name);

    include __DIR__ . DIRECTORY_SEPARATOR . $class_name . '.php';
});
