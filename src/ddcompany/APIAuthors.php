<?php


namespace ddcompany;

use ddcompany\stats\AuthorStats;

require_once "math.php";

class APIAuthors extends AbstractAPI
{
    function run(array $params)
    {
        parent::run($params);

        $db = MySqlHelper::connect();
        $perPage = clamp(5, 20, $this->getOr("count", 20));
        $orderBy = $this->getOr("order_by", "id");
        $order = $this->getOr("order", "desc");
        $date = $_GET["date"];

        $count = AuthorStats::getCount($date);
        $maxPage = floor($count / $perPage);
        $page = clamp(0, $maxPage, $this->getOr("page", 0));
        $records = $count > 0 ? AuthorStats::getList($perPage, $page * $perPage, $orderBy, $order, $date) : [];

        $db->close();
        $this->cancel([
            "page" => intval($page),
            "maxPage" => $maxPage,
            "records" => array_map(function ($author) {
                return [
                    "id" => intval($author["id"]),
                    "name" => $author["name"],
                    "comments" => intval($author["comments"]),
                    "likes" => intval($author["likes"]),
                    "downloads" => intval($author["downloads"])
                ];
            }, $records)
        ]);
    }
}