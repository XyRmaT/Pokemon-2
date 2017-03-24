<?php

class PokemonGeneral {

    public static $count = 0;
    public static $temp = [];


    /**
     * Register into pokedex.
     * @param      $nat_id
     * @param bool $catch
     * @param int $user_id
     * @return bool
     */
    public static function registerPokedex ($nat_id, $user_id = 0, bool $catch = FALSE) : bool {

        // TODO: shiny pokemon register

        if (!$nat_id) return FALSE;

        $caught = DB::result_first('SELECT is_owned FROM pkm_mypokedex WHERE nat_id = ' . $nat_id . ' AND user_id = ' . $user_id);
        if (!$caught) {
            DB::query('INSERT INTO pkm_mypokedex (nat_id, user_id, is_owned) VALUES (' . $nat_id . ', ' . $user_id . ', ' . intval($catch) . ')');
            Trainer::addExp($user_id, 1);
        } elseif ($catch) {
            DB::query('UPDATE pkm_mypokedex SET is_owned = 1 WHERE nat_id = ' . $nat_id . ' AND user_id = ' . $user_id);
            Trainer::addExp($user_id, 1);
        }

        return !!$caught;

    }

    public static function getHatchTime ($egg_cycle) : int {
        return $_SERVER['REQUEST_TIME'] + floor($egg_cycle * 255 * (mt_rand(0, 5) + $egg_cycle * 0.6) / 6);
    }

    public static function getStat ($base_stat, $level, $stat, $nature, $ev, $iv) {
        if (!$base_stat) return 0;
        $ev_factor = floor(min(max($ev, 0), 255) / 4);
        $modifier  = $stat == 0 ? 0 : self::getNatureModifier($nature)[$stat];
        $result    = $stat == 0 ?
            ($base_stat == 1 ? 1 : floor(($base_stat * 2 + $ev_factor + $iv) * $level / 100) + $level + 10) :
            floor((floor(($base_stat * 2 + $ev_factor + $iv) * $level / 100) + 5) * $modifier);

        return $result;
    }

    public static function getStats ($info) {

        Verifier::assertExists($info, [
            'bs', 'level', 'nature', 'ev_hp', 'ev_atk', 'ev_def', 'ev_spatk', 'ev_spdef',
            'ev_spd', 'iv_hp', 'iv_atk', 'iv_def', 'iv_spatk', 'iv_spdef', 'iv_spd'
        ]);

        $lookup = ['hp', 'atk', 'def', 'spatk', 'spdef', 'spd'];
        $result = [];
        for ($i = 0; $i < 6; ++$i) {
            $suffix     = '_' . $lookup[$i];
            $result[$i] = self::getStat($info['bs'] . $suffix, $info['level'], $i, $info['nature'], $info['ev'] . $suffix, $info['iv'] . $suffix);
        }

        return $result;

    }

    public static function getNatureModifier ($nature) {
        $result = [1, 1, 1, 1, 1, 1];
        if (($nature - 1) % 6 !== 0) {
            $checkstr                           = '00121513142100252324515200535431323500344142454300';
            $result[$checkstr[$nature * 2 - 2]] = 1.1;
            $result[$checkstr[$nature * 2 - 1]] = 0.9;
        }
        return $result;
    }


    public static function getDevolution ($nat_id, $previous = FALSE) {
        include __DIR__ . '/../data/evolution-chain.php';

        if (!isset($species_family[$nat_id])) return $nat_id;

        $family = $species_family[$nat_id];
        $count  = count($family);

        if ($count <= 1) return $nat_id;
        if (!$previous) return $family[0][0];

        for ($i = $count - 1; $i > 0; --$i) {
            if (!in_array($nat_id, $family[$i])) continue;
            return $family[$i - 1][0];
        }

        return $nat_id;
    }

    public static function getHealingTime ($max_hp, $hp) {
        return ceil(($max_hp - $hp) * 6.6);
    }


    public static function getRemainHealingTime ($time_pc_sent, $max_hp, $hp) {
        return max(0, $time_pc_sent + self::getHealingTime($max_hp, $hp) - $_SERVER['REQUEST_TIME']);
    }

