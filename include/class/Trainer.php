<?php

class Trainer {

    public static $setting = [];
    private static $resources = [];

    /**
     * Generates initial trainer data for a user.
     * @return bool|int
     */
    public static function generate () {

        // Use prepared statement to find out if the trainer id already exists
        if (!($stmt = DB::prepare('SELECT trainer_id FROM pkm_trainerdata WHERE trainer_id = ? AND secret_id = ?'))) return FALSE;

        $stmt->bind_param('s', $trainer_id);
        $stmt->bind_param('s', $secret_id);
        while (1) {
            $trainer_id = mt_rand(0x0, 0xFFFF);
            $secret_id  = mt_rand(0x0, 0xFFFF);
            if ($stmt->execute() && $stmt->fetch()) break;
        }

        // Insert newly genderated trainer data, also add an stat entry for the trainer
        DB::insert('pkm_trainerdata', [
            'trainer_id'             => [DB_FIELD_NUMBER, $trainer_id],
            'secret_id'              => [DB_FIELD_NUMBER, $secret_id],
            'time_begin'             => [DB_FIELD_NUMBER, $_SERVER['REQUEST_TIME']],
            'time_happiness_checked' => [DB_FIELD_NUMBER, $_SERVER['REQUEST_TIME']]
        ]);
        DB::insert('pkm_trainerstat', ['user_id' => $user_id = DB::insertID()]);

        return (int) $user_id;

    }


    /**
     * Generate a trainer card
     * @param $trainer
     * @param bool $force_refresh
     * @return string
     */
    public static function getCard ($trainer, $force_refresh = FALSE) {

        global $lang;

        $path = ROOT_CACHE . '/image/trainer-card/' . base_convert(hash('joaat', $trainer['user_id']), 16, 32) . '.png';

        if (!$force_refresh && file_exists($path) && filemtime($path) + 600 > $_SERVER['REQUEST_TIME']) return $path;

        $background_resource = imagecreatefromjpeg(ROOT_IMAGE . '/trainer-card/background-1.jpg');
        $avatar_resource     = imagecreatefrompng($trainer['avatar']);
        if (empty(self::$resources['pokemon_icon']))
            self::$resources['pokemon_icon'] = imagecreatefrompng(ROOT_IMAGE . '/pokemon-icon/sheet-32x32.png');
        if (empty(self::$resources['egg_icon']))
            self::$resources['egg_icon'] = imagecreatefrompng(ROOT_IMAGE . '/pokemon-icon/0.png');

        imagecopy($background_resource, $avatar_resource, 45, 10, 0, 0, 40, 40);

        $font_path       = ROOT . '/include/font/yahei-bold.ttf';
        $trainer['rank'] = '#' . $trainer['rank'];
        $text_boxes      = [
            imagettfbbox(9, 0, $font_path, $trainer['trainer_name']),
            imagettfbbox(9, 0, $font_path, $trainer['rank']),
            imagettfbbox(9, 0, $font_path, $trainer['level']),
            imagettfbbox(9, 0, $font_path, $trainer['dex_collected']),
            imagettfbbox(9, 0, $font_path, 0),
            imagettfbbox(9, 0, $font_path, $lang['rank']),
            imagettfbbox(9, 0, $font_path, $lang['level']),
            imagettfbbox(9, 0, $font_path, $lang['pokedex']),
            imagettfbbox(9, 0, $font_path, $lang['achievement']),
        ];
        $left_offsets    = [
            (130 - $text_boxes[0][2] + $text_boxes[0][0]) / 2,
            160 + ($text_boxes[5][2] - $text_boxes[5][0] - $text_boxes[1][2] + $text_boxes[1][0]) / 2,
            220 + ($text_boxes[6][2] - $text_boxes[6][0] - $text_boxes[2][2] + $text_boxes[2][0]) / 2,
            280 + ($text_boxes[7][2] - $text_boxes[7][0] - $text_boxes[3][2] + $text_boxes[3][0]) / 2,
            340 + ($text_boxes[8][2] - $text_boxes[8][0] - $text_boxes[4][2] + $text_boxes[4][0]) / 2,
        ];

        // TODO: achievement count
        Kit::imagettftextblur($background_resource, 9, 0, 161, 51, 0x000000, $font_path, $lang['rank']);
        Kit::imagettftextblur($background_resource, 9, 0, 221, 51, 0x000000, $font_path, $lang['level']);
        Kit::imagettftextblur($background_resource, 9, 0, 281, 51, 0x000000, $font_path, $lang['pokedex']);
        Kit::imagettftextblur($background_resource, 9, 0, 341, 51, 0x000000, $font_path, $lang['achievement']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[0] + 1, 84, 0x000000, $font_path, $trainer['trainer_name']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[1] + 1, 31, 0x000000, $font_path, $trainer['rank']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[2] + 1, 31, 0x000000, $font_path, $trainer['level']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[3] + 1, 31, 0x000000, $font_path, $trainer['dex_collected']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[4] + 1, 31, 0x000000, $font_path, 0);
        imagettftext($background_resource, 9, 0, 160, 50, 0xFFFFFF, $font_path, $lang['rank']);
        imagettftext($background_resource, 9, 0, 220, 50, 0xFFFFFF, $font_path, $lang['level']);
        imagettftext($background_resource, 9, 0, 280, 50, 0xFFFFFF, $font_path, $lang['pokedex']);
        imagettftext($background_resource, 9, 0, 340, 50, 0xFFFFFF, $font_path, $lang['achievement']);
        imagettftext($background_resource, 9, 0, $left_offsets[0], 83, 0xFFFFFF, $font_path, $trainer['trainer_name']);
        imagettftext($background_resource, 9, 0, $left_offsets[1], 30, 0xFFFFFF, $font_path, $trainer['rank']);
        imagettftext($background_resource, 9, 0, $left_offsets[2], 30, 0xFFFFFF, $font_path, $trainer['level']);
        imagettftext($background_resource, 9, 0, $left_offsets[3], 30, 0xFFFFFF, $font_path, $trainer['dex_collected']);
        imagettftext($background_resource, 9, 0, $left_offsets[4], 30, 0xFFFFFF, $font_path, 0);

        $query = DB::query('SELECT nat_id FROM pkm_mypkm WHERE user_id = ' . $trainer['user_id'] . ' AND location IN (' . LOCATION_PARTY . ') LIMIT 6');
        $i     = 0;
        while ($info = DB::fetch($query)) {
            if (!$info['nat_id']) {
                imagecopy($background_resource, self::$resources['egg_icon'], 160 + $i * 36, 63, 0, 0, 32, 32);
            } else {
                imagecopy($background_resource, self::$resources['pokemon_icon'], 160 + $i * 36, 63, ($info['nat_id'] % 12) * 32, floor($info['nat_id'] / 12) * 32, 32, 32);
            }
            ++$i;
        }

        ob_start();
        imagepng($background_resource);
        imagedestroy($background_resource);
        imagedestroy($avatar_resource);
        $content = ob_get_contents();
        ob_clean();
        $handle = fopen($path, 'w+');
        fwrite($handle, $content);
        fclose($handle);

        return $path;
    }


