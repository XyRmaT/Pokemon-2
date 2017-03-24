<?php

class PokemonGenerator {

    public static function getGender (string $gender_rate, $pv) : int {
        switch ($gender_rate) {
            case 255:
                $gender = GENDERLESS;
                break;
            case 254:
                $gender = GENDER_FEMALE;
                break;
            case 0:
                $gender = GENDER_MALE;
                break;
            default:
                $gender = $pv & 0xFF >= $gender_rate ? 1 : 2;
                break;
        }

        return $gender;
    }

    public static function getMoves ($nat_id, $level, $generation = 7) : array {
        $moves = [];
        $query = DB::query('SELECT p.move_id, m.pp, m.pp pp_total, 0 pp_up
                            FROM pkm_pkmmove p
                            LEFT JOIN pkm_movedata m ON p.move_id = m.move_id
                            WHERE p.nat_id = ' . $nat_id . ' AND p.learn_level <= ' . $level . ' AND
                                   p.approach = ' . MOVE_BY_LEVEL . ' AND p.generation = ' . $generation . '
                            ORDER BY p.learn_level DESC LIMIT 4');
        while ($info = DB::fetch($query))
            $moves[] = $info;
        return $moves;
    }


    /**
     * First get the maximum box possible by using the formula:
     * user's boxes + initial boxes + 100
     * Which get a number greater than or equal to 100
     * Then obtain the amount of pokemon in each boxes and party the trainer have
     * @param $box_quantity
     * @param $pkm_limit
     * @param $user_id
     * @return bool|int
     */
    public static function getIdleDepositBox ($box_quantity, $pkm_limit, $user_id) {

        static $box = [];

        $maxboxnum = $box_quantity + 100;

        if (empty($box)) {
            $query = DB::query('SELECT location, COUNT(*) total FROM pkm_mypkm WHERE user_id = ' . $user_id . ' AND (location IN (' . LOCATION_PARTY . ') OR location > 100) GROUP BY location');
            while ($pokemon = DB::fetch($query))
                $box[$pokemon['location']] = $pokemon['total'];
        }
        for ($i = 1; $i <= $maxboxnum; $i++) {
            if (empty($box[$i]) || $i > 100 && $box[$i] < $pkm_limit) {
                $box[$i] = isset($box[$i]) ? $box[$i] + 1 : 1;
                return $i;
            }
            if ($i === 6) $i = 100;
        }

        return FALSE;

    }

    protected static function generateAbility ($ability, $ability_b, $ability_hidden, $is_hidden) {
        return $is_hidden ? $ability_hidden : array_filter($tmp = [$ability, $ability_b])[mt_rand(0, count($tmp) - 1)];
    }

    protected static function generatePV ($trainer_id, $secret_id, $is_shiny) : int {
        if ($is_shiny) {
            $pv_parts = [mt_rand(0x0, 0xFFFF), ''];
            $pv_calc  = str_split(str_pad(decbin($trainer_id ^ $secret_id ^ $pv_parts[0]), 16, '0', STR_PAD_LEFT));
            foreach ($pv_calc as $key => $val)
                $pv_parts[1] .= ($key > 12) ? (($val === '0') ? 1 : 0) : $val;
            return ($pv_parts[0] << 8) + hexdec($pv_parts[1]);
        } else {
            return mt_rand(0x0, 0xFFFFFFFF);
        }
    }

    public static function checkShiny ($trainer_id, $secret_id, $pv) : bool {
        return $trainer_id ^ $secret_id ^ (($pv & 0xFFFF0000) >> 8) ^ ($pv & 0xFFFF) < 16;
    }

    public static function generatePokemon ($nat_id, $user_id, $param = []) {

        global $trainer, $system;

        $default_param = [
            'met_location'      => 0,
            'met_level'         => 1,
            'item_holding'      => 1,
            'is_wild'           => FALSE,
            'is_egg'            => FALSE,
            'is_bad_egg'        => FALSE,
            'is_hidden_ability' => FALSE,
            'is_shiny'          => FALSE,
            'egg_data'          => 'r',
            'father_moves'      => [],
            'mother_moves'      => [],
            'location'          => 0
        ];

        $param = General::combineParams($default_param, $param);

        // If it's an egg, check $default['egg'], applying following possibles
        //  - Equals to 'r', randomize an hatchable pokemon id
        //  - Equals to 's,x', randomlize an egg from a series of ids (obtain from the database pkm_eggdata)
        // If it's a specified id egg, it passes id straight away to the generator
        if ($param['is_egg']) {
            if ($nat_id) {
                $nat_id = PokemonGeneral::getDevolution($nat_id);
            } elseif (!empty($param['egg_data']) && intval($param['egg_data']) === 0) {
                $set = explode(':', $param['egg_data']);
                if ($set[0] === 'r') {
                    $nat_id = DB::result_first('SELECT GROUP_CONCAT(nat_id SEPARATOR \',\') FROM pkm_pkmextra WHERE is_hatchable = 1');
                    $nat_id = $nat_id ? explode(',', $nat_id) : 0;
                    $nat_id = $nat_id ? $nat_id[array_rand($nat_id)] : 0;
                } elseif ($set[0] === 's') {
                    $eggset = DB::fetch_first('SELECT eggset, name_zh name FROM pkm_eggdata WHERE set_id = ' . $set[1]);
                    $nat_id = explode(',', $eggset['eggset']);
                    $nat_id = $nat_id[array_rand($nat_id)];
                } else {
                    $nat_id = 1;
                }
            } else {
                $param['is_bad_egg'] = TRUE;
                $nat_id              = 1;
            }
        }


        // Determine the location of where this pokemon will go using the method Obtain::DepositBox().
        $location = $param['location'] ?: self::getIdleDepositBox($trainer['box_quantity'] + $system['initial_box'], $system['pkm_per_box'], $user_id);
        if ($location === FALSE) return ERROR_NO_SUCH_PCBOX;

        $pokemon = DB::fetch_first('SELECT nat_id, name_zh name, gender_rate, ability, ability_b, ability_hidden, 
                                            happiness_initial, height, weight, has_female_sprite, base_stat, exp_type, 
                                            egg_cycle, type, type_b, bs_hp, hold_item_common, hold_item_rare
                                    FROM pkm_pkmdata
                                    WHERE form = 0 AND nat_id = ' . $nat_id);

        $tids = DB::fetch_first('SELECT trainer_id, secret_id FROM pkm_trainerdata WHERE user_id = ' . $user_id);
        if (!$tids) return ERROR_NO_SUCH_TRAINER;

        $nature       = mt_rand(1, 25);
        $ivs          = [mt_rand(0, 31), mt_rand(0, 31), mt_rand(0, 31), mt_rand(0, 31), mt_rand(0, 31), mt_rand(0, 31)];
        $exp          = PokemonGeneral::getLevelupExp($pokemon['exp_type'], $param['met_level']);
        $hp           = PokemonGeneral::getStat($pokemon['bs_hp'], $param['met_level'], STAT_HP, 0, 0, $ivs[STAT_HP]);
        $pv           = self::generatePV($tids['trainer_id'], $tids['secret_id'], $param['is_shiny']);
        $gender       = self::getGender($pokemon['gender_rate'], $pv);
        $ability      = self::generateAbility($pokemon['ability'], $pokemon['ability_b'], $pokemon['ability_hidden'], $param['is_hidden_ability']);
        $is_shiny     = self::checkShiny($tids['trainer_id'], $tids['secret_id'], $pv) ? 1 : 0;
        $item_holding = ($tmp = mt_rand(0, 100)) < 50 ? $pokemon['hold_item_common'] : ($tmp < 55 ? $pokemon['hold_item_rare'] : 0);

        // TODO - Intial form
        $form = 0;

        $hatch_nat_id = $time_hatched = 0;
        if ($param['is_egg']) {
            $hatch_nat_id = $nat_id;
            $time_hatched = PokemonGeneral::getHatchTime($pokemon['egg_cycle']);
            $nat_id       = 0;
        }

        if (!$nat_id && !$hatch_nat_id) {
            $name = General::getText('bad_egg');
        } elseif (!empty($eggset['name'])) {
            $name = $eggset['name'];
        } elseif (!empty($hatch_nat_id)) {
            $name = $pokemon['name'] . General::getText('part_\'s_egg');
        } else {
            $name = $pokemon['name'];
        }

        // TODO: Move inheritance
        // Decide the moves the pokemon has in the current level.
        $moves = self::getMoves($hatch_nat_id > 0 ? $hatch_nat_id : $nat_id, $param['met_level']);
        $moves = json_encode($moves);

        // Pokedex register
        if ($nat_id) {
            PokemonGeneral::registerPokedex($nat_id, $user_id, !$param['is_wild']);
        }

        // TODO: name for language pack
        $data = [
            'nat_id'          => [DB_FIELD_NUMBER, $nat_id],
            'gender'          => [DB_FIELD_NUMBER, $gender],
            'pv'              => [DB_FIELD_NUMBER, $pv],
            'idv_hp'          => [DB_FIELD_NUMBER, $ivs[0]],
            'idv_atk'         => [DB_FIELD_NUMBER, $ivs[1]],
            'idv_def'         => [DB_FIELD_NUMBER, $ivs[2]],
            'idv_spatk'       => [DB_FIELD_NUMBER, $ivs[3]],
            'idv_spdef'       => [DB_FIELD_NUMBER, $ivs[4]],
            'idv_spd'         => [DB_FIELD_NUMBER, $ivs[5]],
            'is_shiny'        => [DB_FIELD_NUMBER, $is_shiny],
            'nature'          => [DB_FIELD_NUMBER, $nature],
            'level'           => [DB_FIELD_NUMBER, $param['met_level']],
            'exp'             => [DB_FIELD_NUMBER, $exp],
            'item_holding'    => [DB_FIELD_NUMBER, $item_holding],
            'happiness'       => [DB_FIELD_NUMBER, $pokemon['happiness_initial']],
            'met_location'    => [DB_FIELD_NUMBER, $param['met_location']],
            'ability'         => [DB_FIELD_NUMBER, $ability],
            'user_id'         => [DB_FIELD_NUMBER, $user_id],
            'hp'              => [DB_FIELD_NUMBER, $hp],
            'form'            => [DB_FIELD_NUMBER, $form],
            'time_hatched'    => [DB_FIELD_NUMBER, $time_hatched],
            'hatch_nat_id'    => [DB_FIELD_NUMBER, $hatch_nat_id],
            'met_level'       => [DB_FIELD_NUMBER, $param['met_level']],
            'met_time'        => [DB_FIELD_NUMBER, $_SERVER['REQUEST_TIME']],
            'initial_user_id' => [DB_FIELD_NUMBER, $user_id],
            'item_captured'   => [DB_FIELD_NUMBER, 1],
            'location'        => [DB_FIELD_NUMBER, $location],
            'moves'           => [DB_FIELD_STRING, $moves],
            'nickname'        => [DB_FIELD_STRING, $name],
        ];

        return DB::insert('pkm_mypkm', $data);

    }
}