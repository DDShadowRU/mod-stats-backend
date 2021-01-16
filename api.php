<?php
include_once "vendor/autoload.php";

ini_set('max_execution_time', 0);
error_reporting(E_ERROR | E_PARSE);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$handler = new ddcompany\APIHandler();
$handler->handle($_GET);

//$db = \ddcompany\MySqlHelper::connect();
//$result = $db->query("SELECT mod_id, author FROM stats WHERE date=20210115")->fetch_all(MYSQLI_ASSOC);
//foreach ($result as $item) {
//    $db->query("UPDATE `stats` SET author=" . $item["author"] . " WHERE mod_id=" . $item["mod_id"]);
//}