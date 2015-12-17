<?php


class Kit {

    /*
        Author: mail@theopensource.com (01-Feb-2006 03:34)
        $array: the array you want to sort
        $by: the associative array name that is one level deep
        example: name
        $order: ASC or DESC
        $type: quantity or str
    */

    public static function ColumnSort($array, $by, $order, $type) {

        $sortby   = 'sort' . $by;
        $firstval = current($array);
        $vals     = array_keys($firstval);

        foreach($vals as $init) {

            $keyname  = 'sort' . $init;
            $$keyname = [];
        }

        foreach($array as $key => $row) {

            foreach($vals as $names) {

                $keyname    = 'sort' . $names;
                $test       = [];
                $test[$key] = $row[$names];
                $$keyname   = array_merge($$keyname, $test);

            }

        }

        array_multisort($$sortby, ($order === 'DESC') ? SORT_DESC : SORT_ASC, ($type === 'quantity') ? SORT_NUMERIC : SORT_STRING, $array);

        return $array;
    }


    public static function ArrayIconv($from, $to, &$array) {

        if(is_array($array)) {

            foreach($array as &$k) {

                self::ArrayIconv($from, $to, $k);

            }

        } else {

            $array = iconv($from, $to, $array);

        }

        return $array;

    }


    public static function JsonConvert($array) {

        //return json_encode(self::ArrayIconv('gbk', 'utf-8//IGNORE', $array));
        return json_encode($array);

    }


    public static function MultiPage($limit, $count = 0, $ulproperty = '', $tag = 'a') {

        $pagenum    = !empty($_GET['pagenum']) ? max(intval($_GET['pagenum']), 1) : 1;
        $start      = ($pagenum - 1) * $limit;
        $maxpagenum = ($count === 0) ? 9999 : ceil($count / $limit);

        $multipage = '<ul class="flt-r mp"' . ($ulproperty ? ' ' . $ulproperty : '') . '>' . (($pagenum > 1) ? '<li data-pagenum="' . max($pagenum - 1, 1) . '">&lt;&lt;</li>' : '');

        for($i = max($pagenum - 5, 1), $j = min($pagenum + 5, $maxpagenum); $i <= $j; $i++) {

            $multipage .= '<li data-pagenum="' . $i . '"' . (($i == $pagenum) ? ' class="cur"' : '') . '>' . $i . '</li>';

        }

        $multipage .= (($pagenum < $maxpagenum) ? '<li data-pagenum="' . min($pagenum + 1, $maxpagenum) . '">&gt;&gt;</li>' : '') . '</ul>';

        return [
            'start'   => $start,
            'limit'   => $limit,
            'display' => $multipage
        ];

    }


    public static function ColumnSearch($array, $column, $value) {
        if(is_array($array)) {
            foreach($array as $key => $val) {
                if($val[$column] == $value) return $key;
            }
        }
        return FALSE;
    }

    public static function Library($type, $file) {
        foreach($file as $val) {
            if($type === 'class' || $type === 'db')
                require_once ROOT . '/include/' . $type . '-' . $val . '.php';
        }
        return TRUE;
    }

    public static function Memory($size) {
        $i    = 0;
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return ($size <= 0 || round($size / pow(1024, ($i = (int)floor(log($size, 1024)))), 2)) . ' ' . $unit[$i];
    }

    public static function SendMessage($title, $content, $from, $to) {
        DB::query('INSERT INTO pkm_myinbox (title, content, uid_sender, uid_receiver, time_sent) VALUES (\'' . $title . '\', \'' . $content . '\', ' . $from . ', ' . $to . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('UPDATE pkm_trainerdata SET has_new_message = 1 WHERE uid = ' . $to);
    }

    public static function NumberFormat($num) {
        return ($num > 999999) ? round($num / 1000000) . 'm' : (($num > 999) ? round($num / 1000) . 'k' : $num);
    }

    public static function cutstr($string, $length, $dot = ' ...') {

        if(strlen($string) <= $length) return $string;

        $string = str_replace(['&amp;', '&quot;', '&lt;', '&gt;'], ['&', '"', '<', '>'], $string);
        $strcut = '';

        if(strtolower(UC_CHARSET) == 'utf-8') {
            $n = $tn = $noc = 0;
            while($n < strlen($string)) {
                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) $tn = 1 && ++$n && ++$noc;
                elseif(194 <= $t && $t <= 223) $tn = 2 && $n += 2 && $noc += 2;
                elseif(224 <= $t && $t < 239) $tn = 3 && $n += 3 && $noc += 2;
                elseif(240 <= $t && $t <= 247) $tn = 4 && $n += 4 && $noc += 2;
                elseif(248 <= $t && $t <= 251) $tn = 5 && $n += 5 && $noc += 2;
                elseif($t == 252 || $t == 253) $tn = 6 && $n += 6 && $noc += 2;
                else $n++;
                if($noc >= $length) break;
            }
            if($noc > $length) $n -= $tn;
            $strcut = substr($string, 0, $n);
        } else {
            for($i = 0; $i < $length; $i++)
                $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
        $strcut = str_replace(['&', '"', '<', '>'], ['&amp;', '&quot;', '&lt;', '&gt;'], $strcut);

        return $strcut . $dot;
    }

}

class App {

    public static function Initialize() {

        global $user, $system;

        $user = $system = [];

        // Include all the required files, including databse, config data, cache and UC
        include_once ROOT . '/include/data-config.php';
        include_once ROOT . '/../bbs/uc_client/client.php';
        include_once ROOT . '/include/class-database.php';
        include_once ROOT . '/include/class-cache.php';
        include_once ROOT . '/include/function-template.php';

        // Connect to the database
        DB::connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);

        // Check login status & set data
        if(!self::IsLoggedIn($_COOKIE['authcode'])) $user = ['uid' => 0];

    }

    private static function IsLoggedIn($authcode) {

        global $trainer;
        list($username, $password, $questionId, $answer) = explode(',,', uc_authcode($authcode, 'DECODE'));

        if(!$username || !$password) return FALSE;

        list($user['uid'], $user['username'], , $user['email']) = uc_user_login($username, $password, 0, $questionId && $answer, $questionId, $answer);

        // Just a side note that -1 = not existed, -2 = wrong password
        return $user['uid'] > 0;

    }

    public static function Login($username, $password, $questionId = 0, $answer = '') {
        global $user, $synclogin;
        list($user['uid'], $user['username'], , $user['email']) = uc_user_login($username, $password, 0, $questionId && $answer, $questionId, $answer);
        if($user['uid'] <= 0) return FALSE;
        $synclogin = uc_user_synlogin($user['uid']);
        setcookie('authcode', uc_authcode($username . ',,' . $password . ',,' . $questionId . ',,' . $answer, 'ENCODE'), $_SERVER['REQUEST_TIME'] + 99999999);
        return TRUE;
    }

    public static function CreditsUpdate($uid, $value, $type = 'CURRENCY', $isFixed = FALSE) {
        $field = $type === 'EXP' ? $GLOBALS['system']['exp_field'] : $GLOBALS['system']['currency_field'];
        if($isFixed)
            return DB::query('UPDATE pre_common_member_count SET ' . $field . ' = ' . $value . ' WHERE uid = ' . $uid);
        else
            return DB::query('UPDATE pre_common_member_count SET ' . $field . ' = ' . $field . ' + ' . $value . ' WHERE uid = ' . $uid);
    }

    private function GetUserIp() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach($matches[0] AS $xip) {
                if(!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

}