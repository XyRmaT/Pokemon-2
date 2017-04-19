<?php

class BattlePokemon {
    private $stats;
    private $stat_levels;
    private $basic_data;
    private $substatus;
    private $previous = [
        'last_move_id' => 0
    ];
    private $current  = [
        'ability' => 0,
        'hp'      => 0,
        'status'  => 0,
        'types'   => []
    ];
    private $counter  = [
        COUNTER_SLEEP => 0
    ];

    public function __construct ($basic_data) {

        Verifier::assertExists($basic_data, [
            'hp', 'ability', 'type', 'type_b', 'status', 'happiness',
            'weight', 'holding_item'
        ]);

        $this->stats                   = [0, 0, 0, 0, 0, 0, 0, 0];
        $this->basic_data              = $basic_data;
        $this->current['ability']      = $basic_data['ability'];
        $this->current['types']        = array_filter([$basic_data['type'], $basic_data['type_b']]);
        $this->current['hp']           = $basic_data['hp'];
        $this->current['status']       = $basic_data['status'];
        $this->current['holding_item'] = $basic_data['holding_item'];

    }

    private function initBattleData () {
        // TODO
        return [];
    }

    public function calculateBasicStats () : array {
        return $this->stats = PokemonGeneral::getStats($this->basic_data);
    }

    public function setStatus (int $status, int $chance = 100, callable $callback = NULL) {
        if (mt_rand(1, 100) <= $chance) {
            if ($callback !== NULL)
                $callback();

            return $this->current['status'] = $status;
        }
        return FALSE;
    }

    public function getStatus () : int {
        return $this->current['status'];
    }

    public function isStatus ($status) : bool {
        return General::fuzzyHas($this->current['status'], $status);
    }


    public function getSubstatus (int $substatus) {
        return $this->substatus[$substatus] ?? 0;
    }

    public function setSubstatus (int $substatus, $value) {
        $this->substatus[$substatus] = $value;
    }

    public function incrementSubstatus (int $substatus, int $value = 1, callable $callback = NULL) : bool {
        if ($value < 0 && !empty($this->substatus[$substatus]) || $value > 0) {
            if ($callback !== NULL)
                $callback();
            return $this->substatus[$substatus] = isset($this->substatus[$substatus]) ?
                $this->substatus[$substatus] + $value : $value;
        }
        return FALSE;
    }

    public function incrementCounter (int $counter, int $value = 1, callable $callback = NULL) {
        if ($this->counter[$counter]) {
            if ($callback !== NULL)
                $callback();
            return $this->counter[$counter] += $value;
        }
        return FALSE;
    }

    public function getLastMoveID () : int {
        return $this->previous['last_move_id'];
    }

    public function isAbility ($ability) {
        return General::fuzzyHas($this->current['ability'], $ability);
    }

    public function isHolding ($item) {
        return General::fuzzyHas($this->basic_data['item_holding'], $item);
    }

    public function is ($nat_id) {
        return General::fuzzyHas($this->basic_data['nat_id'], $nat_id);
    }

    public function getStat (int $stat_id) : int {
        return $this->stats[$stat_id];
    }

    public function getHPPercent () : float {
        return floor($this->basic_data['hp'] / $this->getStat(STAT_HP));
    }

    public function getStatLevel (int $stat) : int {
        return $this->stat_levels[$stat];
    }

    public function getStatLevels () : array {
        return $this->stat_levels;
    }

    public function isType ($type) : bool {
        // TODO
        return TRUE;
    }

    public function hasEvolution () : bool {
        // TODO
        return TRUE;
    }

    public function getCurrentHP () : int {
        return $this->current['hp'];
    }

    public function getHappiness () : int {
        return $this->basic_data['happiness'];
    }

    public function getWeight () : int {
        return $this->basic_data['weight'];
    }

    public function isFloating () : bool {
        // TODO
        return TRUE;
    }

    public function isHoldingItemPokemonSpecific () : bool {
        // TODO
        return TRUE;
    }

    public function getLevel () : int {
        return $this->basic_data['level'];
    }

    public function getTypes () {
        return $this->current['types'];
    }

    public function setType (int $type, bool $fixed = FALSE) {
        if ($fixed) {
            $this->current['types'] = [$type];
        } else {
            $this->current['types'][] = $type;
            $this->current['types']   = array_unique($this->current['types']);
        }
    }

    public function getHoldingItem () : int {
        return $this->current['holding_item'];
    }

    public function hasField (int $field) : bool {
        // TODO
        return TRUE;
    }

    public function getAbility () : int {
        return $this->current['ability'];
    }
}

class BattleTrainer {
    private $user_id;
    private $party;

    public function __construct (int $user_id) {
        if (!$user_id) return;

        $this->user_id = $user_id;

        $query = DB::query('SELECT m.pkm_id, m.nat_id, m.nickname, m.gender, m.psn_value, m.idv_value, m.eft_value,
                                    m.nature, m.level, m.exp, m.item_holding, m.happiness, m.moves, m.ability, m.user_id,
                                    m.initial_user_id, m.item_captured, m.hp, m.form, m.status, m.new_moves, m.sprite_name,
                                    p.type, p.type_b, p.base_stat, p.height, p.weight
                            FROM pkm_mypkm m
                            LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id AND m.form = p.form
                            WHERE m.user_id = ' . $user_id . ' AND m.location IN (' . LOCATION_PARTY . ') AND m.hatch_nat_id != 0');
        while ($info = DB::fetch($query)) {
            $pokemon = new BattlePokemon($info);
            $pokemon->calculateBasicStats();
            $this->party[] = $pokemon;
        }
    }
}

class BattleMove {
    private $flags;
    private $current = [
        'class'  => 0,
        'type'   => 0,
        'type_b' => 0
    ];
    private $basic_data;
    private $class_name;
    private $targets;

    public function __construct (array $basic_data) {
        Verifier::assertExists($basic_data, ['type', 'pp', 'class', 'move_id', 'base_power', 'flags']);

        // TODO - load move

        $this->basic_data = $basic_data;
        $this->class_name = 'Move' . $basic_data['move_id'];

        // TODO - flags
    }

    public function setType (int $type) {
        $this->current['type'] = $type;
    }

    public function getType () : int {
        return $this->current['type'];
    }

    public function hasFlag (int $flag) : bool {
        return !empty($this->flags{$flag});
    }

