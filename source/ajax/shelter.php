<?php

switch($_GET['process']) {
	case 'claim':

		$cost = 200;

		Kit::Library('class', ['obtain', 'pokemon']);

		$info = DB::fetch_first('SELECT pid, id, originuid FROM pkm_mypkm WHERE pid = ' . intval($_GET['pid']) . ' AND uid = 0 AND place = 9');

		if(empty($info)) {

			$return['msg'] = '已经有好心人领走它了……<br>';

			break;

		} elseif($trainer['money'] - $cost < 0) {

			$return['msg'] = $system['currency_name'] . '没带够！要知道我们也是得生存的啊！';

			break;

		} elseif(($place = Obtain::DepositBox($trainer['uid'])) === FALSE) {

			$return['msg'] = '身上和箱子都满了，你没办法携带更多的精灵了！';

			break;

		}

		DB::query('UPDATE pkm_mypkm SET place = ' . $place . ', uid = ' . $trainer['uid'] . ' WHERE pid = ' . $info['pid']);

        App::CreditsUpdate($trainer['uid'], -$cost);
		Pokemon::Register($info['id'], !0);

		if($info['originuid'] != $trainer['uid'])

			$trainer['addexp'] += 4;

		$return['succeed'] = !0;
		$return['msg']     = '好人一定会有好报的！';

		break;
}