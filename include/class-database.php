<?php

class Database {

    protected static $db;

    public static function connect() {

        include_once './data-config.php';

        self::$db = new mysqli(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME);

        self::$db->query('SET NAMES ' . UC_DBCHARSET);

        if(self::$db->connect_errno) echo 'Failed to connect to MySQL: (' . self::$db->connect_errno . ') ' . self::$db->connect_error;

    }

    public static function query($sql, $values = []) {
        return mysqli_query(self::$db, vsprintf($sql, $values));
    }

    public static function fetch($resource, $type = MYSQLI_ASSOC) {
        return mysqli_fetch_array($resource, $type);
    }

    public static function fetch_first($sql, $values = []) {
        return self::fetch(mysqli_query(self::$db, vsprintf($sql, $values)));
    }

    public static function result_first($sql, $values = []) {
        return self::fetch(mysqli_query(self::$db, vsprintf($sql, $values)), MYSQLI_NUM)[0];
    }

    public static function prepare($statement) {
        return mysqli_prepare(self::$db, $statement);
    }

    public static function affected_rows() {
        return mysqli_affected_rows(self::$db);
    }

}

class DB extends Database { }