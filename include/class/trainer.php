<?php

class Trainer {

    public static $setting = [];

    /**
     * Generates initial trainer data for a user.
     * @return bool
     * @internal param $user_id
     */
    public static function generate() {

        // Use prepared statement to find out if the trainer id already exists
        if(!($stmt = DB::prepare('SELECT trainer_id FROM pkm_trainerdata WHERE trainer_id = ?'))) return FALSE;

        //$stmt->bind_param('s', $trainer_id);
        //while(1) {
            $trainer_id = strtoupper(
                str_pad(dechex(rand(0, 65535)), 4, '0', STR_PAD_LEFT) .
                str_pad(dechex(rand(0, 65535)), 4, '0', STR_PAD_LEFT)
            );
        //    if($stmt->execute() && $stmt->fetch()) break;
        //}

        // Insert newly genderated trainer data, also add an stat entry for the trainer
        DB::query('INSERT INTO pkm_trainerdata (trainer_id, time_begin, time_happiness_checked) VALUES (\'' . $trainer_id . '\', ' . $_SERVER['REQUEST_TIME'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('INSERT INTO pkm_trainerstat (user_id) VALUES (' . ($user_id = DB::insertID()) . ')');

        return $user_id;

    }

    public static function Fetch($user_id) {

        global $system;

        $trainer = DB::fetch_first('SELECT user_id, trainer_name, trainer_id, exp, level, has_starter, currency, box_quantity, time_happiness_checked, is_battling, has_new_message, time_last_visit,
                                    FIND_IN_SET(exp, (SELECT GROUP_CONCAT(exp ORDER BY exp DESC) FROM pkm_trainerdata)) rank
                                    FROM pkm_trainerdata t
                                    WHERE user_id = ' . $user_id);

        if(!$trainer) return FALSE;

        $trainer['gm']            = in_array($trainer['user_id'], explode(',', $system['admins']));
        $trainer['avatar']        = Obtain::Avatar($trainer['user_id']);
        $trainer['stat']          = DB::fetch_first('SELECT * FROM pkm_trainerstat WHERE user_id = ' . $user_id);
        $trainer['dex_collected'] = DB::result_first('SELECT COUNT(*) FROM pkm_mypokedex WHERE user_id = ' . $trainer['user_id'] . ' AND is_owned = 1');
        $trainer['exp_required']  = Trainer::GetRequiredExp($trainer['level'] + 1);
        $trainer['card']          = Obtain::TrainerCard($trainer);
        $trainer['stat_add']      = array_map(function () {
            return 0;
        }, !empty($trainer['stat']) ? $trainer['stat'] : []);
        unset($trainer['stat']['user_id']);

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
            DB::query('UPDATE pkm_trainerdata SET exp = ' . $exp . ', LEVEL = ' . floor(pow(2 * $exp, 1 / 4)) . ' WHERE user_id = ' . $trainer['user_id']);
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
     * @param $user_id
     * @param $stat_new
     * @return bool|mysqli_result
     */
    public static function SaveTemporaryStat($user_id, $stat_new) {

        if(array_sum($stat_new) === 0) return FALSE;

        $keys = array_keys($stat_new);
        $vals = array_values($stat_new);
        $exts = [];

        foreach($keys as $val)
            $exts[] = $val . ' = VALUES(' . $val . ')';

        return DB::query('INSERT INTO pkm_trainerstat (user_id, ' . implode(',', $keys) . ') VALUES (' . $user_id . ', ' . implode(',', $vals) . ') ON DUPLICATE KEY UPDATE ' . implode(',', $exts));

    }

    public static function Item($action, $user_id, $iid, $num, $curnum = 'UNKNOWN', $limit = 0) {

        if($curnum === 'UNKNOWN') {
            $curnum = intval(DB::result_first('SELECT quantity FROM pkm_myitem WHERE item_id = ' . $iid . ' AND user_id = ' . $user_id));
        }

        if($action === 'DROP' && $curnum - $num <= 0) {
            if($curnum - $num < 0) return FALSE;
            DB::query('DELETE FROM pkm_myitem WHERE item_id = ' . $iid . ' AND user_id = ' . $user_id);
        } elseif($action === 'DROP') {
            DB::query('UPDATE pkm_myitem SET quantity = ' . ($curnum - $num) . ' WHERE user_id = ' . $user_id . ' AND item_id = ' . $iid);
        } elseif($action === 'OBTAIN') {
            if($limit !== 0 && $curnum + $num > $limit) return FALSE;
            if(empty($curnum)) {
                DB::query('INSERT INTO pkm_myitem (item_id, quantity, user_id) VALUES (' . $iid . ', ' . $num . ', ' . $user_id . ')');
            } else {
                DB::query('UPDATE pkm_myitem SET quantity = ' . ($curnum + $num) . ' WHERE item_id = ' . $iid . ' AND user_id = ' . $user_id);
            }
        }

        return TRUE;

    }

    public static function GetRequiredExp($level) {
        return ceil(0.5 * pow($level, 4));
    }

}