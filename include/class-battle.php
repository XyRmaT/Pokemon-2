<?php

# Test if Foresight, Miracle Eye, Odor Sleuth can be overlaid.

/**
 * Main Status: 1(Paralysis), 2(Poison), 3(Bad Poison), 4(Sleep), 5(Burn), 6(Freeze)
 * Sub Status:
 *     Negative: 1(Confuse), 2(FIL), 3(Bind), 4(Block), 5(
 */

define('INBATTLE', TRUE);
define('ROOTBATTLE', ROOT . '/cache/battle');

class Battle {

    public static $report    = '';
    public static $item      = '';
    public static $order     = '01';
    public static $movefirst = '00';
    public static $movelast  = '00';
    public static $pokemon   = [];
    public static $move      = [];
    public static $field     = [];
    public static $reportcur = [];
    public static $reportend = [];
    public static $wild      = 1;
    public static $swappid   = 0;
    public static $isend     = FALSE;
    public static $passturn  = FALSE;
    public static $failed    = FALSE;
    public static $swapped   = FALSE;
    public static $faintswap = TRUE;
    public static $charged   = FALSE;

    public static $atkkey   = [];
    public static $defkey   = [];
    public static $atk      = [];
    public static $def      = [];
    public static $atkfield = [];
    public static $deffield = [];
    public static $atkmove  = [];
    public static $defmove  = [];

    protected static $stabledamage = 0;
    protected static $moveflag     = '';
    protected static $m            = [];

    private static $tempsaver = [];

    private static $langtrapped = [
        1 => '被紧紧地勒住了！',
        2 => '被缠住了！',
        3 => '的周围都是火焰！',
        4 => '被紧紧地夹住了！',
        5 => '被卷进了漩涡里！',
        6 => '被沙子束缚住了脚！',
        7 => '被熊熊火焰包围了！'
    ];

    # settings
    private static $num = [
        'ssn' => 46                    # substatus count (for pokemon temp battle data generation)
    ];

    public static function LoadBattleData($uid, $pokemon = []) {

        return unserialize(gzinflate(file_get_contents(ROOTBATTLE . '/user-' . $uid)));

    }

    public static function AlterInstantStatus(&$pokemon, $status, $value, $chance) {

        if(rand(1, 100) <= $chance) $pokemon[1]['insstatus'] = $value;

    }

    public static function AlterStatLevel(&$pokemon, $action, $value = 1, $chance = 100, $report = TRUE) {

        if(rand(1, 100) > $chance) return FALSE;

        $action  = explode('-', $action);
        $namearr = ['ATK' => 0, 'DEF' => 1, 'SPATK' => 2, 'SPDEF' => 3, 'SPD' => 4, 'ACC' => 5, 'EVA' => 6];
        $statarr = ['攻击', '防御', '特攻', '特防', '速度', '命中', '回避'];
        $part    = '提升';
        $key     = $namearr[$action[0]];
        $stat    = &$pokemon[1][1][$key];

        switch($action[1]) {
            case 'INC':
                $diff = 6 - ($stat + $value);
                $stat = min($stat + $value, 6);
                break;
            case 'DEC':
                $diff = 6 + ($stat - $value);
                $part = '下降';
                $stat = max($stat - $value, -6);
                break;
            case 'EMP':
                $stat = 0;
                return FALSE;
                break;
            default:
                return FALSE;
                break;
        }

        if($value > 1 && $diff > -1 * ($value - 1))
            self::$report .= $pokemon[0]['name'] . '的' . $statarr[$key] . '急速' . $part . '！<br>';
        elseif($diff >= -1 * ($value - 1))
            self::$report .= $pokemon[0]['name'] . '的' . $statarr[$key] . $part . '了！<br>';
        elseif($diff >= -$value && $report === TRUE)
            self::$report .= $pokemon[0]['name'] . '的' . $statarr[$key] . '不能再' . $part . '了！<br>';

    }

    public static function AlterStatus(&$pokemon, $status, $chance = 100, $round = 0, $failreport = FALSE) {

        $statusarr = ['BRN' => '烧伤', 'FRZ' => '冰冻', 'PAR' => '麻痹', 'SLP' => '睡眠', 'PSN' => '中毒', 'TXC' => '中毒'];

        if(!empty($pokemon[0]['status']) ||                                                                                                # 存在状态
            $chance >= 0 && rand(0, 100) > $chance ||                                                                                    # 随机数未命中
            $status === 'BUR' && ($pokemon[0]['type'] == '0' || $pokemon[0]['abi'] === '41') ||                                            # 烧伤 - 火属性、水之掩护
            $status === 'FRZ' && ($pokemon[0]['type'] == '13' || $pokemon[0]['abi'] === '40') ||                                         # 冰冻 - 冰属性、熔岩盔甲
            $status === 'SLP' && in_array($pokemon[0]['abi'], ['15', '72']) ||                                                        # 睡眠 - 不眠、干劲
            in_array($status, ['PSN', 'TXC']) && (in_array($pokemon[0]['type'], ['9', '12']) || $pokemon[0]['abi'] === '17')    # 中毒 - 毒属性、钢属性、免疫
        ) {

            if($failreport === TRUE) self::FailMove();

            return FALSE;

        }

        $actarr = [NULL,
            'BRN' => ['1', '烧伤了'],
            'FRZ' => ['2', '被冻住了'],
            'PAR' => ['3', '麻痹了'],
            'SLP' => ['4', '睡着了'],
            'PSN' => ['5', '中毒了'],
            'TXC' => ['6', '中了剧毒']
        ];

        $pokemon[0]['status'] = $actarr[$status][0];

        if($status === 'SLP') { # 睡眠

            /*
                - If pokemon's ability is Early Bird, half the round
                - The sleep counter should be at the front but I made it tail, so the counter should plus 1
            */

            $pokemon[1][11] = (($pokemon[0]['abi'] === '48') ? (($round === 0) ? rand(2, 4) : $round) : (($round === 0) ? rand(1, 2) : floor($round / 2))) + 1;

        }

        self::$reportcur[] = $pokemon[0]['name'] . $actarr[$status][1] . '！';

    }

    public static function FailMove() {

        self::$report .= '什么都没发生……<br>';

        return FALSE;

    }