    /**
     * - 60 type (n <= 50, 50 <= n <= 68, 68 < n < 98, 98 <= n <= 100)
     * - 80, 100, 105, 125 type (All)
     * - 164 type (n < 15, 15 <= n <= 36, 36 <= n <= 100)
     * @param $exptype
     * @param $nextlevel
     * @return int|mixed
     */
    public static function getLevelupExp ($exptype, $nextlevel) {
        if ($nextlevel - 1 <= 0) return 0;

        $nextexp = 0;
        switch ($exptype) {
            case 1:
                if ($nextlevel <= 50) {
                    $nextexp = pow($nextlevel, 3) * (100 - $nextlevel) / 50;
                } elseif ($nextlevel > 50 && $nextlevel <= 68) {
                    $nextexp = pow($nextlevel, 3) * (150 - $nextlevel) / 100;
                } elseif ($nextlevel > 68 && $nextlevel < 98) {
                    $nextexp = pow($nextlevel, 3) * (1911 - 10 * $nextlevel) / 1500;
                } else {
                    $nextexp = floor(pow($nextlevel, 3) * (160 - $nextlevel) / 100);
                }
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
                if ($nextlevel < 15) {
                    $nextexp = pow($nextlevel, 3) * ($nextlevel + 73) / 150;
                } elseif ($nextlevel >= 15 && $nextlevel <= 36) {
                    $nextexp = pow($nextlevel, 3) * ($nextlevel + 14) / 50;
                } else {
                    $nextexp = pow($nextlevel, 3) * ($nextlevel + 64) / 100;
                }
                break;
        }
        return max(0, floor($nextexp));
    }


    public static function getEggPhase ($maturity) {
        return array_search(TRUE, [
            $maturity < 27,
            $maturity >= 27 && $maturity < 51,
            $maturity >= 51 && $maturity < 93,
            $maturity >= 93 && $maturity < 100,
            $maturity >= 100
        ]);
    }

    public static function getHappinessPhase ($happiness) {
        return array_search(TRUE, [
            $happiness < 50,
            $happiness >= 50 && $happiness < 90,
            $happiness >= 90 && $happiness < 150,
            $happiness >= 150 && $happiness < 220,
            $happiness >= 220
        ]);
    }

    public static function getMaturity ($met_time, $hatch_time) {
        return floor(($_SERVER['REQUEST_TIME'] - $met_time) / ($hatch_time - $met_time) * 100);
    }


    public static function getInfo ($info) {

        if ($info['nat_id']) {

            $info['maturity']  = self::getMaturity($info['met_time'], $info['time_hatched']);
            $info['egg_phase'] = self::getEggPhase($info['maturity']);

            if ($info['egg_phase'] === 4) self::hatchEgg($info['pkm_id']);

            unset($info['is_shiny'], $info['nature'], $info['ability'], $info['hp'], $info['new_moves'], $info['moves'], $info['maturity'], $info['time_hatched']);

        } else {

            $info['stats'] = self::getStats($info);

            $info['exp_this_level']  = self::getLevelupExp($info['exp_type'], $info['level']);
            $info['exp_required']    = self::getLevelupExp($info['exp_type'], $info['level'] + 1) - $info['exp_this_level'];
            $info['happiness_phase'] = self::getHappinessPhase($info['happiness']);
            $info['new_moves']       = $info['new_moves'] ? explode(',', $info['new_moves']) : [];
            $info['moves']           = $info['moves'] ? json_decode($info['moves'], true) : [];

        }

        unset($info['ev'], $info['iv'], $info['pv']);

    }


