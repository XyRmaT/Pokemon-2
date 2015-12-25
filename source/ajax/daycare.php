<?php

switch($_GET['process']) {
    case 'pokemon-leave':

        $pkm_count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location = ' . LOCATION_DAYCARE);

        if($pkm_count >= 2) {
            $return['msg'] = Obtain::Text('daycare_max_reached');
            break;
        }

        $pokemon = DB::fetch_first('SELECT nat_id, pkm_id, nickname, location FROM pkm_mypkm WHERE pkm_id = ' . intval($_GET['pkm_id']));

        if(empty($pokemon)) {
            $return['msg'] = Obtain::Text('no_such_pokemon');
        } elseif(empty($pokemon['nat_id'])) {
            $return['msg'] = Obtain::Text('daycare_is_egg');
        } elseif($pokemon['location'] > 6) {
            $return['msg'] = Obtain::Text('daycare_not_in_party', [$pokemon['nickname']]);
        } else {
            DB::query('UPDATE pkm_mypkm SET time_hatched = 0, has_egg = 0, location = ' . LOCATION_DAYCARE . ', time_daycare_sent = ' . $_SERVER['REQUEST_TIME'] . ', time_egg_checked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE pkm_id = ' . $pokemon['pkm_id']);
            $return['msg'] = Obtain::Text('daycare_leave_succeed', [$pokemon['nickname']]);
        }

        break;
    case 'pokemon-take':

        $pkm_id  = intval($_GET['pkm_id']);
        $pokemon = DB::fetch_first('SELECT time_daycare_sent, time_hatched, location, nickname FROM pkm_mypkm WHERE pkm_id = ' . $pkm_id);

        if(empty($pokemon)) {
            $return['msg'] = Obtain::Text('no_such_pokemon');
        } elseif($pokemon['has_egg'] === '1') {
            $return['msg'] = Obtain::Text('daycare_take_egg_first');
        } else {

            $values = Obtain::DaycareInfo($pokemon['time_daycare_sent']);

            if($trainer['currency'] - $values['cost'] < 0) {
                $return['msg'] = Obtain::Text('daycare_no_money');
                break;
            }

            $location = Obtain::DepositBox($trainer['uid']);
            if($location === FALSE) {
                $return['msg'] = Obtain::Text('no_available_box');
                break;
            }

            DB::query('UPDATE pkm_mypkm SET time_egg_checked = 0 WHERE uid = ' . $trainer['uid'] . ' AND location = ' . LOCATION_DAYCARE);
            DB::query('UPDATE pkm_mypkm SET time_daycare_sent = 0, location = ' . $location . ', exp = exp + ' . $values['exp_increased'] . ' WHERE pkm_id = ' . $pkm_id);

            App::CreditsUpdate($trainer['uid'], -$cost);

            $return['msg'] = Obtain::Text('daycare_pay_succeed', [$values['cost'], $pokemon['nickname']]) .
                ($location > 100 ? PHP_EOL . Obtain::Text('daycare_moved_to_box', [$location - 100]) : '');

        }

        break;
    case 'egg-take':

        $query  = DB::query('SELECT nat_id, gender FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location = ' . LOCATION_DAYCARE . ' AND has_egg = 1 LIMIT 2');
        $nat_id = $gender = [];

        while($info = DB::fetch($query)) {
            $nat_id[] = $info['nat_id'];
            $gender[] = $info['gender'];
        }

        if(count($nat_id) < 2) {
            $return['msg'] = Obtain::Text('daycare_no_egg');
        } else {

            Kit::Library('class', ['obtain', 'pokemon']);

            if(($key_ditto = array_search(132, $nat_id)) !== FALSE) {
                $nat_id = Obtain::Devolution($key_ditto === 0 ? $nat_id[1] : $nat_id[0]);
            } else {
                $key_male = array_search(2, $gender);
                $nat_id   = Obtain::Devolution($nat_id[$key_male]);
            }

            $code = Pokemon::Generate($nat_id, $trainer['uid'], ['met_location' => 601, 'time_hatched' => 1]);

            if($code === 3) {
                $return['msg'] = '身上和箱子都满了！';
                break;
            }

            DB::query('UPDATE pkm_mypkm SET time_hatched = 0 WHERE uid = ' . $trainer['uid'] . ' AND location = 7 AND time_hatched = 1 LIMIT 2');

            $trainer['addexp'] += 6;

            $return['msg'] = '请好好照顾它！';

        }

        break;
}

?>