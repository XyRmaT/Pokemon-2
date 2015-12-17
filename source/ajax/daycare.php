<?php

switch($_GET['process']) {
	case 'pmsave':

		$daycareCount = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location = 7');

		if($daycareCount >= 2) {
			$return['msg'] = '不行哟，我们只能帮你照看最多两只精灵哟~';
			break;
		}

		$pokemon = DB::fetch_first('SELECT nat_id, pkm_id, nickname, location FROM pkm_mypkm WHERE pkm_id = ' . intval($_GET['pkm_id']));

		if(empty($pokemon))
			$return['msg'] = '……';
		elseif(empty($pokemon['nat_id']))
			$return['msg'] = '我们不负责孵化鸡蛋！';
		elseif($pokemon['location'] > 6)
			$return['msg'] = '咦？' . $pokemon['nickname'] . '不在身上！';
		else {

			DB::query('UPDATE pkm_mypkm SET location = 7, time_daycare_sent = ' . $_SERVER['REQUEST_TIME'] . ', time_egg_checked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE pkm_id = ' . $pokemon['pkm_id']);
			DB::query('UPDATE pkm_mypkm SET time_hatched = 0 WHERE uid = ' . $trainer['uid'] . ' AND location = 7');

			ob_start();

			include ROOT . '/source/index/daycare.php';
			include template('index/daycare', 'pkm');

			$return['js']  = '$(".dc-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';
			$return['msg'] = '那' . $pokemon['nickname'] . '就交给我们来照顾了，请放心吧！';

		}

		break;
	case 'pmtake':

		Kit::Library('class', ['obtain']);

		$pid     = intval($_GET['pkm_id']);
		$pokemon = DB::fetch_first('SELECT pkm_id, time_daycare_sent, time_hatched, location, nickname FROM pkm_mypkm WHERE pkm_id = ' . $pid);

		if(empty($pokemon))

			$return['msg'] = '……';

		elseif($pokemon['time_hatched'] !== '0')

			$return['msg'] = '请先把精灵蛋领回去！';

		else {

			$cost = (floor(($_SERVER['REQUEST_TIME'] - $pokemon['time_daycare_sent']) / 2400) + 1) * 5;

			if($trainer['currency'] - $cost < 0) {

				$return['msg'] = '抚养费交足了再来领回去吧！';

				break;

			}

			$location = Obtain::DepositBox($trainer['uid']);

			if($location === FALSE) {

				$return['msg'] = '没地方存放精灵了哟~';

				break;

			}

			$incexp = floor((time() - $pokemon['time_daycare_sent']) / 12);

			DB::query('UPDATE pkm_mypkm SET time_hatched = 0 WHERE uid = ' . $trainer['uid'] . ' AND location = 7');
			DB::query('UPDATE pkm_mypkm SET time_daycare_sent = 0, location = ' . $location . ', exp = exp + ' . $incexp . ' WHERE pkm_id = ' . $pokemon['pkm_id']);

            App::CreditsUpdate($trainer['uid'], -$cost);

			$return['msg'] = '抚养费共计' . $cost . $system['currency_name'] . '。' . "\n" . $pokemon['nickname'] . '表示很高兴！' . (($location > 100) ? "\n" . '但身上放不了更多的精灵了，只好将他传送到' . ($location - 100) . '号精灵箱。' : '');

			ob_start();

			include ROOT . '/source/index/daycare.php';
			include template('index/daycare', 'pkm');

			$return['js'] = '$(".dc-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		}

		break;
	case 'eggtake':

		$query = DB::query('SELECT id, gender FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location = 7 AND time_hatched = 1 LIMIT 2');
		$id    = $gender = [];

		while($info = DB::fetch($query)) {

			$id[]     = $info['nat_id'];
			$gender[] = $info['gender'];

		}

		if(count($id) < 2) {

			$return['msg'] = '哪来的蛋……？';

		} else {

			Kit::Library('class', ['obtain', 'pokemon']);

			if(($dittokey = array_search(132, $id)) !== FALSE) {

				$id = Obtain::Devolution(($dittokey === 0) ? $id[1] : $id[0]);

			} else {

				$malekey = array_search(2, $gender);
				$id      = Obtain::Devolution($id[$malekey]);

			}

			$code = Pokemon::Generate($id, $trainer['uid'], ['met_location' => 601, 'time_hatched' => 1]);

			if($code === 3) {

				$return['msg'] = '身上和箱子都满了！';

				break;

			}

			DB::query('UPDATE pkm_mypkm SET time_hatched = 0 WHERE uid = ' . $trainer['uid'] . ' AND location = 7 AND time_hatched = 1 LIMIT 2');

			$trainer['addexp'] += 6;

			ob_start();

			include ROOT . '/source/index/daycare.php';
			include template('index/daycare', 'pkm');

			$return['js']  = '$(".dc-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';
			$return['msg'] = '请好好照顾它！';

		}

		break;
}

?>