    /**
     * This is a rather 'complicated' method, it uses various of jump block, but it's easy to understand.
     * It separates processes into different parts:
     * LEVEL_UP - main loop, first line simulates while() to jump out of the loop
     * CALC_EXP - put outside to reduce code recyclibility, compare between old
     *            level and new level to jump into the loop.
     * LEARN_MOVE - goes after the CALC_EXP, check if there's a new move learnable
     * UPDATE - process after the loop
     * @param array $info (exp_type, level, exp, pkm_id, id, evolution_data, new_moves, moves, initial_user_id)
     * @param bool|int $rarecandy
     * @return array|void
     */
    public static function Levelup (&$info, $rarecandy = FALSE) : void {

        if (!$info['pkm_id'] || $info['level'] >= 100) return;

        $old_level         = $info['level'];
        $info['moves']     = unserialize($info['moves']);
        $info['new_moves'] = $info['new_moves'] ? explode(',', $info['new_moves']) : [];

        CALC_EXP: {
            $exp_required = [
                'current' => self::getLevelupExp($info['exp_type'], $info['level']),
                'next'    => self::getLevelupExp($info['exp_type'], $info['level'] + 1)
            ];
            if ($old_level != $info['level']) goto LEARN_MOVE;
        }

        if ($rarecandy) $info['exp'] = $exp_required['next'];

        LEVEL_UP: {
            if ($info['exp'] < $exp_required['next'] || $info['level'] >= 100) goto UPDATE;

            $evolved = FALSE;
            ++$info['level'];

            goto CALC_EXP;

            LEARN_MOVE: {
                self::LearnMove($info['nat_id'], $info['level'], $info['moves'], $info['new_moves']);
            }

            if (empty($evolved) && !empty($info['evolution_data'])) {
                $evolved = self::checkEvolve($info, $info['user_id']);
                if ($evolved) goto LEVEL_UP;
            }
        }

        UPDATE: {
            if ($old_level !== $info['level']) {
                $old_stats           = Obtain::Stat($old_level, $info['base_stat'], $info['idv_value'], $info['eft_value'], $info['nature'], $info['hp']);
                $new_stats           = Obtain::Stat($info['level'], $info['base_stat'], $info['idv_value'], $info['eft_value'], $info['nature']);
                $info['hp']          = ceil($old_stats['hp_percent'] * $new_stats['max_hp'] / 100);
                $info                = array_merge($info, $new_stats);
                self::$temp['moves'] = [];
                self::Update([
                    'level'     => $info['level'],
                    'new_moves' => '\'' . (!empty($info['new_moves']) ? implode(',', $info['new_moves']) : '') . '\'',
                    'hp'        => $info['hp'],
                    'exp'       => $info['exp'],
                ], ['pkm_id' => $info['pkm_id']], FALSE);
            }
        }

        self::$temp          = [];
        $info['exp_max']     = $exp_required['next'] - $exp_required['current'];
        $info['exp']         = $info['exp'] - $exp_required['current'];
        $info['exp_remain']  = $exp_required['next'] - $info['exp'];
        $info['exp_percent'] = min(round($info['exp'] / $info['exp_max'] * 100), 100);

        return;
    }

    public static function LearnMove ($nat_id, $level, &$moves, &$new_moves) {

        if (empty(self::$temp['moves'][$nat_id])) {
            $query = DB::query('SELECT move_id, learn_level FROM pkm_pkmmove WHERE generation = 6 AND nat_id = ' . $nat_id . ' AND approach = ' . MOVE_BY_LEVEL);
            while ($info = DB::fetch($query)) {
                if (Kit::ColumnSearch($moves, 'move_id', $info['move_id']) === FALSE && in_array($info['move_id'], $new_moves) === FALSE)
                    self::$temp['moves'][$nat_id][$info['learn_level']][] = $info['move_id'];
            }
        }

        if (!empty(self::$temp['moves'][$nat_id][$level]))
            $new_moves = array_merge(!is_array($new_moves) ? [] : $new_moves, self::$temp['moves'][$nat_id][$level]);
    }


