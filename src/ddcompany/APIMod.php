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
            "days" => MySqlHelper::getModStats($db, $modId)
        ]);
    }
}