    public static function getAvatar ($user_id, $refresh = FALSE) {

        $filenameh = base_convert(hash('joaat', $user_id), 16, 32);
        $path      = ROOT_CACHE . '/avatar/' . $filenameh . '.png';

        if (file_exists($path) && $refresh === FALSE) return $path;

        $file  = glob(ROOT_IMAGE . '/avatar-part/skin*');
        $fileb = glob(ROOT_IMAGE . '/avatar-part/eye*');
        $filec = glob(ROOT_IMAGE . '/avatar-part/cos*');
        $filed = glob(ROOT_IMAGE . '/avatar-part/hair*');
        $filee = glob(ROOT_IMAGE . '/avatar-part/bangs*');
        $filef = glob(ROOT_IMAGE . '/avatar-part/hat*');
        $fileg = glob(ROOT_IMAGE . '/avatar-part/dec*');

        $img  = imagecreatefrompng($file[array_rand($file)]);
        $imgb = imagecreatefrompng($fileb[array_rand($fileb)]);
        $imgc = imagecreatefrompng($filec[array_rand($filec)]);
        $imgd = imagecreatefrompng($filed[array_rand($filed)]);
        $imge = imagecreatefrompng($filee[array_rand($filee)]);
        $imgf = imagecreatefrompng($filef[array_rand($filef)]);
        $imgg = imagecreatefrompng($fileg[array_rand($fileg)]);

        imagecopy($img, $imgb, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgc, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgd, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imge, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgf, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgg, 0, 0, 0, 0, 40, 40);

        $translayer = imagecreate(40, 40);
        $trans      = imagecolorallocate($translayer, 255, 255, 255);

        imagecolortransparent($translayer, $trans);
        imagecopy($translayer, $img, 0, 0, 0, 0, 40, 40);
        imagetruecolortopalette($translayer, TRUE, 256);
        imageinterlace($translayer);

        $img = $translayer;

        ob_start();
        imagepng($img);
        imagedestroy($img);
        $content = ob_get_contents();
        ob_clean();
        $handle = fopen($path, 'w+');
        fwrite($handle, $content);
        fclose($handle);
        return $path;

    }