    public function isClass (int $class) {
        return $this->current['class'] == $class;
    }

    public function getPP () : int {
        return $this->basic_data['pp'];
    }

    public function reducePP () {
        return --$this->basic_data['pp'];
    }

    public function is ($move_id) {
        return General::fuzzyHas($this->basic_data['move_id'], $move_id);
    }

    public function getID () : int {
        return $this->basic_data['move_id'];
    }

    public function isType ($type) : bool {
        $ptype = [$this->current['type'], $this->current['type_b']];
        if (!is_array($type)) $type = [$type];
        foreach ($type as $t) {
            if (in_array($t, $ptype, TRUE)) return TRUE;
        }
        return FALSE;
    }

    public function getBasePower () : int {
        return $this->basic_data['base_power'];
    }

    public function getClassName () : string {
        return $this->class_name;
    }

    public function getTargets () : array {
        return $this->targets;
    }
}

class BattleFactory {
    public static $weather_ball = [
        WEATHER_NONE          => TYPE_NORMAL,
        WEATHER_SUNLIGHT      => TYPE_FIRE,
        WEATHER_HARSHSUNLIGHT => TYPE_FIRE,
        WEATHER_RAIN          => TYPE_WATER,
        WEATHER_HEAVYRAIN     => TYPE_WATER,
        WEATHER_SANDSTORM     => TYPE_ROCK,
        WEATHER_HAIL          => TYPE_ICE,
        WEATHER_FOG           => TYPE_NORMAL
    ];

    public static $type_skin_ability = [
        ABILITY_AERILATE    => TYPE_FLYING,
        ABILITY_PIXILATE    => TYPE_FAIRY,
        ABILITY_REFRIGERATE => TYPE_ICE,
        ABILITY_GALVANIZE   => TYPE_ELECTRIC,
        ABILITY_NORMALIZE   => TYPE_NORMAL
    ];

    public static $type_gems = [
        ITEM_FIREGEM     => TYPE_FIRE,
        ITEM_WATERGEM    => TYPE_WATER,
        ITEM_GRASSGEM    => TYPE_GRASS,
        ITEM_ELECTRICGEM => TYPE_ELECTRIC,
        ITEM_NORMALGEM   => TYPE_NORMAL,
        ITEM_FIGHTINGGEM => TYPE_FIGHTING,
        ITEM_FLYINGGEM   => TYPE_FLYING,
        ITEM_BUGGEM      => TYPE_BUG,
        ITEM_POISONGEM   => TYPE_POISON,
        ITEM_ROCKGEM     => TYPE_ROCK,
        ITEM_GROUNDGEM   => TYPE_GROUND,
        ITEM_STEELGEM    => TYPE_STEEL,
        ITEM_ICEGEM      => TYPE_ICE,
        ITEM_PSYCHICGEM  => TYPE_PSYCHIC,
        ITEM_DARKGEM     => TYPE_DARK,
        ITEM_GHOSTGEM    => TYPE_GHOST,
        ITEM_DRAGONGEM   => TYPE_DRAGON,
        ITEM_FAIRYGEM    => TYPE_FAIRY
    ];

    public static $type_plates = [
        ITEM_FLAMEPLATE  => TYPE_FIRE,
        ITEM_SPLASHPLATE => TYPE_WATER,
        ITEM_MEADOWPLATE => TYPE_GRASS,
        ITEM_ZAPPLATE    => TYPE_ELECTRIC,
        ITEM_FISTPLATE   => TYPE_FIGHTING,
        ITEM_SKYPLATE    => TYPE_FLYING,
        ITEM_INSECTPLATE => TYPE_BUG,
        ITEM_TOXICPLATE  => TYPE_POISON,
        ITEM_STONEPLATE  => TYPE_ROCK,
        ITEM_EARTHPLATE  => TYPE_GROUND,
        ITEM_IRONPLATE   => TYPE_STEEL,
        ITEM_ICICLEPLATE => TYPE_ICE,
        ITEM_MINDPLATE   => TYPE_PSYCHIC,
        ITEM_DREADPLATE  => TYPE_DARK,
        ITEM_SPOOKYPLATE => TYPE_GHOST,
        ITEM_DRACOPLATE  => TYPE_DRAGON,
        ITEM_PIXIEPLATE  => TYPE_FAIRY
    ];

    public static $type_boosts = [
        ITEM_CHARCOAL     => TYPE_FIRE,
        ITEM_MYSTICWATER  => TYPE_WATER,
        ITEM_MIRACLESEED  => TYPE_GRASS,
        ITEM_MAGNET       => TYPE_ELECTRIC,
        ITEM_SILKSCARF    => TYPE_NORMAL,
        ITEM_BLACKBELT    => TYPE_FIGHTING,
        ITEM_SHARPBEAK    => TYPE_FLYING,
        ITEM_SILVERPOWDER => TYPE_BUG,
        ITEM_POISONBARB   => TYPE_POISON,
        ITEM_HARDSTONE    => TYPE_ROCK,
        ITEM_SOFTSAND     => TYPE_GROUND,
        ITEM_METALCOAT    => TYPE_STEEL,
        ITEM_NEVERMELTICE => TYPE_ICE,
        ITEM_TWISTEDSPOON => TYPE_PSYCHIC,
        ITEM_BLACKGLASSES => TYPE_DARK,
        ITEM_SPELLTAG     => TYPE_GHOST,
        ITEM_DRAGONFANG   => TYPE_DRAGON
    ];

    public static $type_berries = [
        ITEM_OCCABERRY   => TYPE_FIRE,
        ITEM_PASSHOBERRY => TYPE_WATER,
        ITEM_RINDOBERRY  => TYPE_GRASS,
        ITEM_WACANBERRY  => TYPE_ELECTRIC,
        ITEM_CHILANBERRY => TYPE_NORMAL,
        ITEM_CHOPLEBERRY => TYPE_FIGHTING,
        ITEM_COBABERRY   => TYPE_FLYING,
        ITEM_TANGABERRY  => TYPE_BUG,
        ITEM_KEBIABERRY  => TYPE_POISON,
        ITEM_CHARTIBERRY => TYPE_ROCK,
        ITEM_SHUCABERRY  => TYPE_GROUND,
        ITEM_BABIRIBERRY => TYPE_STEEL,
        ITEM_YACHEBERRY  => TYPE_ICE,
        ITEM_PAYAPABERRY => TYPE_PSYCHIC,
        ITEM_COLBURBERRY => TYPE_DARK,
        ITEM_KASIBBERRY  => TYPE_GHOST,
        ITEM_HABANBERRY  => TYPE_DRAGON,
        ITEM_ROSELIBERRY => TYPE_FAIRY
    ];

