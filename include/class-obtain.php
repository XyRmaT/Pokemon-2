<?php

class Obtain {

    private static $hex = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
    private static $box = [];

    public static function MeetPlace($mtplace) {

        $mapname = DB::result_first('SELECT name_zh name FROM pkm_mapdata WHERE map_id = ' . $mtplace);

        if(!empty($mapname)) {

            return '在' . $mapname . '遇见。';

        } else {

            require ROOT . '/include/data-birthplace.php';

            return isset($birthplace[$mtplace]) ? $birthplace[$mtplace] : '……从石头里蹦出来的？';

        }

    }

    /*
        ObtainDepositBox
            Global variable: $system, $user
            First get the maximum box possible by using the formula: 
                user's boxes + initial boxes + 100
            Which get a number greater than or equal to 100
            Then obtain the amount of pokemon in each boxes and party the trainer have
    */

    public static function DepositBox($uid) {

        global $system, $trainer;

        $maxboxnum = $trainer['boxnum'] + $system['initial_box'] + 100;

        if(empty(self::$box)) {

            $query = DB::query('SELECT location, COUNT(*) total FROM pkm_mypkm WHERE uid = ' . $uid . ' AND (location IN (1, 2, 3, 4, 5, 6) OR location > 100) GROUP BY location');

            while($pokemon = DB::fetch($query)) {

                self::$box[$pokemon['location']] = $pokemon['total'];

            }

        }

        for($i = 1; $i <= $maxboxnum; $i++) {

            if(empty(self::$box[$i]) || $i > 100 && self::$box[$i] < $system['pkm_per_box']) {

                self::$box[$i] = isset(self::$box[$i]) ? self::$box[$i] + 1 : 1;

                return $i;

            }

            if($i === 6) $i = 100;

        }

        return FALSE;

    }

    public static function TypeName($type, $typeb = 0, $image = FALSE, $appendclass = '') {

        $typearr = ['', '火', '水', '草', '电', '普', '格', '飞', '虫', '毒', '岩', '地', '钢', '冰', '超', '恶', '鬼', '龙', '妖'];


        if(!$image) $result = !empty($typearr[$type]) ? $typearr[$type] . (!empty($typearr[$typeb]) ? '+' . $typearr[$typeb] : '') : '';
        else        $result = !empty($typearr[$type]) ? '<span class="type t' . $type . $appendclass . '"></span>' . (!empty($typearr[$typeb]) ? '&nbsp;&nbsp;<span class="type t' . $typeb . $appendclass . '"></span>' : '') : '';

        return $result;

    }

    public static function MoveClassName($class) {

        $classarr = ['变化', '物理', '特殊'];

        return ($classarr[$class]) ? $classarr[$class] : '未知';

    }

    public static function EggGroupName($group, $groupb = 0) {

        $grouparr = ['', '陆上', '昆虫', '飞行', '怪兽', '妖精', '人形', '矿物', '植物', '水中1', '水中2', '水中3', '飞龙', '不定形', '百变怪', '未发现'];
        $result   = !empty($grouparr[$group]) ? $grouparr[$group] . (!empty($grouparr[$groupb]) ? '+' . $grouparr[$groupb] : '') : '';

        return $result;
    }

    public static function ItemClassName($class) {

        $classarr = ['', '球类', '进化石', '携带道具', '药物'];

        return ($classarr[$class]) ? $classarr[$class] : '未知';

    }

    public static function GenderSign($gender) {

        $genderarr = ['', '<span class=gd-m>♂</span>', '<span class=gd-f>♀</span>'];

        return $genderarr[$gender];

    }

    public static function Devolution($id) {

        $da = DB::result_first('SELECT devolve FROM pkm_pkmextra WHERE id = ' . $id);

        if(!empty($da)) {

            $db = DB::result_first('SELECT devolve FROM pkm_pkmextra WHERE id = ' . $da);

            return (!empty($db) ? $db : $da);

        } else

            return $id;

    }

