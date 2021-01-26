<?php
namespace ddcompany\api;

interface IAPI
{
    function run(array $params);

    function cancel(array $result);

    function fall(string $msg);

    function requirePresent($value, string $msg);
}