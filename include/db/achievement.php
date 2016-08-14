<?php

class AchievementDb {

    public static function DexCollect($num) {
        $seen = DB::result_first('SELECT COUNT(*) FROM pkm_mypokedex WHERE user_id = ' . $GLOBALS['user']['user_id']);
        return ($seen >= $num);
    }

    public static function __1() { return self::DexCollect(50); }

    public static function __2() { return self::DexCollect(100); }

    public static function __3() { return self::DexCollect(200); }

    public static function __4() { return self::DexCollect(300); }

    public static function __5() { return self::DexCollect(400); }

    public static function __6() { return self::DexCollect(500); }

    public static function __7() { return self::DexCollect(600); }

    public static function __8() { return self::DexCollect(649); }

}