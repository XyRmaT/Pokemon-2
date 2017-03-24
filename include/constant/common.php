<?php

const DB_FIELD_STRING = 1;
const DB_FIELD_NUMBER = 2;
const DB_FIELD_ORIGIN = 3;

const TYPE_FIRE     = 1;
const TYPE_WATER    = 2;
const TYPE_GRASS    = 3;
const TYPE_ELECTRIC = 4;
const TYPE_NORMAL   = 5;
const TYPE_FIGHTING = 6;
const TYPE_FLYING   = 7;
const TYPE_BUG      = 8;
const TYPE_POISON   = 9;
const TYPE_ROCK     = 10;
const TYPE_GROUND   = 11;
const TYPE_STEEL    = 12;
const TYPE_ICE      = 13;
const TYPE_PSYCHIC  = 14;
const TYPE_DARK     = 15;
const TYPE_GHOST    = 16;
const TYPE_DRAGON   = 17;
const TYPE_FAIRY    = 18;

const EGGGROUP_FIELD        = 1;
const EGGGROUP_BUG          = 2;
const EGGGROUP_FLYING       = 3;
const EGGGROUP_MONSTER      = 4;
const EGGGROUP_FAIRY        = 5;
const EGGGROUP_HUMANLIKE    = 6;
const EGGGROUP_MINERAL      = 7;
const EGGGROUP_GRASS        = 8;
const EGGGROUP_WATER1       = 9;
const EGGGROUP_WATER2       = 10;
const EGGGROUP_WATER3       = 11;
const EGGGROUP_DRAGON       = 12;
const EGGGROUP_AMORPHOUS    = 13;
const EGGGROUP_DITTO        = 14;
const EGGGROUP_UNDISCOVERED = 15;

const STATUS_BURN      = 1;
const STATUS_FREEZE    = 2;
const STATUS_PARALYSIS = 3;
const STATUS_SLEEP     = 4;
const STATUS_POISON    = 5;
const STATUS_TOXIC     = 6;

const GENDERLESS    = 0;
const GENDER_MALE   = 1;
const GENDER_FEMALE = 2;

const ITEM_TYPE_POKEBALL = 1;
const ITEM_TYPE_EVOSTONE = 2;
const ITEM_TYPE_HOLD     = 3;
const ITEM_TYPE_MEDICINE = 4;

const LOCATION_PARTY   = '1, 2, 3, 4, 5, 6';
const LOCATION_DAYCARE = 7;
const LOCATION_PCHEAL  = 8;
const LOCATION_SHELTER = 9;
const LOCATION_TRADE   = 10;
define('LOCATION_BOX', implode(',', range(101, 200)));

const MOVE_BY_LEVEL = 1;
const MOVE_BY_TMHM  = 2;


const MOVECLASS_STATUS   = 0;
const MOVECLASS_PHYSICAL = 1;
const MOVECLASS_SPECIAL  = 2;


const EVOSTRUC_NATID               = 0;
const EVOSTRUC_BY_LEVEL            = 1;
const EVOSTRUC_BY_HAPPINESS        = 2;
const EVOSTRUC_BY_BEAUTY           = 3;
const EVOSTRUC_BY_MAP              = 4;
const EVOSTRUC_BY_ITEM             = 5;
const EVOSTRUC_BY_MOVELEARNT       = 6;
const EVOSTRUC_BY_PARTYPOKEMON     = 7;
const EVOSTRUC_BY_GENDER           = 8;
const EVOSTRUC_BY_TIMEFRAME        = 9;
const EVOSTRUC_BY_OTHER            = 10;
const EVOSTRUC_BY_TRADE            = 11;
const EVOSTRUC_BY_TRADEWITHPOKEMON = 12;
const EVOSTRUC_BY_ALOLA            = 13;

const EVOSTRUC_BY_OTHER_ATKGTDEF        = 1;
const EVOSTRUC_BY_OTHER_ATKLTDEF        = 2;
const EVOSTRUC_BY_OTHER_ATKEQDEF        = 3;
const EVOSTRUC_BY_OTHER_PVEGTEFIVE      = 4;
const EVOSTRUC_BY_OTHER_PVLTFIVE        = 5;
const EVOSTRUC_BY_OTHER_TURN3DS         = 6;
const EVOSTRUC_BY_OTHER_VERSIONSUN      = 7;
const EVOSTRUC_BY_OTHER_VERSIONMOON     = 8;
const EVOSTRUC_BY_OTHER_LEARNTFAIRTMOVE = 9;
const EVOSTRUC_BY_OTHER_PARTYDARKTYPE   = 10;
const EVOSTRUC_BY_OTHER_WEATHERRAIN     = 11;

/*
    * 0
    *        0=进化链接
    *        1=等级（1-100）
    *        2=亲密度（1-255）
    *        3=美丽度（1-255）
    *        4=地图（地图编号）
    *        5=携带道具/使用道具（道具编号）
    *        6=掌握技能（技能编号）
    *        7=队伍中存在精灵（精灵编号）
    *        8=性别（1=无性，2=公，3=母）
    *        9=时段（）
    *        10=特殊（1=攻击>防御，2=攻击<防御，3=攻击=防御，4=性格值尾数>=5，5=性格值尾数<5）
    *        11=其它（1=通信进化，2=使用道具进化）
    *        12=其它进阶值（如果其它=1，值则为特定交换的精灵的编号。如果其它=2，值则为道具的编号。视精灵进化方式决定值是否为空。）
    *     1
    *         ...
 */

define('FIELDS_POKEMON_BASIC',
    'm.pkm_id, m.nat_id, m.gender, m.sprite_name, m.nickname, m.level, m.item_holding, ' .
    'm.item_captured, m.status, m.happiness, m.form, m.exp, m.location');
define('FIELDS_POKEMON_LEVELUP', FIELDS_POKEMON_BASIC .
    ',p.exp_type, p.evolution_data, p.base_stat, p.name_zh name, m.level, m.exp, m.pkm_id, m.nature, ' .
    'm.nat_id, m.moves, m.new_moves, m.idv_value, m.eft_value, m.hp');
define('FIELDS_POKEMON_DETAILED', 'm.nat_id, m.pkm_id, m.gender, m.hp, m.exp, m.level, m.nature, ' .
    'm.nickname, m.form, m.eft_value, m.idv_value, m.new_moves, m.moves, ' .
    'm.sprite_name, m.item_captured, m.time_hatched, m.met_time, m.met_level, ' .
    'm.met_location, m.beauty, m.item_holding, m.happiness, m.psn_value, ' .
    'm.form, m.initial_user_id, m.status, ' .
    'a.name_zh ability, p.base_stat, p.type, p.type_b, p.exp_type, p.name_zh name, p.evolution_data,' .
    'mb.trainer_name');