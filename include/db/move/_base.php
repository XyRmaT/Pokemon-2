<?php

class MoveBase {
    public static function getMovePower(Battle $instance, BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move) {
        return $move->getBasePower();
    }
    public function execute(Battle $instance, BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move) {

    }
}