<?php

namespace ddcompany;

class APIAuthor extends AbstractAPI
{
    function run(array $params)
    {
        parent::run($params);
        $authorId = $params["id"];
        $this->requirePresent($authorId, "Invalid author id");

        $db = MySqlHelper::connect();
        $this->cancel([
            "id" => intval($authorId),
            "downloads" => [
                "month" => MySqlHelper::getDownloadsInMonthByAuthor($db, $authorId),
                "week" => MySqlHelper::getDownloadsInWeekByAuthor($db, $authorId),
                "day" => MySqlHelper::getDownloadsInDayByAuthor($db, $authorId),
            ],
            "likes" => [
                "month" => MySqlHelper::getLikesInMonthByAuthor($db, $authorId),
                "week" => MySqlHelper::getLikesInWeekByAuthor($db, $authorId),
                "day" => MySqlHelper::getLikesInDayByAuthor($db, $authorId),
            ],
            "days" => MySqlHelper::getAuthorStats($db, $authorId)
        ]);
    }
}