    public static function fetch ($user_id) {

        $trainer = DB::fetch_first('SELECT user_id, trainer_name, trainer_id, exp, level, has_starter, currency, box_quantity, time_happiness_checked, is_battling, has_new_message, time_last_visit,
                                    FIND_IN_SET(exp, (SELECT GROUP_CONCAT(exp ORDER BY exp DESC) FROM pkm_trainerdata)) rank
                                    FROM pkm_trainerdata t
                                    WHERE user_id = ' . $user_id);

        if (!$trainer) return FALSE;

        $trainer['avatar']        = Obtain::Avatar($trainer['user_id']);
        $trainer['stat']          = DB::fetch_first('SELECT * FROM pkm_trainerstat WHERE user_id = ' . $user_id);
        $trainer['dex_collected'] = DB::result_first('SELECT COUNT(*) FROM pkm_mypokedex WHERE user_id = ' . $trainer['user_id'] . ' AND is_owned = 1');
        $trainer['exp_required']  = self::getRequiredExp($trainer['level'] + 1);
        $trainer['card']          = self::getCard($trainer);

        return $trainer;

    }

    /**
     * Update trainer's exp
     * @param $user_id
     * @param int $exp_adding
     */
    public static function addExp ($user_id, $exp_adding) {
        if (!$exp_adding) return;

        $exp = DB::result_first('SELECT exp FROM pkm_trainerdata WHERE user_id = ' . $user_id);
        $exp = max(0, $exp + $exp_adding);

        DB::insert('pkm_trainerdata', [
            'user_id' => [DB_FIELD_NUMBER, $user_id],
            'exp'     => [DB_FIELD_NUMBER, $exp],
            'level'   => [DB_FIELD_NUMBER, self::getLevel($exp)]
        ], TRUE);
    }


    /**
     * Gets trainer's level according to the formula:
     * FLOOR((2 * E)^0.25), where E = trainer's exp
     * @param $exp
     * @return float
     */
    public static function getLevel ($exp) {
        return floor(pow(2 * $exp, 1 / 4));
    }


    /**
     * Gets the total amount of exp that trainer needs
     * to next a certain level according to the formula:
     * CEIL(0.5 * L^4), where L = trainer's level
     * @param $level
     * @return float
     */
    public static function getRequiredExp ($level) {
        return ceil(0.5 * pow($level, 4));
    }

    // TODO
    public static function Item ($action, $user_id, $iid, $num, $curnum = 'UNKNOWN', $limit = 0) {

        if ($curnum === 'UNKNOWN') {
            $curnum = intval(DB::result_first('SELECT quantity FROM pkm_myitem WHERE item_id = ' . $iid . ' AND user_id = ' . $user_id));
        }

        if ($action === 'DROP' && $curnum - $num <= 0) {
            if ($curnum - $num < 0) return FALSE;
            DB::delete('pkm_myitem', [
                'item_id' => $iid,
                'user_id' => $user_id
            ]);
        } elseif ($action === 'DROP') {
            DB::query('UPDATE pkm_myitem SET quantity = ' . ($curnum - $num) . ' WHERE user_id = ' . $user_id . ' AND item_id = ' . $iid);
        } elseif ($action === 'OBTAIN') {
            if ($limit !== 0 && $curnum + $num > $limit) return FALSE;
            if (empty($curnum)) {
                DB::query('INSERT INTO pkm_myitem (item_id, quantity, user_id) VALUES (' . $iid . ', ' . $num . ', ' . $user_id . ')');
            } else {
                DB::query('UPDATE pkm_myitem SET quantity = ' . ($curnum + $num) . ' WHERE item_id = ' . $iid . ' AND user_id = ' . $user_id);
            }
        }

        return TRUE;

    }


    public static function updateStat ($user_id, $stat, $value) {
        DB::insert('pkm_trainerdata', [
            'user_id' => [DB_FIELD_NUMBER, $user_id],
            $stat     => [DB_FIELD_ORIGIN, $stat . ' + ' . $value]
        ], TRUE);
    }

}