<?php

namespace ddcompany;

function clamp($min, $max, $value)
{
    return max($min, min($max, $value));
}