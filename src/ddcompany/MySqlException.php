<?php
namespace ddcompany;

use Exception;

class MySqlException extends Exception
{
    public function __construct(string $msg)
    {
        parent::__construct($msg);
    }
}