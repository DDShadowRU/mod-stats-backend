<?php

namespace ddcompany;

use mysqli;
use mysqli_result;

class MySqlHelper
{
    /**
     * @var mysqli
     */
    private static $db;

    /**
     * @return mysqli
     * @throws MySqlException
     */
    static function connect(): mysqli
    {
        if (!self::$db) {
            self::$db = mysqli_connect($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"]);
            if (!self::$db) {
                throw new MySqlException("Unable to connect: " . self::$db->error);
            }
        }

        return self::$db;
    }

    static function query(string $query): mysqli_result
    {
        $db = MySqlHelper::connect();
        $response = $db->query($query);
        if (!$response) {
            throw new MySqlException($db->error);
        }

        return $response;
    }

    public static function mapDayStats(array $stats): array
    {
        return array_map(function ($day) {
            return [
                "date" => $day["date"],
                "downloads" => intval($day["downloads"]),
                "likes" => intval($day["likes"]),
                "downloads_added" => intval($day["downloads_added"]),
                "likes_added" => intval($day["likes_added"])
            ];
        }, $stats);
    }
}