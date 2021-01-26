<?php


namespace ddcompany;


class MathHelper
{
    static function clamp($min, $max, $value)
    {
        return max($min, min($max, $value));
    }
}