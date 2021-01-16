<?php
include_once "vendor/autoload.php";

ini_set('max_execution_time', 0);
error_reporting(E_ERROR | E_PARSE);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$handler = new ddcompany\APIHandler();
$handler->handle();