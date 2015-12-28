<?php

Kit::Library('class', ['obtain']);

switch($_GET['section']) {
	default:

		/*
			Fetch pokemon data where in healing mode
		*/

		$query = DB::query('SELECT m.pkm_id, m.nickname, m.gender, m.eft_value, m.level, m.nature, m.ind_value, m.hp, m.nat_id, m.time_pc_sent, m.sprite_name, p.base_stat FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.location = 8 AND m.uid = ' . $trainer['uid'] . ' ORDER BY m.time_pc_sent DESC');
		$heal  = [];

		while($info = DB::fetch($query)) {

			$info               = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], $info['nature']));
			$info['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
			$info['needtime']   = ceil(($info['maxhp'] - $info['hp']) * 6.6);
			$info['rmtime']     = max(0, $info['time_pc_sent'] + $info['needtime'] - $_SERVER['REQUEST_TIME']);
			$info['hltime']     = [floor($info['rmtime'] / 60 / 24), round($info['rmtime'] / 60)];
			$info['gender']     = Obtain::GenderSign($info['gender']);

			if($info['rmtime'] / 60 <= 0)

				$info['fullheal'] = TRUE;

			$heal[] = $info;

		}

		$count = count($heal);
		$blank = abs(($count - 6) + (floor($count / 3) ^ 1) * 3);
		if($blank < 3) {
			$heal = array_merge($heal, array_fill($count, $blank, ['pkm_sprite' => Obtain::Sprite('pokemon', 'gif', '0')]));
		}

		/*
			Fetch pokemon data in party
		*/

		$query   = DB::query('SELECT m.pkm_id, m.nickname, m.nat_id, m.sprite_name, m.exp, m.hp, m.gender, m.ind_value, m.eft_value, m.level, p.base_stat, p.exp_type FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.nat_id != 0 AND m.location IN (1, 2, 3, 4, 5, 6) AND uid = ' . $trainer['uid'] . ' ORDER BY m.location');
		$pokemon = [];

		while($info = DB::fetch($query)) {

			$info               = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], 1, $info['hp']));
			$info['gender']     = Obtain::GenderSign($info['gender']);
			$info['minexp']     = Obtain::Exp($info['exp_type'], $info['level']);
			$info['exp_max']     = Obtain::Exp($info['exp_type'], $info['level'] + 1) - $info['minexp'];
			$info['exp']        = $info['exp'] - $info['minexp'];
			$info['exp_percent']     = min(round($info['exp'] / $info['exp_max'] * 100), 100);
			$info['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
			$pokemon[]          = $info;

		}

		$count = count($pokemon);
		$blank = abs(($count - 6) + (floor($count / 3) ^ 1) * 3);
		if($blank < 3) {
			$pokemon = array_merge($pokemon, array_fill($count, $blank, ['pkm_sprite' => Obtain::Sprite('pokemon', 'gif', '0')]));
		}
		break;
	case 'box':

		/*for($i = 1; $i < 100; $i++) {

			rename(ROOT_IMAGE . '/pokemon-icon/' . ($i < 10 ? '00' : '0') . $i . '.png', ROOT_IMAGE . '/pokemon-icon/' . $i . '.png');
			
		}*/

		$query   = DB::query('SELECT m.pkm_id, m.nat_id, m.location, m.nickname, m.level, m.gender, m.sprite_name, p.name, p.type, p.type_b, a.name ability FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id LEFT JOIN pkm_abilitydata a ON a.abi_id = m.ability WHERE m.uid = ' . $trainer['uid'] . ' ORDER BY m.location ASC');
		$pokemon = [];
		$boxnum  = $system['initial_box'] + $trainer['box_quantity'];

		for($i = 1; $i <= $boxnum; $i++)
			$pokemon[$i + 100] = [];

		while($info = DB::fetch($query)) {

			if(!isset($pokemon[$info['location']]) && $info['location'] > 6 || $info['location'] > 6 && $info['location'] < 101)

				continue;

			if($info['location'] < 7)

				$info['location'] = 'party';

			$info['gender'] = Obtain::GenderSign($info['gender']);
			$info['type']   = Obtain::TypeName($info['type'], $info['type_b']);

			$pokemon[$info['location']][] = $info;

		}

		break;
	case 'tradeb':

		/*
			Fetch pokemon data in party
		*/

		$query = DB::query('SELECT m.pkm_id, m.nickname, m.level, m.gender, m.pkm_id, m.sprite_name, p.type, p.type_b FROM pkm_mypkm m, pkm_pkmdata p WHERE m.nat_id = p.nat_id AND location IN (1, 2, 3, 4, 5, 6) AND uid = ' . $trainer['uid']);
		$party = [];

		while($info = DB::fetch($query)) {

			$info['type']       = Obtain::TypeName($info['type'], $info['type_b']);
			$info['gender']     = Obtain::GenderSign($info['gender']);
			$info['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
			$party[]            = $info;

		}


		/*
			Fetch pokemon data which are in trade or been requested
		*/

		$query = DB::query('SELECT m.nickname, m.level, m.gender, m.pkm_id, m.sprite_name,
				mb.pkm_id pidb, mb.nickname nicknameb, mb.level levelb, mb.gender genderb, mb.pkm_id pidb, mb.sprite_name imgnameb,
				p.type, p.type_b, pb.type typeba, pb.type_b typebb,
				mbr.username, mt.pkm_id, mt.time
			FROM pkm_mytrade mt 
			LEFT JOIN pkm_mypkm m ON m.pkm_id = mt.pkm_id
			LEFT JOIN pkm_mypkm mb ON mb.pkm_id = mt.oPid
			LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
			LEFT JOIN pkm_pkmdata pb ON pb.id = mb.id 
			LEFT JOIN pre_common_member mbr ON mbr.uid = mt.uid
			WHERE mt.uid = ' . $trainer['uid'] . ' OR mt.uid_target = ' . $trainer['uid']);
		$trade = [];

		while($info = DB::fetch($query)) {
			$info['type']        = Obtain::TypeName($info['type'], $info['type_b']);
			$info['typeba']      = Obtain::TypeName($info['typeba'], $info['typebb']);
			$info['gender']      = Obtain::GenderSign($info['gender']);
			$info['genderb']     = Obtain::GenderSign($info['gender2']);
			$info['pkm_sprite']  = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
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

			$query = DB::query('SELECT t.pkm_id, t.time, t.uid, t.uid_target, m.nat_id, m.nickname, m.level, m.gender, m.nature, m.sprite_name, p.name, p.type, p.type_b, mo.id oid, mo.nickname onickname, mo.level olevel, mo.gender ogender, mo.nature onature, mo.sprite_name oimgname, po.name oname, po.type otype, po.type_b otypeb, mb.username
				FROM pkm_mytrade t 
				LEFT JOIN pkm_mypkm m ON m.pkm_id = t.pkm_id
				LEFT JOIN pkm_mypkm mo ON mo.pkm_id = t.pkm_id_target
				LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
				LEFT JOIN pkm_pkmdata po ON po.id = mo.id 
				LEFT JOIN pre_common_member mb ON mb.uid = t.uid
				WHERE t.uid = ' . $trainer['uid'] . ' OR t.uid_target = ' . $trainer['uid']
			);
			$sent  = $received = [];

			while($info = DB::fetch($query)) {

				$info['time']        = date('Y-m-d H:i:s', $info['time']);
				$info['type']        = Obtain::TypeName($info['type'], $info['type_b']);
				$info['gender']      = Obtain::GenderSign($info['gender']);
				$info['nature']      = Obtain::NatureName($info['nature']);
				$info['pkm_sprite']  = empty($info['nat_id']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
				$info['otype']       = Obtain::TypeName($info['otype'], $info['otypeb']);
				$info['ogender']     = Obtain::GenderSign($info['ogender']);
				$info['onature']     = Obtain::NatureName($info['onature']);
				$info['opkmimgpath'] = empty($info['oid']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'gif', $info['oimgname']);

				if($info['uid'] == $trainer['uid'])

					$sent[] = $info;

				elseif($info['uid_target'] == $trainer['uid'])

					$received[] = $info;

			}

			/*
				Obtaining received requests
			*/

		}

		break;
}

?>