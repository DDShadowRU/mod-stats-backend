<?php


namespace ddcompany;

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

        $count = $db->query("SELECT count(*) FROM authors")->fetch_array()[0];
        $maxPage = floor($count / $perPage);
        $page = clamp(0, $maxPage, $this->getOr("page", 0));
        $records = MySqlHelper::getAuthorList($db, $perPage, $page * $perPage, $orderBy, $order, null);

        $db->close();
        $this->cancel([
            "page" => $page,
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