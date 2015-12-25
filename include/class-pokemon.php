<?php

class Pokemon {

    public static  $count = 0;
    public static  $pmtmp = [];
    private static $hex   = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';

    public static function Generate($nat_id, $uid, $param = []) {

        global $trainer;

        // Compare parameters with the default one and overwrite if existed.
        $default_param = [
            'wild'              => 0,
            'met_location'      => 0,
            'met_level'         => 1,
            'is_egg'            => TRUE,
            'is_hidden_ability' => FALSE,
            'is_shiny'          => FALSE,
            'father_moves'      => [],
            'mother_moves'      => []
        ];
        foreach($default_param as $key => $val)
            if(!isset($param[$key])) $param[$key] = $val;

        // If it's an egg, check $default['egg'], applying following possibles
        //  - Equals to TRUE, obtain a random number between 0 and the amount of pokemon which allow trainer to hatch as fakeid
        //  - Equals to 's,x', randomlize an egg from a series of ids (obtain from the database pkm_eggdata)
        // If it's a specified id egg, it passes id straight away to the generator
        if($nat_id == 0) {
            if(!empty($param['egg']) && intval($param['egg']) === 0) {
                $set = explode(':', $param['egg']);
                if($set[0] === 's') {
                    $eggset = DB::fetch_first('SELECT eggset, name_zh FROM pkm_eggdata WHERE set_id = ' . $set[1]);
                    $nat_id = explode(',', $eggset['eggset']);
                    $nat_id = $nat_id[array_rand($nat_id)];
                } else {
                    return 1;
                }
            } elseif($param['is_egg'] === TRUE) {
                $fake_nat_id = rand(0, DB::result_first('SELECT COUNT(*) FROM pkm_pkmextra WHERE is_hatchable = 1') - 1);
            } else {
                return 2;
            }
        }

        // Determine the location of where this pokemon will go using the method Obtain::DepositBox().
        $location = $param['wild'] === 0 ? 0 : Obtain::DepositBox($uid);
        if($location === FALSE) return 3;

        $pokemon = DB::fetch_first('SELECT nat_id, name_zh, gender_rate, ability, ability_b, ability_hidden, happiness_initial,
                                        height, weight, has_female_sprite, base_stat, exp_type, egg_cycle, type, type_b
                                    FROM pkm_pkmdata
                                    WHERE form = 0' . (!empty($fake_nat_id) ? ' LIMIT ' . $fake_nat_id . ', 1' : ' AND id = ' . $nat_id));

        // Generate trainer id
        $trainer_id      = $trainer['uid'] != $uid ? DB::result_first('SELECT trainer_id FROM pkm_trainerdata WHERE uid = ' . $uid) : $GLOBALS['user']['trainer_id'];
        $trainer_id_part = str_split($trainer_id, 4);
        $trainer_id_part = [hexdec($trainer_id_part[0]), hexdec($trainer_id_part[1])];

        // Generate personality value
        if($param['is_shiny']) {
            $psn_value_part_a = hexdec(substr(str_shuffle(self::$hex), 0, 4));
            if($param['is_hidden_ability'] && $psn_value_part_a % 2 !== 0) $psn_value_part_a = max($psn_value_part_a + 1, 0xFFFE);

            $psn_value_part_b = '';
            $psn_value_calc   = str_split(str_pad(decbin($trainer_id_part[0] ^ $trainer_id_part[1] ^ $psn_value_part_a), 16, '0', STR_PAD_LEFT));

            foreach($psn_value_calc as $key => $val)
                $psn_value_part_b .= ($key > 12) ? (($val === '0') ? 1 : 0) : $val;

            $psn_value       = str_pad(dechex($psn_value_part_a), 4, '0', STR_PAD_LEFT) . str_pad(base_convert($psn_value_part_b, 2, 16), 4, '0', STR_PAD_LEFT);
            $psn_value_bytes = str_split($psn_value, 2);
        } else {
            $psn_value       = '';
            $psn_value_bytes = [];

            for($i = 0; $i < 4; $i++) {
                $psn_value_bytes[$i] = str_pad(dechex(rand(0, ($i === 0 || $i === 1) ? 255 : 254)), 2, '0', STR_PAD_LEFT);
                $psn_value .= $psn_value_bytes[$i];
            }
        }

        // Generate individual value
        $idv_value_parts = [rand(0, 31), rand(0, 31), rand(0, 31), rand(0, 31), rand(0, 31), rand(0, 31)];
        $idv_value       = implode(',', $idv_value_parts);

        // Generate gender
        if($pokemon['gender_rate'] === '255') $gender = 0;
        else if($pokemon['gender_rate'] === '254') $gender = 2;
        else if($pokemon['gender_rate'] === '0') $gender = 1;
        else $gender = base_convert($psn_value_bytes[3], 16, 10) >= $pokemon['gender_rate'] ? 1 : 2;

        // Generate ability
        $ability = !$param['is_hidden_ability'] ?
            $pokemon[!(base_convert($psn_value{3}, 16, 10) % 2) || !$pokemon['ability_b'] ? 'ability' : 'ability_b'] :
            $pokemon['ability_hidden'];

        // Generate base stat, hp, exp, nature and shiny status
        $base_stat_parts = explode(',', $pokemon['base_stat']);
        $hp              = $base_stat_parts[0] != 1 ? floor(floor($base_stat_parts[0] * 2 + $idv_value_parts[0]) * $param['met_level'] / 100 + $param['met_level'] + 10) : 1;
        $exp             = Obtain::Exp($pokemon['exp_type'], $param['met_level']);
        $nature          = rand(1, 25);
        $is_shiny        = ($trainer_id_part[0] ^ $trainer_id_part[1] ^
            base_convert($psn_value_bytes[0] . $psn_value_bytes[1], 16, 10) ^
            base_convert($psn_value_bytes[2] . $psn_value_bytes[3], 16, 10) <= 16) ? 1 : 0;

        // TODO: Other elements such as carry item, form etc which are in process
        $item_carrying = 0;
        $form          = 0;

        $hatch_nat_id = $time_hatched = 0;
        if($param['is_egg']) {
            $hatch_nat_id = $nat_id;
            $time_hatched = Obtain::HatchTime($pokemon['egg_cycle']);
            $nat_id       = 0;
        }

        // TODO: Inherit move
        // Decide the moves the pokemon has in the current level.
        $moves = [];
        $query = DB::query('SELECT p.move_id, m.pp, m.pp pp_total, 0 pp_up
                            FROM pkm_pkmmove p
                            LEFT JOIN pkm_movedata m ON p.move_id = m.move_id
                            WHERE p.nat_id = ' . ($hatch_nat_id > 0 ? $hatch_nat_id : $nat_id) . ' AND
                                   p.level <= ' . $param['met_level'] . ' AND
                                   p.way = ' . MOVE_BY_LEVEL . '
                            ORDER BY p.level DESC LIMIT 4');

        while($info = DB::fetch($query))
            $moves[] = $info;
        $moves = !empty($moves) ? serialize($moves) : '';

        if($param['is_egg']) {
            if($param['father_moves']) {
                $egg_moves = Obtain::EggMoves($pokemon['pkm_id']);
            }
        }

        // Build sprite name
        $sprite_name = 'pkm_' .
            ($hatch_nat_id || $nat_id) . '_' .
            ~~($pokemon['has_female_sprite'] == 1 && $gender === 2) . '_' .
            $form . '_' .
            $is_shiny .
            (in_array(327, [$nat_id, $hatch_nat_id]) ? '_' . $psn_value : '');

        // Pokedex register
        if(!empty($nat_id)) self::DexRegister($nat_id, !$param['is_wild'], $uid);

        $data = [
            'nat_id'        => $nat_id,
            'gender'        => $gender,
            'psn_value'     => $psn_value,
            'ind_value'     => $idv_value,
            'is_shiny'      => $is_shiny,
            'nature'        => $nature,
            'level'         => $param['met_level'],
            'exp'           => $exp,
            'item_carrying' => $item_carrying,
            'happiness'     => $pokemon['happiness_initial'],
            'moves'         => $moves,
            'met_location'  => $param['met_location'],
            'ability'       => $ability,
            'uid'           => $uid,
            'hp'            => $hp,
            'form'          => $form
        ];

        if($param['wild'] === 1) {
            return array_merge($data, [
                'eft_value'   => '0,0,0,0,0,0',
                'type'        => $pokemon['type'],
                'type_b'      => $pokemon['type_b'],
                'sprite_name' => $sprite_name,
                'base_stat'   => $pokemon['base_stat'],
                'name'        => $pokemon['name'],
                'height'      => $pokemon['height'] / 10,
                'weight'      => $pokemon['weight'] / 10,
            ]);
        } else {
            $data = array_merge($data, [
                'nickname'      => !empty($eggset['name']) ? $eggset['name'] : $pokemon['name'] . (!empty($hatch_nat_id) ? Obtain::Text('part_\'s_egg') : ''),
                'time_hatched'  => $time_hatched,
                'hatch_nat_id'  => $hatch_nat_id,
                'met_level'     => $param['met_level'],
                'met_time'      => $_SERVER['REQUEST_TIME'],
                'uid_initial'   => $uid,
                'item_captured' => '',
                'location'      => $location,
                'sprite_name'   => $sprite_name
            ]);
            DB::query('INSERT INTO pkm_mypkm (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_values($data)) . ')');
            return 0;
        }
    }

    /**
     * Register into pokedex.
     * @param      $id
     * @param bool $catch
     * @param int  $uid
     * @return bool
     */
    public static function DexRegister($id, $catch = FALSE, $uid = 0) {

        if(!$id) return FALSE;
        if(!$uid) $uid = $GLOBALS['trainer']['uid'];

        $caught = DB::result_first('SELECT is_owned FROM pkm_mypokedex WHERE nat_id = ' . $id . ' AND uid = ' . $uid);
        if(!$caught) {
            DB::query('INSERT INTO pkm_mypokedex (nat_id, uid, is_owned) VALUES (' . $id . ', ' . $uid . ', ' . ~~$catch . ')');
            Trainer::AddExp($trainer, 1, TRUE);
        } elseif($catch) {
            DB::query('UPDATE pkm_mypokedex SET is_owned = 1 WHERE nat_id = ' . $id . ' AND uid = ' . $uid);
            Trainer::AddExp($trainer, 1, TRUE);
        }

        return !!$caught;

    }

    /**
     * @param array $info (exp_type, level, exp, pkm_id, id, evolution_data, moves_new, moves, uid_initial)
     * @param int   $rarecandy
     * @return array
     */
    public static function Levelup(&$info, $rarecandy = 0) {

        $exp_required   = [
            'current' => Obtain::Exp($info['exp_type'], $info['level']),
            'next'    => Obtain::Exp($info['exp_type'], $info['level'] + 1)
        ];
        $exp_difference = [
            'current' => $info['exp'] - $exp_required['current'],
            'next'    => $exp_required['next'] - $exp_required['current']
        ];
        $remain         = [
            'exp'     => $exp_required['next'] - $info['exp'],
            'percent' => min(round($exp_difference['current'] / $exp_difference['next'] * 100), 100)
        ];
        $old_level      = $info['level'];

        // Firstly check the availabitity for the level up operation
        // Except for empty pokemon id / pokemon with full level / exp under the next level's exp
        if($info['pkm_id'] && $info['level'] < 100) {

            $i           = 0;
            $check_times = floor($exp_difference['current'] / $exp_difference['next']);

            LEVEL_UP: {
                ++$i;
                if($remain['exp'] <= 0 && $info['level'] != 100) {

                    ++$info['level'];

                    LEARN_MOVE: {
                        if(empty(self::$pmtmp['moves'][$info['nat_id']])) {
                            $query = DB::query('SELECT p.move_id, m.name_zh, p.learn_level FROM pkm_pkmmove p, pkm_movedata m WHERE m.move_id = p.move_id AND p.nat_id = ' . $info['nat_id'] . ' AND p.way = ' . MOVE_BY_LEVEL);
                            while($infob = DB::fetch($query)) {
                                if(Kit::ColumnSearch($info['moves'], 0, $infob['move_id']) === FALSE &&
                                    Kit::ColumnSearch($info['moves_new'], 0, $infob['move_id']) === FALSE
                                )
                                    self::$pmtmp['moves'][$info['nat_id']][$infob['learn_level']][] = [$infob['move_id'], $infob['name']];
                            }
                        }

                        if(!empty(self::$pmtmp['moves'][$info['nat_id']][$info['level']]))
                            $info['moves_new'] = array_merge($info['moves_new'], self::$pmtmp['moves'][$info['nat_id']][$info['level']]);

                    }

                    if(!empty($evolved)) {
                        $evolved = !1;
                        goto NEXT_LOOP;
                    }

                    $exp_required   = [
                        'current' => Obtain::Exp($info['exp_type'], $info['level']),
                        'next'    => Obtain::Exp($info['exp_type'], $info['level'] + 1)
                    ];
                    $exp_difference = [
                        'current' => $info['exp'] - $exp_required['current'],
                        'next'    => $exp_required['next'] - $exp_required['current']
                    ];

                    $remain = [
                        'exp'     => $exp_required['next'] - $info['exp'],
                        'percent' => min(round($exp_difference['current'] / $exp_difference['next'] * 100), 100)
                    ];

                    if(!empty($info['evolution_data']) && ($evolved = self::Evolve($info)) === !0)

                        goto LEARN_MOVE;

                } else {

                    goto UPDATE;

                }

                NEXT_LOOP:

                if($i < $check_times) goto LEVEL_UP;

            }

            UPDATE: {

                if($old_level !== $info['level']) {

                    $old_level  = Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], $info['nature']);
                    $info['hp'] = ceil($info['hpper'] * $old_level['maxhp'] / 100);

                    DB::query('UPDATE pkm_mypkm SET level = ' . $info['level'] . ', moves_new = \'' . (!empty($info['moves_new']) ? serialize($info['moves_new']) : '') . '\', hp = ' . $info['hp'] . ' WHERE pkm_id = ' . $info['pkm_id'] . ' LIMIT 1');

                    $info = array_merge($info, $old_level);

                    self::$pmtmp['moves'] = [];

                }

            }

        }

        return [$exp_difference['next'], $exp_difference['current'], $remain['exp'], $remain['percent']];

    }

