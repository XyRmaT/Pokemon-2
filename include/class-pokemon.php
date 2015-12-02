<?php

class Pokemon {

    public static  $count = 0;
    public static  $pmtmp = [];
    private static $hex   = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';

    public static function Generate($id, $uid, $param = []) {

        /*
            Setup the default parameters for the pokemon that is going to generate
            And then check if the value is customized, if no set one with the default
        */

        $dftparam = [
            'egg'      => 0,
            'wild'     => 0,
            'mtplace'  => 0,
            'mtlevel'  => 1,
            'dreamabi' => 0,
            'shiny'    => 0
        ];

        foreach($dftparam as $key => $val) {

            if(!isset($param[$key])) $param[$key] = $val;

        }

        $egg = !empty($param['egg']) ? 1 : 0;

        /*
            If it's an egg, check $default['egg'], applying following possibles
                - Equals to 1, obtain a random number between 0 and the amount of pokemon which allow trainer to hatch as fakeid
                - Equals to 's,x', randomlize an egg from a series of ids (obtain from the database pkm_eggdata)
            If it's a specified id egg, it passes id straight away to the generator
        */

        if($id == 0) {

            if(!empty($param['egg']) && intval($param['egg']) === 0) {

                $egg = explode(':', $param['egg']);

                if($egg[0] === 's') {

                    $eggset = DB::fetch_first('SELECT eggset, name FROM pkm_eggdata WHERE sid = ' . $egg[1]);
                    $id     = explode(',', $eggset['eggset']);
                    $id     = $id[array_rand($id)];

                } else {

                    return 1; // Unknown egg identifier

                }

            } elseif($egg === 1) {

                $fakeid = rand(0, DB::result_first('SELECT COUNT(*) FROM pkm_pkmextra WHERE alwhatch = 1') - 1);

            } else {

                return 2; // Invalid pokemon id for egg input

            }

        }

        /*
            Position determination
            If it is not an egg or there are space(s) in the party, get an availble box number
        */

        if($param['wild'] === 0) {

            $place = Obtain::DepositBox($uid);

            if($place === FALSE) {

                return 3; // No box

            }

        }

        // Fetch basic pokemon information

        $pokemon = DB::fetch_first('SELECT id, name_zh, genderrt, abi, abib, abic, stthpns, height, weight, female, bs, exptype' . (!empty($egg) ? ', eggcycle' : '') . (!empty($param['wild']) ? ', type, typeb' : '') . '
                                    FROM pkm_pkmdata
                                    WHERE form = 0' . (!empty($fakeid) ? ' LIMIT ' . $fakeid . ', 1' : ' AND id = ' . $id));

        // Generate personality values

        $tid     = ($GLOBALS['user']['uid'] != $uid) ? DB::result_first('SELECT tid FROM pkm_trainerdata WHERE uid = ' . $uid) : $GLOBALS['user']['tid'];
        $tidpart = str_split($tid, 4);
        $tidpart = [
            ('0x' . $tidpart[0]) * 1,
            ('0x' . $tidpart[1]) * 1
        ];

        if($param['shiny'] === 1) {

            $pvpartb = '';
            $pvpart  = ('0x' . substr(str_shuffle(self::$hex), 0, 4)) * 1;
            $pvcalc  = str_split(str_pad(decbin($tidpart[0] ^ $tidpart[1] ^ $pvpart), 16, '0', STR_PAD_LEFT));

            foreach($pvcalc as $key => $val) {

                $pvpartb .= ($key > 12) ? (($val === '0') ? 1 : 0) : $val;

            }

            $pv     = str_pad(dechex($pvpart), 4, '0', STR_PAD_LEFT) . str_pad(base_convert($pvpartb, 2, 16), 4, '0', STR_PAD_LEFT);
            $pvpart = str_split($pv, 2);

        } else {

            $pv     = '';
            $pvpart = [];

            for($i = 0; $i < 4; $i++) {

                $pvpart[$i] = str_pad(dechex(rand(0, ($i === 0 || $i === 1) ? 255 : 254)), 2, '0', STR_PAD_LEFT);
                $pv .= $pvpart[$i];

            }

        }


        // Generate individual value

        $ivpart = [rand(0, 31), rand(0, 31), rand(0, 31), rand(0, 31), rand(0, 31), rand(0, 31)];
        $iv     = implode(',', $ivpart);

        // Generate nature

        $nature = rand(1, 25);

        // Generate gender

        switch($pokemon['genderrt']) {
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
                $gender = (('0x' . $pvpart[3]) * 1 >= $pokemon['genderrt']) ? 1 : 2;
                break;
        }

        // Generate ability

        if($param['dreamabi'] === 0) {

            $tmp = base_convert($pv{3}, 16, 2);
            $abi = $pokemon[(substr($tmp, -1, 1) === '1' || empty($pokemon['abib'])) ? 'abi' : 'abib'];

        } else {

            $abi = $pokemon['abic'];

        }

        /*
         * Calculate the maximum hp
         * Check if it is shiny
         * Obtain the exp
         */

        $bspart = explode(',', $pokemon['bs']);
        $hp     = ($bspart[0] != 1) ? floor(floor($bspart[0] * 2 + $ivpart[0]) * $param['mtlevel'] / 100 + $param['mtlevel'] + 10) : 1;
        $shiny  = (($tidpart[0] ^ $tidpart[1] ^ (('0x' . $pvpart[0] . $pvpart[1]) * 1) ^ (('0x' . $pvpart[2] . $pvpart[3]) * 1)) <= 7) ? 1 : 0;
        $exp    = Obtain::Exp($pokemon['exptype'], $param['mtlevel']);

        // TODO: Other elements such as carry item, form etc which are in process

        $crritem = 0;
        $form    = 0;

        // If it is an egg, generate the hatch time according to the formula

        if(!empty($egg)) {

            $egg      = $id;
            $egghatch = $_SERVER['REQUEST_TIME'] + floor($pokemon['eggcycle'] * 255 * (rand(0, 5) + $pokemon['eggcycle'] * 0.6) / 6);
            $id       = 0;

        } else {

            $egg = $egghatch = 0;

        }

        /*
         * Decide the moves the pokemon has in a certain level
         * Obtain it from the database and then serialize it
         */

        $query = DB::query('SELECT p.mid, m.pp, m.name, m.pp ppb, 0 FROM pkm_pkmmove p LEFT JOIN pkm_movedata m ON p.mid = m.mid WHERE p.id = ' . ($egg > 0 ? $egg : $id) . ' AND p.level <= ' . $param['mtlevel'] . ' AND p.way = 1 ORDER BY p.level DESC LIMIT 4');
        $move  = [];

        while($tmp = DB::fetch($query)) $move[] = array_values($tmp);

        $move = !empty($move) ? serialize($move) : '';

        /*
         * Cached image file name
         * Format: pkm_{id}_{female}_{form}_shiny<if it is spinda>_{personality value}</if>
         */

        $imgname = 'pkm_' . ((!empty($egg)) ? $egg : $id) . '_' . (($pokemon['female'] == 1 && $gender === 2) ? '1' : '0') . '_' . $form . '_' . $shiny . (($id == 327) ? '_' . $pv : '');

        if($param['wild'] === 1) {

            $data = [
                'id'      => $id,
                'name'    => $pokemon['name'],
                'gender'  => $gender,
                'hp'      => $hp,
                'abi'     => $abi,
                'pv'      => $pv,
                'iv'      => $iv,
                'ev'      => '0,0,0,0,0,0',
                'shiny'   => $shiny,
                'nature'  => $nature,
                'level'   => $param['mtlevel'],
                'crritem' => $crritem,
                'hpns'    => $pokemon['stthpns'],
                'move'    => $move,
                'uid'     => $uid,
                'form'    => $form,
                'status'  => 0,
                'exp'     => $exp,
                'mtplace' => $param['mtplace'],
                'height'  => $pokemon['height'] / 10,
                'weight'  => $pokemon['weight'] / 10
            ];

            return array_merge($data, [
                'type'    => $pokemon['type'],
                'typeb'   => $pokemon['typeb'],
                'imgname' => $imgname,
                'bs'      => $pokemon['bs']
            ]);

        } else {

            if(!empty($id)) self::Register($id, !0);

            DB::query('INSERT INTO pkm_mypkm (id, nickname, gender, pv, iv, ev, shiny, originuid, dayctime, egghatch, egg, nature, level, exp, crritem, 
                    hltime, hpns, beauty, move, mtlevel, mtdate, mtplace, abi, uid, capitem, hp, form, place, status, newmove, imgname) 
                VALUES (' . $id . ', \'' . (!empty($eggset['name']) ? $eggset['name'] : $pokemon['name'] . (!empty($egg) ? '的蛋' : '')) . '\', ' . $gender . ', \'' . $pv . '\', \'' . $iv . '\', \'0,0,0,0,0,0\', ' . $shiny . ', ' . $uid . ', 0, ' . $egghatch . ', ' . $egg . ', ' . $nature . ', ' . $param['mtlevel'] . ', ' . $exp . ', ' . $crritem . ', 0, ' . $pokemon['stthpns'] . ', 0, \'' . $move . '\', ' . $param['mtlevel'] . ', ' . $_SERVER['REQUEST_TIME'] . ', ' . $param['mtplace'] . ', ' . $abi . ', ' . $uid . ', 1, ' . $hp . ', 0, ' . $place . ', 0, \'\', \'' . $imgname . '\')');

        }

        return 0;

    }

    /*
        @ &$info require: evldata, crritem, level, hpns, beauty, unserialized move, gender, atk, def, pv, [name, nickname]{reversed in battle}, abi, abic, form, pid, id, originuid
    */

    public static function Register($id, $catch = FALSE, $uid = 0) {

        if(empty($id)) return FALSE;

        if($uid === 0) {

            global $user;

        } else {

            $user = ['uid' => $uid, 'addexp' => 0];

        }

        $catch  = ($catch === FALSE) ? 0 : 1;
        $caught = DB::result_first('SELECT own FROM pkm_mypokedex WHERE id = ' . $id . ' AND uid = ' . $trainer['uid']);

        if($caught === FALSE || is_null($caught)) {

            $caught = FALSE;

            DB::query('INSERT INTO pkm_mypokedex (id, uid, own) VALUES (' . $id . ', ' . $trainer['uid'] . ', ' . $catch . ')');

            ++$trainer['addexp'];

        } elseif($caught === '0' && $catch === 1) {

            DB::query('UPDATE pkm_mypokedex SET own = 1 WHERE id = ' . $id . ' AND uid = ' . $trainer['uid']);

            ++$trainer['addexp'];

        }

        return $caught;

    }

    /*
        @ &$info require column: exptype, level, exp, pid, id, evldata, newmove, move, originuid
    */

    public static function Levelup(&$info, $rarecandy = 0) {

        $exp    = [
            'now'  => Obtain::Exp($info['exptype'], $info['level']),
            'next' => Obtain::Exp($info['exptype'], $info['level'] + 1)
        ];
        $diff   = [
            'now'  => $info['exp'] - $exp['now'],
            'next' => $exp['next'] - $exp['now']
        ];
        $remain = [
            'exp'     => $exp['next'] - $info['exp'],
            'percent' => min(round($diff['now'] / $diff['next'] * 100), 100)
        ];
        $tmp    = $info['level'];

        /*
            Firstly check the availabitity for the level up operation
            Exclusive for empty pokemon id / pokemon with full level / exp under the next level's exp
        */

        if(!empty($info['pid']) && $info['level'] < 100 && $exp['next'] - $info['exp'] <= 0) {

            $i = 0;
            $j = floor($diff['now'] / $diff['next']);


            LEVELUP: {

                ++$i;

                if($exp['next'] - $info['exp'] <= 0 && $info['level'] != 100) {

                    ++$info['level'];

                    LEVELUPLEARNMOVE: {

                        if(empty(self::$pmtmp['move'][$info['id']])) {

                            $query = DB::query('SELECT p.mid, m.name, p.level FROM pkm_pkmmove p, pkm_movedata m WHERE m.mid = p.mid AND p.id = ' . $info['id'] . ' AND p.way = 1');

                            while($infob = DB::fetch($query)) {

                                if(Kit::ColumnSearch($info['move'], 0, $infob['mid']) === FALSE && Kit::ColumnSearch($info['newmove'], 0, $infob['mid']) === FALSE)

                                    self::$pmtmp['move'][$info['id']][$infob['level']][] = [$infob['mid'], $infob['name']];

                            }

                        }

                        if(!empty(self::$pmtmp['move'][$info['id']][$info['level']]))

                            $info['newmove'] = array_merge($info['newmove'], self::$pmtmp['move'][$info['id']][$info['level']]);

                    }

                    if(!empty($evolved)) {

                        $evolved = !1;

                        goto LEVELUPLOOP;

                    }

                    $exp  = [
                        'now'  => Obtain::Exp($info['exptype'], $info['level']),
                        'next' => Obtain::Exp($info['exptype'], $info['level'] + 1)
                    ];
                    $diff = [
                        'now'  => $info['exp'] - $exp['now'],
                        'next' => $exp['next'] - $exp['now']
                    ];

                    $remain = [
                        'exp'     => $exp['next'] - $info['exp'],
                        'percent' => min(round($diff['now'] / $diff['next'] * 100), 100)
                    ];

                    if(!empty($info['evldata']) && ($evolved = self::Evolve($info)) === !0)

                        goto LEVELUPLEARNMOVE;

                } else {

                    goto LEVELUPUPDATE;

                }

                LEVELUPLOOP:

                if($i < $j) goto LEVELUP;

            }

            LEVELUPUPDATE: {

                if($tmp !== $info['level']) {

                    $tmp        = Obtain::Stat($info['level'], $info['bs'], $info['iv'], $info['ev'], $info['nature']);
                    $info['hp'] = ceil($info['hpper'] * $tmp['maxhp'] / 100);

                    DB::query('UPDATE pkm_mypkm SET level = ' . $info['level'] . ', newmove = \'' . (!empty($info['newmove']) ? serialize($info['newmove']) : '') . '\', hp = ' . $info['hp'] . ' WHERE pid = ' . $info['pid'] . ' LIMIT 1');

                    $info = array_merge($info, $tmp);

                    self::$pmtmp['move'] = [];

                }

            }

        }

        return [$diff['next'], $diff['now'], $remain['exp'], $remain['percent']];

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

        if($info['crritem'] != '207' && !empty($info['evldata'])) {

            $evolution = unserialize($info['evldata']);

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
                        !empty($val[2]) && $info['hpns'] >= $val[2] && ++$processcount;
                        !empty($val[3]) && $info['beauty'] >= $val[3] && ++$processcount;
                        !empty($val[4]) && !empty($param['mapid']) && $param['mapid'] == $val[4] && ++$processcount;
                        !empty($val[5]) && $info['crritem'] == $val[5] && ++$processcount;
                        !empty($val[6]) && in_array($val[6], [$info['move'][0][0], $info['move'][1][0], $info['move'][2][0], $info['move'][3][0]]) && ++$processcount;

                        if(!empty($val[7])) {

                            $tmp = DB::fetch_first('SELECT id FROM pkm_mypkm WHERE place IN (1, 2, 3, 4, 5, 6) AND uid = ' . $param['uid'] . ' AND id = ' . $val[7]);

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
                                    ((('0x' . substr($info['pv'], 4, 4)) * 1) % 65535 % 10 < 5) && ++$processcount;
                                    break;
                                case 5:
                                    ((('0x' . substr($info['pv'], 4, 4)) * 1) % 65535 % 10 >= 5) && ++$processcount;
                                    break;
                            }

                        }

                    } elseif(!empty($param['useitem']) && $param['useitem'] == $val[5]) {

                        ++$processcount;

                    }

                    !empty($val[8]) && $info['gender'] + 1 == $val[8] && ++$processcount;


                    if($processtotal === $processcount) {

                        $user = DB::fetch_first('SELECT uid, exp, tid FROM pkm_trainerdata WHERE uid = ' . $param['uid']);

                        Trainer::AddExp($user, 2);

                        $evoinfo = DB::fetch_first('SELECT abi, abib, abic, genderrt, name_zh, female, evldata, bs FROM pkm_pkmdata WHERE id = ' . $val[0]);
                        $pvpart  = str_split($info['pv'], 2);
                        $tidpart = str_split($trainer['tid'], 4);
                        $tidpart = [
                            ('0x' . $tidpart[0]) * 1,
                            ('0x' . $tidpart[1]) * 1
                        ];
                        $shiny   = (($tidpart[0] ^ $tidpart[1] ^ (('0x' . $pvpart[0] . $pvpart[1]) * 1) ^ (('0x' . $pvpart[2] . $pvpart[3]) * 1)) <= 7) ? 1 : 0;

                        if($evoinfo['abic'] !== $info['abi']) {

                            $tmp = base_convert($info['pv']{3}, 16, 2);
                            $abi = $evoinfo[(substr($tmp, -1, 1) === '1' || empty($evoinfo['abib'])) ? 'abi' : 'abib'];

                        } else {

                            $abi = $evoinfo['abic'];

                        }

                        switch($evoinfo['genderrt']) {
                            case 255:
                                $gender = 0;
                                break;
                            case 254:
                                $gender = 2;
                                break;
                            case 255:
                                $gender = 1;
                                break;
                            default:
                                $factor = ('0x' . $pvpart[3]) * 1;
                                $gender = ($factor >= $evoinfo['genderrt']) ? 1 : 2;
                                break;
                        }

                        $imgname = 'pkm_' . $val[0] . '_' . (($evoinfo['female'] == 1 && $gender === 2) ? '1' : '0') . '_' . $info['form'] . '_' . $shiny;

                        $crritem = (!empty($val[5]) && $val[5] == $info['crritem']) ? 0 : $info['crritem'];

                        Obtain::Sprite('pokemon', 'png', $imgname);
                        DB::query('UPDATE pkm_mypkm SET ' . (($info['name'] === $info['nickname']) ? 'nickname = \'' . $evoinfo['name'] . '\', ' : '') . 'id = ' . $val[0] . ', abi = ' . $abi . ', shiny = ' . $shiny . ', gender = ' . $gender . ', imgname = \'' . $imgname . '\', crritem = ' . $crritem . ' WHERE pid = ' . $info['pid']);
                        DB::query('UPDATE pkm_trainerstat SET pmevolve = pmevolve + 1 WHERE uid = ' . $param['uid']);

                        if(self::$count === 0) {

                            self::$count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE place IN (1, 2, 3, 4, 5, 6) AND uid = ' . $param['uid']);

                        }

                        self::Register($val[0], !0, $param['uid']);


                        if($info['id'] == 290 && self::$count < 6) {

                            DB::query('INSERT INTO pkm_mypkm
                                (id, nickname, gender, pv, iv, ev, shiny, originuid, dayctime, egghatch, egg, nature, level, exp,
                                    hltime, hpns, beauty, move, mtlevel, mtdate, mtplace, abi, uid, capitem, hp, form, place, status, newmove, imgname)
                                SELECT 292, \'脱壳忍者\', 0, pv, iv, ev, shiny, originuid, dayctime, egghatch, egg, nature, level, exp,
                                    hltime, hpns, beauty, move, mtlevel, mtdate, mtplace, (SELECT abi FROM pkm_pkmdata WHERE id = 292), uid, capitem, hp, form, ' . Obtain::DepositBox($param['uid']) . ', status, newmove, \'' . str_replace('291', '292', $imgname) . '\'
                                FROM pkm_mypkm WHERE pid = ' . $info['pid']);

                            self::Register(292, !0, $param['uid']);

                        }

                        $info = array_merge($info, [
                            'id'       => $val[0],
                            'imgname'  => $imgname,
                            'name'     => $evoinfo['name'],
                            'nickname' => ($info['name'] === $info['nickname']) ? $evoinfo['name'] : $info['nickname'],
                            'gender'   => $gender,
                            'abi'      => $abi,
                            'evldata'  => $evoinfo['evldata'],
                            'bs'       => $evoinfo['bs']
                        ]);

                        return TRUE;

                    }

                }

            }

        }

        return FALSE;

    }

    public static function Hatch($pid) {

        $pokemon = DB::fetch_first('SELECT p.id, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.egg WHERE m.pid = ' . $pid);

        self::Register($pokemon['id'], !0);

        Trainer::AddTemporaryStat('pkm_hatched');

        DB::query('UPDATE pkm_mypkm SET id = egg, nickname = \'' . $pokemon['name'] . '\', exp = 0, LEVEL = 1, egg = 0, egghatch = ' . $_SERVER['REQUEST_TIME'] . ', hpns = 120 WHERE pid = ' . $pid);

    }

    public static function CorrectAbility($pv, $curabi, $abi, $abib, $dreamabi) {

        $sql   = [];
        $query = DB::query('SELECT m.pid, m.abi curabi, m.pv, p.abi, p.abib, p.abic FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id');

        while($info = DB::fetch($query)) {

            //if($info['abic'] !== $info['curabi'])

            $tmp = (substr(base_convert($info['pv']{3}, 16, 2), -1, 1) === '1' || empty($info['abib'])) ? $info['abi'] : $info['abib'];

            //else

            //    $tmp = $info['abic'];

            if($tmp !== $info['curabi'])

                $sql[] = '(' . $info['pid'] . ', ' . $tmp . ')';

        }

        if(!empty($sql))

            DB::query('INSERT INTO pkm_mypkm (pid, abi) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE abi = VALUES(abi)');

    }

    public static function RefreshPartyOrder($uid = 0) {

        $query = DB::query('SELECT pid FROM pkm_mypkm WHERE uid = ' . (($uid !== 0) ? $uid : $GLOBALS['user']['uid']) . ' AND place IN (1, 2, 3, 4, 5, 6) ORDER BY place ASC');
        $sql   = [];
        $i     = 0;

        while($info = DB::fetch($query))

            $sql[] = '(' . $info['pid'] . ', ' . (($i < 6) ? ++$i : 0) . ')';

        if(!empty($sql))

            DB::query('INSERT INTO pkm_mypkm (pid, place) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE place = VALUES(place)');

    }

}