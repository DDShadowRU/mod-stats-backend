<?php

namespace ddcompany\stats;

use ddcompany\MySqlHelper;
use ddcompany\Period;
use InvalidArgumentException;
use mysqli;

class AuthorStats
{
    /**
     * @var mysqli
     */
    private $db;
    /**
     * @var int
     */
    private $id;

    private function __construct(int $id)
    {
        $this->db = MySqlHelper::connect();
        $this->id = $id;
    }

    static function of($id): AuthorStats
    {
        if ($id < 0) {
            throw new InvalidArgumentException();
        }

        return new AuthorStats($id);
    }

    static function getCount(?string $date = null): int
    {
        return intval(MySqlHelper::query("SELECT count(distinct author) FROM stats WHERE date="
            . ($date ? $date : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)"))->fetch_array()[0]);
    }

    static function getList(int $limit, int $offset = 0, string $orderBy = "id", string $order = "desc", ?string $date = null)
    {
        $order = strtolower($order);
        if ($order !== "asc" && $order !== "desc") {
            $order = "desc";
        }

        if (!in_array($orderBy, ["id", "downloads", "likes", "comments"])) {
            $orderBy = "id";
        }

        return MySqlHelper::query("
            SELECT author as id, (SELECT name FROM authors WHERE id=stats.author) name,
            SUM(downloads) as downloads, SUM(likes) as likes, SUM(comments) as comments
            FROM stats
            WHERE date = ifnull(" . (!$date ? "null" : $date) . ", (SELECT date FROM stats ORDER BY date DESC LIMIT 1))
            GROUP BY author
            ORDER BY $orderBy $order
            LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
    }

    function getLikes(int $period): int
    {
        return $this->getStats($period, "likes");
    }

    function getDownloads(int $period): int
    {
        return $this->getStats($period, "downloads");
    }

    function getDays(): array
    {
        return MySqlHelper::mapDayStats(MySqlHelper::query("call GetAuthorStats(" . $this->id . ")")
            ->fetch_all(MYSQLI_ASSOC));
    }

    private function getStats(int $period, string $type): int
    {
        $dates = Period::getDates($period);
        return intval(MySqlHelper::query("
                SELECT SUM($type -
                        (SELECT $type FROM stats WHERE mod_id = st.mod_id AND id < st.id ORDER BY date DESC LIMIT 1))
                FROM stats st
                WHERE author = " . $this->id . " 
                    AND date >= " . ($dates[0] ? "\"" .  $dates[0] . "\"" : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)") . "
                    " . ($dates[1] ? "AND date <= \"$dates[1]\"" : ""))->fetch_array(MYSQLI_NUM)[0]);
    }
}