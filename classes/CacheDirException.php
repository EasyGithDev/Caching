<?php

namespace Caching;


class CacheDirException extends \Exception
{

    function __construct($dir)
    {
        parent::__construct("Directory $dir is not available");
    }
}
