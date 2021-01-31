<?php

namespace ddcompany\stats;

use ddcompany\MySqlHelper;
use ddcompany\Period;
use InvalidArgumentException;
use mysqli;

class ModStats
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

    static function of($id): ModStats
    {
        if ($id < 0) {
            throw new InvalidArgumentException();
        }

        return new ModStats($id);
    }

    public static function getCount(?int $author = null, ?string $date = null)
    {
        $date = !$date ? "null" : $date;
        return intval(MySqlHelper::query("
                SELECT COUNT(DISTINCT mod_id) FROM stats WHERE date = ifnull($date, 
                (SELECT date FROM stats ORDER BY date DESC LIMIT 1)) " . ($author ? "AND author = $author" : ""))->fetch_array(MYSQLI_NUM)[0]);
    }

    static function getList(int $limit, int $offset, string $orderBy, string $order, ?int $author = null, ?string $date = null)
    {
        $order = strtolower($order);
        if ($order !== "asc" && $order !== "desc") {
            $order = "desc";
        }

        if (!in_array($orderBy, ["id", "downloads", "likes", "comments"])) {
            $orderBy = "id";
        }

        $date = !$date ? "null" : $date;
        return MySqlHelper::query("
                SELECT mod_id as id, author, downloads, likes, comments
                FROM stats
                WHERE date = ifnull($date, (SELECT date FROM stats ORDER BY date DESC LIMIT 1)) " . ($author ? "AND author = $author" : "") . "
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
        return MySqlHelper::mapDayStats(MySqlHelper::query("call GetDaysStats(" . $this->id . ")")
            ->fetch_all(MYSQLI_ASSOC));
    }

    private function getStats(int $period, string $type): int
    {
        $dates = Period::getDates($period);
        return intval(MySqlHelper::query("
                SELECT SUM($type -
                    (SELECT $type FROM stats WHERE mod_id = " . $this->id . " AND id < st.id ORDER BY date DESC LIMIT 1))
                FROM stats st
                WHERE mod_id = " . $this->id . " 
                    AND date >= " . ($dates[0] ? "\"" . $dates[0] . "\"" : "(SELECT date FROM stats ORDER BY date DESC LIMIT 1)") . "
                    " . ($dates[1] ? "AND date <= \"$dates[1]\"" : ""))->fetch_array(MYSQLI_NUM)[0]);
    }
}