    public static function calculatePositiveTotal ($c, $v) {
        return $c + ($v > 0 ? $v : 0);
    }
}

class Battle {

    private $report = [];
    private $field  = [
        'weather' => ['type' => 0, 'counter' => 0],
        'terrain' => ['type' => 0, 'counter' => 0]
    ];

    private $is_wild  = TRUE;
    private $type     = BATTLETYPE_SINGLE;
    private $trainers = [];

    private $attacking_queue = [];


    public function Battle (int $battle_id, int $type, bool $is_wild, array $user_ids) {
        include_once '../constant/battle.php';

        $this->is_wild = $is_wild;
        $this->type    = $type;

        $this->initTrainers($user_ids);
    }

    private function initTrainers (array $user_ids) {
        switch ($this->type) {
            case BATTLETYPE_SINGLE:
                $this->trainers = [[new BattleTrainer($user_ids[0][0])]];
                break;
            case BATTLETYPE_DOUBLE:
                $this->trainers = [
                    [new BattleTrainer($user_ids[0][0])],
                    [new BattleTrainer($user_ids[1][0])],
                ];
                break;
            case BATTLETYPE_MULTI:
                $this->trainers = [
                    [new BattleTrainer($user_ids[0][0]), new BattleTrainer($user_ids[0][1])],
                    [new BattleTrainer($user_ids[1][0]), new BattleTrainer($user_ids[1][1])]
                ];
                break;
        }
    }

    public function isWild () : bool {
        return $this->is_wild;
    }

    public function swapPokemon () {
        // TODO
    }

    public function reorderPokemon () {
        // TODO
    }

    private function decideOrder () {
        // TODO
    }

    private function main () {

    }

    private function processTurn (BattlePokemon $attacker, BattlePokemon $defencer, &$field, $move_id) {

        /*$substatus = &$attacker['battle']['substatus'];
        $counters  = &$attacker['battle']['counters'];*/

        // Reset Destiny Bond, Grudge and Rage status
        $attacker->setSubstatus(SUBSTATUS_DESTINYBOND, 0);
        $attacker->setSubstatus(SUBSTATUS_GRUDGE, 0);
        $attacker->setSubstatus(SUBSTATUS_RAGE, 0);

        // Decrease Torment and Encore counter, also record last used move id if being encored
        $attacker->incrementSubstatus(SUBSTATUS_TORMENT, -1);
        $attacker->incrementSubstatus(SUBSTATUS_ENCORE, -1, function () use (&$move_id, $attacker) {
            $move_id = $attacker->getLastMoveID();
        });

        // Move recharging, if so reset the variable then ends the attack
        if ($attacker->getSubstatus(SUBSTATUS_RECHARGE)) {
            $attacker->setSubstatus(SUBSTATUS_RECHARGE, 0);
            $this->appendReport('recharging', [$attacker['nickname']]);
            return;
        }

        $move = $this->retrieveMove($move_id, $attacker);

        PROCESS_CHECKMOBILITY: {

            // Freeze hax and pokemon asleep
            if ($attacker['status'] == STATUS_FREEZE) {
                // Freeze has 25% chance defrost
                if ($attacker->setStatus(0, 25) !== FALSE) {
                    $this->appendReport('defrosted', [$attacker['nickname']]);
                } else {
                    $this->appendReport('frozen', [$attacker['nickname']]);
                    return;
                }
            } elseif ($attacker['status'] == STATUS_SLEEP) {
                if ($attacker->incrementCounter(COUNTER_SLEEP, -1) === FALSE) {
                    $this->appendReport('woke', [$attacker['nickname']]);
                } else {
                    $this->appendReport('sleeping', [$attacker['nickname']]);
                    // Only Snore & Sleeptalk can bypass the sleeping status
                    if (!$move->is([MOVE_SNORE, MOVE_SLEEPTALK]))
                        return;
                }
            }

            // Truant
            if ($attacker->getSubstatus(SUBSTATUS_TRUANT)) {
                $attacker->setSubstatus(SUBSTATUS_TRUANT, 0);
                $this->appendReport('truanted', [$attacker['nickname']]);
                return;
            }

            // Disable, if the move used is disabled, then cannot move
            if ($move_id == $attacker->getSubstatus(SUBSTATUS_DISABLE)) {
                $this->appendReport('disabled', [$attacker['nickname']]);
                return;
            }

            // Imprison
            $imprisoned_moves = $attacker->getSubstatus(SUBSTATUS_IMPRISON);
            if ($imprisoned_moves && in_array($move_id, $imprisoned_moves)) {
                $this->appendReport('imprisoned', [$attacker['nickname']]);
                return;
            }

            // Heal block
            if ($attacker->getSubstatus(SUBSTATUS_HEALBLOCK) && $move->hasFlag(FLAG_HEALING)) {
                $this->appendReport('healblocked', [$attacker['nickname']]);
                return;
            }

            // Confused
            $confuse_turn = $attacker->getSubstatus(SUBSTATUS_CONFUSE);
            if ($confuse_turn) {
                $attacker->setSubstatus(SUBSTATUS_CONFUSE, --$confuse_turn);
                if ($confuse_turn) {
                    $this->appendReport('confused', [$attacker['nickname']]);
                    if (mt_rand(0, 1)) {
                        // Attacking itself using a special move
                        $move = $this->retrieveMove(SPECIAL_CONFUSED_MOVE_ID);
                        $this->appendReport('attacked_itself', [$attacker['nickname']]);
                    }
                } else {
                    $this->appendReport('not_confused', [$attacker['nickname']]);
                }
            }

            // Flinch
            if ($attacker->getSubstatus(SUBSTATUS_FLINCH)) {
                $attacker->setSubstatus(SUBSTATUS_FLINCH, 0);
                $this->appendReport('flinched', [$attacker['nickname']]);

                return;
            }

            // Taunt
            if ($attacker->getSubstatus(SUBSTATUS_TAUNT) && $move->isClass(MOVECLASS_STATUS)) {
                $this->appendReport('taunted', [$attacker['nickname']]);
                return;
            }

            // Gravity
            if ($this->hasField(FIELD_GRAVITY) && $move->hasFlag(FLAG_LEVITATING)) {
                $this->appendReport('gravitational_force', [$attacker['nickname']]);
                return;
            }

            // Attract, has 50% percent of chance not being able to move
            if ($attacker->getSubstatus(SUBSTATUS_ATTRACT) && mt_rand(0, 1)) {
                $this->appendReport('attracted', [$attacker['nickname']]);
                return;
            }

            // Paralyze, has 25% percent of chance not being able to move
            if ($attacker->isStatus(STATUS_PARALYSIS) && !mt_rand(0, 3)) {
                $this->appendReport('paralyzed', [$attacker['nickname']]);
                return;
            }

        }

        // PP
        if ($move->getPP() <= 0) {
            $move = $this->retrieveMove(SPECIAL_STRUGGLE_MOVE_ID);
        } else {
            $move->reducePP();
        }

        if (!$this->isHit($attacker, $defencer, $move) && in_array($move['move_id'], [MOVE_HIGHJUMPKICK, MOVE_JUMPKICK])) {
            // TODO - Jump kick
        }

        $this->calculateDamage($attacker, $defencer, $move);

        // Use move
        $this->useMove($move['move_id']);

        PROCESS_END: {
        }

    }

