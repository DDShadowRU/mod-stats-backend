<?php

namespace ddcompany;

abstract class AbstractAPI implements IAPI
{
    private $startTime = 0;

    function run()
    {
        $this->startTime = microtime(true);
    }

    function requirePresent($value, string $msg)
    {
        if (!isset($value)) {
            $this->fall($msg);
        }

        return $value;
    }

    function getOr($name, $default)
    {
        if (!isset($_GET[$name])) {
            return $default;
        }

        return $_GET[$name];
    }

    function cancel(array $result)
    {
        echo json_encode(["error" => "", "time" => $this->getRunTime(), "result" => $result]);
        exit();
    }

    function fall(string $msg)
    {
        echo json_encode(["error" => $msg, "time" => $this->getRunTime()]);
        exit();
    }

    protected function getRunTime()
    {
        return microtime(true) - $this->startTime;
    }
}