    public static function StatusIcon($status) {

        if(!$status) return '<font class=status></font>';

        $statusarr = [
            1 => ['red', '烧'],
            2 => ['lightblue', '冻'],
            3 => ['orange', '痹'],
            4 => ['blue', '眠'],
            5 => ['purple', '毒'],
            6 => ['purple', '剧']
        ];

        return '<font class=status color=' . $statusarr[$status][0] . '>' . $statusarr[$status][1] . '</font>';

    }

    public static function Sprite($class, $type, $filename, $refresh = FALSE, $side = 0) {

        $filenameh = base_convert(hash('joaat', $filename . ($side === 1 ? '_b' : '')), 16, 32);
        $path      = ROOT_CACHE . '/image/' . $filenameh . '.' . $type;

        if(file_exists($path) && $refresh === FALSE) return $path;

        $data = explode('_', $filename);

        switch($class) {
            case 'pokemon':

                if(count($data) < 5) {

                    /*
                     * Invalid parameters
                     *  Shown as the Bug pokemon in games
                     */

                    return ROOT_CACHE . '/image/_unknownpokemon.png';

                } elseif($data[1] == 327 && $side === 0) {

                    /*
                        This is for spinda front sprite only
                        Do some spot's placement calculation and special layers to generate
                    */

                    $pv = [];

                    for($i = 0; $i < 8; $i++) {
                        $pv[$i] = ('0x' . $data[5]{$i}) * 1;
                    }

                    $spot = [
                        [$pv[7], $pv[6]],
                        [$pv[5] + 24, $pv[4] + 2],
                        [$pv[3] + 3, $pv[2] + 16],
                        [$pv[1] + 15, $pv[0] + 18]];

                    $extrapath = ($data[4] == 1) ? 'is_shiny' : 'normal';

                    $img  = imagecreatefrompng(ROOT_IMAGE . '/pokemon/' . $extrapath . '/front/common/327.' . $type);
                    $imgb = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_1.png');
                    $imgc = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_2.png');
                    $imgd = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_3.png');
                    $imge = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_4.png');
                    $imgf = imagecreatefromgif(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_overlap.gif');

                    imagecopymerge($img, $imgb, $spot[0][0] + 23, $spot[0][1] + 15, 0, 0, 8, 8, 80);
                    imagecopymerge($img, $imgc, $spot[1][0] + 23, $spot[1][1] + 15, 0, 0, 8, 8, 80);
                    imagecopymerge($img, $imgd, $spot[2][0] + 23, $spot[2][1] + 15, 0, 0, 7, 9, 80);
                    imagecopymerge($img, $imge, $spot[3][0] + 23, $spot[3][1] + 15, 0, 0, 9, 10, 80);
                    imagecopymerge($img, $imgf, 0, 0, 0, 0, 96, 96, 100);

                    $translayer = imagecreatetruecolor(96, 96);
                    $trans      = imagecolorallocate($translayer, 255, 255, 255);

                    imagecolortransparent($translayer, $trans);
                    imagecopy($translayer, $img, 0, 0, 0, 0, 96, 96);
                    imagetruecolortopalette($translayer, TRUE, 256);
                    imageinterlace($translayer);

                    $img = $translayer;

                } else {

                    $extrapath  = (($data[4] == 1) ? '/is_shiny' : '/normal') .
                        (($side === 1) ? '/back' : '/front') .
                        (($data[2] == 1) ? '/female' : '/common') .
                        (($data[3] > 0) ? '/' . $data[1] . '_' . $data[3] : '/' . $data[1] . '.') .
                        (($type === 'jpeg') ? 'jpg' : $type);
                    $img        = imagecreatefrompng(ROOT_IMAGE . '/pokemon' . $extrapath);
                    $translayer = imagecreate(96, 96);
                    $trans      = imagecolorallocate($translayer, 255, 255, 255);

                    imagecolortransparent($translayer, $trans);
                    imagecopy($translayer, $img, 0, 0, 0, 0, 96, 96);
                    imagetruecolortopalette($translayer, TRUE, 256);
                    imageinterlace($translayer);

                    $img = $translayer;

                }

                /*
                    [Currently unavailable]
                    Gray filter for the dead pokemon
                    if($data['hp'] == 0) {
                        //imagefilter($img, IMG_FILTER_GRAYSCALE);
                        imagecopymergegray($img, $img, 0, 0, 0, 0, 96, 96, 0);
                    }
                */

                break;
            case 'item':

                if(!file_exists(ROOT_IMAGE . '/item/' . $data[1] . '.' . $type)) {

                    /*
                        Same as pokemon above
                        If can't find item's sprite, locate it to the unknown item' sprite
                    */

                    return ROOT_CACHE . '/image/_unknownitem.png';

                }

                $img        = imagecreatefrompng(ROOT_IMAGE . '/item/' . $data[1] . '.' . $type);
                $translayer = imagecreate(24, 24);
                $trans      = imagecolorallocate($translayer, 255, 255, 255);

                imagecolortransparent($translayer, $trans);
                imagecopy($translayer, $img, 0, 0, 0, 0, 24, 24);
                imagetruecolortopalette($translayer, TRUE, 256);
                imageinterlace($translayer);

                $img = $translayer;

                break;
            case 'other':

                /*
                    Other sprites such as hp bar or exp bar
                    Maybe more in the future
                */

                if(in_array($data[0], ['hp', 'exp'])) {

                    $img  = imagecreatefromgif(ROOT_IMAGE . '/other/' . $data[0] . '_border.' . $type);
                    $imgb = imagecreatefromgif(ROOT_IMAGE . '/other/' . $data[0] . '_fill.' . $type);

                    imagecopy($img, $imgb, 1, 1, 0, 0, $data[2], 4);

                } else {

                    $head = 'imagecreatefrom' . $type;
                    $img  = $head(ROOT_IMAGE . '/other/' . $data[0] . '.' . $type);

                }

                break;
            case 'egg':

                /*
                    This is only for egg
                    It may be more appearance in the future
                */

                $img = imagecreatefrompng(ROOT_IMAGE . '/pokemon/0.' . $type);

                break;
            case 'pokemon-icon':

                $img        = imagecreatefrompng(ROOT_IMAGE . '/pokemon-icon/' . $data[1] . '.' . $type);
                $translayer = imagecreate(32, 32);
                $trans      = imagecolorallocate($translayer, 255, 255, 255);

                imagecolortransparent($translayer, $trans);
                imagecopy($translayer, $img, 0, 0, 0, 0, 32, 32);
                imagetruecolortopalette($translayer, TRUE, 256);
                imageinterlace($translayer);

                $img = $translayer;

                break;
        }

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

    /*
        Exp
        - 60 type (n <= 50, 50 <= n <= 68, 68 < n < 98, 98 <= n <= 100)
        - 80, 100, 105, 125 type (All)
        - 164 type (n < 15, 15 <= n <= 36, 36 <= n <= 100)
    */

    public static function Exp($exptype, $nextlevel) {
        if($nextlevel - 1 <= 0) return 0;
        switch($exptype) {
            case 1:
                if($nextlevel <= 50) $nextexp = pow($nextlevel, 3) * (100 - $nextlevel) / 50;
                elseif($nextlevel > 50 && $nextlevel <= 68) $nextexp = pow($nextlevel, 3) * (150 - $nextlevel) / 100;
                elseif($nextlevel > 68 && $nextlevel < 98) $nextexp = pow($nextlevel, 3) * (1911 - 10 * $nextlevel) / 1500;
                else                                            $nextexp = floor(pow($nextlevel, 3) * (160 - $nextlevel) / 100);
                break;
            case 2:
                $nextexp = 0.8 * pow($nextlevel, 3);
                break;
            case 3:
                $nextexp = pow($nextlevel, 3);
                break;
            case 4:
                $nextexp = 1.2 * pow($nextlevel, 3) - 15 * pow($nextlevel, 2) + 100 * $nextlevel - 140;
                break;
            case 5:
                $nextexp = 1.25 * pow($nextlevel, 3);
                break;
            case 6:
                if($nextlevel < 15) $nextexp = pow($nextlevel, 3) * ($nextlevel + 73) / 150;
                elseif($nextlevel >= 15 && $nextlevel <= 36) $nextexp = pow($nextlevel, 3) * ($nextlevel + 14) / 50;
                else                                            $nextexp = pow($nextlevel, 3) * ($nextlevel + 64) / 100;
                break;
        }
        if($nextexp < 0) $nextexp = 0;
        return floor($nextexp);
    }

    public static function NatureName($nature) {

        $naturearr = ['努力', '寂寞', '勇敢', '固执', '顽皮',
            '大胆', '坦率', '悠闲', '淘气', '无虑',
            '胆小', '急躁', '认真', '开朗', '天真',
            '谨慎', '温和', '冷静', '腼腆', '马虎',
            '安静', '温顺', '傲慢', '慎重', '浮躁'];

        return $naturearr[$nature - 1];

    }

    public static function Stat($level, $bs, $iv, $ev, $nature = 1, $hp = FALSE) {

        $bs       = explode(',', $bs);
        $iv       = explode(',', $iv);
        $ev       = explode(',', $ev);
        $modifier = self::NatureModifier($nature);

        $prefix = ['maxhp', 'atk', 'def', 'spatk', 'spdef', 'spd'];
        foreach($prefix as $key => $val) {
            switch($key) {
                default:
                    $result[$val] = floor(floor(floor($bs[$key] * 2 + $ev[$key] / 4 + $iv[$key]) * $level / 100 + 5) * $modifier[$key]);
                    break;
                case 0:
                    $result['maxhp'] = ($bs[$key] != 1) ? floor(floor($bs[$key] * 2 + $ev[$key] / 4 + $iv[$key]) * $level / 100 + $level + 10) : 1;
                    break;
            }
        }

        if($hp !== FALSE)

            $result['hpper'] = min(ceil($hp / $result['maxhp'] * 100), 100);

        return $result;
    }

    public static function NatureModifier($nature) {

        $result = [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1];

        if(($nature - 1) % 6 !== 0) {

            $checkstr                           = '00121314152100232425313200343541424300455152535400';
            $result[$checkstr{$nature * 2 - 2}] = 1.1;
            $result[$checkstr{$nature * 2 - 1}] = 0.9;

        }

        return $result;

    }

    public static function BagItem($condition = '', $orderby = '', $mode = '') {

        global $trainer;

        $condition = ($condition !== '') ? ' AND ' . $condition : '';
        $orderby   = ($orderby !== '') ? ' ORDER BY ' . $orderby : '';
        $mode      = ($mode !== '') ? explode(':', $mode) : '';
        $query     = DB::query('SELECT mi.item_id, mi.quantity, i.name_zh name, i.description, i.type
                                 FROM pkm_myitem mi
                                 LEFT JOIN pkm_itemdata i ON i.item_id = mi.item_id
                                 WHERE mi.uid = ' . $trainer['uid'] . $condition . $orderby);
        $item      = [];

        while($info = DB::fetch($query)) {

            if($mode !== '') $item[$info[$mode[1]]][] = $info;
            else                $item[] = $info;

        }

        return $item;

    }

    public static function TrainerRequireExp($level) {

        return ceil(0.5 * pow($level, 4));

    }

    public static function TrainerAvatar($uid, $size = 'middle') {

        $uid  = sprintf("%09d", abs(intval($uid)));
        $path = '../bbs/uc_server/data/avatar/' . substr($uid, 0, 3) . '/' .
            substr($uid, 3, 2) . '/' .
            substr($uid, 5, 2) . '/' .
            substr($uid, -2) . '_avatar_' . (in_array($size, ['big', 'middle', 'small']) ? $size : 'middle') . '.jpg';

        return (file_exists($path) ? $path : '../bbs/uc_server/images/noavatar_' . $size . '.gif');

    }

    public static function Avatar($uid, $refresh = FALSE) {


        $filenameh = base_convert(hash('joaat', $uid), 16, 32);
        $path      = ROOT_CACHE . '/avatar/' . $filenameh . '.png';

        if(file_exists($path) && $refresh === FALSE) return $path;

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

}