<?php

//$db->query('update pkm_mypkm set exp = exp + 222222');

//pkmGenerate(130, 8, 600, rand(1, 100), 0, 1);
//pkmGenerate(rand(1,649), 8, 600, rand(1, 100), 0, 1);
//pkmGenerate(rand(1,649), 8, 600, rand(1, 100), 0, 1);
//pkmGenerate(rand(1,649), 8, 600, rand(1, 100), 0, 1);
//pkmGenerate(rand(1,649), 8, 600, rand(1, 100), 0, 1);
//pkmGenerate(rand(1,649), 8, 600, rand(1, 100), 0, 1);
//$db->query("DELETE FROM pkm_mypkm");
//pkmGenerate(rand(1,649), 8, 600);
//$db->query("UPDATE pkm_mypkm set ev = '255,0,255,0,0,0'");
//$db->query("UPDATE pkm_trnrdata set hpnsChk=0");
//$db->query("ALTER TABLE pkm_trnrdata ADD hpnsChk INT UNSIGNED NOT NULL DEFAULT 0");
//$db->query('delete from pkm_ranking_pkmscore');
//$db->query('UPDATE pkm_mypkm SET egg = 1 WHERE place = 7');

function hatchEgg($pid) {
	global $db;
	$db->query('UPDATE pkm_mypkm SET id = egg, egghatch = 0 WHERE pid = ' . $pid);
}


function eggGenerate() {
}


function msgBox($content) {
	exit("<div id=\"msgBox\" width=\"100%\" align=\"center\" style=\"margin: 20px 20px 20px 20px\">{$content}</div>");
}

function itemUse($pkmArr, $itemArr) {

}

function in_clArray($column, $needle, $haystack) {
	foreach($haystack as $arr) {
		if($arr[$column] == $needle) {
			return TRUE;
		}
	}
	return FALSE;
}

function in_clArraySearch($column, $needle, $haystack) {
	foreach($haystack as $key => $arr) {
		if($arr[$column] == $needle) {
			return $key;
		}
	}
	return FALSE;
}


function groupArr($array, $column) {
	//The key of array that group by must be increase of 0
	$length = count($array);
	$name   = $result = [];
	foreach($array as $arr) {
		if(!in_array($arr[$column], $name)) {
			$name[]     = $arr[$column];
			$result[][] = $arr;
		} else {
			$key            = array_search($arr[$column], $name);
			$result[$key][] = $arr;
		}
	}
	return $result;
}

function getRmTime($now, $timestamp) {
	$rmTime = $timestamp - $now;
	$strVal = [floor($rmTime / 86400), ($rmTime / 3600) % 24, ($rmTime / 60) % 60];
	return $strVal;
}

function generateToken($pageName) {
	$token     = str_shuffle("1055d3e698d289f2af8663725127bd4b");
	$tokenName = $pageName . "_ptk";
	setCookie($tokenName, $pToken);
}

function checkToken($pToken, $pageName, $clearToken = 1) {
	/**
	 * For $pageName, there are several values
	 * it => itemshop,
	 * fm => fleamarket,
	 * fmpt => fleamarket_put,

	 */
	$tokenName = $pageName . "_ptk";
	if($pToken === $_COOKIE[$tokenName] && !empty($_COOKIE[$tokenName]) && !empty($pToken)) {
		if($clearToken === 1)
			setCookie($tokenName, "");
		return TRUE;
	}
	if($clearToken === 1)
		setCookie($tokenName, "");
	return FALSE;
}


function breakValue($value, $split = 2, $value2 = 0, $value3 = 0) {
	$result = [];
	$count  = (!$value2) ? 1 : ((!$value3) ? 2 : 3);
	for($i = 0; $i < $count; $i++) {
		$temp       = ($i === 0) ? "value" : "value" . ($i + 1);
		$result[$i] = str_split(${$temp}, $split); //拆分
		for($j = 0; $j < 6; $j++) {
			$result[$i][$j] = hexdec($result[$i][$j]); //转为十进制
		}
	}
	return $result;
}


?>