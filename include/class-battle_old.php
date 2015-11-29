<?php

# Test if Foresight, Miracle Eye, Odor Sleuth can be overlaid.

/**
 * Main Status: 1(Paralysis), 2(Poison), 3(Bad Poison), 4(Sleep), 5(Burn), 6(Freeze)
 * Sub Status:
 * 	Negative: 1(Confuse), 2(FIL), 3(Bind), 4(Block), 5(
 */

define('INBATTLE', TRUE);

class battle {
	
	/**
	 * boolean savaData()
	 * This function will write the battle information into files for the next action
	 * Just only write in the temporary data such as sub status alias and so on
	 */
	function saveData() {
		global $btlDataPath;
		$path = $btlDataPath;
		foreach(self::$pokemon as $val) {
			$result[] = $val[1];
		}
		unset($btlDataPath, self::$pokemon);
		if(!($handle = fopen($btlDataPath, "w")) || !flock($fp, LOCK_EX) || !fwrite($handle, serialize($result))) {
			return false;
		}
		fclose($handle);
		return true;
	}

	// A function which initialize the variables for individuals to avoid error
	private function initialIndividualVar() {
		self::$ohko = self::$critical = self::$accHit = self::$hitFrequency = self::$invHit = 0;
		self::$damageMul = 1;
	}
	

	
	
	
	function getItemEffect($effectStr) {
		preg_match_all("/[\(\[]([^\(]+)[\)\]]/", $effectStr, $results);
		foreach($results[1] as $result) {
			self::$itemEffect[] = explode("|", $result);
		}
		return $itemEffect;
	}
	
	function wildPkmMaterialization($uid, $mpid, $capItem) {
		global $db, $timestamp;
		list($level, $expType) = $db->fetch_first("SELECT mwp.level, pd.expType FROM pkm_mywildpkm mwp LEFT JOIN pkm_pkmdata pd ON pd.id = mwp.id WHERE mwp.uid = {$uid}");
		if(empty($pokemon)) {
			return false;
		}
		$exp = nextLevelUp($expType, $level - 1);
		$place = boxCheck($uid);
		return $db->query("INSERT INTO pkm_mypkm (id, nickname, gender, pv, iv, ev, shiny, oOUid, daycTime, egghatch, egg, nature, level, exp, crritem, hlTime, hpns, beauty, move, mtLevel, mtDate, mtPlace, abi, uid, capItem, hp, form, place, status, newMove) (SELECT id, name, gender, pv, iv, ev, shiny, uid oOUid, 0, 0, 0, nature, level, {$exp}, crritem, 0, hpns, 0, move, level, {$timestamp}, {$mpid}, abi, uid, {$capItem}, hp, form, {$place}, status, '' FROM pkm_mywildpkm WHERE pid = {$pid})");
	}
	
	function gainExp($baseEXP, $attackerLevel, $oppLevel, $pkmUid, $uid, $crritem, $pkmCount) {
		$argA = sqrt($oppLevel * 2 + 10);
		$argB = pow($oppLevel * 2 + 10, 2);
		$argC = (($baseEXP * $oppLevel / 5) * (($pkmUid == $uid) ? 1 : 1.5)) * (($crritem == "幸福蛋") ? 1.5 : 1);
		$argD = sqrt($oppLevel + $attackerLevel + 10);
		$argE = pow($oppLevel + $attackerLevel + 10, 2);
		$exp = ($argA * $argB * $argC / $pkmCount) / ($argD * $argE) + 1;
		return $exp;
	}
	
	function end() {
		global $btlDataPath, $discuz_uid, $db;
		$handle = fopen($btlDataPath, 'w');
		list(self::$pokemon[7], self::$pokemon[8]) = self::$field;
		/*echo '<pre>';
		print_r(self::$pokemon);
		echo '</pre>';*/
		fwrite($handle, serialize(self::$pokemon));
		fclose($handle);
		switch(self::$pos) {
			case 'firstTurn':	$db->query('UPDATE pkm_trnrdata SET inBtl = 1 WHERE uid = ' . $discuz_uid);	break;
			case 'lastTurn':	$db->query('UPDATE pkm_trnrdata SET inBtl = 0 WHERE uid = ' . $discuz_uid);	break;
		}
		unset($btlDataPath, $handle, $discuz_uid, $db);
	}

}
?>