    private function retrieveMove ($move_id, &$pokemon = FALSE) : BattleMove {
        // TODO
        return new BattleMove([]);
    }

    private function loadMove ($move_id, $process) {
        // TODO
    }

    private function hasField (int $field) : bool {
        // TODO
        return FALSE;
    }

    public function isMulti () : bool {
        return $this->type !== BATTLETYPE_SINGLE;
    }

    public function calculateDamage (BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move) {

        $self_attack  = $this->calculateAttack($attacker, $defencer, $move);
        $oppo_defence = $this->calculateDefence($attacker, $defencer, $move);
        $move_power   = $this->calculateMoveBasePower($attacker, $defencer, $move);
        $damage       = floor(($attacker->getLevel() * 2 + 10) / 250 * $move_power * ($self_attack / $oppo_defence) + 2);
        $is_crit      = $this->isCriticalHit($attacker, $defencer, $move);
        $oppo_type    = $defencer->getTypes();
        $type_mod     = $this->calculateTypeModifier($move->getType(), $oppo_type, $move);

        // Multi battle modifier
        // When a single field has more than 1 pokemon, it's defined as multi battle
        if ($this->isMulti() && isset($move->getTargets()[1]))
            $damage = floor($damage * 0.75);

        // Weather modifier
        // Sunny: water * 0.5, fire * 1.5; rainy: water * 1.5, fire * 0.5
        $weather_mod = 1;
        if ($this->isWeather(WEATHER_SUNLIGHT_GROUP)) {
            if ($move->isType(TYPE_FIRE))
                $weather_mod = 1.5;
            elseif ($move->isType(TYPE_WATER))
                $weather_mod = 0.5;
        } elseif ($this->isWeather(WEATHER_RAIN_GROUP)) {
            if ($move->isType(TYPE_WATER))
                $weather_mod = 1.5;
            elseif ($move->isType(TYPE_FIRE))
                $weather_mod = 0.5;
        }
        $damage = floor($damage * $weather_mod);

        // Critical hit modifier
        // Sniper is calculated in calculateDamageModifier
        if ($is_crit)
            $damage = floor($damage * 1.5);

        // Random modifier
        $damage = floor($damage * rand(85, 100) / 100);

        // STAB modifier
        // Adaptability will increase the damage by 2*, instead of 1.5*
        if ($move->isType($attacker->getTypes()))
            $damage = floor($damage * ($attacker->isAbility(ABILITY_ADAPTABILITY) ? 2 : 1.5));

        // Type modifier
        $damage = floor($damage * $type_mod);

        // Burn modifier
        if ($attacker->isStatus(STATUS_BURN) && $move->isClass(MOVECLASS_PHYSICAL) && !$attacker->isAbility(ABILITY_GUTS))
            $damage = floor($damage * 0.5);

        return floor($damage * $this->calculateDamageModifier($attacker, $defencer, $move, $is_crit, $type_mod));

    }

    public function calculateAttack (BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move) : int {

        $atk_hppercent = $attacker->getHPPercent();
        $move_type     = $move->getType();
        $is_foulplay   = $move->is(MOVE_FOULPLAY);
        $attack_key    = $move->isClass(MOVECLASS_PHYSICAL) ? STAT_ATTACK : STAT_SPATTACK;

        $attack = $is_foulplay ? $defencer->getStat(STAT_ATTACK) : $attacker->getStat($attack_key);
        $attack = floor($attack * $this->getStatModifier(($is_foulplay ? $attacker : $defencer)->getStatLevel($attack_key)));

        $mod = 1.0;

        // TODO
        if (($index = $attacker->isAbility([ABILITY_OVERGROW, ABILITY_BLAZE, ABILITY_TORRENT, ABILITY_SWARM])) &&
            $move_type === [TYPE_GRASS, TYPE_FIRE, TYPE_WATER, TYPE_BUG][$index] && $atk_hppercent < 33
        )
            $mod *= 1.5;
        elseif ($attacker->isAbility(ABILITY_GUTS) && $attacker->getStatus())
            $mod *= 1.5;
        elseif ($attacker->isAbility(ABILITY_DEFEATIST) && $atk_hppercent <= 50)
            $mod *= 0.5;
        elseif ($attacker->isAbility([ABILITY_HUGEPOWER, ABILITY_PUREPOWER]) && $move->isClass(MOVECLASS_PHYSICAL))
            $mod *= 2.0;
        elseif ($attacker->isAbility(ABILITY_SOLARPOWER) && $move->isClass(MOVECLASS_SPECIAL) &&
            $this->isWeather([WEATHER_SUNLIGHT, WEATHER_HARSHSUNLIGHT])
        )
            $mod *= 1.5;
        elseif ($attacker->isAbility(ABILITY_HUSTLE) && $move->isClass(MOVECLASS_PHYSICAL))
            $mod *= 1.5;

        if ($attacker->isHolding(ITEM_THICKCLUB) && $move->isClass(MOVECLASS_PHYSICAL) &&
            $attacker->is([POKEMON_CUBONE, POKEMON_MAROWAK, POKEMON_MAROWAKALOLAFORM])
        )
            $mod *= 2.0;
        elseif ($attacker->isHolding(ITEM_DEEPSEATOOTH) && $attacker->is(POKEMON_CLAMPERL) && $move->isClass(MOVECLASS_SPECIAL))
            $mod *= 2.0;
        elseif ($attacker->isHolding(ITEM_LIGHTBALL) && $attacker->is(POKEMON_PIKACHU))
            $mod *= 2.0;
        elseif ($attacker->isHolding(ITEM_CHOICEBAND) && $move->isClass(MOVECLASS_PHYSICAL))
            $mod *= 1.5;
        elseif ($attacker->isHolding(ITEM_CHOICESPECS) && $move->isClass(MOVECLASS_SPECIAL))
            $mod *= 1.5;

        // TODO - plus/minus

        return floor($attack * $mod);
    }

