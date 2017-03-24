<?php

class ItemDb {

    public static $pokemon = [];
    public static $message = '';

    public static function __170() { # 力量之粉

        self::$pokemon['happiness'] -= min((self::$pokemon['happiness'] >= 200) ? 10 : 5, 255 - self::$pokemon['happiness']);
        self::$message = General::getText('medicine_sucks', [self::$pokemon], FALSE, TRUE);

    }

    public static function __171() { # 力量之根

        self::$pokemon['happiness'] -= min((self::$pokemon['happiness'] >= 200) ? 15 : 10, 255 - self::$pokemon['happiness']);
        self::$message = General::getText('medicine_sucks', [self::$pokemon], FALSE, TRUE);

    }

    public static function __186() { # 万能粉

        self::$pokemon['happiness'] -= min((self::$pokemon['happiness'] >= 200) ? 10 : 5, 255 - self::$pokemon['happiness']);
        self::$message = General::getText('medicine_sucks', [self::$pokemon], FALSE, TRUE);

    }

    public static function __192() { # 复活草

        self::$pokemon['happiness'] -= min((self::$pokemon['happiness'] >= 200) ? 20 : 15, 255 - self::$pokemon['happiness']);
        self::$message = General::getText('medicine_sucks', [self::$pokemon], FALSE, TRUE);

    }

}