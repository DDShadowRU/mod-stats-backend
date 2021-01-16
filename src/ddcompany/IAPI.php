<?php
namespace ddcompany;

interface IAPI
{
    function run();

    function cancel(array $result);

    function fall(string $msg);

    function requirePresent($value, string $msg);
}