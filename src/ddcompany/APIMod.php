<?php

namespace ddcompany;

use ddcompany\stats\ModStats;

class APIMod extends AbstractAPI
{
    function run(array $params)
    {
        parent::run($params);
        $modId = $params["id"];
        $this->requirePresent($modId, "Invalid mod id");

        $mod = ModStats::of($modId);
        $this->cancel([
            "id" => intval($modId),
            "downloads" => [
                "month" => $mod->getDownloads(Period::MONTH),
                "week" => $mod->getDownloads(Period::WEEK),
                "day" => $mod->getDownloads(Period::DAY),
            ],
            "likes" => [
                "month" => $mod->getLikes(Period::MONTH),
                "week" => $mod->getLikes(Period::WEEK),
                "day" => $mod->getLikes(Period::DAY),
            ],
            "days" => $mod->getDays()
        ]);
    }
}