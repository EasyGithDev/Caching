<?php

namespace Caching;


class ArgumentException extends \Exception
{

    function __construct($message)
    {
        parent::__construct($message);
    }
}
