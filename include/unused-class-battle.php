<?php

# Test if Foresight, Miracle Eye, Odor Sleuth can be overlaid.

/**
 * Main Status: 1(Paralysis), 2(Poison), 3(Bad Poison), 4(Sleep), 5(Burn), 6(Freeze)
 * Sub Status:
 *    Negative: 1(Confuse), 2(FIL), 3(Bind), 4(Block), 5(
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
            return FALSE;
        }
        fclose($handle);
        return TRUE;
    }

    // A function which initialize the variables for individuals to avoid error

    function getItemEffect($effectStr) {
        preg_match_all("/[\(\[]([^\(]+)[\)\]]/", $effectStr, $results);
        foreach($results[1] as $result) {
            self::$itemEffect[] = explode("|", $result);
        }
        return $itemEffect;
    }

    function wildPkmMaterialization($uid, $map_id, $capItem) {
        global $db, $timestamp;
        list($level, $expType) = $db->fetch_first("SELECT mwp.level, pd.expType FROM pkm_mywildpkm mwp LEFT JOIN pkm_pkmdata pd ON pd.id = mwp.id WHERE mwp.uid = {$uid}");
        if(empty($pokemon)) {
            return FALSE;
        }
        $exp   = nextLevelUp($expType, $level - 1);
        $location = boxCheck($uid);
        return $db->query("INSERT INTO pkm_mypkm (id, nickname, gender, psn_value, ind_value, eft_value, is_shiny, oOUid, daycTime, time_hatched, time_hatched, nature, level, exp, item_carrying, hlTime, happiness, beauty, moves, mtLevel, mtDate, mtPlace, ability, uid, capItem, hp, form, location, status, newMove) (SELECT id, name_zh name, gender, psn_value, ind_value, eft_value, is_shiny, uid oOUid, 0, 0, 0, nature, level, {$exp}, item_carrying, 0, happiness, 0, moves, level, {$timestamp}, {$map_id}, ability, uid, {$capItem}, hp, form, {$location}, status, '' FROM pkm_mywildpkm WHERE pkm_id = {$pid})");
    }

    function gainExp($baseEXP, $attackerLevel, $oppLevel, $pkmUid, $uid, $crritem, $pkmCount) {
        $argA = sqrt($oppLevel * 2 + 10);
        $argB = pow($oppLevel * 2 + 10, 2);
        $argC = (($baseEXP * $oppLevel / 5) * (($pkmUid == $uid) ? 1 : 1.5)) * (($crritem == "幸福蛋") ? 1.5 : 1);
        $argD = sqrt($oppLevel + $attackerLevel + 10);
        $argE = pow($oppLevel + $attackerLevel + 10, 2);
        $exp  = ($argA * $argB * $argC / $pkmCount) / ($argD * $argE) + 1;
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
            case 'firstTurn':
                $db->query('UPDATE pkm_trnrdata SET inBtl = 1 WHERE uid = ' . $discuz_uid);
                break;
            case 'lastTurn':
                $db->query('UPDATE pkm_trnrdata SET inBtl = 0 WHERE uid = ' . $discuz_uid);
                break;
        }
        unset($btlDataPath, $handle, $discuz_uid, $db);
    }

    private function initialIndividualVar() {
        self::$ohko      = self::$critical = self::$accHit = self::$hitFrequency = self::$invHit = 0;
        self::$damageMul = 1;
    }

}

?>