    public function calculateDefence (BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move) : int {

        $def_key = $move->isClass(MOVECLASS_PHYSICAL) ? STAT_DEFENCE : STAT_SPDEFENCE;
        $defence = $attacker->getStat($def_key);
        $defence = floor($defence * $this->getStatModifier($attacker->getStatLevel($def_key)));

        $mod = 1.0;

        if ($this->isWeather(WEATHER_SANDSTORM) && $defencer->isType(TYPE_ROCK) && $move->isClass(MOVECLASS_SPECIAL))
            $mod *= 1.5;
        elseif ($this->isWeather(WEATHER_SUNLIGHT) && $defencer->getSubstatus(ABILITY_FLOWERGIFT) && $move->isClass(MOVECLASS_SPECIAL))
            $mod *= 1.5;

        if ($defencer->isAbility(ABILITY_MARVELSCALE) && $defencer->getStatus() && $move->isClass(MOVECLASS_PHYSICAL))
            $mod *= 1.5;

        if ($defencer->isHolding(ITEM_DEEPSEASCALE) && $defencer->is(POKEMON_CLAMPERL) && $move->isClass(MOVECLASS_SPECIAL))
            $mod *= 2.0;
        elseif ($defencer->isHolding(ITEM_EVIOLITE) && $defencer->hasEvolution())
            $mod *= 1.5;
        elseif ($defencer->isHolding(ITEM_ASSAULTVEST) && $move->isClass(MOVECLASS_SPECIAL))
            $mod *= 1.5;

        return floor($defence * $mod);
    }

