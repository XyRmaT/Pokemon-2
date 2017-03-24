<?php

class Database {

    protected static $db;
    protected static $query_num;

    public static function connect($host, $user, $password, $database, $charset = 'utf8') {

        self::$db = new mysqli($host, $user, $password, $database);
        self::$db->query('SET NAMES ' . $charset);

        if(self::$db->connect_errno)
            echo 'Failed to connect to MySQL: (' . self::$db->connect_errno . ') ' . self::$db->connect_error;

    }

    public static function query($sql, $values = []) {
        $query = mysqli_query(self::$db, vsprintf($sql, $values));
        if(mysqli_errno(self::$db))
            echo 'Failed to connect to MySQL: (' . mysqli_errno(self::$db) . ') ' . mysqli_error(self::$db);
        self::$query_num++;
        return $query;
    }

    public static function fetch_first($sql, $values = []) {
        return self::fetch(self::query($sql, $values));
    }

    public static function fetch($resource, $type = MYSQLI_ASSOC) {
        return mysqli_fetch_array($resource, $type);
    }

    public static function result_first($sql, $values = []) {
        return self::fetch(self::query($sql, $values), MYSQLI_NUM)[0];
    }

    public static function prepare($statement) {
        return mysqli_prepare(self::$db, $statement);
    }

    public static function affected_rows() {
        return mysqli_affected_rows(self::$db);
    }

    public static function get_query_num() {
        return self::$query_num;
    }

    public static function insert($table, $value, $update = FALSE) {
        if(!$value) return FALSE;

        if(!isset($value[0])) {
            $fields = array_keys($value);
            $value = [$value];
        } else {
            $fields = array_keys(reset($value));
        }

        $values = '';
        foreach($value as $row) {
            $temp = '(';
            foreach($row as $field => $item) {
                $temp .= ($temp === '(' ? '' : ',') . ($item[0] === DB_FIELD_STRING ? '\'' . $item[1] . '\'' : $item[1]);
            }
            $values .= ($values === '' ? '' : ',') . $temp .')';
        }

        $extra = '';
        if($update) {
            foreach($fields as $field) {
                $extra .= (!$extra ? '' : ',') . $field . ' = VALUES(' . $field . ')';
            }
            $extra = ' ON DUPLICATE KEY UPDATE ' . $extra;
        }

        //echo 'INSERT INTO ' . $table . ' (' . implode(',', $fields) . ') VALUES ' . $values . $extra;
        return DB::query('INSERT INTO ' . $table . ' (' . implode(',', $fields) . ') VALUES ' . $values . $extra);
    }

    public static function insertID() {
        return mysqli_insert_id(self::$db);
    }

}

class DBBuilder {
    private static $table = '';
    private static $where = '';
    private static $join = '';

    public static function table(string $table) {
        self::$table = $table;
    }

    public static function where(array $values) {
        self::$where = '';
        if(!is_array($values[0])) {
            $values = [$values[0]];
        }

        $temp = [];
        foreach($values as $group) {
            $tempb = [];
            foreach($group as $key => $value) {
                $tempb[] = $key . ' ' . $value[0] . ' ' . self::valueFilter($value[1], $value[2]);
            }
            $temp[] = implode(' AND ', $tempb);
        }
    }

    public static function valueFilter($type, $str) {
        return $type === DB_FIELD_STRING ? '\'' . $str . '\'' : $str;
    }
}

class DB extends Database {
}