    /**
     * The evolution table structure will a serialized string which will be parsing
     * into a two dimensional array in this format:
     *      [[id => 1, conditions => [condition1 => value1, condition2 => ...]], ...]
     * @external Kit::paramMerge
     * @param $pokemon
     * @param $user_id
     * @param array $param
     * @return bool
     */
    public static function checkEvolve ($pokemon, $user_id, $param = []) {

        // reject to evolve if target is holding a Everstone or cannot evolve
        if ($pokemon['item_holding'] == ITEM_EVERSTONE || !$pokemon['evolution_data']) return FALSE;

        // set up the parameters
        $param = General::combineParams([
            'map_id'            => 0,
            'item_used'         => 0,
            'trade_to_uid'      => 0,
            'trade_with_nat_id' => 0,
            'is_update'         => FALSE
        ], $param);

        $evolution_data = unserialize($pokemon['evolution_data']);
        if (!is_array($evolution_data)) return FALSE;

        $evolved   = $item_used = FALSE;
        $to_nat_id = 0;
        foreach ($evolution_data as $branch) {
            if (empty($branch)) continue;
            foreach ($branch as $condition => $value) {
                if ($condition == EVOSTRUC_BY_LEVEL && $pokemon['level'] < $value ||
                    $condition == EVOSTRUC_BY_HAPPINESS && $pokemon['happiness'] < $value ||
                    $condition == EVOSTRUC_BY_MAP && $param['map_id'] != $value ||
                    $condition == EVOSTRUC_BY_MOVELEARNT && !in_array($value, array_column($pokemon['moves'], 'move_id')) ||
                    $condition == EVOSTRUC_BY_GENDER && $pokemon['gender'] != $value ||
                    $condition == EVOSTRUC_BY_TIMEFRAME && General::getTimeFrame() != $value ||
                    $condition == EVOSTRUC_BY_TRADE && !$param['trade_to_uid'] ||
                    $condition == EVOSTRUC_BY_TRADEWITHPOKEMON && (!$param['trade_to_uid'] || $pokemon['nat_id'] != $param['trade_with_nat_id'])
                ) continue 2;

                if ($condition == EVOSTRUC_BY_PARTYPOKEMON) {
                    $party_pokemon = DB::result_first('SELECT nat_id FROM pkm_mypkm WHERE user_id = ' . $user_id . ' AND location IN (' . LOCATION_PARTY . ') AND nat_id = ' . $value);
                    if (!$party_pokemon) continue 2;
                } elseif ($condition == EVOSTRUC_BY_ITEM) {
                    $is_usable = DB::result_first('SELECT is_usable FROM pkm_itemdata_new WHERE item_id = ' . $value);
                    if ($is_usable && $param['item_used'] != $value || !$is_usable && $pokemon['item_holding'] != $value) {
                        continue 2;
                    } elseif ($is_usable && $param['item_used'] == $value) {
                        $item_used = TRUE;
                    }
                } elseif ($condition == EVOSTRUC_BY_OTHER) {
                    if ($value == EVOSTRUC_BY_OTHER_ATKGTDEF && $pokemon['atk'] < $pokemon['def'] ||
                        $value == EVOSTRUC_BY_OTHER_ATKLTDEF && $pokemon['atk'] > $pokemon['def'] ||
                        $value == EVOSTRUC_BY_OTHER_ATKEQDEF && $pokemon['atk'] != $pokemon['def'] ||
                        $value == EVOSTRUC_BY_OTHER_PVEGTEFIVE && ((($pokemon['pv'] & 0xFFFF0000) >> 8) % 65535) % 10 < 5 ||
                        $value == EVOSTRUC_BY_OTHER_PVLTFIVE && ((($pokemon['pv'] & 0xFFFF0000) >> 8) % 65535) % 10 >= 5
                    ) continue 2;
                }
            }
            $evolved   = TRUE;
            $to_nat_id = $branch[0];
            break;
        }

        return $param['is_update'] && $evolved ? self::evolve($pokemon, $to_nat_id, $user_id, $item_used) : $evolved;
    }


