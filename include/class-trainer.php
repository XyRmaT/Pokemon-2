<?php

class Trainer {

    public static $setting = [];

    /**
     * Generates initial trainer data for a user.
     * @param $uid
     * @return bool
     */
    public static function Generate($uid) {

        // Use prepared statement to find out if the trainer id already exists
        if(!($stmt = DB::prepare('SELECT trainer_id FROM pkm_trainerdata WHERE trainer_id = ?'))) return FALSE;

        $stmt->bind_param('s', $trainerId);
        while(1) {
            $trainerId = strtoupper(str_pad(dechex(rand(0, 65535)), 4, '0', STR_PAD_LEFT) . str_pad(dechex(rand(0, 65535)), 4, '0', STR_PAD_LEFT));
            if($stmt->execute() && $stmt->fetch()) break;
        }

        // Insert newly genderated trainer data, also add an stat entry for the trainer
        DB::query('INSERT INTO pkm_trainerdata (uid, trainer_id, time_begin, time_happiness_checked) VALUES (' . $uid . ', \'' . $trainerId . '\', ' . $_SERVER['REQUEST_TIME'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('INSERT INTO pkm_trainerstat (uid) VALUES (' . $uid . ')');

        return TRUE;

    }

    public static function Fetch($uid) {

        $trainer = DB::fetch_first('SELECT uid, trainer_id, exp, level, has_starter, box_quantity, time_happiness_checked, is_battling, has_new_message, time_last_visit,
                                    FIND_IN_SET(exp, (SELECT GROUP_CONCAT(exp ORDER BY exp DESC) FROM pkm_trainerdata)) AS rank
                                    FROM pkm_trainerdata WHERE uid = ' . $uid);

        if(!empty($trainer)) {
            $trainer['stat']          = DB::fetch_first('SELECT * FROM pkm_trainerstat WHERE uid = ' . $uid);
            $trainer['dex_collected'] = DB::result_first('SELECT COUNT(*) FROM pkm_mypokedex WHERE uid = ' . $trainer['uid'] . ' AND is_owned = 1');
            unset($trainer['stat']['uid']);
        }

        return $trainer;

    }

    /**
     * Update trainer's exp
     * @param array $trainer array for trainer's info
     * @param int   $exp_adding
     * @param bool  $is_temporary
     */
    public static function AddExp(&$trainer, $exp_adding, $is_temporary = FALSE) {
        if($is_temporary) {
            $trainer['exp'] += $exp_adding;
        } elseif($exp_adding && !empty($trainer['exp'])) {
            $exp = max(0, $trainer['exp'] + $exp_adding);
            DB::query('UPDATE pkm_trainerdata SET exp = ' . $exp . ', level = ' . floor(pow(2 * $exp, 1 / 4)) . ' WHERE uid = ' . $trainer['uid']);
        }
    }

    /**
     * Update trainer's stat
     * @param     $stat
     * @param int $adding
     * @return int
     */
    public static function AddTemporaryStat($stat, $adding = 1) {
        return !empty($GLOBALS['trainer']['stat_add'][$stat]) ? $GLOBALS['trainer']['stat_add'][$stat] += $adding : 0;
    }

    /**
     * @param $uid
     * @param $stat_new
     * @return bool|mysqli_result
     */
    public static function SaveTemporaryStat($uid, $stat_new) {

        if(array_sum($stat_new) === 0) return FALSE;

        $keys = array_keys($stat_new);
        $vals = array_values($stat_new);
        $exts = [];

        foreach($keys as $val)
            $exts[] = $val . ' = VALUES(' . $val . ')';

        return DB::query('INSERT INTO pkm_trainerstat (uid, ' . implode(',', $keys) . ') VALUES (' . $uid . ', ' . implode(',', $vals) . ') ON DUPLICATE KEY UPDATE ' . implode(',', $exts));

    }

    public static function Item($action, $uid, $iid, $num, $curnum = 'UNKNOWN', $limit = 0) {

        if($curnum === 'UNKNOWN')
            $curnum = DB::result_first('SELECT quantity FROM pkm_myitem WHERE item_id = ' . $iid . ' AND uid = ' . $uid);

        if($action === 'DROP' && $curnum - $num <= 0)
            DB::query('DELETE FROM pkm_myitem WHERE item_id = ' . $iid . ' AND uid = ' . $uid);
        elseif($action === 'DROP')
            DB::query('UPDATE pkm_myitem SET quantity = ' . ($curnum - $num) . ' WHERE uid = ' . $uid . ' AND item_id = ' . $iid);
        elseif($action === 'OBTAIN') {

            if($limit !== 0 && $curnum + $num > $limit) return FALSE;

            if(empty($curnum))
                DB::query('INSERT INTO pkm_myitem (item_id, quantity, uid) VALUES (' . $iid . ', ' . $num . ', ' . $uid . ')');
            else
                DB::query('UPDATE pkm_myitem SET quantity = quantity' . ($action === 'DROP' ? '-' : '+') . $num . ' WHERE item_id = ' . $iid . ' AND uid = ' . $uid);

        }

    }

    public static function LogIn() {

    }

}