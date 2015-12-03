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

        $trainer = DB::fetch_first('SELECT uid, trainer_id, exp, level, has_starter, box_quantity, time_happiness_checked, is_battling, has_new_message FROM pkm_trainerdata WHERE uid = ' . $uid);

        if(!empty($trainer))
            $trainer['stat'] = DB::fetch_first('SELECT * FROM pkm_trainerstat WHERE uid = ' . $uid);

        return $trainer;

    }

    /**
     * Update trainer's exp
     * @param     $trainer array for trainer's info
     * @param int $exp_adding
     * @return bool|mysqli_result
     */
    public static function AddExp($trainer, $exp_adding = 0) {

        // Performing an exp check, if it's non-zero value then the trainer's
        // exp will be updated based on this value
        if($exp_adding && !empty($trainer['exp'])) {
            $exp = max(0, $trainer['exp'] + $exp_adding);
            return DB::query('UPDATE pkm_trainerdata SET exp = ' . $exp . ', level = ' . floor(pow(2 * $exp, 1 / 4)) . ' WHERE uid = ' . $trainer['uid']);
        }

        return FALSE;

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
     * @param $statNew
     * @return bool|mysqli_result
     */
    public static function SaveTemporaryStat($uid, $statNew) {

        if(array_sum($statNew) === 0) return FALSE;

        $keys = array_keys($statNew);
        $vals = array_values($statNew);
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