    public static function evolve ($pokemon, $to_nat_id, $user_id, bool $item_used) {

        static $pokemon_info = [];

        $trainer = DB::fetch_first('SELECT user_id, exp, trainer_id, secret_id FROM pkm_trainerdata WHERE user_id = ' . $user_id);

        Trainer::addExp($trainer['user_id'], 2);

        // Caching data
        if (!isset($pokemon_info[$pokemon['nat_id']])) {
            $pokemon_info[$pokemon['nat_id']] = DB::fetch_first('SELECT * FROM pkm_pkmdata WHERE nat_id = ' . $to_nat_id);
        }
        $info = &$pokemon_info[$pokemon['nat_id']];

        if (!$info) return FALSE;

        $gender   = PokemonGenerator::getGender($info['gender_ratio'], $pokemon['pv']);
        $is_shiny = PokemonGenerator::checkShiny($trainer['trainer_id'], $trainer['secret_id'], $pokemon['pv']);

        $item_holding = $item_used ? 0 : $info['item_holding'];
        $nickname     = $info['name'] === $info['nickname'] ? $info['name_zh'] : $pokemon['nickname'];

        $data = [
            'pkm_id'       => [DB_FIELD_NUMBER, $pokemon['pkm_id']],
            'nickname'     => [DB_FIELD_STRING, $nickname],
            'nat_id'       => [DB_FIELD_STRING, $to_nat_id],
            'gender'       => [DB_FIELD_NUMBER, $gender],
            'is_shiny'     => [DB_FIELD_NUMBER, (int) $is_shiny],
            'item_holding' => [DB_FIELD_NUMBER, $item_holding]
        ];
        DB::insert('pkm_mypkm', $data, TRUE);

        Trainer::updateStat($user_id, 'pkm_evolved', 1);

        self::registerPokedex($to_nat_id, $user_id, TRUE);

        // TODO
        if ($info['nat_id'] == 290) {
            $count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE location IN (' . LOCATION_PARTY . ') AND user_id = ' . $user_id);
            if ($count < 6) {
                DB::query('INSERT INTO pkm_mypkm
                        (nat_id, nickname, gender, psn_value, idv_value, eft_value, is_shiny, initial_user_id, time_daycare_sent, time_hatched, time_hatched, nature, level, exp,
                            time_pc_sent, happiness, beauty, moves, met_level, met_time, met_location, ability, user_id, item_captured, hp, form, location, status, new_moves, sprite_name)
                        SELECT 292, \'脱壳忍者\', 0, psn_value, idv_value, eft_value, is_shiny, initial_user_id, time_daycare_sent, time_hatched, time_hatched, nature, level, exp,
                            time_pc_sent, happiness, beauty, moves, met_level, met_time, met_location, (SELECT ability FROM pkm_pkmdata WHERE id = 292), user_id, item_captured, hp, form, ' . Obtain::DepositBox($param['user_id']) . ', status, new_moves, \'' . str_replace('291', '292', $spriteName) . '\'
                        FROM pkm_mypkm WHERE pkm_id = ' . $info['pkm_id']);
                self::registerPokedex(POKEMON_SHEDINJA, $param['user_id'], TRUE);
            }
        }

        $pokemon = array_merge($pokemon, [
            'id'             => $to_nat_id,
            'name'           => $info['name'],
            'nickname'       => $nickname,
            'gender'         => $gender,
            'evolution_data' => $info['evolution_data'],
            'base_stat'      => $info['base_stat']
        ]);

        return $info;

    }

    public static function hatchEgg ($pid) {

        $pokemon = DB::fetch_first('SELECT p.nat_id, p.name_zh name, user_id FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.hatch_nat_id WHERE m.pkm_id = ' . $pid);

        self::registerPokedex($pokemon['nat_id'], $pokemon['user_id'], !0);
        Trainer::updateStat($pokemon['user_id'], 'pkm_hatched', 1);

        DB::insert('pkm_mypkm', [
            'pkm_id'       => [DB_FIELD_NUMBER, $pid],
            'nat_id'       => [DB_FIELD_ORIGIN, 'hatch_nat_id'],
            'nickname'     => [DB_FIELD_STRING, $pokemon['name']],
            'exp'          => [DB_FIELD_NUMBER, 0],
            'level'        => [DB_FIELD_NUMBER, 1],
            'time_hatched' => [DB_FIELD_NUMBER, $_SERVER['REQUEST_TIME']],
            'happiness'    => [DB_FIELD_NUMBER, 120]
        ], TRUE);

    }


    public static function refreshPartyOrder ($user_id) {

        $query = DB::query('SELECT pkm_id FROM pkm_mypkm WHERE user_id = ' . $user_id . ' AND location IN (' . LOCATION_PARTY . ') ORDER BY location ASC');
        $data  = [];
        $i     = 0;

        while ($info = DB::fetch($query)) {
            $data[] = [
                'pkm_id'   => [DB_FIELD_NUMBER, $info['pkm_id']],
                'location' => [DB_FIELD_NUMBER, $i < 6 ? ++$i : 0]
            ];
        }

        if ($data) DB::insert('pkm_mypkm', $data, TRUE);

    }

    public static function Update ($fields, $where_clause, $level_up_check = TRUE, &$info = [], $pkm_id = 0) {
        if (!empty($where_clause['pkm_id'])) $pkm_id = $where_clause['pkm_id'];
        array_walk($fields, function (&$value, $key) {
            $value = $key . ' = ' . $value;
        });
        array_walk($where_clause, function (&$value, $key) {
            $value = $key . ' = ' . $value;
        });
        DB::query('UPDATE pkm_mypkm SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where_clause));
        // FIXME: level up
        if ($level_up_check && (isset($fields['exp']) || isset($fields['level']))) {
            if (empty($info) && $pkm_id)
                $info = DB::fetch_first('SELECT ' . Kit::FetchFields([FIELDS_POKEMON_LEVELUP]) . ' FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id WHERE m.pkm_id = ' . $pkm_id);
            self::Levelup($info);
        }
    }

    public static function moveLocation ($pkm_id, $to, $param = []) {

        $param = General::combineParams([
            'user_id'      => -1,
            'time_pc_sent' => -1,
            'hp'           => -1
        ], $param);

        $data = ['pkm_id' => $pkm_id, 'location' => $to];
        foreach ($param as $key => $value) {
            if ($value === -1) continue;
            $data[$key] = [DB_FIELD_NUMBER, $value];
        }
        DB::insert('pkm_mypkm', $data, TRUE);

    }


    public static function getSprite ($class, $type, $filename, $refresh = FALSE, $side = 0) {

        $filenameh = base_convert(hash('joaat', $filename . ($side === 1 ? '_b' : '')), 16, 32);
        $path      = ROOT_CACHE . '/image/' . $filenameh . '.' . $type;

        if (file_exists($path) && $refresh === FALSE) return $path;

        $data = explode('_', $filename);

        switch ($class) {
            case 'pokemon':

                if (count($data) < 5) {

                    return ROOT_CACHE . '/image/_unknownpokemon.png';

                } elseif ($data[1] == 327111 && $side === 0) {

                    /*
                        This is for spinda front sprite only
                        Do some spot's placement calculation and special layers to generate
                    */

                    $pv = [];

                    for ($i = 0; $i < 8; $i++) {
                        $pv[$i] = ('0x' . $data[5]{$i}) * 1;
                    }

                    $spot = [
                        [$pv[7], $pv[6]],
                        [$pv[5] + 24, $pv[4] + 2],
                        [$pv[3] + 3, $pv[2] + 16],
                        [$pv[1] + 15, $pv[0] + 18]
                    ];

                    $extrapath = ($data[4] == 1) ? '-shiny' : '';

                    $img  = imagecreatefrompng(ROOT_IMAGE . '/pokemon/front' . $extrapath . '/327.' . $type);
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

                    $extrapath = (($side === 1) ? '/back' : '/front') .
                        (($data[4] == 1) ? '-shiny' : '') .
                        (($data[2] == 1) ? '/female' : '') .
                        (($data[3] > 0) ? '/' . $data[1] . '-' . $data[3] : '/' . $data[1] . '.') .
                        (($type === 'jpeg') ? 'jpg' : $type);

                    copy(ROOT_IMAGE . '/pokemon' . $extrapath, $path);

                    return $path;
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

                if (!file_exists(ROOT_IMAGE . '/item/' . $data[1] . '.' . $type))
                    return ROOT_CACHE . '/image/_unknownitem.png';

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

                // Other sprites such as hp bar or exp bar, maybe more in the future

                if (in_array($data[0], ['hp', 'exp'])) {
                    $img  = imagecreatefromgif(ROOT_IMAGE . '/other/' . $data[0] . '_border.' . $type);
                    $imgb = imagecreatefromgif(ROOT_IMAGE . '/other/' . $data[0] . '_fill.' . $type);
                    imagecopy($img, $imgb, 1, 1, 0, 0, $data[2], 4);
                } else {
                    $head = 'imagecreatefrom' . $type;
                    $img  = $head(ROOT_IMAGE . '/other/' . $data[0] . '.' . $type);
                }

                break;
            case 'egg':
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

        if (isset($img)) {
            ob_start();
            imagepng($img);
            imagedestroy($img);
            $content = ob_get_contents();
            ob_clean();
            $handle = fopen($path, 'w+');
            fwrite($handle, $content);
            fclose($handle);
        }

        return $path;

    }

}