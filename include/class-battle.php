<?php


class Battle {

    private $pokemon = [];
    private $party   = [];
    private $uid     = 0;
    private $report  = [];

    public function Battle($battle_id) {
        include_once __DIR__ . '/data-constant-battle.php';
    }

    public function swapPokemon() {

    }

    public function reorderPokemon() {

    }

    private function alterStatus(&$status, $value, $chance = 100) {
        if($status || rand(1, 100) > $chance) return FALSE;
        $status = $value;
        return TRUE;
    }

    private function fetchParty($uid) {

        $query = DB::query('SELECT m.pkm_id, m.nat_id, m.nickname, m.gender, m.psn_value, m.ind_value, m.eft_value,
                                    m.nature, m.level, m.exp, m.item_holding, m.happiness, m.moves, m.ability, m.uid,
                                    m.uid_initial, m.item_captured, m.hp, m.form, m.status, m.new_moves, m.sprite_name,
                                    p.type, p.type_b, p.base_stat, p.height, p.weight
                            FROM pkm_mypkm m
                            LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id AND m.form = p.form
                            WHERE m.uid = ' . $uid . ' AND m.location IN (' . LOCATION_PARTY . ') AND m.hatch_nat_id != 0');
        while($info = DB::fetch($query)) {
            $info['temporary'] = [];
            $info['battle']    = $this->fetchBattleData();
            $info['stats']     = Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], $info['nature'], TRUE);
            $this->party[]     = $info;
        }

        // Initialize pokemon array by putting self as index 0
        if(!empty($this->party[0])) {
            $this->pokemon[0] = $this->party[0];
        }

        return !$this->party ? FALSE : $this;

    }

    private function decideOrder() {

        foreach($this->pokemon as &$pokemon) {

        }

    }

    private function main() {

    }

    private function processTurn(&$attacker, &$defencer, &$field, $move_id) {

        $substatus = &$attacker['battle']['substatus'];
        $counters  = &$attacker['battle']['counters'];

        // Reset Destiny Bond, Grudge and Rage status
        $substatus[SUBSTATUS_DESTINYBOND] = 0;
        $substatus[SUBSTATUS_GRUDGE]      = 0;
        $substatus[SUBSTATUS_RAGE]        = 0;

        // Decrease Torment and Encore counter, also record last used move id if being encored
        $substatus[SUBSTATUS_TORMENT] = $substatus[SUBSTATUS_TORMENT] ? $substatus[SUBSTATUS_TORMENT] - 1 : 0;
        if($substatus[SUBSTATUS_ENCORE]) {
            $move_id = $attacker['temporary']['last_move_id'];
            --$substatus[SUBSTATUS_ENCORE];
        }

        // Move recharging, if so reset the variable then ends the attack
        if($substatus[SUBSTATUS_RECHARGE]) {
            $substatus[SUBSTATUS_RECHARGE] = 0;
            $this->appendReport('recharging', [$attacker['nickname']]);
            return;
        }

        $move = $this->retrieveMove($move_id, $attacker);

        PROCESS_CHECKMOBILITY: {
            // Freeze hax and pokemon asleep
            if($attacker['status'] == STATUS_FREEZE) {
                if(mt_rand(1, 4) === 1) {
                    $attacker['status'] = 0;
                    $this->appendReport('defrosted', [$attacker['nickname']]);
                } else {
                    $this->appendReport('frozen', [$attacker['nickname']]);
                    return;
                }
            } elseif($attacker['status'] == STATUS_SLEEP) {
                if(--$counters[COUNTER_SLEEP] < 1) {
                    $attacker['status'] = 0;
                    $this->appendReport('woke', [$attacker['nickname']]);
                } else {
                    $this->appendReport('sleeping', [$attacker['nickname']]);
                    return;
                }
            }

            // Truant
            if($substatus[SUBSTATUS_TRUANT]) {
                $substatus[SUBSTATUS_TRUANT] = 0;
                $this->appendReport('truanted', [$attacker['nickname']]);
                return;
            }

            // Disable
            if($move_id == $substatus[SUBSTATUS_DISABLE]) {
                $this->appendReport('disabled', [$attacker['nickname']]);
                return;
            }

            // Imprison
            if($substatus[SUBSTATUS_IMPRISON] &&
                array_uintersect($attacker['moves'], $defencer['moves'], function($a, $b) { return $a['move_id'] - $b['move_id'];})) {
                $this->appendReport('imprisoned', [$attacker['nickname']]);
                return;
            }

            // Heal block
            if($substatus[SUBSTATUS_HEALBLOCK] && $move['flags']{FLAG_HEALING}) {
                $this->appendReport('healblocked', [$attacker['nickname']]);
                return;
            }

            // Confused
            if($substatus[SUBSTATUS_CONFUSE]) {
                if(--$substatus[SUBSTATUS_CONFUSE]) {
                    $this->appendReport('confused', [$attacker['nickname']]);
                    if(mt_rand(0, 1)) {
                        $move = $this->retrieveMove(SPECIAL_CONFUSED_MOVE_ID);
                        $this->appendReport('attacked_itself', [$attacker['nickname']]);
                    }
                } else {
                    $this->appendReport('not_confused', [$attacker['nickname']]);
                }
            }

            // Flinch
            if($substatus[SUBSTATUS_FLINCH]) {
                $substatus[SUBSTATUS_FLINCH] = 0;
                $this->appendReport('flinched', [$attacker['nickname']]);
                return;
            }

            // Taunt
            if($substatus[SUBSTATUS_TAUNT] && $move['class'] == MOVECLASS_STATUS) {
                $this->appendReport('taunted', [$attacker['nickname']]);
                return;
            }

            // Gravity
            if($field[FIELD_GRAVITY] && $move['flags']{FLAG_LEVITATING}) {
                $this->appendReport('gravitational_force', [$attacker['nickname']]);
                return;
            }

            // Attract
            if($substatus[SUBSTATUS_ATTRACT] && mt_rand(0, 1)) {
                $this->appendReport('attracted', [$attacker['nickname']]);
                return;
            }

            // Paralyze
            if($attacker['status'] == STATUS_PARALYSIS && !mt_rand(0, 3)) {
                $this->appendReport('paralyzed', [$attacker['nickname']]);
                return;
            }

            // PP
            if(!$move['pp']) {
                $move = $this->retrieveMove(SPECIAL_STRUGGLE_MOVE_ID);
            } else {
                --$move['pp'];
            }

        }

    }

    private function &retrieveMove($move_id, &$pokemon = FALSE) {
        return [];
    }

    public function initiateBattleField() {

    }

    private function appendReport($id, $args) {
        $this->report[] = Obtain::Text('battle_' . $id, $args);
    }

}