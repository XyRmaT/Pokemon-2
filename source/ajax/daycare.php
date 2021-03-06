<?php

switch($process) {
    case 'put-pokemon':

        $pkm_id = !empty($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if(!$pkm_id) {
            $return['msg'] = General::getText('no_such_pokemon');
            break;
        }

        $pkm_count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE user_id = ' . $trainer['user_id'] . ' AND location = ' . LOCATION_DAYCARE);

        if($pkm_count >= 2) {
            $return['msg'] = General::getText('daycare_max_reached');
            break;
        }

        $pokemon = DB::fetch_first('SELECT nat_id, pkm_id, nickname, location FROM pkm_mypkm WHERE pkm_id = ' . $pkm_id);

        if(empty($pokemon)) {
            $return['msg'] = General::getText('no_such_pokemon');
        } elseif(!$pokemon['nat_id']) {
            $return['msg'] = General::getText('daycare_is_egg');
        } elseif($pokemon['location'] > 6) {
            $return['msg'] = General::getText('daycare_not_in_party', [$pokemon['nickname']]);
        } else {
            DB::query('UPDATE pkm_mypkm SET has_egg = 0' . ', time_egg_checked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE location = ' . LOCATION_DAYCARE);
            DB::query('UPDATE pkm_mypkm SET has_egg = 0, location = ' . LOCATION_DAYCARE . ', time_egg_checked = ' . $_SERVER['REQUEST_TIME'] . ', time_daycare_sent = ' . $_SERVER['REQUEST_TIME'] . ' WHERE pkm_id = ' . $pkm_id);
            $return['msg'] = General::getText('daycare_put_succeed', [$pokemon['nickname']]);
        }

        break;
    case 'take-pokemon':

        $pkm_id = !empty($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if(!$pkm_id) {
            $return['msg'] = General::getText('no_such_pokemon');
            break;
        }

        $pokemon = DB::fetch_first('SELECT time_daycare_sent, location, nickname, has_egg FROM pkm_mypkm WHERE pkm_id = ' . $pkm_id);

        if(empty($pokemon)) {
            $return['msg'] = General::getText('no_such_pokemon');
        } elseif($pokemon['has_egg'] === '1') {
            $return['msg'] = General::getText('daycare_take_egg_first');
        } else {

            $values = Obtain::DaycareInfo($pokemon['time_daycare_sent']);

            if($trainer['currency'] - $values['cost'] < 0) {
                $return['msg'] = General::getText('daycare_no_money');
                break;
            }

            $location = Obtain::DepositBox($trainer['user_id']);
            if($location === FALSE) {
                $return['msg'] = General::getText('no_available_box');
                break;
            }

            $info = [];
            PokemonGeneral::Update(['time_egg_checked' => 0], [
                'user_id'      => $trainer['user_id'],
                'location' => LOCATION_DAYCARE
            ]);
            PokemonGeneral::Update([
                'time_daycare_sent' => 0,
                'location'          => $location,
                'exp'               => 'exp + ' . $values['exp_increased']
            ], ['pkm_id' => $pkm_id], TRUE, $info, $pkm_id);

            App::CreditsUpdate($trainer['user_id'], -$values['cost']);

            $return['msg'] = General::getText('daycare_pay_succeed', [$values['cost'], $pokemon['nickname']]) .
                ($location > 100 ? General::getText('daycare_moved_to_box', [$location - 100]) : '');

        }

        break;
    case 'take-egg':

        $query  = DB::query('SELECT nat_id, gender FROM pkm_mypkm WHERE user_id = ' . $trainer['user_id'] . ' AND location = ' . LOCATION_DAYCARE . ' AND has_egg = 1 LIMIT 2');
        $nat_id = $gender = [];

        while($info = DB::fetch($query)) {
            $nat_id[] = $info['nat_id'];
            $gender[] = $info['gender'];
        }

        if(count($nat_id) < 2) {
            $return['msg'] = General::getText('daycare_no_egg');
        } else {

            $key_ditto  = array_search(132, $nat_id);
            $key_male   = array_search(2, $gender);
            $is_bad_egg = $key_ditto === FALSE && $key_male === FALSE || count($nat_id) > 2;
            $nat_id     = $is_bad_egg ? 0 : $nat_id[$key_ditto === FALSE ? intval(!$key_ditto) : $key_male];
            $code       = PokemonGeneral::Generate($nat_id, $trainer['user_id'], [
                'met_location' => 601,
                'time_hatched' => 1,
                'is_egg'       => TRUE,
                'is_bad_egg'   => $is_bad_egg
            ]);

            if($code === 3) {
                $return['msg'] = General::getText('location_full');
                break;
            }

            DB::query('UPDATE pkm_mypkm
                        SET has_egg = 0, time_egg_checked = ' . $_SERVER['REQUEST_TIME'] . '
                        WHERE user_id = ' . $trainer['user_id'] . ' AND location = ' . LOCATION_DAYCARE);

            Trainer::AddExp($trainer, 6, TRUE);
            $return['msg'] = General::getText('daycare_take_care');
        }

        break;
}

include ROOT . '/source/index/daycare.php';
$return['data'] = ['pokemon' => $pokemon, 'party' => $party, 'egg_chance' => $egg_chance];