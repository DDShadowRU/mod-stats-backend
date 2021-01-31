<?php


namespace ddcompany\api;

use ddcompany\MathHelper;
use ddcompany\MySqlHelper;
use ddcompany\stats\ModStats;

class APIMods extends AbstractAPI
{
    function run(array $params)
    {
        parent::run($params);

        $db = MySqlHelper::connect();
        $perPage = MathHelper::clamp(5, 20, $this->getOr("count", 20));
        $orderBy = $this->getOr("order_by", "id");
        $order = $this->getOr("order", "desc");
        $author = $this->getOr("author", null);
        $date = $_GET["date"];

        $count = ModStats::getCount($author, $date);
        $maxPage = floor($count / $perPage);
        $page = MathHelper::clamp(0, $maxPage, $this->getOr("page", 0));
        $records = $count > 0 ? ModStats::getList($perPage, $page * $perPage, $orderBy, $order, $author, $date) : [];

        $ids = implode(",", array_map(function ($author) {
            return $author["id"];
        }, $records));
        $list = $this->fetchJson("https://icmods.mineprogramming.org/api/list?lang=en&ids=$ids");

        $db->close();
        $this->cancel([
            "page" => intval($page),
            "maxPage" => $maxPage,
            "records" => array_map(function ($key, $item) use ($list) {
                $mod = $list[$key];
                return [
                    "id" => intval($item["id"]),
                    "name" => $mod->title,
                    "description" => $mod->description,
                    "icon" => "https://icmods.mineprogramming.org/api/img/" . $mod->icon,
                    "author" => intval($item["author"]),
                    "comments" => intval($item["comments"]),
                    "likes" => intval($item["likes"]),
                    "downloads" => intval($item["downloads"])
                ];
            }, array_keys($records), $records),
        ]);
    }
}