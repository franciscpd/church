<?php
class TUtil
{
    public static function getSlugStr($str)
    {
        $str = strtolower($str);
        $str = preg_replace("/[^a-z0-9_\s-]/", "", $str);
        $str = preg_replace("/[\s-]+/", " ", $str);
        $str = preg_replace("/[\s_]/", "-", $str);
        return $str;
    }
}
?>