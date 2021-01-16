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
                "month" => MySqlHelper::getDownloadsAddedInMonth($db, $modId),
                "week" => MySqlHelper::getDownloadsAddedInThisWeek($db, $modId),
                "day" => MySqlHelper::getDownloadsAddedInLastDay($db, $modId),
            ],
            "likes" => [
                "month" => MySqlHelper::getLikesAddedInMonth($db, $modId),
                "week" => MySqlHelper::getLikesAddedInThisWeek($db, $modId),
                "day" => MySqlHelper::getLikesAddedInLastDay($db, $modId),
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
        }, MySqlHelper::getStats($db, $modId));
    }
}