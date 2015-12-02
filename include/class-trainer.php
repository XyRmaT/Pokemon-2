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
        if(!($stmt = DB::prepare('SELECT trainerId FROM pkm_trainerdata WHERE trainerId = ?'))) return FALSE;

        $stmt->bind_param('s', $trainerId);
        while(1) {
            $trainerId = strtoupper(str_pad(dechex(rand(0, 65535)), 4, '0', STR_PAD_LEFT) . str_pad(dechex(rand(0, 65535)), 4, '0', STR_PAD_LEFT));
            if($stmt->execute() && $stmt->fetch()) break;
        }

        // Insert newly genderated trainer data, also add an stat entry for the trainer
        DB::query('INSERT INTO pkm_trainerdata (uid, trainerId, time_begin, time_happiness_checked) VALUES (' . $uid . ', \'' . $trainerId . '\', ' . $_SERVER['REQUEST_TIME'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('INSERT INTO pkm_trainerstat (uid) VALUES (' . $uid . ')');

        return TRUE;

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

        global $user;
        return !empty($trainer['stat']['new'][$stat]) ? $trainer['stat']['new'][$stat] += $adding : 0;

    }

    /**
     * Using
     * @return bool|mysqli_result
     */
    public static function SaveTemporaryStat() {

        global $user;

        if(!($diff = array_diff($trainer['stat']['new'], $trainer['stat']['old']))) return FALSE;

        $keys = array_keys($diff);
        $vals = array_values($diff);
        $exts = [];

        foreach($keys as $val)
            $exts[] = $val . ' = VALUES(' . $val . ')';

        return DB::query('INSERT INTO pkm_trainerstat (uid, ' . implode(',', $keys) . ') VALUES (' . $trainer['uid'] . ', ' . implode(',', $vals) . ') ON DUPLICATE KEY UPDATE ' . implode(',', $exts));

    }

    public static function Item($action, $uid, $iid, $num, $curnum = 'UNKNOWN', $limit = 0) {

        if($curnum === 'UNKNOWN')
            $curnum = DB::result_first('SELECT num FROM pkm_myitem WHERE iid = ' . $iid . ' AND uid = ' . $uid);

        if($action === 'DROP' && $curnum - $num <= 0)
            DB::query('DELETE FROM pkm_myitem WHERE iid = ' . $iid . ' AND uid = ' . $uid);
        elseif($action === 'DROP')
            DB::query('UPDATE pkm_myitem SET num = ' . ($curnum - $num) . ' WHERE uid = ' . $uid . ' AND iid = ' . $iid);
        elseif($action === 'OBTAIN') {

            if($limit !== 0 && $curnum + $num > $limit) return FALSE;

            if(empty($curnum))
                DB::query('INSERT INTO pkm_myitem (iid, num, uid) VALUES (' . $iid . ', ' . $num . ', ' . $uid . ')');
            else
                DB::query('UPDATE pkm_myitem SET num = num' . ($action === 'DROP' ? '-' : '+') . $num . ' WHERE iid = ' . $iid . ' AND uid = ' . $uid);

        }

    }

    public static function LogIn() {

    }

}