    public static function Evolve(&$info, $param = []) {

        $dftparam = [
            'mapid'    => 0,
            'other'    => 0,
            'otherobj' => 0,
            'useitem'  => 0,
            'uid'      => $GLOBALS['user']['uid']
        ];

        foreach($dftparam as $key => $val) {
            if(!isset($param[$key])) {
                $param[$key] = $val;
            }
        }

        /**
         *    0
         *        0=进化链接
         *        1=等级（1-100）
         *        2=亲密度（1-255）
         *        3=美丽度（1-255）
         *        4=地图（地图编号）
         *        5=携带道具/使用道具（道具编号）
         *        6=掌握技能（技能编号）
         *        7=队伍中存在精灵（精灵编号）
         *        8=性别（1=无性，2=公，3=母）
         *        9=时段（）
         *        10=特殊（1=攻击>防御，2=攻击<防御，3=攻击=防御，4=性格值尾数>=5，5=性格值尾数<5）
         *        11=其它（1=通信进化，2=使用道具进化）
         *        12=其它进阶值（如果其它=1，值则为特定交换的精灵的编号。如果其它=2，值则为道具的编号。视精灵进化方式决定值是否为空。）
         *     1
         *         ...
         **/

        if($info['item_carrying'] != '207' && !empty($info['evolution_data'])) {

            $evolution = unserialize($info['evolution_data']);

            foreach($evolution as $val) {

                /*
                    Use this two variables to validate if this pokemon satisfy all the conditions for evolve
                    $processtotal records the total processes for evolve
                    $processcount counts the current process
                */

                $processtotal = $processcount = 0;

                if(!empty($val[11])) {

                    //是否通信和是否使用道具进化的判定，如果设置为1并且方式不为这个，则直接跳到下个分支的进化判定
                    if($param['other'] !== 0) {

                        if(!empty($val[12])) {

                            if($val[12] == $param['otherobj'])

                                $processcount += 2;

                        } else

                            $processcount += 1;

                    } else

                        continue;

                }

                foreach($val as $key => $valb) {

                    if(empty($valb) || $key === 0) continue;

                    ++$processtotal;

                }


                if($processtotal !== 0) {

                    if(empty($param['useitem'])) {

                        !empty($val[1]) && $info['level'] >= $val[1] && ++$processcount;
                        !empty($val[2]) && $info['happiness'] >= $val[2] && ++$processcount;
                        !empty($val[3]) && $info['beauty'] >= $val[3] && ++$processcount;
                        !empty($val[4]) && !empty($param['mapid']) && $param['mapid'] == $val[4] && ++$processcount;
                        !empty($val[5]) && $info['item_carrying'] == $val[5] && ++$processcount;
                        !empty($val[6]) && in_array($val[6], [$info['moves'][0][0], $info['moves'][1][0], $info['moves'][2][0], $info['moves'][3][0]]) && ++$processcount;

                        if(!empty($val[7])) {

                            $tmp = DB::fetch_first('SELECT id FROM pkm_mypkm WHERE location IN (1, 2, 3, 4, 5, 6) AND uid = ' . $param['uid'] . ' AND id = ' . $val[7]);

                            !empty($tmp) && ++$processcount;

                        }


                        if(!empty($val[9]) && ($hour = date('H', $_SERVER['REQUEST_TIME'])) &&
                            ($val[9] < 3 && $hour > 4 && $hour < 19 || $val[9] > 2 && ($hour < 5 || $hour > 18))
                        )

                            ++$processcount;

                        if($val[10]) {

                            switch($val[10]) {
                                case 1:
                                    ($info['atk'] > $info['def']) && ++$processcount;
                                    break;
                                case 2:
                                    ($info['atk'] < $info['def']) && ++$processcount;
                                    break;
                                case 3:
                                    ($info['atk'] === $info['def']) && ++$processcount;
                                    break;
                                case 4:
                                    ((('0x' . substr($info['psn_value'], 4, 4)) * 1) % 65535 % 10 < 5) && ++$processcount;
                                    break;
                                case 5:
                                    ((('0x' . substr($info['psn_value'], 4, 4)) * 1) % 65535 % 10 >= 5) && ++$processcount;
                                    break;
                            }

                        }

                    } elseif(!empty($param['useitem']) && $param['useitem'] == $val[5]) {

                        ++$processcount;

                    }

                    !empty($val[8]) && $info['gender'] + 1 == $val[8] && ++$processcount;


                    if($processtotal === $processcount) {

                        $user = DB::fetch_first('SELECT uid, exp, trainer_id FROM pkm_trainerdata WHERE uid = ' . $param['uid']);

                        Trainer::AddExp($user, 2);

                        $evoinfo = DB::fetch_first('SELECT ability, ability_b, ability_hidden, gender_rate, name_zh name, female, evolution_data, base_stat FROM pkm_pkmdata WHERE id = ' . $val[0]);
                        $pvpart  = str_split($info['psn_value'], 2);
                        $tidpart = str_split($trainer['trainer_id'], 4);
                        $tidpart = [
                            ('0x' . $tidpart[0]) * 1,
                            ('0x' . $tidpart[1]) * 1
                        ];
                        $shiny   = (($tidpart[0] ^ $tidpart[1] ^ (('0x' . $pvpart[0] . $pvpart[1]) * 1) ^ (('0x' . $pvpart[2] . $pvpart[3]) * 1)) <= 7) ? 1 : 0;

                        if($evoinfo['ability_hidden'] !== $info['ability']) {
                            $tmp = base_convert($info['psn_value']{3}, 16, 2);
                            $abi = $evoinfo[(substr($tmp, -1, 1) === '1' || empty($evoinfo['ability_b'])) ? 'ability' : 'ability_b'];
                        } else {
                            $abi = $evoinfo['ability_hidden'];
                        }

                        switch($evoinfo['gender_rate']) {
                            case 255:
                                $gender = 0;
                                break;
                            case 254:
                                $gender = 2;
                                break;
                            case 0:
                                $gender = 1;
                                break;
                            default:
                                $factor = ('0x' . $pvpart[3]) * 1;
                                $gender = ($factor >= $evoinfo['gender_rate']) ? 1 : 2;
                                break;
                        }

                        $spriteName = 'pkm_' . $val[0] . '_' . (($evoinfo['female'] == 1 && $gender === 2) ? '1' : '0') . '_' . $info['form'] . '_' . $shiny;

                        $crritem = (!empty($val[5]) && $val[5] == $info['item_carrying']) ? 0 : $info['item_carrying'];

                        Obtain::Sprite('pokemon', 'gif', $spriteName);
                        DB::query('UPDATE pkm_mypkm SET ' . (($info['name'] === $info['nickname']) ? 'nickname = \'' . $evoinfo['name'] . '\', ' : '') . 'id = ' . $val[0] . ', ability = ' . $abi . ', is_shiny = ' . $shiny . ', gender = ' . $gender . ', sprite_name = \'' . $spriteName . '\', item_carrying = ' . $crritem . ' WHERE pkm_id = ' . $info['pkm_id']);
                        DB::query('UPDATE pkm_trainerstat SET pkm_evolved = pkm_evolved + 1 WHERE uid = ' . $param['uid']);

                        if(self::$count === 0)
                            self::$count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE location IN (1, 2, 3, 4, 5, 6) AND uid = ' . $param['uid']);

                        self::DexRegister($val[0], !0, $param['uid']);


                        if($info['nat_id'] == 290 && self::$count < 6) {

                            DB::query('INSERT INTO pkm_mypkm
                                (id, nickname, gender, psn_value, ind_value, eft_value, is_shiny, uid_initial, time_daycare_sent, time_hatched, time_hatched, nature, level, exp,
                                    time_pc_sent, happiness, beauty, moves, met_level, met_time, met_location, ability, uid, item_captured, hp, form, location, status, moves_new, sprite_name)
                                SELECT 292, \'脱壳忍者\', 0, psn_value, ind_value, eft_value, is_shiny, uid_initial, time_daycare_sent, time_hatched, time_hatched, nature, level, exp,
                                    time_pc_sent, happiness, beauty, moves, met_level, met_time, met_location, (SELECT ability FROM pkm_pkmdata WHERE id = 292), uid, item_captured, hp, form, ' . Obtain::DepositBox($param['uid']) . ', status, moves_new, \'' . str_replace('291', '292', $spriteName) . '\'
                                FROM pkm_mypkm WHERE pkm_id = ' . $info['pkm_id']);

                            self::DexRegister(292, !0, $param['uid']);

                        }

                        $info = array_merge($info, [
                            'id'             => $val[0],
                            'sprite_name'    => $spriteName,
                            'name'           => $evoinfo['name'],
                            'nickname'       => ($info['name'] === $info['nickname']) ? $evoinfo['name'] : $info['nickname'],
                            'gender'         => $gender,
                            'ability'        => $abi,
                            'evolution_data' => $evoinfo['evolution_data'],
                            'base_stat'      => $evoinfo['base_stat']
                        ]);

                        return TRUE;

                    }

                }

            }

        }

        return FALSE;

    }

    public static function Hatch($pid) {

        $pokemon = DB::fetch_first('SELECT p.nat_id, p.name_zh name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.time_hatched WHERE m.pkm_id = ' . $pid);

        self::DexRegister($pokemon['nat_id'], !0);

        Trainer::AddTemporaryStat('pkm_hatched');

        DB::query('UPDATE pkm_mypkm SET nat_id = hatch_nat_id, nickname = \'' . $pokemon['name'] . '\', exp = 0, LEVEL = 1, time_hatched = 0, time_hatched = ' . $_SERVER['REQUEST_TIME'] . ', happiness = 120 WHERE pkm_id = ' . $pid);

    }

    public static function CorrectAbility($pv, $curabi, $abi, $abib, $dreamabi) {

        $sql   = [];
        $query = DB::query('SELECT m.pkm_id, m.ability curabi, m.psn_value, p.ability, p.ability_b, p.ability_hidden FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id');

        while($info = DB::fetch($query)) {

            //if($info['ability_hidden'] !== $info['curabi'])

            $tmp = (substr(base_convert($info['psn_value']{3}, 16, 2), -1, 1) === '1' || empty($info['ability_b'])) ? $info['ability'] : $info['ability_b'];

            //else

            //    $tmp = $info['ability_hidden'];

            if($tmp !== $info['curabi'])

                $sql[] = '(' . $info['pkm_id'] . ', ' . $tmp . ')';

        }

        if(!empty($sql))

            DB::query('INSERT INTO pkm_mypkm (pkm_id, ability) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE ability = VALUES(ability)');

    }

    public static function RefreshPartyOrder($uid = 0) {

        $query = DB::query('SELECT pkm_id FROM pkm_mypkm WHERE uid = ' . (($uid !== 0) ? $uid : $GLOBALS['user']['uid']) . ' AND location IN (1, 2, 3, 4, 5, 6) ORDER BY location ASC');
        $sql   = [];
        $i     = 0;

        while($info = DB::fetch($query))
            $sql[] = '(' . $info['pkm_id'] . ', ' . (($i < 6) ? ++$i : 0) . ')';

        if(!empty($sql))
            DB::query('INSERT INTO pkm_mypkm (pkm_id, location) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE location = VALUES(location)');

    }

}