    public function calculateMoveBasePower (BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move) : int {

        $power = $move->getBasePower();

        /*$class_name = $move->getClassName();
        if (method_exists($class_name, 'getBasePower')) {
            call_user_func([$class_name, 'getBasePower']);
        }*/

        // TODO - place them into different files
        switch ($move->getID()) {
            case MOVE_WRINGOUT:
            case MOVE_CRUSHGRIP:
                $power = floor(1 + 120 * $defencer->getCurrentHP() / $defencer->getStat(STAT_HP));
                break;
            case MOVE_ELECTROBALL:
                $speed_diff = $attacker->getStat(STAT_SPEED) / $defencer->getStat(STAT_SPEED);
                if ($speed_diff >= 4) $power = 150;
                elseif ($speed_diff >= 3) $power = 120;
                elseif ($speed_diff >= 2) $power = 80;
                elseif ($speed_diff >= 1) $power = 60;
                else                      $power = 40;
                break;
            case MOVE_ERUPTION:
            case MOVE_WATERSPOUT:
                $power = floor(max(1, 150 * $attacker->getCurrentHP() / $attacker->getStat(STAT_HP)));
                break;
            case MOVE_FLAIL:
            case MOVE_REVERSAL:
                $remain = 48 * $attacker->getCurrentHP() / $attacker->getStat(STAT_HP);
                if ($remain <= 1) $power = 200;
                elseif ($remain <= 4) $power = 150;
                elseif ($remain <= 9) $power = 100;
                elseif ($remain <= 16) $power = 80;
                elseif ($remain <= 32) $power = 40;
                else                   $power = 20;
                break;
            case MOVE_FRUSTRATION:
                $power = floor((255 - $attacker->getHappiness()) / 2.5);
                break;
            case MOVE_RETURN:
                $power = floor($attacker->getHappiness() / 2.5);
                break;
            case MOVE_LOWKICK:
            case MOVE_GRASSKNOT:
                $weight = $defencer->getWeight();
                if ($weight >= 200) $power = 200;
                elseif ($weight >= 100) $power = 100;
                elseif ($weight >= 50) $power = 80;
                elseif ($weight >= 25) $power = 60;
                elseif ($weight >= 10) $power = 40;
                else                   $power = 20;
                break;
            case MOVE_HEAVYSLAM:
            case MOVE_HEATCRASH:
                $diff = $attacker->getWeight() / $defencer->getWeight();
                if ($diff >= 5) $power = 120;
                elseif ($diff >= 4) $power = 100;
                elseif ($diff >= 3) $power = 80;
                elseif ($diff >= 2) $power = 60;
                else                $power = 40;
                break;
            case MOVE_GYROBALL:
                $power = floor(min(150, 25 * $defencer->getStat(STAT_SPEED) / $attacker->getStat(STAT_SPEED)));
                break;
            case MOVE_STOREDPOWER:
            case MOVE_POWERTRIP:
                $power = 20 + 20 * array_reduce($attacker->getStatLevels(), ['BattleFactory', 'calculatePositiveTotal'], 0);
                break;
            case MOVE_PUNISHMENT:
                $power = min(200, 60 + 20 * array_reduce($defencer->getStatLevels(), ['BattleFactory', 'calculatePositiveTotal'], 0));
                break;
            case MOVE_PRESENT:
            case MOVE_TRUMPCARD:
            case MOVE_WATERSHURIKEN:
                // TODO
                break;
        }

        $mod = 1.0;
        if ($move->is(MOVE_ACROBATICS) && $attacker->isHolding(0) ||
            $move->is(MOVE_BRINE) && $defencer->getHPPercent() <= 50 ||
            $move->is(MOVE_FACADE) && $attacker->getStatus() ||
            $move->is(MOVE_HEX) && $defencer->getStatus() ||
            $move->is(MOVE_RETALIATE) && 1 === 2 /* TODO */ ||
            $move->is(MOVE_VENOSHOCK) && in_array($defencer->getStatus(), [STATUS_POISON, STATUS_TOXIC], TRUE) ||
            $move->is([MOVE_ASSURANCE, MOVE_AVALANCHE, MOVE_PAYBACK]) && !$this->isFasterThan($attacker, $defencer)
        ) {
            $mod *= 2.0;
        } elseif ($move->is(MOVE_SOLARBEAM) && $this->isWeather([WEATHER_RAIN, WEATHER_HEAVYRAIN, WEATHER_SANDSTORM, WEATHER_HAIL])) {
            $mod *= 0.5;
        } elseif ($move->is(MOVE_WEATHERBALL) && ($weather = $this->getWeather())) {
            $mod *= 2.0;
            $move->setType(BattleFactory::$weather_ball[$weather] ?? TYPE_NORMAL);
        }

        if (!$attacker->isAbility(ABILITY_LEVITATE) && !$attacker->isType(TYPE_FLYING) &&
            (
                $this->isTerrain(TERRAIN_GRASSY) && $move->isType(TYPE_GRASS) ||
                $this->isTerrain(TERRAIN_ELECTRIC) && $move->isType(TYPE_ELECTRIC) ||
                $this->isTerrain(TERRAIN_PSYCHIC) && $move->isType(TYPE_PSYCHIC)
            )
        )
            $mod *= 1.5;

        $ability = $attacker->getAbility();
        if ($attacker->isAbility(ABILITY_TECHNICIAN) && $power <= 60 ||
            $attacker->isAbility(ABILITY_FLAREBOOST) && $attacker->isStatus(STATUS_BURN) && $move->isClass(MOVECLASS_SPECIAL) ||
            $attacker->isAbility(ABILITY_TOXICBOOST) && $attacker->isStatus(STATUS_POISON_GROUP) && $move->isClass(MOVECLASS_PHYSICAL) ||
            $attacker->isAbility(ABILITY_STRONGJAW) && $move->hasFlag(FLAG_BITE) ||
            $attacker->isAbility(ABILITY_STEELWORKER) && $move->isType(TYPE_STEEL) ||
            $attacker->isAbility(ABILITY_MEGALAUNCHER) && $move->hasFlag(FLAG_AURAPULSE)
        ) {
            $mod *= 1.5;
        } elseif (
            $attacker->isAbility(ABILITY_ANALYTIC) && !$move->is([MOVE_FUTURESIGHT, MOVE_DOOMDESIRE]) && !$this->isFasterThan($attacker, $defencer) ||
            $attacker->isAbility(ABILITY_SANDFORCE) && $this->isWeather(WEATHER_SANDSTORM) && $attacker->isType([TYPE_ROCK, TYPE_GROUND, TYPE_STEEL]) ||
            $attacker->isAbility(ABILITY_SHEERFORCE) && 1 === 2 /* TODO */
        ) {
            $mod *= 1.3;
        } elseif ($attacker->isAbility(ABILITY_IRONFIST) && $move->hasFlag(FLAG_PUNCH)) {
            $mod *= 1.2;
        } elseif (($type = BattleFactory::$type_skin_ability[$ability] ?? FALSE) !== FALSE &&
            ($move->isType(TYPE_NORMAL) || $ability === ABILITY_NORMALIZE)
        ) {
            $mod *= 1.2;
            $move->setType($type);
        } elseif ($attacker->isAbility(ABILITY_TOUGHCLAWS) && $move->hasFlag(FLAG_CONTACT)) {
            $mod *= 4 / 3;
        } elseif ($attacker->isAbility(ABILITY_WATERBUBBLE) && $move->isType(TYPE_WATER)) {
            $mod *= 2.0;
        }

        if ($defencer->isAbility(ABILITY_HEATPROOF) && $move->isType(TYPE_FIRE))
            $mod *= 0.5;
        elseif ($defencer->isAbility(ABILITY_DRYSKIN) && $move->isType(TYPE_FIRE))
            $mod *= 1.25;

        if (($type = BattleFactory::$type_gems[$attacker->getHoldingItem()] ?? FALSE) !== FALSE && $move->isType($type)) {
            $mod *= 1.5;
            $move->gemBoosted();
        } elseif (
            $attacker->isHolding(ITEM_MUSCLEBAND) && $move->isClass(MOVECLASS_PHYSICAL) ||
            $attacker->isHolding(ITEM_WISEGLASSES) && $move->isClass(MOVECLASS_SPECIAL)
        ) {
            $mod *= 1.1;
        } elseif (
            $attacker->isHolding(ITEM_ADAMANTORB) && $attacker->is(POKEMON_DIALGA) && $move->isType([TYPE_STEEL, TYPE_DRAGON]) ||
            $attacker->isHolding(ITEM_LUSTROUSORB) && $attacker->is(POKEMON_PALKIA) && $move->isType([TYPE_WATER, TYPE_DRAGON]) ||
            ($type = BattleFactory::$type_boosts[$move->getType()] ?? FALSE) !== FALSE && $move->isType($type)
        ) {
            $mod *= 1.2;
        }

        if ($attacker->getSubstatus(SUBSTATUS_MEFIRST))
            $mod *= 1.5;
        if ($attacker->getSubstatus(SUBSTATUS_CHARGE) && $move->isType(TYPE_ELECTRIC))
            $mod *= 2.0;
        if ($attacker->getSubstatus(SUBSTATUS_HELPHAND))
            $mod *= 1.5;
        if ($this->hasField(FIELD_WATERSPORT) && $move->isType(TYPE_FIRE) ||
            $this->hasField(FIELD_MUDSPORT) && $move->isType(TYPE_ELECTRIC)
        )
            $mod *= 1 / 3;

        // TODO - BW dragon's signature moves
        // TODO - air lock, sheer force, echoed voice, pledges, furry cutter, gust, magnitude, ice ball, pursuit, reckless
        // TODO - rollout, round, stomping tantrum, consecutive moves, twister, whirlpool, fling, frustration, natural gift, power trip, present, accuracy & evasion, trump card, stakeout, battery

        return floor($power * $mod);
    }

