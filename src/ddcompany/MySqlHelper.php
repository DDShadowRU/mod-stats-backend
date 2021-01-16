<?php

namespace ddcompany;

use mysqli;
use mysqli_result;

class MySqlHelper
{
    /**
     * @return mysqli
     * @throws MySqlException
     */
    static function connect(): mysqli
    {
        $db = mysqli_connect($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"]);
        if (!$db) {
            throw new MySqlException("Unable to connect: " . $db->error);
        }

        return $db;
    }

    static function query(mysqli $db, string $query): mysqli_result
    {
        $response = $db->query($query);
        if (!$response) {
            throw new MySqlException($db->error);
        }

        return $response;
    }

    static function getStats(mysqli $db, int $modId)
    {
        return self::query($db, "call GetDaysStats($modId)")->fetch_all(MYSQLI_ASSOC);
    }

    static function getAuthorList(mysqli $db, int $limit, int $offset, string $orderBy, string $order, ?string $date)
    {
        $order = strtolower($order);
        if ($order !== "asc" && $order !== "desc") {
            $order = "desc";
        }

        if (!in_array($orderBy, ["id", "downloads", "likes", "comments"])) {
            $orderBy = "id";
        }

        return self::query($db, "
            SELECT author as id, (SELECT name FROM authors WHERE id=stats.author) name,
            SUM(downloads) as downloads, SUM(likes) as likes, SUM(comments) as comments
            FROM stats
            WHERE date = ifnull(" . (!$date ? "null" : $date) . ", (SELECT date FROM stats ORDER BY date DESC LIMIT 1))
            GROUP BY author
            ORDER BY $orderBy $order
            LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
    }

    static function getDownloadsAddedInMonth(mysqli $db, int $modId)
    {
        return intval(self::query($db, "SELECT GetDownloadsAddedInMonth($modId)")->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getDownloadsAddedInThisWeek(mysqli $db, int $modId)
    {
        $date = date("y-m-d", strtotime("this week"));
        return intval(self::query($db, "SELECT GetDownloadsAddedInWeek($modId, '$date')")->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getDownloadsAddedInLastDay(mysqli $db, int $modId)
    {
        return intval(self::query($db, "SELECT GetDownloadsAddedInLastDay($modId)")->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getLikesAddedInMonth(mysqli $db, int $modId)
    {
        return intval(self::query($db, "SELECT GetLikesAddedInMonth($modId)")->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getLikesAddedInThisWeek(mysqli $db, int $modId)
    {
        $date = date("y-m-d", strtotime("this week"));
        return intval(self::query($db, "SELECT GetLikesAddedInWeek($modId, '$date')")->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getLikesAddedInLastDay(mysqli $db, int $modId)
    {
        return intval(self::query($db, "SELECT GetLikesAddedInLastDay($modId)")->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getAuthorsCount(mysqli $db, ?string $date)
    {
        return $db->query("SELECT count(distinct author) FROM stats WHERE date="
            . ($date ? $date : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)"))->fetch_array()[0];
    }
}