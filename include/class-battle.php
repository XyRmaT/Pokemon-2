<?php


class Battle {

    private $pokemon = [];
    private $party = [];
    private $uid = 0;
    private $report = [];
    private $field = ['weather' => ['type' => 0, 'turn' => 0]];

    public function Battle($battle_id) {
        include_once ROOT . '/include/data-constant-battle.php';
        include_once ROOT . '/include/db-moves.php';
    }

    public function swapPokemon() {

    }

    public function reorderPokemon() {

    }

    private function alterStatus(&$status, $value, $chance = 100) {
        if($status || rand(1, 100) > $chance)
            return FALSE;
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
                if(!mt_rand(0, 3)) {
                    $attacker['status'] = 0;
                    $this->appendReport('defrosted', [$attacker['nickname']]);
                }
                else {
                    $this->appendReport('frozen', [$attacker['nickname']]);
                    return;
                }
            }
            elseif($attacker['status'] == STATUS_SLEEP) {
                if(--$counters[COUNTER_SLEEP] < 1) {
                    $attacker['status'] = 0;
                    $this->appendReport('woke', [$attacker['nickname']]);
                }
                else {
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
            if($substatus[SUBSTATUS_IMPRISON] && array_uintersect($attacker['moves'], $defencer['moves'], function($a, $b) {
                    return $a['move_id'] - $b['move_id'];
                })
            ) {
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
                }
                else {
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

        }

        // PP
        if(!$move['pp']) {
            $move = $this->retrieveMove(SPECIAL_STRUGGLE_MOVE_ID);
        }
        else {
            --$move['pp'];
        }

        if(!$this->isHit($attacker, $defencer, $move) && ($move['move_id'] == MOVE_HIGHJUMPKICK || $move['move_id'] == MOVE_JUMPKICK)) {
            // TODO - Jump kick
        }
        $this->calculateDamage($attacker, $defencer, $move);

        // Use move
        $this->useMove($move['move_id']);

    }

    private function &retrieveMove($move_id, &$pokemon = FALSE) {
        return [];
    }

    private function loadMove($move_id, $process) {

    }

    private function calculateDamage($attacker, $defencer, &$move) {

        $this->loadMove($move['move_id'], MOVEPHASE_CALBASEPOWER);

        $power = $move['power'];

        if($attacker['ability'] == ABILITY_TECHNICIAN && $move['power'] <= 60)
            $power *= 1.5;
        elseif($attacker['ability'] == ABILITY_FLAREBOOST && $attacker['status'] == STATUS_BURN && $move['class'] == MOVECLASS_SPECIAL)
            $power *= 1.5;
        elseif($attacker['ability'] == ABILITY_TOXICBOOST && in_array($attacker['status'], [STATUS_POISON, STATUS_TOXIC]) && $move['class'] == MOVECLASS_PHYSICAL)
            $power *= 1.5;
        elseif($attacker['ability'] == ABILITY_ANALYTIC && !in_array($move['move_id'], [MOVE_FUTURESIGHT, MOVE_DOOMDESIRE]) && $attacker['battle']['is_last'])
            $power *= 1.3;
        elseif($attacker['ability'] == ABILITY_RECKLESS && $move['flags']{FLAG_RECOIL})
            $power *= 1.2;
        elseif($attacker['ability'] == ABILITY_IRONFIST && $move['flags']{FLAG_PUNCH})
            $power *= 1.2;
        elseif($attacker['ability'] == ABILITY_COMPETITIVE && !in_array(GENDERLESS, [$attacker['gender'], $defencer['gender']]))
            $power *= $attacker['gender'] == $defencer['gender'] ? 1.25 : 0.75;
        elseif($attacker['ability'] == ABILITY_SANDFORCE && in_array($move['type'], [TYPE_ROCK, TYPE_STEEL, TYPE_GROUND]))
            $power *= 1.3;
        elseif($attacker['ability'] == ABILITY_SHEERFORCE && $move['flags'][FLAG_BENEFICIAL])
            $power *= 1.3;

        if($defencer['ability'] == ABILITY_HEATPROOF && $move['type'] == TYPE_FIRE)
            $power *= 0.5;
        elseif($defencer['ability'] == ABILITY_DRYSKIN && $move['type'] == TYPE_FIRE)
            $power *= 1.25;

        if($move['move_id'] == MOVE_BRINE && $defencer['hp'] / $defencer['max_hp'] <= 0.5 || $move['move_id'] == MOVE_VENOSHOCK && in_array($defencer['status'], [STATUS_POISON, STATUS_TOXIC]) || $move['move_id'] == MOVE_RETALIATE && $attacker['battle']['is_revenge'] || $move['move_id'] == MOVE_FUSIONFLARE && $this->field['last_move'] == MOVE_FUSIONBOLT || $move['move_id'] == MOVE_FUSIONBOLT && $this->field['last_move'] == MOVE_FUSIONFLARE)
            $power *= 2;
        if($move['is_me_first'])
            $power *= 1.5;
        if($move['move_id'] == MOVE_SOLARBEAM && !$this->field['weather_block'] && in_array($this->field['weather']['type'], [WEATHER_RAIN, WEATHER_SANDSTORM, WEATHER_HAIL]))
            $power *= 0.5;
        if($attacker['battle']['substatus'] == SUBSTATUS_CHARGE && $move['type'] == TYPE_ELECTRIC)
            $power *= 2;
        if($attacker['battle']['substatus'] == SUBSTATUS_HELPINGHAND)
            $power *= 1.5;
        if($this->field['water_sport'] && $move['type'] == TYPE_FIRE || $this->field['mud_sport'] && $move['type'] == TYPE_ELECTRIC)
            $power *= 0.5;

        /**
         * TODO
         * 如果攻击方携带属性强化道具，且技能是对应属性，威力修正×1.2。
         * 如果攻击方携带力量头巾，且使用物理技能，威力修正×1.1。
         * 如果攻击方携带知识眼镜，且使用特殊技能，威力修正×1.1。
         * 如果攻击方携带怪异之香，且技能是超能属性，威力修正×1.2。
         * 如果攻击方是携带金刚玉的帝牙卢卡，且技能是钢或龙属性，威力修正×1.2。
         * 如果攻击方是携带白玉的帕路奇犽，且技能是水或龙属性，威力修正×1.2。
         * 如果攻击方是携带白金玉的骑拉帝纳，且技能是鬼或龙属性，威力修正×1.2。
         * 如果此次攻击发动了对应属性宝石，威力修正×1.5。
         */



    }

    private function fetchBattleData() {

    }

    private function isHit($attacker, $defencer, $move) {

        if(in_array(ABILITY_NOGUARD, [$attacker['ability'], $defencer['ability']]) || $defencer['battle']['substatus'][SUBSTATUS_LOCK])
            return TRUE;

        if($move['flags']{FLAG_OHKO})
            return $attacker['level'] <= $defencer['level'] && mt_rand(1, 100) <= 30 + $attacker['level'] - $defencer['level'];

        if($defencer['battle']['substatus'][SUBSTATUS_TELEKINESIS] || $move['accuracy'] == 101)
            return TRUE;

        if($attacker['ability'] == ABILITY_UNAWARE)
            $defencer['battle']['stat_level']['evasion'] = 0;
        if($defencer['ability'] == ABILITY_UNAWARE)
            $attacker['battle']['stat_level']['accuracy'] = 0;
        if(in_array($defencer['battle']['substatus'], [SUBSTATUS_FORESIGHT, SUBSTATUS_MIRACLEEYE]))
            $defencer['battle']['stat_level']['evasion'] = min(0, $defencer['battle']['stat_level']['evasion']);

        $accuracy = max(-6, min(6, $attacker['battle']['stat_level']['accuracy'] - $defencer['battle']['stat_level']['evasion']));
        $accuracy = ($accuracy >= 0 ? 3 + $accuracy : 3) / ($accuracy <= 0 ? 3 - $accuracy : 3);
        $accuracy = floor($move['accuracy'] * $accuracy);

        if($attacker['ability'] == ABILITY_COMPOUNDEYES)
            $accuracy *= 1.3;
        elseif($attacker['ability'] == ABILITY_HUSTLE && $move['class'] == MOVECLASS_PHYSICAL)
            $accuracy *= 0.8;
        elseif($attacker['ability'] == ABILITY_VICTORYSTAR)
            $accuracy *= 1.1;

        if(($this->field['weather']['type'] == WEATHER_SANDSTORM && $defencer['ability'] == ABILITY_SANDVEIL) || ($this->field['weather']['type'] == WEATHER_HAIL && $defencer['ability'] == ABILITY_SNOWCLOAK))
            $accuracy *= 0.8;
        elseif($this->field['weather']['type'] == WEATHER_FOG)
            $accuracy *= 0.6;

        if($defencer['ability'] == ABILITY_TANGLEDFEET && $defencer['battle']['substatus'][SUBSTATUS_CONFUSE])
            $accuracy *= 0.8;

        /**
         * TODO
         * 如果防御方携带光粉或舒畅之香，命中×0.9。
         * 如果攻击方携带广角镜，命中×1.1。
         * 如果攻击方携带放大镜，并且是当回合最后一个行动，命中×1.2。
         * 如果攻击方发动了神秘果，命中×1.1。
         */

        if($this->field['gravity'])
            $accuracy *= 5 / 3;

        return mt_rand(1, 100) <= $accuracy;
    }

    public function initiateBattleField() {

    }

    private function appendReport($id, $args) {
        $this->report[] = Obtain::Text('battle_' . $id, $args);
    }

    private function useMove($move_id) {
        call_user_func(['MoveDB', '__' . $move_id]);
    }

}