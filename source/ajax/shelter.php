<?php

switch($_GET['process']) {
	case 'claim':

		$cost = 200;

		Kit::Library('class', ['obtain', 'pokemon']);

		$info = DB::fetch_first('SELECT pkm_id, nat_id, uid_initial FROM pkm_mypkm WHERE pkm_id = ' . intval($_GET['pkm_id']) . ' AND uid = 0 AND location = 9');

		if(empty($info)) {

			$return['msg'] = '已经有好心人领走它了……<br>';

			break;

		} elseif($trainer['currency'] - $cost < 0) {

			$return['msg'] = $system['currency_name'] . '没带够！要知道我们也是得生存的啊！';

			break;

		} elseif(($location = Obtain::DepositBox($trainer['uid'])) === FALSE) {

			$return['msg'] = '身上和箱子都满了，你没办法携带更多的精灵了！';

			break;

		}

		DB::query('UPDATE pkm_mypkm SET location = ' . $location . ', uid = ' . $trainer['uid'] . ' WHERE pkm_id = ' . $info['pkm_id']);

        App::CreditsUpdate($trainer['uid'], -$cost);
		Pokemon::DexRegister($info['nat_id'], !0);

		if($info['uid_initial'] != $trainer['uid'])

			$trainer['addexp'] += 4;

		$return['succeed'] = !0;
		$return['msg']     = '好人一定会有好报的！';

		break;
}