<?php

Kit::Library('class', ['obtain']);

switch($_GET['section']) {
	default:

		/*
			Fetch pokemon data where in healing mode
		*/

		$query = DB::query('SELECT m.pid, m.nickname, m.gender, m.ev, m.level, m.nature, m.iv, m.hp, m.id, m.hltime, m.imgname, p.bs FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.place = 8 AND m.uid = ' . $_G['uid'] . ' ORDER BY m.hltime DESC');
		$heal  = [];

		while($info = DB::fetch($query)) {

			$info               = array_merge($info, Obtain::Stat($info['level'], $info['bs'], $info['iv'], $info['ev'], $info['nature']));
			$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['imgname']);
			$info['needtime']   = ceil(($info['maxhp'] - $info['hp']) * 6.6);
			$info['rmtime']     = max(0, $info['hltime'] + $info['needtime'] - $_SERVER['REQUEST_TIME']);
			$info['hltime']     = [floor($info['rmtime'] / 60 / 24), round($info['rmtime'] / 60)];
			$info['gender']     = Obtain::GenderSign($info['gender']);

			if($info['rmtime'] / 60 <= 0)

				$info['fullheal'] = TRUE;

			$heal[] = $info;

		}

		$count = count($heal);
		$blank = abs(($count - 6) + (floor($count / 3) ^ 1) * 3);
		if($blank < 3) {
			$heal = array_merge($heal, array_fill($count, $blank, ['pkmimgpath' => Obtain::Sprite('pokemon', 'png', '0')]));
		}

		/*
			Fetch pokemon data in party
		*/

		$query   = DB::query('SELECT m.pid, m.nickname, m.id, m.imgname, m.exp, m.hp, m.gender, m.iv, m.ev, m.level, p.bs, p.exptype FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.id != 0 AND m.place IN (1, 2, 3, 4, 5, 6) AND uid = ' . $_G['uid'] . ' ORDER BY m.place');
		$pokemon = [];

		while($info = DB::fetch($query)) {

			$info               = array_merge($info, Obtain::Stat($info['level'], $info['bs'], $info['iv'], $info['ev'], 1, $info['hp']));
			$info['gender']     = Obtain::GenderSign($info['gender']);
			$info['minexp']     = Obtain::Exp($info['exptype'], $info['level']);
			$info['maxexp']     = Obtain::Exp($info['exptype'], $info['level'] + 1) - $info['minexp'];
			$info['exp']        = $info['exp'] - $info['minexp'];
			$info['expper']     = round($info['exp'] / $info['maxexp'] * 100);
			$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['imgname']);
			$pokemon[]          = $info;

		}

		$count = count($pokemon);
		$blank = abs(($count - 6) + (floor($count / 3) ^ 1) * 3);
		if($blank < 3) {
			$pokemon = array_merge($pokemon, array_fill($count, $blank, ['pkmimgpath' => Obtain::Sprite('pokemon', 'png', '0')]));
		}
		break;
	case 'box':

		/*for($i = 1; $i < 100; $i++) {

			rename(ROOTIMG . '/pokemon-icon/' . ($i < 10 ? '00' : '0') . $i . '.png', ROOTIMG . '/pokemon-icon/' . $i . '.png');
			
		}*/

		$query   = DB::query('SELECT m.pid, m.id, m.place, m.nickname, m.level, m.gender, m.imgname, p.name, p.type, p.typeb, a.name abi FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id LEFT JOIN pkm_abilitydata a ON a.aid = m.abi WHERE m.uid = ' . $user['uid'] . ' ORDER BY m.place ASC');
		$pokemon = [];
		$boxnum  = $SYS['sttbox'] + $user['boxnum'];

		for($i = 1; $i <= $boxnum; $i++) {

			$pokemon[$i + 100] = [];

		}

		while($info = DB::fetch($query)) {

			if(!isset($pokemon[$info['place']]) && $info['place'] > 6 || $info['place'] > 6 && $info['place'] < 101)

				continue;

			if($info['place'] < 7)

				$info['place'] = 'party';

			$info['gender'] = Obtain::GenderSign($info['gender']);
			$info['type']   = Obtain::TypeName($info['type'], $info['typeb']);

			$pokemon[$info['place']][] = $info;

		}

		break;
	case 'tradeb':

		/*
			Fetch pokemon data in party
		*/

		$query = DB::query('SELECT m.pid, m.nickname, m.level, m.gender, m.pid, m.imgname, p.type, p.typeb FROM pkm_mypkm m, pkm_pkmdata p WHERE m.id = p.id AND place IN (1, 2, 3, 4, 5, 6) AND uid = ' . $_G['uid']);
		$party = [];

		while($info = DB::fetch($query)) {

			$info['type']       = Obtain::TypeName($info['type'], $info['typeb']);
			$info['gender']     = Obtain::GenderSign($info['gender']);
			$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['imgname']);
			$party[]            = $info;

		}


		/*
			Fetch pokemon data which are in trade or been requested
		*/

		$query = DB::query('SELECT m.nickname, m.level, m.gender, m.pid, m.imgname, 
				mb.pid pidb, mb.nickname nicknameb, mb.level levelb, mb.gender genderb, mb.pid pidb, mb.imgname imgnameb, 
				p.type, p.typeb, pb.type typeba, pb.typeb typebb, 
				mbr.username, mt.tradeid, mt.time 
			FROM pkm_mytrade mt 
			LEFT JOIN pkm_mypkm m ON m.pid = mt.pid 
			LEFT JOIN pkm_mypkm mb ON mb.pid = mt.oPid 
			LEFT JOIN pkm_pkmdata p ON p.id = m.id 
			LEFT JOIN pkm_pkmdata pb ON pb.id = mb.id 
			LEFT JOIN pre_common_member mbr ON mbr.uid = mt.uid
			WHERE mt.uid = ' . $_G['uid'] . ' OR mt.ouid = ' . $_G['uid']);
		$trade = [];

		while($info = DB::fetch($query)) {
			$info['type']        = Obtain::TypeName($info['type'], $info['typeb']);
			$info['typeba']      = Obtain::TypeName($info['typeba'], $info['typebb']);
			$info['gender']      = Obtain::GenderSign($info['gender']);
			$info['genderb']     = Obtain::GenderSign($info['gender2']);
			$info['pkmimgpath']  = Obtain::Sprite('pokemon', 'png', $info['imgname']);
			$info['pkmimgpathb'] = Obtain::sprite('pokemon', 'png', $info['imgnameb']);
			$info['time']        = date('Y/m/d H:i', $_SERVER['REQUEST_TIME']);
			$trade[]             = $info;
		}

		break;
	case 'trade':

		if(empty($_GET['part'])) {

			/*
				Obtaining sent requests
			*/

			$query = DB::query('SELECT t.tradeid, t.time, t.uid, t.ouid, m.id, m.nickname, m.level, m.gender, m.nature, m.imgname, p.name, p.type, p.typeb, mo.id oid, mo.nickname onickname, mo.level olevel, mo.gender ogender, mo.nature onature, mo.imgname oimgname, po.name oname, po.type otype, po.typeb otypeb, mb.username 
				FROM pkm_mytrade t 
				LEFT JOIN pkm_mypkm m ON m.pid = t.pid 
				LEFT JOIN pkm_mypkm mo ON mo.pid = t.opid 
				LEFT JOIN pkm_pkmdata p ON p.id = m.id 
				LEFT JOIN pkm_pkmdata po ON po.id = mo.id 
				LEFT JOIN pre_common_member mb ON mb.uid = t.uid
				WHERE t.uid = ' . $user['uid'] . ' OR t.ouid = ' . $user['uid']
			);
			$sent  = $received = [];

			while($info = DB::fetch($query)) {

				$info['time']        = date('Y-m-d H:i:s', $info['time']);
				$info['type']        = Obtain::TypeName($info['type'], $info['typeb']);
				$info['gender']      = Obtain::GenderSign($info['gender']);
				$info['nature']      = Obtain::NatureName($info['nature']);
				$info['pkmimgpath']  = empty($info['id']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'png', $info['imgname']);
				$info['otype']       = Obtain::TypeName($info['otype'], $info['otypeb']);
				$info['ogender']     = Obtain::GenderSign($info['ogender']);
				$info['onature']     = Obtain::NatureName($info['onature']);
				$info['opkmimgpath'] = empty($info['oid']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'png', $info['oimgname']);

				if($info['uid'] == $user['uid'])

					$sent[] = $info;

				elseif($info['ouid'] == $user['uid'])

					$received[] = $info;

			}

			/*
				Obtaining received requests
			*/

		}

		break;
}

?>