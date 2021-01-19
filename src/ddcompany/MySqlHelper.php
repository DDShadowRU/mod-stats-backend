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

    static function getAuthorStats(mysqli $db, int $authorId): array
    {
        return self::mapDayStats(self::query($db, "call GetAuthorStats($authorId)")->fetch_all(MYSQLI_ASSOC));
    }

    static function getAuthorsCount(mysqli $db, ?string $date)
    {
        return $db->query("SELECT count(distinct author) FROM stats WHERE date="
            . ($date ? $date : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)"))->fetch_array()[0];
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

    static function getDownloadsInMonthByAuthor(mysqli $db, int $authorId): int
    {
        return self::getDiffOfAuthor($db, $authorId, "downloads", date("Y-m-d", strtotime("-30 days")));
    }

    static function getDownloadsInWeekByAuthor(mysqli $db, int $authorId): int
    {
        return self::getDiffOfAuthor($db, $authorId, "downloads",
            date('Y-m-d', strtotime('-7 days')), date("Y-m-d"));
    }

    static function getDownloadsInDayByAuthor(mysqli $db, int $authorId): int
    {
        return self::getDiffOfAuthor($db, $authorId, "downloads");
    }

    static function getLikesInMonthByAuthor(mysqli $db, int $authorId): int
    {
        return self::getDiffOfAuthor($db, $authorId, "likes", date("Y-m-d", strtotime("-30 days")));
    }

    static function getLikesInWeekByAuthor(mysqli $db, int $authorId): int
    {
        return self::getDiffOfAuthor($db, $authorId, "likes",
            date('Y-m-d', strtotime('-7 days')), date("Y-m-d"));
    }

    static function getLikesInDayByAuthor(mysqli $db, int $authorId): int
    {
        return self::getDiffOfAuthor($db, $authorId, "likes");
    }

    static function getModStats(mysqli $db, int $modId): array
    {
        return self::mapDayStats(self::query($db, "call GetDaysStats($modId)")->fetch_all(MYSQLI_ASSOC));
    }

    static function getDownloadsInMonth(mysqli $db, int $modId): int
    {
        return self::getDiffOfMod($db, $modId, "downloads", date("Y-m-d", strtotime("-30 days")));
    }

    static function getDownloadsInWeek(mysqli $db, int $modId): int
    {
        return self::getDiffOfMod($db, $modId, "downloads",
            date('Y-m-d', strtotime('-7 days')), date("Y-m-d"));
    }

    static function getDownloadsInDay(mysqli $db, int $modId): int
    {
        return self::getDiffOfMod($db, $modId, "downloads");
    }

    static function getLikesInMonth(mysqli $db, int $modId): int
    {
        return self::getDiffOfMod($db, $modId, "likes", date("Y-m-d", strtotime("this week")));
    }

    static function getLikesInWeek(mysqli $db, int $modId): int
    {
        return self::getDiffOfMod($db, $modId, "likes",
            date('Y-m-d', strtotime('-7 days')), date("Y-m-d"));
    }

    static function getLikesInDay(mysqli $db, int $modId): int
    {
        return self::getDiffOfMod($db, $modId, "likes");
    }

    private static function getDiffOfMod(mysqli $db, int $id, string $field, ?string $startDate = null, ?string $endDate = null): int
    {
        return intval(self::query($db, "
            SELECT SUM($field -
                (SELECT $field FROM stats WHERE mod_id = $id AND id < st.id ORDER BY date DESC LIMIT 1))
            FROM stats st
            WHERE mod_id = $id 
                AND date >= " . ($startDate ? $startDate : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)") . "
                " . ($endDate ? "AND date <= $endDate" : ""))->fetch_array(MYSQLI_NUM)[0]);
    }

    private static function getDiffOfAuthor(mysqli $db, int $id, string $field, ?string $startDate = null, ?string $endDate = null): int
    {
        return intval(self::query($db, "
            SELECT SUM($field -
                    (SELECT $field FROM stats WHERE mod_id = st.mod_id AND id < st.id ORDER BY date DESC LIMIT 1))
            FROM stats st
            WHERE author = $id 
                AND date >= " . ($startDate ? $startDate : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)") . "
                " . ($endDate ? "AND date <= $endDate" : ""))->fetch_array(MYSQLI_NUM)[0]);
    }

    private static function mapDayStats(array $stats): array
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