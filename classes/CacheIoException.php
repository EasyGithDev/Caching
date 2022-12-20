<?php

namespace Caching;


class CacheIoException extends \Exception
{

    function __construct($message)
    {
        parent::__construct($message);
    }
}
