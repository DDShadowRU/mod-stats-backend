<?php


namespace ddcompany;


use InvalidArgumentException;

class Period
{
    const MONTH = 0;
    const WEEK = 1;
    const DAY = 2;

    public static function getDates(int $period): array
    {
        $startDate = null;
        $endDate = null;

        switch ($period) {
            case self::DAY:
                break;
            case self::WEEK:
                $startDate = date('Y-m-d', strtotime('-7 days'));
                $endDate = date("Y-m-d");
                break;
            case self::MONTH:
                $startDate = date("Y-m-d", strtotime("-30 days"));
                break;
            default:
                throw new InvalidArgumentException("Invalid period");
        }

        return array($startDate, $endDate);
    }
}