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


    public static function JsonConvert($array) {
        return json_encode($array, defined('DEBUG_MODE') && DEBUG_MODE ? JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE : JSON_NUMERIC_CHECK);
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
            require_once ROOT . '/include/' . $type . '/' . $val . '.php';
        }
        return TRUE;
    }

    public static function memoryFormat($size) {
        $units = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        $pow   = $size ? log($size) / log(1024) : 0;
        $size /= pow(1024, $pow);
        return round($size, 2) . ' ' . $units[(int) $pow];
    }

    public static function SendMessage($title, $content, $from, $to) {
        DB::query('INSERT INTO pkm_myinbox (title, content, sender_user_id, receiver_user_id, time_sent) VALUES (\'' . $title . '\', \'' . $content . '\', ' . $from . ', ' . $to . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('UPDATE pkm_trainerdata SET has_new_message = 1 WHERE user_id = ' . $to);
    }

    public static function NumberFormat($num) {
        return ($num > 999999) ? round($num / 1000000) . 'm' : (($num > 999) ? round($num / 1000) . 'k' : $num);
    }

    public static function stringCut($string, $length, $dot = ' ...') {

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

    public static function FetchFields($fields) {
        return implode(',', array_unique(explode(',', implode(',', $fields))));
    }

    /**
     * This was originally written by Andrew G. but since it was using the width and height for whole
     * image to do calculation which will cause plenty of unecessary operations so the performance was
     * terriblly slow. I optimized it by bounding the scanning pixels into a certain range, and optimized
     * for loop declaration a bit. Since intensity is not used by me so I removed related code as well.
     * The performance now increased by 90%.
     * To see changes, compare the following code with the source code in Git.
     * @author    Andrew G. Johnson <andrew@andrewgjohnson.com>, Sam Y. <pokeuniv@gmail.com>
     * @link      http://github.com/andrewgjohnson/imagettftextblur
     * @param      $image
     * @param      $size
     * @param      $angle
     * @param      $x
     * @param      $y
     * @param      $color
     * @param      $fontfile
     * @param      $text
     * @return array
     */
    public static function imagettftextblur(&$image, $size, $angle, $x, $y, $color, $fontfile, $text) {
        $text_shadow_image   = imagecreatetruecolor($image_x = imagesx($image), $image_y = imagesy($image));
        $text_box            = imagettfbbox(9, 0, $fontfile, $text);
        $text_shadow_image_x = min($x + $text_box[2] - $text_box[0] + 5, $image_x);
        $text_shadow_image_y = min($y + $text_box[3] - $text_box[1] + 5, $image_y);

        imagefill($text_shadow_image, 0, 0, imagecolorallocate($text_shadow_image, 0x00, 0x00, 0x00));
        imagettftext($text_shadow_image, $size, $angle, $x, $y, imagecolorallocate($text_shadow_image, 0xFF, 0xFF, 0xFF), $fontfile, $text);
        imagefilter($text_shadow_image, IMG_FILTER_GAUSSIAN_BLUR);

        for($x_offset = $x - 10; $x_offset < $text_shadow_image_x; $x_offset++) {
            for($y_offset = $y - 10; $y_offset < $text_shadow_image_y; $y_offset++) {
                $visibility = (imagecolorat($text_shadow_image, $x_offset, $y_offset) & 0xFF) / 255;
                if($visibility > 0)
                    imagesetpixel($image, $x_offset, $y_offset, imagecolorallocatealpha($image, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF, (1 - $visibility) * 127));
            }
        }
        imagedestroy($text_shadow_image);
    }

}

class App {

    public static function Initialize() {

        global $user, $start_time, $lang, $system;

        $user       = $lang = $system = [];
        $start_time = microtime(TRUE);

        include ROOT . '/include/language/' . LANGUAGE . '.php';
        include ROOT . '/include/constant/pokemon.php';
        include ROOT . '/include/constant/common.php';
        include ROOT . '/include/data/config.php';
        include ROOT . '/include/class/database.php';
        include ROOT . '/include/class/cache.php';

        // Connect to the database
        DB::connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);

        // Check login status & set data
        $user = self::loginByToken($_COOKIE['token'] ?? '');

    }

    public static function loginByEmail($email, $password) {
        $user = DB::fetch_first('SELECT * FROM pkm_trainerdata WHERE email = \'' . addslashes($email) . '\' AND password = \'' . self::encryptPassword($password) . '\'');

        if(!$user) return [];

        $token = self::generateToken();
        DB::insert('pkm_usertoken', [
            'user_id'     => [DB_FIELD_NUMBER, $user['user_id']],
            'token'       => [DB_FIELD_STRING, $token],
            'expire_time' => [DB_FIELD_NUMBER, 0]
        ], TRUE);

        setcookie('token', $token, $_SERVER['REQUEST_TIME'] + 99999999);
        return $user;
    }

    public static function loginByToken($token) {
        if(!$token) return [];
        $user = DB::fetch_first('SELECT t.* FROM pkm_usertoken u 
            LEFT JOIN pkm_trainerdata t ON t.user_id = u.user_id 
            WHERE u.token = \'' . addslashes($token) . '\'');
        return $user ?: [];
    }

    private static function generateToken() {
        return md5(mt_rand());
    }

    private static function encryptPassword($password) {
        return md5(md5($password));
    }

    public static function register($email, $password, $trainer_name) {

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return ERROR_NOT_EMAIL;

        if(preg_match('/[\'\"]/', $trainer_name)) return ERROR_INVALID_TRAINER_NAME;

        $existed = DB::fetch_first('SELECT user_id, email, trainer_name FROM pkm_trainerdata WHERE email = \'' . $email . '\' OR trainer_name = \'' . $trainer_name . '\'');
        if($existed) {
            return $existed['email'] === $email ? ERROR_DUPLICATE_EMAIL : ERROR_DUPLICATE_TRAINER_NAME;
        }

        $user_id = Trainer::generate();
        if(!$user_id) return FALSE;

        $password = self::encryptPassword($password);
        DB::insert('pkm_trainerdata', [
            'user_id'      => [DB_FIELD_NUMBER, $user_id],
            'trainer_name' => [DB_FIELD_STRING, $trainer_name],
            'password'     => [DB_FIELD_STRING, $password],
            'email'        => [DB_FIELD_STRING, $email]
        ], TRUE);

        self::loginByEmail($email, $password);

        return TRUE;
    }

    public static function CreditsUpdate($user_id, $value, $type = 'CURRENCY', $isFixed = FALSE) {
        // TODO: STAB
    }

}