    public static function Fight() {

        global $user, $_G;

        /**
         * Damage = (Attack Stat * (Stat Level * 2 / 5 + 2) * Move Power / Defence Stat / 50 * Correction 1 + 2) * Correction2
         *     [Move Power]
         *         - If attacker's ability is Techinician and the original move power is less or equal to 60, multiply by 1.5
         *         - If attacker's ability is
         */

        // Obtain the final stat of pokemon

        for($i = 0; $i < 2; $i++) {

            $tmp = $i ^ 1;

            self::ObtainStat(self::$pokemon[$i], self::$pokemon[$tmp], isset(self::$move[$i]) ? self::$move[$i] : [], isset(self::$move[$tmp]) ? self::$move[$tmp] : []);

        }

        // Obtain the attack order with priority calculations

        self::ObtainAttackOrder();
        Kit::Library('db', ['move']);

        // Starts to attack

        $i = -1;

        BATTLE: {

            if($i > -1) {

                BATTLEEND: {

                    if(self::$failed === FALSE && $_GET['process'] === 'usemove' && !self::$charged)

                        --$atk[0]['move'][$atkmove['key']][1];

                    if(self::$isend === TRUE || $atk[0]['hp'] < 1 || $def[0]['hp'] < 1)

                        goto TAIL;

                    elseif($i >= 1) {

                        $atk[1]['insstatus'] = 0;
                        $def[1]['insstatus'] = 0;

                        goto TAIL;

                    }

                    self::$stabledamage = 0;
                    self::$m            = [];
                    self::$failed       = FALSE;
                    self::$charged      = FALSE;

                    $atk[1][7] = $atkmove['mid'] ? $atkmove['mid'] : 0;

                }

            }

            ++$i;

            /*
                Define simplified variables by identifier the attacker and defender
                Duplicate keys of everything used in battle, class properties should be use in database code
                And other plain variables should be use in the main code as abbreviated.
            */

            $atkkey = !isset($defkey) ? strpos(self::$order, '1') : $defkey;
            $defkey = $atkkey ^ 1;

            self::$atkkey   = $atkkey;
            self::$defkey   = $defkey;
            self::$atk      = &self::$pokemon[$atkkey];
            self::$def      = &self::$pokemon[$defkey];
            self::$atkfield = &self::$field[7 + $atkkey];
            self::$deffield = &self::$field[7 + $defkey];
            self::$atkmove  = &self::$move[$atkkey];
            self::$defmove  = &self::$move[$defkey];

            $atk      = &self::$pokemon[$atkkey];
            $def      = &self::$pokemon[$defkey];
            $atkfield = &self::$field[$atkkey];
            $deffield = &self::$field[$defkey];
            $atkmove  = &self::$move[$atkkey];
            $defmove  = &self::$move[$defkey];

            $abifunc  = '__' . $atk[0]['abi'];
            $movefunc = '__' . $atkmove['mid'];

            if($_GET['process'] === 'useitem' && $atkkey === 1) {

                self::UseItem();

                goto BATTLE;

            } elseif($_GET['process'] === 'swappm' && $atkkey === 1) {

                Battle::$swapped = self::ReorderPokemon(intval($_GET['pid']));

                goto BATTLE;

            }

            if($i === 1) {

                for($j = 0; $j < 2; $j++) {

                    if($j === 0) self::ObtainStat($atk, $def, $atkmove, isset($defmove) ? $defmove : []);
                    elseif($j === 1) self::ObtainStat($def, $atk, isset($defmove) ? $defmove : [], $atkmove);

                }

            }

            ($atk[0]['crritem'] === '140') && self::$report .= $atk[0]['name'] . '漂浮在了空中。<br>'; # 气球


            /*
                Pre processor for swapping pokemon
                which allows several ability, field to activate
            */

            if(1 === 2 && $_GET['process'] === 'swappm' && self::$order{1} === '1') {

                /*
                    because reference variables do not update values after the source has been updated when it's an value of an array
                    so $atk should be redeclare for further calculation
                */

                self::$pokemon = self::ReorderPokemon(self::$pokemon, self::$swapid);
                $atk           = &self::$pokemon[$atkkey];

                if($atk[0]['abi'] !== '98') { # 魔法守护

                    if($atkfield{0} > 0 && !in_array('7', [$atk[0]['type'], $atk[0]['typeb']]) && $atk[0]['abi'] !== '26' && $atk[0]['crritem'] != '140') {

                        $atk[0]['hp'] -= floor($atk[0]['maxhp'] / (10 - 2 * $field{0}));
                        self::$report .= '锋利的菱片扎在了' . $atk[0]['name'] . '的脚上<br>';

                    }


                    // Stealth Rock activation, damage formula: [type restriction] * [max hp] / 8

                    if($atkfield{1} > 0) {


                        $atk[0]['hp'] -= floor(self::ObtainRestrictModifier('10', $atk[0]['type'], $atk[0]['typeb']) * $atk[0]['maxhp'] / 8);
                        self::$report .= '锐利的岩石扎在了' . $atk[0]['name'] . '的脚上<br>';

                    }


                    /*
                        Toxic spikes activation
                            eliminate if Defender is poison type
                            immune if
                                - Defender is poison type
                                - Defender has ability Immunity / Levitate
                                - Defender's field has effect of Safeguard
                            It poisons the current pokemon, the poison level based on times it has been used, maximum 2
                    */

                    if($atkfield{2} > 0 && in_array('9', [$atk[0]['type'], $atk[0]['typeb']])) {

                        $atkfield{2} = '0';
                        self::$report .= '脚低下的毒菱都没了！<br>';

                    } elseif($atkfield{2} > 0 && $atk[1][2][36] === FALSE && !in_array(7, [$atk[0]['type'], $atk[0]['typeb']]) && !in_array($atk[0]['abi'], ['17', '26'])) {

                        $atk[0]['status'] = strval(min(4 + $atkfield{2}, 6));
                        self::$report .= '紫红的妖菱扎在了' . $atk[0]['name'] . '的脚上<br>';

                    }

                }

                # 降雨、无天气、威吓、复制、起沙、压力、干旱、天气锁、下载、破格、预知梦、缓慢启动、降雪、洞察、替代物、涡轮火花、垓级电压

                if(in_array($atk[0]['abi'], ['2', '13', '22', '36', '45', '46', '46', '76', '88', '104', '108', '112', '117', '119', '150', '163', '164'])) {

                    //AbilityDb::$abifunc();

                }

                $atk[0]['crritem'] === '140' && self::$report .= $atk[0]['name'] . '漂浮在了空中。<br>'; # 气球

            } elseif($_GET['process'] === 'usemove' || in_array($_GET['process'], ['useitem', 'swappm']) && $atkkey === 0) {

                /*
                    Focus punch in process..
                */

                // determine whether or not there is a charged move

                if(self::$atk[1][6]) {

                    self::$charged   = TRUE;
                    self::$atk[1][6] = 0;

                }

                // zerofy the counter for various status

                $atk[1][2][32] = FALSE; # 同旅
                $atk[1][2][33] = FALSE; # 怨念
                $atk[1][2][43] = FALSE; # 愤怒

                $atk[1][2][6] && --$atk[1][2][6];            # 状态 - 寻衅
                $atk[1][2][15] && --$atk[1][2][15];        # 状态 - 鼓掌
                $atk[1][2][3] && --$atk[1][2][3];            # 状态 - 束缚


                if($atk[1][2][44]) { # 僵直

                    $atk[1][2][44] = FALSE;
                    self::$failed  = TRUE;
                    self::$report .= $atk[0]['name'] . '无法动弹。<br>';

                    goto BATTLE;

                }

                if($atk[1][2][15] > 0) {

                    // Awaiting for the report

                    $atk[1][2][15] -= 1;

                    $atkmove = self::ObtainMoveData($atk[1][7]);

                }


                if($atk[0]['status'] === '2') { # 冰冻

                    if(rand(1, 10) > 3) {

                        self::$report .= $atk[0]['name'] . '被冻住了。<br>';
                        self::$failed = TRUE;

                    } else {

                        $atk[0]['status'] = '0';

                    }

                } elseif($atk[0]['status'] === '4' && !in_array($atkmove['mid'], ['173', '214'], TRUE)) { # 睡眠

                    if($atk[1][11] === 0) {

                        $atk[1][11]       = FALSE;
                        $atk[0]['status'] = '0';
                        self::$report .= $atk[0]['name'] . '醒来了！<br>';

                    } else {

                        self::$failed = TRUE;
                        self::$report .= $atk[0]['name'] . '呼呼大睡。<br>';

                        goto BATTLE;

                    }

                }


                if($atk[1][2][37]) { # 懒惰

                    $atk[1][2][37] = FALSE;
                    self::$failed  = TRUE;
                    self::$report .= $atk[0]['name'] . '懒洋洋地。<br>';

                    goto BATTLE;

                }

                if($atk[1][2][16] && $atkmove['mid'] == $atk[1][7]) { // 3. 残废 && 待确认回合数

                    self::$report .= $atk[0]['name'] . '的技能被封锁了。<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if($atk[1][2][30] && in_array($atkmove['mid'], $atk[1][2][30])) { // 4. 封印

                    self::$report .= $atk[0]['name'] . '的技能被封印了。<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if($atk[1][2][9] && $atkmove['btlefct']{9} === '1') { // 5. 回复封印

                    self::$report .= $atk[0]['name'] . '无法回复。<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if($atk[1][2][0] === 0) {

                    $atk[1][2][0] = FALSE;
                    self::$report .= $atk[0]['name'] . '解除了混乱！<br>';

                } elseif($atk[1][2][0] && $atk[0]['abi'] !== '20') { # 主状态 - 混乱、自我中心

                    self::$report .= $atk[0]['name'] . '混乱了！<br>';

                    self::$failed = TRUE;

                    if(rand(1, 2) === 2) {

                        self::$report .= $atk[0]['name'] . '打了自己。<br>';
                        $atk[0]['hp'] -= min(floor(floor(floor($atk[0]['level'] * 2 / 5 + 2) * 40 * $atk[0]['atk'] / $atk[0]['def']) / 50) + 2, $atk[0]['maxhp'] - $atk[0]['hp']);

                        ($atk[0]['hp'] <= 0) && self::$report .= $atk[0]['name'] . '倒下了。<br>';

                        goto BATTLE;

                    }

                }

                if($atk[1]['insstatus'] === 1 && $atk[0]['abi'] != '39') { # 特性 - 精神力

                    if($atk[0]['abi'] === '80') {# 特性 - 不屈之心

                        //AbilityDb::$abifunc();

                    } else {

                        self::$report .= $atk[0]['name'] . '感到很害怕。<br>';

                        self::$failed = TRUE;

                        goto BATTLE;

                    }

                }

                if($atk[1][2][5] && $atkmove['class'] === '0') { # 副状态 - 挑拨

                    self::$report .= $atk[0]['name'] . '盯着对方。<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if(self::$field['gravity'] > 0 && in_array($atkmove['mid'], [19, 26, 136, 150, 340, 393, 477, 507])) { # 场地状态 - 重力

                    self::$report .= $atk[0]['name'] . '感觉身体很重。<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if($atk[1][2][1] && $def[0]['pid'] == $atk[1][2][1] && rand(1, 2) === 2) { # 副状态 - 着迷

                    self::$report .= $atk[0]['name'] . '用迷离的眼神望着对方。<br>';
                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if($atk[0]['status'] === '3' && $atk[0]['abi'] !== '95' && rand(1, 4) === 1) {

                    self::$report .= $atk[0]['name'] . '被麻痹折服了无法动弹！<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                if($atk[0]['move'][$atkmove['key']][1] <= 0 && !self::$charged) {

                    self::$report .= $atk[0]['name'] . '的PP不足。<br>';

                    self::$failed = TRUE;

                    goto BATTLE;

                }

                /*
                    Choice item series in progress...
                */

                /*
                    Calculate Heal Block and Gravity again?
                    Needs to prove
                */

                MOVEBEGIN: {

                    self::$m['power'] = $atkmove['power'];

                    self::$report .= $atk[0]['name'] . '使出了' . $atkmove['name'] . '！<br>';

                    if($atkmove['btlefct']{6} === '1' && $atk[0]['crritem'] === '83') { # 力量香草

                        //self::DropItem($atk); // @ marked: DropItem

                        self::$report .= '力量香草给予了' . $atk[0]['name'] . '力量！';

                    } elseif(!self::$charged && self::$atkmove['btlefct']{6} === '1' && method_exists('MoveDb', $movefunc)) {

                        MoveDb::$movefunc();

                        goto BATTLE;

                    }

                    self::__MoveCurrentPlace($atk, FALSE);

                    self::$m = [
                        'type'      => $atkmove['type'],
                        'power'     => $atkmove['power'],
                        'defstat'   => ($atkmove['class'] === '1') ? 'def' : (($atkmove['class'] === '2') ? 'spdef' : NULL),
                        'recoilper' => 0
                    ];

                    /*
                        Calculating move's type
                    */

                    if(in_array($atkmove['mid'], ['546', '449', '363', '237'], TRUE)) { # 科技爆破、制裁之砾、自然恩惠、觉醒力量

                        self::$moveflag = 'CALTYPE';

                        if(method_exists('MoveDb', $movefunc))

                            MoveDb::$movefunc();

                    } elseif($atk[0]['abi'] === '96') { # 普通皮肤

                        //AbilityDb::$abifunc();

                    }

                    /*
                        Filtering the target
                        note that because currently no double in this system so some determination is not included
                        Dream Eater, Synchronoise and Captivate are also excluded here for later move function activation
                    */

                    $typemod = (in_array($atkmove['class'], ['1', '2']) ||
                        $atkmove['mid'] == '86' && $def[0]['type'] == '11')
                        ? self::ObtainRestrictModifier($atkmove['type'], $def[0]['type'], $def[0]['typeb']) : 1;

                    if($typemod === 0) {

                        self::$report .= '技能没有效果……<br>';

                        goto MOVEFAILED;

                    } elseif($atkmove['type'] == '11' && $atkmove['class'] != 0 && $def[0]['abi'] === '26') { # 浮游

                        //Ability::$abifunc();

                        goto MOVEFAILED;

                    } elseif($def[1]['insstatus'] === 2 && $atk[1][2][42] !== 4) { # 保护

                        self::$report .= $def[0]['name'] . '保护了自己！';

                        goto MOVEFAILED;

                    } elseif(($def[1]['insstatus'] === 4 || $def[0]['abi'] === '156') && $atkmove['btlefct']{3} === '1') { # 魔装反射

                        self::$moveflag = 'REFLECTED';

                        if($def[1]['status'] === 4) {

                            if(method_exists('MoveDb', $movefunc))

                                MoveDb::$movefunc();

                        } else {

                            //AbilityDb::$abifunc();
                        }

                        self::$tempsaver[0] = $def;
                        self::$tempsaver[1] = $defmove;
                        $def                = $atk;
                        $defmove            = $atkmove;
                        $abifunc            = '__' . $atk[0]['abi'];

                    }

                    /*
                        The effect of Roar and Whirlwind currently disabled
                        wait for the future determination on variable changes caused by Magic Coat and Magic Bounce
                    */

                    /*
                        Because Magic Bounce is equivalent to Magic Coat so no need to repeat in ability database
                    */

                    # 蓄电、贮水、干燥皮肤、吸盘、奇异守护、避雷针、电力引擎、引水、食草、钝感、防音、黏着、超感知觉、引火、魔法反射

                    //if(in_array($def[0]['abi'], array('10', '11', '87', '21', '25', '31', '78', '114', '157', '12', '43', '60', '140', '18'))) {

                    //AbilityDb::$abifunc();

                    //goto MOVEFAILED;

                    //}


                    // actually I can calculate this in move database too

                    /*
                        calculate move's type and then obtain the modifier
                        if there is a modifier which greater than 0, mark $movehit as TRUE
                    */

                    /*
                    if($atkmove['mid'] == '546' && in_array($atk[0]['crritem'], array('海洋卡带', '雷电卡带', '火焰卡带', '冰冻卡带'))) {

                        $discntype = array('海洋卡带' => 2, '雷电卡带' => 4, '火焰卡带' => 1, '冰冻卡带' => 13);



                    }*/

                    /*
                        Check if the move is effective against the opposite
                        also appends different restriction alert to the report
                        if the move is no effect at all, straight jump to the miss process
                    */

                    $movehit = FALSE;

                    if(in_array('99', [$atk[0]['abi'], $def[0]['abi']]) ||    # 无防御
                        $def[1][2][4] && $atk[0]['pid'] == $def[1][2][4] ||        # 锁定、心眼
                        //$atkmove['btlefct']{14} !== '1' && $def[1][2][39] ||                                                # 念动力 @ marked: btlefct
                        //$atk[0]['crritem'] === '神秘果' && $atk[0]['hpper'] < $atk[0]['hpeat'] && self::DropItem($atk) ||    # 神秘果 @ marked: DropItem
                        $atkmove['acc'] > 100 ||                                                                            # 必中技能
                        in_array($atkmove['mid'], ['89', '90', '222'], TRUE) && $def[1][2][42] === 3 ||                        # 地震、地裂震级变化
                        in_array($atkmove['mid'], ['57', '250'], TRUE) && $def[1][2][42] === 2 ||                            # 冲浪、漩涡
                        in_array($atkmove['mid'], ['16', '89', '239', '327', '479', '542'], TRUE) && $def[1][2][42] === 1 || # 起风、雷电、龙卷风、升空拳、击坠、暴风
                        self::$field['weather']{0} === '2' && in_array($atkmove['mid'], ['87', '542'], TRUE) ||                # 雨天、雷电、暴风
                        self::$field['weather']{0} === '4' && $atkmove['mid'] === '59'
                    ) {                                        # 冰雹、暴风雪

                        $movehit = TRUE;

                    } elseif($def[1][2][42]) {

                        // do nothing

                    } elseif($atkmove['btlefct']{14} === '1') { # OHKO

                        if($atk[0]['level'] >= $def[0]['level'] && rand(1, 100) <= 30 + $atk[0]['level'] - $def[0]['level']) {

                            $movehit = TRUE;

                        } elseif($atk[0]['level'] < $def[0]['level']) {

                            self::$report .= '可惜没有效果！<br>';

                            goto BATTLE;

                        }

                    } else {

                        $accmod  = 1;                                    # accuracy modifier
                        $accneva = [$atk[1][1][5], $def[1][1][6]];    # accuracy and evasive for pokemon

                        if(self::$field['weather']{0} === '3' && $def[0]['abi'] === '8' ||
                            self::$field['weather']{0} === '4' && $def[0]['abi'] === '81'
                        ) $accmod *= 0.8;        # 沙暴+沙隐、冰雹+雪隐
                        elseif(self::$field['weather']{0} === '5') $accmod *= 0.6;        # 雾

                        if($atk[0]['abi'] === '14') $accmod *= 1.3;        # 复眼
                        elseif($atk[0]['abi'] === '55' && $atkmove['class'] == '1') $accmod *= 0.8;     # 紧张
                        elseif($atk[0]['abi'] === '162') $accmod *= 1.1;        # 胜利之星
                        elseif($atk[0]['abi'] === '109') $accneva[1] = 0;    # 天然

                        if($def[0]['abi'] === '77' && $def[1][2][0] !== FALSE) $accmod *= 0.8;        # 蹒跚
                        elseif($def[0]['abi'] === '109') $accneva[0] = 0;    # 天然

                        if(!in_array(0, [$atk[1][2][40], $atk[1][2][41]], TRUE) && $accneva[1] > 0) $accneva[1] = 0;    # 识破、嗅觉、奇迹之眼

                        if($atk[0]['crritem'] === '77') $accmod *= 1.1;        # 广角镜
                        elseif($atk[0]['crritem'] === '88' && self::$order{1} === '1') $accmod *= 1.2;        # 放大镜
                        elseif(in_array($def[0]['crritem'], ['39', '72'], TRUE)) $accmod *= 0.9;        # 光粉、舒畅之香

                        if(self::$field['gravity'] == '1') $accmod *= 5 / 3;    # 重力

                        if(self::$field['weather']{0} === '1' && in_array($atkmove['mid'], ['87', '542'], TRUE))            # 晴天、雷电、暴风

                            $atkmove['acc'] = 50;

                        $acclevel    = max(-6, min(6, $accneva[0] - $accneva[1]));
                        $acclevelarr = [33, 36, 43, 50, 60, 75, 100, 133, 166, 200, 250, 266, 300];
                        $accuracy    = floor($atkmove['acc'] * $acclevelarr[$acclevel + 6] / 100 * $accmod);

                        if(rand(1, 100) <= $accuracy) $movehit = TRUE;

                    }

                    if($movehit === FALSE) {

                        if(!($typemod === 0))

                            self::$report .= '技能没有命中。<br>';

                        MOVEFAILED:

                        if(in_array($atkmove['mid'], ['26', '136'], TRUE) && $atk[0]['abi'] != '98') # 飞踢、飞膝踢、魔法守护

                            self::__MoveMissRecoil();

                        // actually I can write this in move database to prevent multiple call and easy to control
                        // elseif(in_array($atkmove['mid'], array('120', '153')))    self::__MoveExplosion();    # 自爆、大爆炸

                        //self::EndTurn($atk, $atkmove);

                        goto BATTLE;

                    }

                    unset($accuracy, $acclevel, $acclevelarr, $accneva, $accmod);

                }

                if($typemod > 1) {

                    self::$report .= '效果拔群！<br>';

                } elseif($typemod < 1 && $typemod > 0) {

                    self::$report .= '不是很有效……<br>';

                }

                if($atkmove['btlefct']{14} === '1')

                    self::__MoveOHKO();

                if(method_exists('MoveDb', $movefunc))

                    MoveDb::$movefunc();

                if($atkmove['class'] === '0')

                    goto REPORTMAKE;

                if(!empty($atkmove['freq'])) {

                    if($atkmove['freq'] === '25') {

                        $rand            = rand(0, 3);
                        $atkmove['freq'] = ($rand === 0) ? 2 : (($rand === 1) ? 3 : rand(0, 3) + 2);

                    } else {

                        $atkmove['freq'] = $atkmove['freq']{0};

                    }

                    $moveused = 1;

                }

                MOVEUSE: {

                    $damage = (self::$stabledamage !== 0) ? self::$stabledamage : min(self::ObtainDamage($typemod), $def[0]['hp']);

                    self::$report .= $atk[0]['name'] . '对' . $def[0]['name'] . '造成了' . $damage . '点伤害。<br>';

                    if($def[1][8] > 0) { # 替身

                        if($def[0]['crritem'] === '140')

                            self::$report .= '气球爆了！<br>';

                        if(($def[1][8] = max(0, $def[1][8] - $damage)) === 0)

                            self::$report .= $def[0]['name'] . '的替身没了！';

                    } else {

                        // 气息腰带、气息头巾、忍耐

                        if(($def[0]['crritem'] === '87' && $def[0]['maxhp'] == $def[0]['hp'] ||
                                $def[0]['crritem'] === '50' && rand(0, 99) < 10 ||
                                $def[1]['insstatus'] === 3) &&
                            $damage >= $def[0]['hp']
                        ) {

                            $def[0]['hp'] = 1;
                            self::$report .= $def[0]['name'] . '承受住了攻击！';

                            if(in_array($def[0]['crritem'], ['87', '50'], TRUE))

                                self::Item($def, 'DROP');

                        } else {

                            $def[0]['hp'] = max(0, $def[0]['hp'] - $damage);

                        }

                    }


                    if($atk[1][2][43] - 1 === 0 && in_array($atkmove['mid'], ['37', '80', '200'])) { // Thrash, Petal Dance, Outrage

                        self::$report .= $atk[0]['name'] . '的头有点晕...';

                        $atk[1][2][43] = FALSE;

                        self::AlterSubStatus($atk, 'CFS');

                    }

                    if($def[0]['hp'] === 0) {

                        self::$report .= $def[0]['name'] . '倒下了！<br>';

                        goto BATTLEEND;

                    }

                    if(self::$atkmove['btlefct']{16} === '1' &&
                        self::$m['recoilper'] &&
                        !in_array(self::$atk[0]['abi'], ['69', '98'], TRUE)
                    )

                        self::__MoveRecoil($damage); // 技能反伤

                    if(!empty($atkmove['freq'])) self::$report .= $moveused . '连击！<br>';

                    if($atk[0]['hp'] === 0) {

                        self::$report .= $def[0]['name'] . '倒下了！<br>';

                        goto BATTLEEND;

                    }

                    if(!empty($atkmove['freq']) && $moveused < $atkmove['freq']) {

                        ++$moveused;

                        goto MOVEUSE;

                    }


                }


                REPORTMAKE: {

                    if(!empty(self::$reportcur)) {

                        self::$report .= implode('<br>', self::$reportcur) . '<br>';

                        self::$reportcur = [];

                    }

                }


                if(!self::$moveflag === 'REFLECTED') {

                    self::$def       = self::$tempsaver[0];
                    self::$defmove   = self::$tempsaver[1];
                    self::$tempsaver = NULL;

                }

            }

            goto BATTLE;

        }

        unset(self::$atk, self::$def, $atk, $def);

        TAIL:

        for($i = 0; $i < 2; $i++) {

            $atkkey = !isset($defkey) ? strpos(self::$order, '1') : $defkey;
            $defkey = $atkkey ^ 1;
            $atk    = &self::$pokemon[$atkkey];
            $def    = &self::$pokemon[$defkey];

            self::$atk = &self::$pokemon[$atkkey];
            self::$def = &self::$pokemon[$defkey];


            if($atk[1][2][3] !== FALSE) { # 状态 - 束缚

                $tmp = substr($atk[1][2][3], -1, 1);

                if($tmp === '0') {

                    self::$reportend[] = $atk[0]['name'] . '摆脱了束缚！';
                    $atk[1][2][3]      = FALSE;

                } else {

                    self::$reportend[] = $atk[0]['name'] . self::$langtrapped[floor($atk[1][2][3] / 10)];

                    if($atk[0]['abi'] !== '98')

                        $atk[0]['hp'] = max(0, $atk[0]['hp'] - floor($def[0]['maxhp'] / (($def[0]['crritem'] === '143') ? 8 : 16)));

                    if(self::SelfExamine()) continue;

                }

            }

            if($atk[1][2][16] === 0) {    # 状态 - 残废

                $atk[1][2][16]     = FALSE;
                self::$reportcur[] = $atk[0]['name'] . '行动自如了！';

            } elseif($atk[1][2][16]) {

                --$atk[1][2][16];

            }

            ($atk[1][11] !== FALSE) && --$atk[1][11]; # 状态 - 睡眠

        }

        if(!empty(self::$reportend))

            self::$report .= implode('<br>', self::$reportend) . '<br>';

        self::End();

    }

    public static function ObtainStat(&$atk, $def, $atkmove, $defmove, $specific = []) {

        $atk[0]          = array_merge($atk[0], Obtain::Stat($atk[0]['level'], $atk[0]['bs'], $atk[0]['iv'], $atk[0]['ev'], $atk[0]['nature'], $atk[0]['hp']));
        $atk[0]['hpeat'] = ($atk[0]['abi'] === '82') ? 50 : 25;

        if($atk[0]['abi'] != '109') {

            $atk[0]['atk'] *= ($atk[1][1][0] > 0 ? 2 + $atk[1][1][0] : 2) / ($atk[1][1][0] < 0 ? 2 + abs($atk[1][1][0]) : 2);
            $atk[0]['def'] *= ($atk[1][1][1] > 0 ? 2 + $atk[1][1][1] : 2) / ($atk[1][1][1] < 0 ? 2 + abs($atk[1][1][1]) : 2);
            $atk[0]['spatk'] *= ($atk[1][1][2] > 0 ? 2 + $atk[1][1][2] : 2) / ($atk[1][1][2] < 0 ? 2 + abs($atk[1][1][2]) : 2);
            $atk[0]['spdef'] *= ($atk[1][1][3] > 0 ? 2 + $atk[1][1][3] : 2) / ($atk[1][1][3] < 0 ? 2 + abs($atk[1][1][3]) : 2);
            $atk[0]['spd'] *= ($atk[1][1][4] > 0 ? 2 + $atk[1][1][4] : 2) / ($atk[1][1][4] < 0 ? 2 + abs($atk[1][1][4]) : 2);

        }

        if(empty($atkmove))

            $atkmove['type'] = '0';

        if($def[0]['abi'] === '47' && !empty($atkmove['class']) && in_array($atkmove['type'], ['1', '13']) || $atk[0]['abi'] === '129' && $atk[0]['hpper'] < 50) { # 厚脂肪、懦弱

            $atk[0]['atk'] *= 0.5;
            $atk[0]['spatk'] *= 0.5;

        } elseif(($atk[0]['abi'] === '65' && $atkmove['type'] == '3' ||
                $atk[0]['abi'] === '66' && $atkmove['type'] == '1' ||
                $atk[0]['abi'] === '67' && $atkmove['type'] == '2' ||
                $atk[0]['abi'] === '68' && $atkmove['type'] == '8') && $atk[0]['hpper'] < 33
        ) { # 深绿、猛火、激流、虫之预感

            $atk[0]['atk'] *= 1.5;
            $atk[0]['spatk'] *= 1.5;

        } elseif($atk[0]['abi'] === '62' && !empty($atk[0]['status'])) { # 根性

            $atk[0]['atk'] *= 1.5;

        } elseif(in_array($atk[0]['abi'], ['37', '74'])) { # 大力士、瑜伽之力

            $atk[0]['atk'] *= 2;

        } elseif($atk[0]['abi'] === '94' && self::$field['weather']{0} === '1') { # 太阳力量

            $atk[0]['spatk'] *= 1.5;

        } elseif($atk[0]['abi'] === '55') { # 紧张

            $atk[0]['atk'] *= 1.5;
            // $atk[1]['evamul']    *= 0.8;

        } elseif($atk[0]['abi'] === '112' && $atk[1][2][45]) { # 缓慢启动

            $atk[0]['atk'] *= 0.5;
            $atk[0]['spd'] *= 0.5;

        } elseif($atk[0]['abi'] === '122' && self::$field['weather']{0} === '1') { # 花之礼物

            $atk[0]['atk'] *= 1.5;
            $atk[0]['spdef'] *= 1.5;

        } elseif($atk[0]['abi'] === '63' && !empty($atk[0]['status'])) { # 神秘鳞片

            $atk[0]['def'] *= 1.5;

        } elseif($atk[0]['abi'] === '34' && self::$field['weather']{0} === '1' || $atk[0]['abi'] === '33' && self::$field['weather']{0} === '2' || $atk[0]['abi'] === '146' && self::$field['weather']{0} === '3') { # 叶绿素、轻快、挖沙

            $atk[0]['spd'] *= 2;

        }

        if($atk[0]['crritem'] === '75' && in_array($atk[0]['id'], ['104', '105'])) { # 可拉可拉、嘎啦嘎啦

            $atk[0]['atk'] *= 2;

        } elseif($atk[0]['crritem'] === '78' && $atk[0]['id'] === '366') { #珍珠贝

            $atk[0]['spatk'] *= 2;

        } elseif($atk[0]['crritem'] === '79' && $atk[0]['id'] == '366') { # 珍珠贝

            $atk[0]['spdef'] *= 1.5;

        } elseif($atk[0]['crritem'] === '54' && $atk[0]['id'] === '25') { # 皮卡丘

            $atk[0]['atk'] *= 2;
            $atk[0]['spatk'] *= 2;

        } elseif($atk[0]['crritem'] === '47' && in_array($atk[0]['id'], ['380', '381'])) { # 拉帝亚斯、拉帝欧斯

            $atk[0]['spatk'] *= 1.5;
            $atk[0]['spdef'] *= 1.5;

        } elseif($atk[0]['crritem'] === '74' && $atk[0]['id'] == '132') { # 金属粉末（百变怪）

            $atk[0]['def'] *= 1.5;

        } elseif($atk[0]['crritem'] === '86' && $atk[0]['id'] == '132') { # 速度粉末（百变怪）

            $atk[0]['spd'] *= 2;

        } elseif($atk[0]['crritem'] === '137' && !empty($atk[0]['evldata'])) { # 进化辉石

            $atk[0]['def'] *= 1.5;
            $atk[0]['spdef'] *= 1.5;

        } elseif($atk[0]['crritem'] === '44') { # 专爱头巾

            $atk[0]['atk'] *= 1.5;

        } elseif($atk[0]['crritem'] === '109') { # 专爱眼镜

            $atk[0]['spatk'] *= 1.5;

        } elseif($atk[0]['crritem'] === '99') { # 专爱围巾

            $atk[0]['spd'] *= 1.5;

        } elseif(in_array($atk[0]['crritem'], ['41', '101', '102', '103', '104', '105', '106'], TRUE)) { # 矫正背心、力量负重、力量护腕、力量腰带、力量镜片、力量束带、力量护踝

            $atk[0]['spd'] *= 0.5;

        }

        if($atk[0]['status'] === '3' && $atk[0]['abi'] != '95') { # 非早足麻痹

            $atk[0]['spd'] *= 0.25;

        } elseif($atk[0]['status'] === '3' && $atk[0]['abi'] === '95') { # 早足

            $atk[0]['spd'] *= 1.5;

        }

        if($atk[1][2][35] && $atkmove['type'] == '1') { # 引火

            $atk[0]['spatk'] *= 1.5;

        }

        if($atk[1][2][38]) { # 顺风

            $atk[0]['spd'] *= 2;

        }

        if(self::$field['weather']{0} === '3' && in_array('10', [$atk[0]['type'], $atk[0]['typeb']])) { # 沙暴

            $atk[0]['spdef'] *= 1.5;

        }

        $atk[0]['atk']   = floor($atk[0]['atk']);
        $atk[0]['def']   = floor($atk[0]['def']);
        $atk[0]['spatk'] = floor($atk[0]['spatk']);
        $atk[0]['spdef'] = floor($atk[0]['spdef']);
        $atk[0]['spd']   = floor($atk[0]['spd']);

        return TRUE;
    }

    public static function ObtainAttackOrder() {

        /*
            Initialization for the same-speed random order selection
            because the default value of variable order is 01
            which means that we have the priority but not opposition
            variable ospeed contains the boolean value for if opposites speed is greater than self
            then determine change it the order to 10 or not
        */

        $spddiff   = self::$pokemon[0][0]['spd'] - self::$pokemon[1][0]['spd'];
        $randorder = (rand(0, 1) === 0) ? '10' : '01';

        if(in_array($_GET['process'], ['useitem', 'swappm'])) { # 捕获、换人

            return TRUE;

        } elseif($_GET['process'] === 'swappm' && self::$move[0]['mid'] == '228') {

            # MOVE ACTIVATION - 追击

            self::$move[$key]['prio'] = 7;

        } elseif($_GET['process'] === 'swappm') {

            /*
                To switch to another pokemon
                Which makes the priority 6
            */

            self::$move[$key]['prio'] = 6;

        }

        # ABILITY ACTIVATION - 恶作剧之心

        for($i = 0; $i < 2; $i++) {

            if(self::$pokemon[$i][0]['abi'] === '158' && self::$move[$i]['class'] === '0')

                ++self::$move[$i]['prio'];

        }

        if(self::$move[0]['prio'] > self::$move[1]['prio']) {

            self::$order = '10';

        } elseif(self::$move[0]['prio'] == self::$move[1]['prio']) {

            /*
                The class properties movefirst and movelast are a fixed priority state
                It determine the order of the invasion first or last straight away
                But if they are the same, follow the conditions
            */

            foreach(self::$pokemon as $key => $val) {

                if($val[0]['crritem'] === '42' && rand(1, 100) <= 20) { # 先制之爪

                    self::$movefirst{$key} = '1';

                } elseif($val[0]['crritem'] === '番荔果' && $val[0]['hpper'] < $val[0]['hpeat']) { # 番荔果

                    self::$movefirst{$key} = '1';

                    self::MakeReport($val[0]['name'] . '发动了' . self::$item[$val[0]['crritem']] . '！', $val[0]['id'], 'BERRY1');

                } elseif(in_array($val[0]['crritem'], ['91', '128'], TRUE) || $val[0]['abi'] === '100') { # 后攻尾巴 满腹之香

                    self::$movelast{$key} = '1';

                }

                if($key > 0) break;

            }

            /*
                If two pokemon activate first / last element at the same time
                also do some same speed calculation if they have the same speed
            */

            $tmp = [
                strpos('1', self::$movefirst),
                strpos('1', self::$movelast)
            ];

            if(!in_array(FALSE, $tmp)) {

                if(self::$movefirst === '11')

                    self::$order = ($spddiff > 0) ? '10' : (($spddiff === 0) ? $randorder : '01');

                elseif(self::$movelast === '11')

                    self::$order = ($spddiff < 0) ? '10' : (($spddiff === 0) ? $randorder : '01');

                elseif($tmp[0] == '0')

                    self::$order = '10';

                elseif($tmp[1] == '0')

                    self::$order = '01';

                return TRUE;

            }

            # MOVE ACTIVATION - 欺骗空间

            if(self::$field['trkroom'] !== '0') {

                self::$order = ($spddiff < 0) ? '10' : (($spddiff === 0) ? $randorder : '01');

                return TRUE;

            }

            self::$order = ($spddiff > 0) ? '10' : (($spddiff === 0) ? $randorder : '01');


        }

        return TRUE;

    }

    public static function UseItem() {

        global $user;

        $iid = isset($_GET['iid']) ? intval($_GET['iid']) : 0;

        if($iid === 0) goto ITEMFAILED;

        $item = DB::fetch_first('SELECT mi.iid, mi.num, i.name, i.effect, i.btlefct, i.usable, i.type FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON mi.iid = i.iid WHERE mi.iid = ' . $iid . ' AND mi.uid = ' . $trainer['uid']);

        if(empty($item) || $item['num'] <= 0 || $item['effect'] === '' && $item['btlefct'] === '' && $item['type'] !== '1')

            goto ITEMFAILED;

        self::$report .= $GLOBALS['_G']['username'] . '使用了' . $item['name'] . '！<br>';

        if($item['type'] === '1') {

            $success = self::CatchPokemon($item['iid']);
            $pokemon = &self::$pokemon[0][0];

        } else {

            $effect  = [];
            $pokemon = &self::$pokemon[1][0];

            if(!empty($item['effect']))

                foreach(explode('|', $item['effect']) as $val) {

                    $tmp             = explode(':', $val);
                    $effect[$tmp[0]] = $tmp[1];

                }

            if($pokemon['hp'] <= 0 && (!empty($effect['hp']) || !empty($effect['status'])))

                goto ITEMFAILED;

            else {

                if($item['type'] === '4') {

                    $success     = FALSE;
                    $effectcount = 0;

                    foreach($effect as $key => $val) {

                        switch($key) {

                            default:

                                continue;

                                break;

                            case 'hp':

                                if($pokemon['hp'] == $pokemon['maxhp'])

                                    continue;

                                $pokemon['hp'] += min((substr($val, -1, 1) === '%') ? floor($pokemon['maxhp'] * $val / 100) : $val, $pokemon['maxhp'] - $pokemon['hp']);
                                $success = TRUE;

                                ++$effectcount;

                                break;

                            case 'status':

                                if($pokemon['status'] === '0' || $val !== 'all' && $pokemon['status'] == $val)

                                    break;

                                $pokemon['status'] = 0;

                                ++$effectcount;

                                break;

                            case 'sp':

                                if(Kit::Library('db', ['item']) !== FALSE && ($tmp = new ItemDb()) && method_exists($tmp, '__' . $iid)) {

                                    ItemDb::$pokemon = &$pokemon;
                                    call_user_func(['ItemDb', '__' . $iid]);

                                    if(!empty(ItemDb::$message))

                                        self::$report .= ItemDb::$message . '<br>';

                                }

                                break;
                        }
                    }

                    if($effectcount > 0)

                        $success = TRUE;

                }

            }

        }

        if($success === TRUE || $item['type'] === '1') {

            Trainer::Item('DROP', $trainer['uid'], $iid, 1, $item['num']);

            if($item['type'] === '4')

                DB::query('UPDATE pkm_mypkm SET hp = ' . $pokemon['hp'] . ', status = ' . $pokemon['status'] . ' WHERE pid = ' . $pokemon['pid']);

            if($item['type'] !== '1')

                self::$report .= '对' . $pokemon['name'] . '使用' . $item['name'] . '成功！<br>';

        } elseif($item['type'] !== '1') {

            ITEMFAILED: {

                self::$report .= '这个道具没什么效果……<br>';

            }

        }

    }

    public static function CatchPokemon($iid) {

        global $user;

        $mod  = $smod = 1;
        $heal = $caught = FALSE;

        $catchrate = DB::result_first('SELECT catchrt FROM pkm_pkmdata WHERE id = ' . self::$pokemon[0][0]['id']);

        switch($iid) {
            case '2':
                $mod = 1.5;
                break;    # 超级球
            case '3':
                $mod = 2;
                break;    # 特级球
            case '4':
                $mod = 100;
                break;    # 大师球
            case '6':                        # 等级球

                if(self::$pokemon[1][0]['level'] > self::$pokemon[0][0]['level'] * 4) $mod = 8;
                elseif(self::$pokemon[1][0]['level'] > self::$pokemon[0][0]['level'] * 2) $mod = 4;
                elseif(self::$pokemon[1][0]['level'] > self::$pokemon[0][0]['level']) $mod = 2;

                break;
            case '7':                        # 月亮球

                if(unserialize(self::$pokemon[0][0]['evldata'])[5] === '31') $mod = 4;

                break;
            case '10':                        # 爱心球

                $chain = [];
                $query = DB::query('SELECT id FROM pkm_pkmextra WHERE devolve = ' . self::$pokemon[0][0]['id']);

                while($info = DB::fetch($query)) {

                    $chain[] = $info['id'];

                }

                if((!empty($inchain) && in_array(self::$pokemon[1][0]['id'], $inchain, TRUE) ||
                        self::$pokemon[0][0]['id'] === self::$pokemon[1][0]['id']) &&
                    self::$pokemon[0][0]['gender'] != self::$pokemon[1][0]['gender']
                )

                    $mod = 8;

                break;
            case '11':                        # 速度球

                if(explode(',', self::$pokemon[0][0]['bs'])[5] > 99) $mod = 4;

                break;
            case '12':                        # 重量球

                $weight = self::$pokemon[0][0]['weight'] / 10;

                if($weight > 400) $catchrate += 40;
                elseif($weight > 300 && $weight < 401) $catchrate += 30;
                elseif($weight > 200 && $weight < 301) $catchrate += 20;
                elseif($weight < 100) $catchrate = max(3, $catchrate - 20);

                break;
            case '13':
                $mod = 1.5;
                break;    # 运动球
            case '14':                        # 网球

                if(in_array(self::$pokemon[0][0]['type'], ['2', '8']) ||
                    in_array(self::$pokemon[0][0]['typeb'], ['2', '8'])
                )

                    $mod = 3;

                break;
            case '16':                        # 巢球

                if(self::$pokemon[0][0]['level'] < 30) $mod = (40 - self::$pokemon[0][0]['level']) / 10;

                break;
            case '18':
                $mod = min(4, 1 + self::$field['turn'] * 0.3);
                break; # 时间球
            case '21':
                $mod = 1;
                break; # 黑暗球
            case '23':
                $mod = 1;
                break; # 快速球

        }

        if(in_array((string)self::$pokemon[0][0]['status'], ['1', '3', '5', '6'], TRUE))

            $smod = 1.5;

        elseif(in_array((string)self::$pokemon[0][0]['status'], ['2', '4'], TRUE))

            $smod = 2;

        $diff = self::$pokemon[0][0]['level'] - self::$pokemon[1][0]['level'];

        $lmod = min(($diff > 0) ? 1 - $diff / 100 : 1 + $diff / 100, 1.05);

        $x = (3 * self::$pokemon[0][0]['maxhp'] - 2 * self::$pokemon[0][0]['hp']) * $catchrate * (($catchrate < 10) ? 0.5 : 1) * $mod * $smod * $lmod / 3 / self::$pokemon[0][0]['maxhp'];

        if($x >= 255) {

            $caught = TRUE;

        } else {

            $caught = TRUE;
            $y      = 65535 / pow(255 / $x, 0.25);

            for($i = 0; $i < 3; $i++) {

                if(rand(0, 65535) >= $y) {

                    $caught = FALSE;

                    break;

                }

            }

        }

        if($caught === FALSE) {

            self::$report .= self::$pokemon[0][0]['name'] . '从球里逃了出来！<br>';

            return FALSE;

        }

        $place = Obtain::DepositBox($trainer['uid']);

        if($place === FALSE) return FALSE;

        $info = [
            'id'        => self::$pokemon[0][0]['id'],
            'nickname'  => '\'' . self::$pokemon[0][0]['name'] . '\'',
            'gender'    => self::$pokemon[0][0]['gender'],
            'pv'        => '\'' . self::$pokemon[0][0]['pv'] . '\'',
            'iv'        => '\'' . self::$pokemon[0][0]['iv'] . '\'',
            'ev'        => '\'' . self::$pokemon[0][0]['ev'] . '\'',
            'shiny'     => self::$pokemon[0][0]['shiny'],
            'originuid' => $trainer['uid'],
            'nature'    => self::$pokemon[0][0]['nature'],
            'level'     => self::$pokemon[0][0]['level'],
            'exp'       => self::$pokemon[0][0]['exp'],
            'crritem'   => self::$pokemon[0][0]['crritem'],
            'hpns'      => ($iid == '9') ? 200 : self::$pokemon[0][0]['hpns'],
            'move'      => '\'' . serialize(self::$pokemon[0][0]['move']) . '\'',
            'mtlevel'   => self::$pokemon[0][0]['level'],
            'mtdate'    => $_SERVER['REQUEST_TIME'],
            'mtplace'   => self::$pokemon[0][0]['mtplace'],
            'abi'       => self::$pokemon[0][0]['abi'],
            'uid'       => $trainer['uid'],
            'capitem'   => $iid,
            'hp'        => self::$pokemon[0][0][($iid == '22' || $place > 6) ? 'maxhp' : 'hp'],
            'place'     => $place,
            'status'    => ($iid == '22' || $place > 6) ? 0 : self::$pokemon[0][0]['status'],
            'imgname'   => '\'' . self::$pokemon[0][0]['imgname'] . '\''
        ];

        self::$isend = TRUE;
        self::$report .= '捕获成功！<br>';

        DB::query('INSERT INTO pkm_mypkm (' . implode(',', array_keys($info)) . ') VALUES (' . implode(',', array_values($info)) . ')');

        if(in_array(Pokemon::Register($info['id'], TRUE), ['0', FALSE], TRUE))

            self::$report .= self::$pokemon[0][0]['name'] . '的信息记录在了图鉴中。<br>';

        ++$trainer['addexp'];

        return TRUE;

    }

    public static function ReorderPokemon($focus = 0) {

        if(!empty($focus) && self::$pokemon[1][0]['pid'] == $focus) {

            self::$report .= self::$pokemon[1][0]['name'] . '正在战斗呢！<br>';

            return FALSE;

        } elseif(!empty($focus) && self::$atk[1][2][3] && self::$atk[0]['crritem'] !== '107') { # 束缚、漂亮脱壳

            self::$report .= self::$pokemon[1][0]['name'] . '被束缚住了无法交换！<br>';

            return FALSE;

        }

        $pokemon = [];

        ksort(self::$pokemon);

        foreach(self::$pokemon as $key => $val) {

            if($key > 0 && $key < 7) {

                $pokemon[] = $val;

            }

        }

        $checked = FALSE;

        foreach($pokemon as $key => $val) {

            if($focus === 0 && $val[0]['hp'] < 1 && (empty($pokemon[$key - 1]) || $pokemon[$key - 1][0]['hp'] < 1)) {

                $checked = TRUE;

            } elseif(!empty($focus) && $focus == $val[0]['pid']) {

                $checked = TRUE;

                self::$report .= '就决定是你了！' . $val[0]['name'] . '！<br>';

                unset($pokemon[$key]);

                array_unshift($pokemon, $val);

                break;

            } elseif($checked) {

                unset($pokemon[$key]);

                array_unshift($pokemon, $val);

                break;

            }

        }

        if(!empty($focus) && $checked === FALSE)

            self::$report .= '这是什么精灵？<br>';

        array_unshift($pokemon, 0);

        unset($pokemon[0]);

        $pokemon[1][1][4] = 1;

        self::$pokemon = array_replace(self::$pokemon, $pokemon);

        ksort(self::$pokemon);

        return $checked;

    }

    public static function ObtainRestrictModifier($atktype, $deftype, $deftypeb) {

        $modifier = 1;
        $atktype  = intval($atktype);
        $deftypes = [intval($deftype), intval($deftypeb)];

        foreach($deftypes as $val) {

            if($val === 0) continue;

            $arr = [];

            switch($atktype) {
                case 1:
                    $arr = [[3, 8, 12, 13], [1, 2, 10, 17], []];
                    break;
                case 2:
                    $arr = [[1, 10, 11], [2, 3, 17], []];
                    break;
                case 3:
                    $arr = [[2, 10, 11], [1, 2, 7, 8, 9, 12, 17], []];
                    break;
                case 4:
                    $arr = [[2, 7], [3, 4], [11]];
                    break;
                case 5:
                    $arr = [[], [10, 12], [16]];
                    break;
                case 6:
                    $arr = [[5, 10, 12, 13, 15], [7, 8, 9, 14, 18], [16]];
                    break;
                case 7:
                    $arr = [[3, 6, 8], [4, 10, 12], []];
                    break;
                case 8:
                    $arr = [[3, 14, 15], [1, 6, 7, 9, 12, 16, 18], []];
                    break;
                case 9:
                    $arr = [[3, 18], [9, 10, 11, 16], [12]];
                    break;
                case 10:
                    $arr = [[1, 7, 8, 13], [6, 11, 12], []];
                    break;
                case 11:
                    $arr = [[1, 4, 9, 10, 12], [3, 8], [7]];
                    break;
                case 12:
                    $arr = [[10, 13, 18], [1, 2, 4, 12], []];
                    break;
                case 13:
                    $arr = [[3, 7, 11, 17], [1, 2, 13, 12], []];
                    break;
                case 14:
                    $arr = [[6, 9], [12, 14], [15]];
                    break;
                case 15:
                    $arr = [[14, 16], [6, 15, 18], []];
                    break;
                case 16:
                    $arr = [[14, 16], [15], [5]];
                    break;
                case 17:
                    $arr = [[17], [12], [18]];
                    break;
                case 18:
                    $arr = [[6, 15, 17], [1, 9, 12], []];
                    break;
            }

            $modifier *= in_array($val, $arr[0]) ? 2 : (in_array($val, $arr[1]) ? 0.5 : (in_array($val, $arr[2]) ? 0 : 1));

        }

        return $modifier;

    }

    public static function __MoveCurrentPlace(&$subject, $location) {

        $subject[1][2][42] = $location;

    }

    public static function __MoveMissRecoil() {

        self::$atk[0]['hp'] = max(0, self::$atk[0]['hp'] - floor(self::$atk[0]['maxhp'] / 2));
        self::$report .= self::$atk[0]['name'] . '击中了地面，碎石飞溅！<br>';

    }

    public static function __MoveOHKO() {

        if(self::$def[0]['abi'] === '5') {

            self::$report .= self::$def[0]['abi'] . '坚如磐石，无法被击倒！<br>';

            return FALSE;

        }

        self::$stabledamage = self::$def[0]['hp'];
        self::$report .= '一击必杀！<br>';

    }

    public static function ObtainDamage($typemod) {

        $isct    = FALSE;
        $ctlevel = intval(self::$atkmove['ctrate']);

        if(!in_array(self::$def[0]['abi'], ['4', '75']) || # 战斗盔甲、贝壳盔甲
            self::$deffield{8} === '0'
        ) {

            if($ctlevel === 6) {

                $isct = TRUE;

                goto DAMAGECAL;

            }

            if(self::$atk[1][2][27]) $ctlevel += 2;
            if(self::$atk[0]['abi'] === '105') $ctlevel += 1; # 强运

            if(in_array(self::$atk[0]['crritem'], ['51', '131'])) # 聚焦镜、锋锐之爪

                $ctlevel += 1;

            elseif(self::$atk[0]['crritem'] === '73' && self::$atk[0]['id'] == '113' || self::$atk[0]['crritem'] === '76' && self::$atk[0]['id'] == '83') # 幸运拳套（幸福蛋）、长葱（大葱鸭）

                $ctlevel += 2;

            if($ctlevel > 4) $ctlevel = 4;

            $tmp  = [16, 8, 4, 3, 2];
            $isct = rand(1, 100) <= 100 / $tmp[$ctlevel];

        }

        DAMAGECAL: {

            $damage = (((self::$atk[0]['level'] * 2 / 5 + 2) * self::$m['power'] * self::ObtainMoveModifier() * self::$atk[0][self::$atkmove['class'] == '1' ? 'atk' : 'spatk'] / self::$def[0][self::$m['defstat']]) / 50) + 2;

            if(in_array(self::$field['weather']{0}, ['1', '2']) &&
                !(in_array('13', [self::$atk[0]['abi'], self::$def[0]['abi']]) ||    # 无天气
                    in_array('76', [self::$atk[0]['abi'], self::$def[0]['abi']]))        # 天气锁
            ) {

                if(self::$field['weather']{0} == self::$m['type']) $damage *= 1.5;
                elseif(self::$field['weather']{0} == (self::$m['type'] - 1 ^ 1) + 1) $damage *= 0.5;

            }

            if($isct === TRUE) {

                $damage *= 2;

                self::$report .= '命中要害！<br>';

            }

            $damage *= rand(85, 100) / 100 * $typemod;

            if(in_array(self::$m['type'], [self::$atk[0]['type'], self::$atk[0]['typeb']]))                # STAB、适应力

                $damage *= (self::$atk[0]['abi'] === '91') ? 2 : 1.5;

            if(self::$atk[0]['status'] === '1' && self::$atkmove['class'] == '1' && self::$atk[0]['abi'] != '62')    # 非根性烧伤

                $damage *= 0.5;

            if(self::$atkfield{3} > 0 && self::$atkmove['class'] == '1' && $isct === FALSE ||                    # 反射盾
                self::$atkfield{4} > 0 && self::$atkmove['class'] == '2' && $isct === FALSE
            )                    # 光之壁

                $damage *= 0.5;

            if(self::$def[0]['abi'] === '136' && self::$def[0]['hp'] == self::$def[0]['maxhp'])                    # 多重鳞片

                $damage *= 0.5;

            elseif(in_array(self::$def[0]['abi'], ['111', '116']) && $typemod > 1)                            # 过滤器、坚岩

                $damage *= 0.75;

            if(self::$atk[0]['abi'] === '110' && $typemod > 0 && $typemod < 1)                                    # 有色眼镜

                $damage *= 2;

            elseif(self::$atk[0]['abi'] === '97' && $isct === TRUE)                                                # 狙击手

                $damage *= 1.5;

            if(self::$atk[0]['crritem'] === '89' && self::$atk[1][9] > 1)                                        # 节拍器

                $damage *= min(1 + 0.2 * (self::$atk[1][8] - 1), 2);

            elseif(self::$atk[0]['crritem'] === '80' && $typemod > 1)                                        # 达人腰带

                $damage *= 1.2;

            elseif(self::$atk[0]['crritem'] === '82')                                                        # 生命之玉

                $damage *= 1.3;

            // Currently type restraint berries

            if(in_array(self::$atkmove['mid'], ['205', '537'], TRUE) && self::$def[1][2][20] === 1 ||     # 坚硬、坚硬滚动
                in_array(self::$atkmove['mid'], ['89', '222'], TRUE) && self::$def[1][2][42] === 3 ||    # 地震、震级变化
                in_array(self::$atkmove['mid'], ['57', '250'], TRUE) && self::$def[1][2][42] === 2 ||    # 冲浪、漩涡
                in_array(self::$atkmove['mid'], ['16', '239'], TRUE) && self::$def[1][2][42] === 1
            )    # 起风、龙卷风

                $damage *= 2;

        }

        return floor($damage);

    }

    public static function ObtainMoveModifier() {

        $mod = 1;

        if(self::$atk[0]['abi'] === '101' && self::$m['power'] <= 60 ||
            self::$atk[0]['abi'] === '138' && self::$atk[0]['status'] === '1' && self::$atkmove['class'] === '2' ||
            self::$atk[0]['abi'] === '137' && in_array(self::$atk[0]['status'], ['5', '6'], TRUE) && self::$atkmove['class'] === '1'
        ) { # 技师、热暴走、毒暴走

            $mod *= 1.5;

        } elseif(self::$atk[0]['abi'] === '148' && !in_array(self::$atkmove['mid'], ['248', '353']) && self::$order{1} === '1' ||
            self::$atk[0]['abi'] === '159' && in_array(self::$m['type'], ['10', '11', '12']) ||
            self::$atk[0]['abi'] === '125' && self::$atkmove['posefct'] === '1'
        ) { # 分析、沙之力量、全力攻击

            $mod *= 1.3;

        } elseif(self::$atk[0]['abi'] === '79') { # 斗争心

            switch(self::$atk[0]['gender'] + self::$def[0]['gender']) {
                case 3:
                    $mod *= 0.75;
                    break;
                case 2:
                    $mod *= 1.25;
                    break;
            }

        } elseif(self::$atk[0]['abi'] === '120' && self::$atkmove['btlefct']{16} === '1' ||
            self::$atk[0]['abi'] === '89' && self::$atkmove['btlefct']{0} === '1'
        ) { # 舍身、铁拳

            $mod *= 1.2;

        }

        if(self::$def[0]['abi'] === '85' && self::$m['type'] == '1') { # 耐热

            $mod *= 0.5;

        } elseif(self::$def[0]['abi'] === '87' && self::$m['type'] == '1') { # 干燥肌肤

            $mod *= 1.25;

        }

        /*
            如果攻击方携带属性强化道具，且技能是对应属性，威力修正×1.2。
            如果攻击方携带力量头巾，且使用物理技能，威力修正×1.1。
            如果攻击方携带知识眼镜，且使用特殊技能，威力修正×1.1。
            如果攻击方携带怪异之香，且技能是超能属性，威力修正×1.2。
            如果攻击方是携带金刚玉的帝牙卢卡，且技能是钢或龙属性，威力修正×1.2。
            如果攻击方是携带白玉的帕路奇犽，且技能是水或龙属性，威力修正×1.2。
            如果攻击方是携带白金玉的骑拉帝纳，且技能是鬼或龙属性，威力修正×1.2。
            如果此次攻击发动了对应属性宝石，威力修正×1.5。
            如果技能是潮水，且防御方HP≤50%，威力修正×2。
            如果技能是毒液冲击，且防御方处于中毒状态，威力修正×2。
            如果技能是报仇，且攻击方的队伍上回合有精灵濒死，威力修正×2。
            如果技能是交织火焰，且回合内上一个成功使用的技能是交织闪电，威力修正×2，反之亦然。
            如果技能是通过先取发出，威力修正×1.5。
            如果天气是雨天、沙暴或冰雹，且技能是太阳光线，威力修正×0.5。如果场上存在无天气或天气锁特性的精灵，跳过此步骤。
            如果攻击方处于充电状态，且技能是电属性，威力修正×2。
            如果攻击方处于帮手状态，威力修正×1.5。
            如果场上存在玩水状态，且攻击方是火属性，威力修正×0.5。
            如果场上存在玩泥状态，且攻击方是电属性，威力修正×0.5。
        */

        return $mod;

    }

    public static function Item(&$pokemon, $action, $iid = 0) {

        if($action === 'DROP' && !empty($pokemon[0]['crritem'])) {

            $pokemon[1][10]        = $pokemon[0]['crritem'];
            $pokemon[0]['crritem'] = '0';

        } elseif($action === 'OBTAIN' && !empty($iid)) {

            $pokemon[0]['crritem'] = $iid;

        } elseif($action === 'SWAP' && is_array($swappokemon)) {

            $tmp                     = self::$atk[0]['crritem'];
            self::$atk[0]['crritem'] = self::$def[0]['crritem'];
            self::$def[0]['crritem'] = self::$atk[0]['crritem'];

        }

    }

    public static function AlterSubStatus(&$pokemon, $status, $chance = 100, $round = '2-5', $failreport = FALSE) {

        $statusarr = ['CFS' => '混乱'];

        if(/*$pokemon[1][2][$actarr[$status][0]] !== FALSE || */
            $chance >= 0 && rand(0, 100) > $chance ||
            $status === 'CFS' && $pokemon[0]['abi'] === '20' # Confusion - Own Tempo
        ) {

            if($failreport === TRUE) self::FailMove();

            return FALSE;

        } else {

            $actarr = [NULL,
                'CFS' => ['0', '混乱了']
            ];

            $pokemon[1][2][$actarr[$status][0]] = rand($round{0}, substr($round, -1));

        }

        self::$report .= $pokemon[0]['name'] . $actarr[$status][1] . '！';

    }

    /*
        It re-orders pokemon in a specified focus pokemon
        and also place pokemon with hp to the first position
    */

    public static function __MoveRecoil($damage) {

        self::$atk[0]['hp'] = max(0, self::$atk[0]['hp'] - floor($damage * self::$m['recoilper']));
        self::$report .= self::$atk[0]['name'] . '受到了反伤！<br>';

    }

    public static function SelfExamine() {

        if(self::$atk[0]['hp'] < 1) {

            self::$reportend[] = self::$atk[0]['name'] . '倒下了！';

            return TRUE;

        }

    }

    public static function End() {

        global $user;

        if(BATTLEMODE === 'WILD') {

            $hptotal = 0;

            foreach(self::$pokemon as $key => $val) {

                if($key > 0 && $key < 7) $hptotal += $val[0]['hp'];

            }

            if(self::$pokemon[0][0]['hp'] === 0) {

                self::GainExp();

                define('ITEMDROP', FALSE);

                if(ITEMDROP === TRUE && rand(1, 100) <= 8 && Trainer::Item('OBTAIN', $trainer['uid'], 213, 1, 'UNKNOWN', 99))

                    self::$report .= '<i>好像从' . self::$pokemon[0][0]['name'] . '的身上掉下了什么……</i><br>';

                if(self::$pokemon[1][0]['crritem'] === '212' && rand(1, 100) <= 3) {

                    $iid = range(1, 3) + range(165, 169) + range(179, 184) + [190, 209];
                    $iid = $iid[array_rand($iid)];

                    $item = DB::fetch_first('SELECT mi.num, i.name FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON i.iid = mi.iid WHERE mi.iid = ' . $iid . ' AND mi.uid = ' . $trainer['uid']);

                    if(Trainer::Item('OBTAIN', $trainer['uid'], $iid, 1, $item['num'], 99))

                        self::$report .= '<i>在脚底下发现了一个' . $item['name'] . '！</i><br>';

                }

            } elseif(self::$isend === FALSE && $hptotal > 0) {

                self::WriteBattleData($trainer['uid'], self::$pokemon);

                if(!self::$faintswap)

                    DB::query('UPDATE pkm_battlefield SET weather = \'' . self::$field['weather'] . '\', trkroom = ' . self::$field['trkroom'] . ', gravity = ' . self::$field['gravity'] . ', turn = ' . ++self::$field['turn'] . ' WHERE uid = ' . $trainer['uid']);

                goto UPDATEPOKEMON;

            }

            DB::query('DELETE FROM pkm_battlefield WHERE uid = ' . $trainer['uid']);
            DB::query('UPDATE pkm_trainerdata SET inbtl = 0 WHERE uid = ' . $trainer['uid']);

            self::WriteBattleData($trainer['uid'], [], 'DEL');

            self::$isend = TRUE;

            UPDATEPOKEMON: {

                $sql = [];

                foreach(self::$pokemon as $key => $val) {

                    if($key < 1 || $key > 6) continue;

                    $sql[] = '(' . $val[0]['pid'] . ', ' . $val[0]['hp'] . ', ' . $val[0]['status'] . ', \'' . serialize($val[0]['move']) . '\')';

                }

                DB::query('INSERT INTO pkm_mypkm (pid, hp, status, move) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE hp = VALUES(hp), STATUS = VALUES(STATUS), move = VALUES(move)');

            }

        }

    }

    public static function GainExp() {

        global $user;

        $baseexp = DB::result_first('SELECT baseexp FROM pkm_pkmdata WHERE id = ' . self::$pokemon[0][0]['id']);

        $sql         = [];
        $participate = 0;

        foreach(self::$pokemon as $key => $val) {

            if($key < 1 || $key > 6) continue;

            if($val[1][4] === 1) ++$participate;

        }

        foreach(self::$pokemon as $key => $val) {

            if($key < 1 || $key > 6 || $val[0]['level'] > 99 || $val[0]['hp'] < 1 || $val[1][4] === 0 && $val[0]['crritem'] != '208') continue;

            $exp = floor(floor(sqrt(self::$pokemon[0][0]['level'] * 2 + 10) * pow(self::$pokemon[0][0]['level'] * 2 + 10, 2)) * floor(floor(floor(floor(floor($baseexp * self::$pokemon[0][0]['level'] / 5) * (($val[0]['uid'] === $trainer['uid']) ? 1 : 1.5)) * (($val[0]['crritem'] === '214') ? 1.5 : 1)) * ($val[0]['crritem'] == '208' ? 0.5 : 1)) / $participate) / floor(sqrt(self::$pokemon[0][0]['level'] + $val[0]['level'] + 10) * pow(self::$pokemon[0][0]['level'] + $val[0]['level'] + 10, 2))) + 1;

            self::$pokemon[$key][0]['exp'] += $exp;

            self::$report .= $val[0]['name'] . '获得了' . $exp . '点经验。<br>';

            $tmp = [$val[0]['level'], $val[0]['id']];

            Pokemon::LevelUp(self::$pokemon[$key][0]);

            if($tmp[0] != self::$pokemon[$key][0]['level'])

                self::$report .= $val[0]['name'] . '升到了' . self::$pokemon[$key][0]['level'] . '级！<br>';

            if($tmp[1] != self::$pokemon[$key][0]['id'])

                self::$report .= $val[0]['name'] . '进化成了' . self::$pokemon[$key][0]['name'] . '！<br>';

            $sql[] = '(' . $val[0]['pid'] . ', ' . $exp . ', ' . (($val[0]['crritem'] == '211') ? 2 : 1) . ')';

        }

        if(!empty($sql))

            DB::query('INSERT INTO pkm_mypkm (pid, exp, hpns) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE exp = exp + VALUES(exp), hpns = hpns + VALUES(hpns)');

    }

    /**
     * This method creates/deletes user's battle data saved in filesystem.
     * @param        $uid    uid of the user
     * @param        $data   content that is gonna be written in
     * @param string $action DEL for deletion, otherwise write the files
     */
    public static function WriteBattleData($uid, $data, $action = '') {

        $path = ROOTBATTLE . '/user-' . $uid;

        // Performing deletion and ends the method directly
        if($action === 'DEL') {
            unlink($path);
            return;
        }

        $fp = fopen($path, 'w+');
        fwrite($fp, gzdeflate(serialize($data), 9));
        fclose($fp);

    }

    public static function GenerateBattleData($pid = 0, $place = 0) {

        return [$pid, [0, 0, 0, 0, 0, 0, 0], array_fill(0, self::$num['ssn'], FALSE), $place, 0, 0, 0, 0, 0, 0, 0, FALSE, 'insstatus' => 0];

    }

    public static function GenerateFieldData() {

        return '0000000000';

    }

    /*
        1 - Sky
        2 - Underwater
        3 - Underground
        4 - Mystery location
    */

    public static function __MoveCharge($report) {

        if(!self::$charged) {

            self::$atk[1][6] = self::$atkmove['mid'];
            self::$report .= $report . '<br>';

        }

    }

    public static function __MoveExplosion() {

        if(self::$def[0]['abi'] === '6') { # 潮湿

            self::$report .= self::$atk[0]['name'] . '的周围太潮湿，无法引爆！<br>';

            return FALSE;

        }

        self::$atk['hp'] = 0;

    }

    public static function __MoveTrap($code) {

        if(self::$def[1][2][3]) return FALSE;

        # For condition issue, effect last turn counter is set to TURN + 1
        # Additional value has been added to the counter with $code * 10 to record trap type

        self::$def[1][2][3] = $code * 10 + ((self::$atk[0]['crritem'] === '98') ? 8 : rand(5, 6));

    }

    //public static function __Move


}

?>