    public function calculateDamageModifier (BattlePokemon $attacker, BattlePokemon $defencer, BattleMove $move, bool $is_crit, float $type_mod) : float {

        $mod = 1;

        $double_mod = $this->isMulti() ? 2 / 3 : 0.5;
        if ($defencer->hasField(SINGLEFIELD_REFLECT) && $move->isClass(MOVECLASS_PHYSICAL) ||
            $defencer->hasField(SINGLEFIELD_LIGHTSCREEN) && $move->isClass(MOVECLASS_SPECIAL)
        )
            $mod *= $double_mod;
        if ($defencer->hasField(SINGLEFIELD_AURORAVEIL))
            $mod *= $double_mod;

        if ($defencer->isAbility([ABILITY_MULTISCALE, ABILITY_SHADOWSHIELD]) && $defencer->getHPPercent() >= 100 ||
            $defencer->isAbility(ABILITY_WATERBUBBLE) && $move->isType(TYPE_FIRE)
        ) {
            $mod *= 0.5;
        } elseif ($defencer->isAbility([ABILITY_FILTER, ABILITY_SOLIDROCK, ABILITY_PRISMARMOR]) && $type_mod > 1) {
            $mod *= 0.75;
        } elseif ($defencer->isAbility(ABILITY_FLUFFY)) {
            if ($move->hasFlag(FLAG_CONTACT)) $mod *= 0.5;
            if ($move->isType(TYPE_FIRE)) $mod *= 2.0;
        } elseif ($defencer->isAbility(ABILITY_FURCOAT) && $move->isClass(MOVECLASS_PHYSICAL)) {
            $mod *= 0.5;
        }

        if ($attacker->isAbility(ABILITY_TINTEDLENS) && $type_mod < 1)
            $mod *= 2;
        elseif ($attacker->isAbility(ABILITY_SNIPER) && $is_crit)
            $mod *= 1.5;

        if (!$defencer->isFloating() &&
            (
                $this->isTerrain(TERRAIN_MISTY) && $move->isType(TYPE_DRAGON) ||
                $this->isTerrain(TERRAIN_GRASSY) && $move->is([MOVE_EARTHQUAKE, MOVE_BULLDOZE, MOVE_MAGNITUDE])
            )
        )
            $mod *= 0.5;

        if ($attacker->isHolding(ITEM_EXPERTBELT) && $type_mod > 1)
            $mod *= 1.2;
        elseif ($attacker->isHolding(ITEM_LIFEORB))
            $mod *= 1.3;
        elseif (($type = BattleFactory::$type_berries[$defencer->getHoldingItem()] ?? FALSE) !== FALSE &&
            $move->isType($type) && ($move->isType(TYPE_NORMAL) || $type_mod > 1)
        )
            $mod *= 0.5;

        if ($move->is([MOVE_STOMP, MOVE_BODYSLAM, MOVE_DRAGONRUSH, MOVE_HEAVYSLAM, MOVE_HEATCRASH,
                       MOVE_STEAMROLLER, MOVE_FLYINGPRESS, MOVE_PHANTOMFORCE]) && $defencer->getSubstatus(SUBSTATUS_MINIMIZE) ||
            $move->is(MOVE_WAKEUPSLAP) && $defencer->isStatus(STATUS_SLEEP) ||
            $move->is(MOVE_SMELLINGSALTS) && $defencer->isStatus(STATUS_PARALYSIS)
        )
            $mod *= 2.0;

        if ($move->is(MOVE_KNOCKOFF) && !$attacker->isHoldingItemPokemonSpecific())
            $mod *= 1.5;

        // TODO - Friend Guard, Surf, Earthquake

        return $mod;
    }

    public function isTerrain (int $terrain) {
        return $terrain === $this->field['terrain']['type'];
    }

    public function isFasterThan (BattlePokemon $a, BattlePokemon $b) : bool {
        // TODO
        return TRUE;
    }

    public function getWeather () : int {
        return $this->field['weather']['type'];
    }

    public function getStatModifier (int $stat_level) : float {
        return ($stat_level > 0 ? 2 + $stat_level : 2) / ($stat_level < 0 ? 2 - $stat_level : 2);
    }

