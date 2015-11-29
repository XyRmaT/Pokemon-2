<?php

switch($_GET['process']) {
	case 'pmsave':
		
		$dccount = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $_G['uid'] . ' AND place = 7');
		
		if($dccount >= 2) {
		
			$return['msg'] = '不行哟，我们只能帮你照看最多两只精灵哟~';
			
			break;

		}
		
		$pokemon = DB::fetch_first('SELECT id, pid, nickname, place FROM pkm_mypkm WHERE pid = ' . intval($_GET['pid']));
		
		if(empty($pokemon))
		
			$return['msg'] = '……';
			
		elseif(empty($pokemon['id']))
		
			$return['msg'] = '我们不负责孵化鸡蛋！';
		
		elseif($pokemon['place'] > 6)
		
			$return['msg'] = '咦？' . $pokemon['nickname'] . '不在身上！';
			
		else {
		
			DB::query('UPDATE pkm_mypkm SET place = 7, dayctime = ' . $_SERVER['REQUEST_TIME'] . ', eggcheck = ' . $_SERVER['REQUEST_TIME'] . ' WHERE pid = ' . $pokemon['pid']);
			DB::query('UPDATE pkm_mypkm SET egg = 0 WHERE uid = ' . $_G['uid'] . ' AND place = 7');
			
			ob_start();
			
			include ROOT . '/source/index/daycare.php';
			include template('index/daycare', 'pkm');
			
			$return['js']	= '$(".dc-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';
			$return['msg']	= '那' . $pokemon['nickname'] . '就交给我们来照顾了，请放心吧！';
		
		}
		
	break;
	case 'pmtake':
	
		Kit::Library('class', array('obtain'));

		$pid		= intval($_GET['pid']);
		$pokemon	= DB::fetch_first('SELECT pid, dayctime, egg, place, nickname FROM pkm_mypkm WHERE pid = ' . $pid);

		if(empty($pokemon))
		
			$return['msg'] = '……';
			
		elseif($pokemon['egg'] !== '0')
		
			$return['msg'] = '请先把精灵蛋领回去！';

		else {

			$cost = (floor(($_SERVER['REQUEST_TIME'] - $pokemon['dayctime']) / 2400) + 1) * 5;

			if($user['money'] - $cost < 0) {

				$return['msg'] = '抚养费交足了再来领回去吧！';
				
				break;

			}

			$place = Obtain::DepositBox($_G['uid']);

			if($place === FALSE) {

				$return['msg'] = '没地方存放精灵了哟~';
				
				break;
				
			}
			
			$incexp = floor((time() - $pokemon['dayctime']) / 12);

			DB::query('UPDATE pkm_mypkm SET egg = 0 WHERE uid = ' . $_G['uid'] . ' AND place = 7');
			DB::query('UPDATE pkm_mypkm SET dayctime = 0, place = ' . $place . ', exp = exp + ' . $incexp . ' WHERE pid = ' . $pokemon['pid']);
			
			DB::query('UPDATE pre_common_member_count SET ' . $SYS['moneyext'] . ' = ' . $SYS['moneyext'] . ' - ' . $cost . ' WHERE uid = ' . $_G['uid']);

			$return['msg']	= '抚养费共计' . $cost . $SYS['moneyname'] . '。' . "\n" . $pokemon['nickname'] . '表示很高兴！' . (($place > 100) ? "\n" . '但身上放不了更多的精灵了，只好将他传送到' . ($place - 100) . '号精灵箱。' : '');
			
			ob_start();
			
			include ROOT . '/source/index/daycare.php';
			include template('index/daycare', 'pkm');
			
			$return['js']	= '$(".dc-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';
			
		}

	break;
	case 'eggtake':

		$query	= DB::query('SELECT id, gender FROM pkm_mypkm WHERE uid = ' . $_G['uid'] . ' AND place = 7 AND egg = 1 LIMIT 2');
		$id		= $gender = array();
		
		while($info = DB::fetch($query)) {

			$id[]		= $info['id'];
			$gender[]	= $info['gender'];

		}

		if(count($id) < 2) {

			$return['msg'] = '哪来的蛋……？';

		} else {
		
			Kit::Library('class', array('obtain', 'pokemon'));

			if(($dittokey = array_search(132, $id)) !== FALSE) {

				$id = Obtain::Devolution(($dittokey === 0) ? $id[1] : $id[0]);
			
			} else {

				$malekey	= array_search(2, $gender);
				$id			= Obtain::Devolution($id[$malekey]);

			}

			$code = Pokemon::Generate($id, $_G['uid'], array('mtplace' => 601, 'egg' => 1));

			if($code === 3) {

				$return['msg'] = '身上和箱子都满了！';
				
				break;

			}

			DB::query('UPDATE pkm_mypkm SET egg = 0 WHERE uid = ' . $_G['uid'] . ' AND place = 7 AND egg = 1 LIMIT 2');
			
			$user['addexp'] += 6;
			
			ob_start();
			
			include ROOT . '/source/index/daycare.php';
			include template('index/daycare', 'pkm');
			
			$return['js']	= '$(".dc-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';
			$return['msg'] = '请好好照顾它！';

		}

	break;
}

?>