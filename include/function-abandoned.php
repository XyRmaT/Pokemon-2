<?php

function LargeNum($num,$point=0) {
    $num = (string)$num;
    $lnum = sprintf('%.' . $point . 'f', $num);
    return $lnum;
}

function A2L($str, $str2, $str3) {
    $Result = $str * 27 * 27 + $str2 * 27 + $str3;
    return $Result;
}