    private function calculateTypeModifier ($atktypes, $deftypes, BattleMove $move = NULL) : float {
        $type_chart = [
            TYPE_NORMAL   => [[], [TYPE_ROCK, TYPE_STEEL], [TYPE_GHOST]],
            TYPE_FIRE     => [[TYPE_GRASS, TYPE_ICE, TYPE_BUG, TYPE_STEEL], [TYPE_FIRE, TYPE_WATER, TYPE_ROCK, TYPE_DRAGON], []],
            TYPE_WATER    => [[TYPE_FIRE, TYPE_GROUND, TYPE_ROCK], [TYPE_WATER, TYPE_GRASS, TYPE_DRAGON], []],
            TYPE_GRASS    => [[TYPE_WATER, TYPE_GROUND, TYPE_ROCK], [TYPE_FIRE, TYPE_GRASS, TYPE_POISON, TYPE_FLYING, TYPE_BUG, TYPE_DRAGON, TYPE_STEEL], []],
            TYPE_ELECTRIC => [[TYPE_WATER, TYPE_FLYING], [TYPE_GRASS, TYPE_ELECTRIC, TYPE_DRAGON], [TYPE_GROUND]],
            TYPE_ICE      => [[TYPE_GRASS, TYPE_GROUND, TYPE_FLYING, TYPE_DRAGON], [TYPE_FIRE, TYPE_WATER, TYPE_ICE, TYPE_STEEL], []],
            TYPE_FIGHTING => [[TYPE_NORMAL, TYPE_ICE, TYPE_ROCK, TYPE_DARK, TYPE_STEEL], [TYPE_POISON, TYPE_FLYING, TYPE_PSYCHIC, TYPE_BUG, TYPE_FAIRY], [TYPE_GHOST]],
            TYPE_POISON   => [[TYPE_GRASS, TYPE_FAIRY], [TYPE_POISON, TYPE_GROUND, TYPE_ROCK, TYPE_GHOST], [TYPE_STEEL]],
            TYPE_GROUND   => [[TYPE_FIRE, TYPE_ELECTRIC, TYPE_POISON, TYPE_ROCK, TYPE_STEEL], [TYPE_GRASS, TYPE_BUG], [TYPE_FLYING]],
            TYPE_FLYING   => [[TYPE_GRASS, TYPE_FIGHTING, TYPE_BUG], [TYPE_ELECTRIC, TYPE_ROCK, TYPE_STEEL], []],
            TYPE_PSYCHIC  => [[TYPE_FIGHTING, TYPE_POISON], [TYPE_PSYCHIC, TYPE_STEEL], [TYPE_DARK]],
            TYPE_BUG      => [[TYPE_GRASS, TYPE_PSYCHIC, TYPE_DARK], [TYPE_FIRE, TYPE_FIGHTING, TYPE_POISON, TYPE_FLYING, TYPE_GHOST, TYPE_STEEL, TYPE_FAIRY], []],
            TYPE_ROCK     => [[TYPE_FIRE, TYPE_ICE, TYPE_FLYING, TYPE_BUG], [TYPE_FIGHTING, TYPE_GROUND, TYPE_STEEL], []],
            TYPE_GHOST    => [[TYPE_PSYCHIC, TYPE_GHOST], [TYPE_DARK], [TYPE_NORMAL]],
            TYPE_DRAGON   => [[TYPE_DRAGON], [TYPE_STEEL], [TYPE_FAIRY]],
            TYPE_DARK     => [[TYPE_PSYCHIC, TYPE_GHOST], [TYPE_FIGHTING, TYPE_DARK, TYPE_FAIRY], []],
            TYPE_STEEL    => [[TYPE_ICE, TYPE_ROCK, TYPE_FAIRY], [TYPE_FIRE, TYPE_WATER, TYPE_ELECTRIC, TYPE_STEEL], []],
            TYPE_FAIRY    => [[TYPE_FIGHTING, TYPE_DRAGON, TYPE_DARK], [TYPE_FIRE, TYPE_POISON, TYPE_STEEL], []]
        ];

        if ($move !== NULL && $move->is(MOVE_FLYINGPRESS))
            $atktypes[] = TYPE_FIGHTING;

        $mod = 1;
        foreach ($atktypes as $atktype) {
            foreach ($deftypes as $deftype) {
                if (in_array($atktype, $type_chart[$atktype][0]) ||
                    $move && $move->is(MOVE_FREEZEDRY) && $deftype === TYPE_WATER
                ) {
                    $mod *= 2.0;
                } elseif (in_array($atktype, $type_chart[$deftype][1])) {
                    $mod *= 0.5;
                } elseif (in_array($atktype, $type_chart[$deftype][2])) {
                    $mod *= 0.0;
                    return $mod;
                }
            }
        }

        return $mod;
    }

    private function isCriticalHit ($attacker, $defencer, $move) : bool {
        // TODO
        return TRUE;
    }

    private function checkWeathers ($weathers) {
        return !$this->field['weather_block'] && in_array($this->field['weather']['type'], $weathers);
    }

    // TODO - Cloud Nine, Delta Stream
    private function isWeather ($weather) : bool {
        return General::fuzzyHas($this->field['weather'], $weather);
    }

    // TODO
    private function fetchBattleData () {

    }

    private function isHit ($attacker, $defencer, $move) {

        if (in_array(ABILITY_NOGUARD, [$attacker['ability'], $defencer['ability']]) || $defencer['battle']['substatus'][SUBSTATUS_LOCK])
            return TRUE;

        if ($move['flags']{FLAG_OHKO})
            return $attacker['level'] <= $defencer['level'] && mt_rand(1, 100) <= 30 + $attacker['level'] - $defencer['level'];

        if ($defencer['battle']['substatus'][SUBSTATUS_TELEKINESIS] || $move['accuracy'] == 101)
            return TRUE;

        if ($attacker['ability'] == ABILITY_UNAWARE)
            $defencer['battle']['stat_level']['evasion'] = 0;
        if ($defencer['ability'] == ABILITY_UNAWARE)
            $attacker['battle']['stat_level']['accuracy'] = 0;
        if (in_array($defencer['battle']['substatus'], [SUBSTATUS_FORESIGHT, SUBSTATUS_MIRACLEEYE]))
            $defencer['battle']['stat_level']['evasion'] = min(0, $defencer['battle']['stat_level']['evasion']);

        $accuracy = max(-6, min(6, $attacker['battle']['stat_level']['accuracy'] - $defencer['battle']['stat_level']['evasion']));
        $accuracy = ($accuracy >= 0 ? 3 + $accuracy : 3) / ($accuracy <= 0 ? 3 - $accuracy : 3);
        $accuracy = floor($move['accuracy'] * $accuracy);

        if ($attacker['ability'] == ABILITY_COMPOUNDEYES) {
            $accuracy *= 1.3;
        } elseif ($attacker['ability'] == ABILITY_HUSTLE && $move['class'] == MOVECLASS_PHYSICAL) {
            $accuracy *= 0.8;
        } elseif ($attacker['ability'] == ABILITY_VICTORYSTAR) {
            $accuracy *= 1.1;
        }

        if (($this->checkWeathers([WEATHER_SANDSTORM]) && $defencer['ability'] == ABILITY_SANDVEIL) || ($this->checkWeathers([WEATHER_HAIL]) && $defencer['ability'] == ABILITY_SNOWCLOAK)) {
            $accuracy *= 0.8;
        } elseif ($this->checkWeathers([WEATHER_FOG])) {
            $accuracy *= 0.6;
        }

        if ($defencer['ability'] == ABILITY_TANGLEDFEET && $defencer['battle']['substatus'][SUBSTATUS_CONFUSE])
            $accuracy *= 0.8;

        /**
         * TODO
         * 如果防御方携带光粉或舒畅之香，命中×0.9。
         * 如果攻击方携带广角镜，命中×1.1。
         * 如果攻击方携带放大镜，并且是当回合最后一个行动，命中×1.2。
         * 如果攻击方发动了神秘果，命中×1.1。
         */

        if ($this->field['gravity'])
            $accuracy *= 5 / 3;

        return mt_rand(1, 100) <= $accuracy;
    }

    public function initiateBattleField () {

    }

    /**
     * Append a line of report.
     * @param $id   - The identifier of the text
     * @param $args - The replacement texts for formatting
     */
    private function appendReport ($id, $args) {
        $this->report[] = General::getText('battle_' . $id, $args);
    }

    private function useMove ($move_id) {
        call_user_func(['MoveDB', '__' . $move_id]);
    }

}