<?php

namespace ddcompany;

use mysqli;

class APIMod extends AbstractAPI
{
    function run(array $params)
    {
        parent::run($params);
        $modId = $params["id"];
        $this->requirePresent($modId, "Invalid mod id");

        $db = MySqlHelper::connect();
        $this->cancel([
            "id" => intval($modId),
            "downloads" => [
                "month" => MySqlHelper::getDownloadsInMonth($db, $modId),
                "week" => MySqlHelper::getDownloadsInWeek($db, $modId),
                "day" => MySqlHelper::getDownloadsInDay($db, $modId),
            ],
            "likes" => [
                "month" => MySqlHelper::getLikesInMonth($db, $modId),
                "week" => MySqlHelper::getLikesInWeek($db, $modId),
                "day" => MySqlHelper::getLikesInDay($db, $modId),
            ],
            "days" => $this->getDays($db, $modId)
        ]);
    }

    function getDays(mysqli $db, int $modId)
    {
        return array_map(function ($day) {
            return [
                "date" => $day["date"],
                "downloads" => intval($day["downloads"]),
                "likes" => intval($day["likes"]),
                "downloads_added" => intval($day["downloads_added"]),
                "likes_added" => intval($day["likes_added"])
            ];
        }, MySqlHelper::getModStats($db, $modId));
    }
}