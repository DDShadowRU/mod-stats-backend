<?php

namespace ddcompany\api;

use ddcompany\Period;
use ddcompany\stats\AuthorStats;

class APIAuthor extends AbstractAPI
{
    function run(array $params)
    {
        parent::run($params);
        $authorId = $params["id"];
        $this->requirePresent($authorId, "Invalid author id");

        $author = AuthorStats::of($authorId);
        $this->cancel([
            "id" => intval($authorId),
            "downloads" => [
                "month" => $author->getDownloads(Period::MONTH),
                "week" => $author->getDownloads(Period::WEEK),
                "day" => $author->getDownloads(Period::DAY),
            ],
            "likes" => [
                "month" => $author->getLikes(Period::MONTH),
                "week" => $author->getLikes(Period::WEEK),
                "day" => $author->getLikes(Period::DAY),
            ],
            "days" => $author->getDays()
        ]);
    }
}