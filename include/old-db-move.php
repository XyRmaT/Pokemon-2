<?php

class MoveDb extends Battle {

    public static function __6() { # 招财猫

        parent::$report .= '满地都是金币！不过不是你的【【<br>';

    }

    public static function __7() { # 火焰拳

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __8() { # 冷冻拳

        parent::AlterStatus(parent::$def, 'FRZ', 10);

    }

    public static function __9() { # 雷电拳

        parent::AlterStatus(parent::$def, 'PAR', 10);

    }

    public static function __13() { # 镰鼬风

        parent::__MoveCharge(parent::$atk[0]['name'] . '的周围出现了空气涡流！');

    }

    public static function __14() { # 剑之舞

        parent::AlterStatLevel(parent::$atk, 'ATK-INC', 2);

    }

    public static function __18() { # 吹飞、in process...

        if(parent::$atkkey === 0) {

            $hp = 0;

            foreach(parent::$pokemon as $key => $val) {

                if($key == parent::$atkkey || $key == parent::$defkey) {

                    continue;

                } else {

                    $hp += $val[0]['hp'];

                }

            }

        }

        if(parent::$atkkey === 0 && $hp === 0 || (parent::$atk[0]['level'] + parent::$def[0]['level']) * rand(0, 255) < parent::$def[0]['level'] / 4 || parent::$def[0]['ability'] === '21' || parent::$def[1][2][23] === 1) {

            return parent::FailMove();

        } else {

        }

    }

    public static function __19() { # 飞空

        parent::__MoveCharge(parent::$atk[0]['name'] . '飞上了高空！');

        parent::$charged || parent::__MoveCurrentPlace(parent::$atk, 1);

    }

    public static function __20() { # 勒住

        parent::__MoveTrap(1);

    }

    public static function __23() { # 践踏

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __27() { # 回旋踢

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __28() { # 扔沙

        parent::AlterStatLevel(parent::$def, 'ACC-DEC');

    }

    public static function __29() { # 头槌

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __34() { # 压制

        parent::AlterStatus(parent::$def, 'PAR', 30);

    }

    public static function __35() { # 卷紧

        parent::__MoveTrap(2);

    }

    public static function __36() { # 突进

        parent::$m['recoilper'] = 1 / 4;

    }

    public static function __38() { # 舍身撞

        parent::$m['recoilper'] = 1 / 3;

    }

    public static function __39() { # 甩尾

        parent::AlterStatLevel(parent::$def, 'DEF-DEC');

    }

    public static function __40() { # 毒针

        parent::AlterStatus(parent::$def, 'PSN', 30);

    }

    public static function __43() { # 瞪眼

        parent::AlterStatLevel(parent::$def, 'DEF-DEC');

    }

    public static function __44() { # 啃咬

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __45() { # 叫声

        parent::AlterStatLevel(parent::$def, 'ATK-DEC');

    }

    public static function __47() { # 歌唱

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __48() { # 超音波

        parent::AlterSubStatus(parent::$def, 'CFS', 100, '2-5', TRUE);

    }

    public static function __49() { # 音波爆

        parent::$stabledamage = 20;

    }

    public static function __50() { # 残废

        if(parent::$def[1][2][16]) {

            return parent::FailMove();

        } else {

            parent::$def[1][2][16] = 4;

        }

    }

    public static function __51() { # 溶解液

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 1, 30);

    }

    public static function __52() { # 火苗

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __53() { # 火焰放射

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __58() { # 冷冻光线

        parent::AlterStatus(parent::$def, 'FRZ', 10);

    }

    public static function __59() { # 暴风雪

        parent::AlterStatus(parent::$def, 'FRZ', 10);

    }

    public static function __60() { # 精神光线

        parent::AlterSubStatus(parent::$def, 'CFS', 10);

    }

    public static function __61() { # 泡沫光线

        parent::AlterStatLevel(parent::$def, 'SPD-DEC', 1, 10);

    }

    public static function __62() { # 极光光线

        parent::AlterStatLevel(parent::$def, 'ATK-DEC', 1, 10);

    }

    public static function __66() { # 地狱车

        parent::$m['recoilper'] = 1 / 4;

    }

    public static function __67() { # 过肩摔

        if(parent::$def[0]['weight'] < 11)
            parent::$m['power'] = 20; elseif(parent::$def[0]['weight'] < 25)
            parent::$m['power'] = 40;
        elseif(parent::$def[0]['weight'] < 50)
            parent::$m['power'] = 60;
        elseif(parent::$def[0]['weight'] < 100)
            parent::$m['power'] = 80;
        elseif(parent::$def[0]['weight'] < 200)
            parent::$m['power'] = 100;
        else                                    parent::$m['power'] = 120;

    }

    public static function __69() { # 地球投

        parent::$stabledamage = parent::$atk[0]['level'];

    }

    public static function __74() { # 成长

        parent::AlterStatLevel(parent::$atk, 'ATK-INC', (parent::$field['weather']{0} === '1') ? 2 : 1);
        parent::AlterStatLevel(parent::$atk, 'SPATK-INC', (parent::$field['weather']{0} === '1') ? 2 : 1);

    }

    public static function __76() { # 太阳光线

        parent::__MoveCharge(parent::$atk[0]['name'] . '正在吸收阳光！');

    }

    public static function __77() { # 毒粉

        parent::AlterStatus(parent::$def, 'PSN', 100, 0, TRUE);

    }

    public static function __78() { # 麻痹粉

        parent::AlterStatus(parent::$def, 'PAR', 100, 0, TRUE);

    }

    public static function __79() { # 催眠粉

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __81() { # 吐丝

        parent::AlterStatLevel(parent::$def, 'ATK-DEC');

    }

    public static function __82() { # 龙之怒

        parent::$stabledamage = 40;

    }


    public static function __83() { # 火漩涡

        parent::__MoveTrap(3);

    }

    public static function __84() { # 电击

        parent::AlterStatus(parent::$def, 'PAR', 10);

    }

    public static function __85() { # 十万伏特

        parent::AlterStatus(parent::$def, 'PAR', 10);

    }

    public static function __86() { # 电磁波

        parent::AlterStatus(parent::$def, 'PAR', 100, 0, TRUE);

    }

    public static function __87() { # 雷电

        parent::AlterStatus(parent::$def, 'PAR', 30);

    }

    public static function __91() { # 挖洞

        parent::__MoveCharge(parent::$atk[0]['name'] . '潜入了地下！');

        parent::$charged || parent::__MoveCurrentPlace(parent::$atk, 3);

    }

    public static function __92() { # 剧毒

        parent::AlterStatus(parent::$def, 'TXC', 100, 0, TRUE);

    }

    public static function __93() { # 念力

        parent::AlterSubStatus(parent::$def, 'CFS', 10);

    }

    public static function __95() { # 催眠术

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __96() { # 瑜珈之形

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');

    }

    public static function __97() { # 高速移动

        parent::AlterStatLevel(parent::$atk, 'SPD-INC', 2);

    }

    public static function __101() { # 黑夜魔影

        parent::$stabledamage = parent::$atk[0]['level'];

    }

    public static function __103() { # 噪音

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 2);

    }

    public static function __104() { # 影分身

        parent::AlterStatLevel(parent::$atk, 'EVA-INC');

    }

    public static function __106() { # 变硬

        parent::AlterStatLevel(parent::$atk, 'DEF-INC');

    }

    public static function __107() { # 变小

        parent::$atk[1][2][20] = 1;

        parent::AlterStatLevel(parent::$atk, 'EVA-INC', 2);

    }

    public static function __108() { # 烟幕

        parent::AlterStatLevel(parent::$def, 'ACC-DEC', 1);

    }

    public static function __109() { # 怪异光线

        parent::AlterSubStatus(parent::$def, 'CFS', 100, '2-5', TRUE);

    }

    public static function __110() { # 躲进贝壳

        parent::AlterStatLevel(parent::$atk, 'DEF-INC');

    }

    public static function __111() { # 变圆

        parent::$atk[1][2][21] = 1;

        parent::AlterStatLevel(parent::$atk, 'DEF-INC');

    }

    public static function __112() { # 栅栏

        parent::AlterStatLevel(parent::$atk, 'DEF-INC', 2);

    }

    public static function __120() { # 自爆

        if(parent::$def[0]['ability'] === '6') {

            return parent::FailMove();

        } else {

            parent::$atk[0]['hp'] = 0;

        }

    }

    public static function __122() { # 舌舔

        parent::AlterStatus(parent::$def, 'PAR', 30);

    }

    public static function __123() { # 毒雾

        parent::AlterStatus(parent::$def, 'PSN', 40);

    }

    public static function __124() { # 淤泥攻击

        parent::AlterStatus(parent::$def, 'PSN', 30);

    }

    public static function __125() { # 骨头棍

        parent::AlterInstantStatus(parent::$def, 1, 1, 10);

    }

    public static function __126() { # 大字火

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __127() { # 登瀑

        parent::AlterInstantStatus(parent::$def, 1, 1, 20);

    }

    public static function __128() { # 贝壳夹

        parent::__MoveTrap(4);

    }

    public static function __130() { # 火箭头槌

        parent::__MoveCharge(parent::$atk[0]['name'] . '将脑袋缩进了壳里！');

        parent::$charged || parent::AlterStatLevel(parent::$atk, 'DEF-INC', 1, 100, FALSE);

    }

    public static function __132() { # 缠绕

        parent::AlterStatLevel(parent::$def, 'SPD-DEC', 1, 10, FALSE);

    }

    public static function __133() { # 超级健忘

        parent::AlterStatLevel(parent::$atk, 'SPDEF-INC', 2);

    }

    public static function __134() { # 弄弯勺子

        parent::AlterStatLevel(parent::$def, 'ACC-DEC', 1);

    }

    public static function __137() { # 蛇瞪眼

        parent::AlterStatus(parent::$def, 'PAR', 100, 0, TRUE);

    }

    public static function __139() { # 毒瓦斯

        parent::AlterStatus(parent::$def, 'PSN', 100, 0, TRUE);

    }

    public static function __142() { # 恶魔之吻

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __143() { # 神鸟

        parent::__MoveCharge(parent::$atk[0]['name'] . '正从高空飞速俯冲而下！');

        parent::$charged && parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __145() { # 水泡

        parent::AlterStatLevel(parent::$def, 'SPD-DEC', 1, 10, FALSE);

    }

    public static function __146() { # 飘飘拳

        parent::AlterSubStatus(parent::$def, 'CFS', 20);

    }

    public static function __147() { # 蘑菇孢子

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __148() { # 闪光

        parent::AlterStatLevel(parent::$def, 'ACC-DEC');

    }

    public static function __149() { # 精神波动

        parent::$stabledamage = parent::$atk[0]['level'] * rand(5, 15) / 10;

    }


    public static function __150() { # 弹跳

        parent::$report .= parent::$atk[0]['name'] . '跳啊跳~<br>';

    }

    public static function __151() { # 溶化

        parent::AlterStatLevel(parent::$atk, 'DEF-INC', 2);

    }

    public static function __153() { # 大爆炸

        if(parent::$def[0]['ability'] === '6') {

            return parent::FailMove();

        } else {

            parent::$atk[0]['hp'] = 0;

        }

    }

    public static function __157() { # 岩崩

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __158() { # 必杀门牙

        parent::AlterInstantStatus(parent::$def, 1, 1, 10);

    }

    public static function __159() { # 变方

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');

    }

    public static function __161() { # 三角攻击

        $randnum = rand(0, 255);

        if($randnum < 17)
            parent::AlterStatus(parent::$def, 'PAR'); elseif($randnum < 34)
            parent::AlterStatus(parent::$def, 'BRN');
        elseif($randnum < 51)
            parent::AlterStatus(parent::$def, 'FRZ');

    }

    public static function __162() { # 愤怒门牙

        parent::$stabledamage = max(floor(parent::$def[0]['hp'] / 2), 1);

    }

    public static function __172() { # 火焰车

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __173() { # 鼾声

        if(parent::$atk[0]['status'] !== '4') {

            return parent::FailMove();

        } else {

            parent::$report .= parent::$atk[0]['name'] . '发出了震耳欲聋的鼾声！<br>';

        }

    }

    public static function __174() { # 诅咒

        if(!in_array('16', [parent::$atk[0]['type'], parent::$atk[0]['type_b']])) {

            parent::AlterStatLevel(parent::$atk, 'ATK-INC');
            parent::AlterStatLevel(parent::$atk, 'DEF-INC');
            parent::AlterStatLevel(parent::$atk, 'SPD-DEC');

        } elseif(parent::$def[1][2][10]) {

            return parent::FailMove();

        } else {

            parent::$def[1][2][10] = 1;
            parent::$report .= parent::$def[0]['name'] . '被诅咒了！<br>';

        }

    }

    public static function __178() { # 棉花孢子

        parent::AlterStatLevel(parent::$def, 'SPD-DEC', 2);

    }

    public static function __181() { # 细雪

        parent::AlterStatus(parent::$def, 'FRZ', 10);

    }

    public static function __184() { # 恐惧颜

        parent::AlterStatLevel(parent::$def, 'SPD-DEC', 2);

    }

    public static function __186() { # 天使之吻

        parent::AlterSubStatus(parent::$def, 'CFS', 100, '2-5', TRUE);

    }

    public static function __188() { # 淤泥爆弹

        parent::AlterStatus(parent::$def, 'PSN', 30);

    }

    public static function __192() { # 电磁炮

        parent::AlterStatus(parent::$def, 'PAR');

    }

    public static function __204() { # 撒娇

        parent::AlterStatLevel(parent::$def, 'ATK-DEC', 2);

    }

    public static function __207() { # 虚张声势

        parent::AlterSubStatus(parent::$def, 'CFS', 100, '2-5', TRUE);
        parent::AlterStatLevel(parent::$def, 'ATK-INC', 2);

    }

    public static function __209() { # 电火花

        parent::AlterStatus(parent::$def, 'PAR', 30);

    }

    public static function __211() { # 钢之翼

        parent::AlterStatLevel(parent::$atk, 'DEF-INC', 1, 10);

    }

    public static function __214() { # 梦话、in process...

        $exception          = '13,19,76,91,117,119,130,143,214,253,291,382,383,448,467,507,553,554,264';
        $count              = DB::result_first('SELECT COUNT(*) FROM pkm_movedata WHERE move_id NOT IN (' . $exception);
        $move               = DB::fetch_first('SELECT move_id, name_zh name, type, class, power, acc, pp, prio, freq, critrt, effect, battle_effect FROM pkm_movedata LIMIT ' . rand(0, $count - 1) . ', 1');
        parent::$m['power'] = $move['power'];
        parent::$report .= parent::$atk[0]['name'] . '使出了' . parent::$atkmove['name'] . '！<br>';

        if($move['effect'] !== '1') {

            $movename = '__' . $move['move_id'];

            self::$movename();

        }

    }

    public static function __217() { # 礼物

        $randnum = rand(0, 255);

        if($randnum < 102)
            parent::$m['power'] = 40; elseif($randnum < 178)
            parent::$m['power'] = 80;
        elseif($randnum < 204)
            parent::$m['power'] = 120;
        else {

            parent::$def[0]['hp']     = min(parent::$def[0]['hp'] + floor(parent::$def[0]['max_hp'] * 0.25), parent::$def[0]['max_hp']);
            parent::$atkmove['class'] = '0';

        }

        parent::$report .= parent::$def[0]['name'] . '打开一看，' . (($randnum < 204) ? '原来是个炸弹！' : '里面是瓶伤药！') . '<br>';

    }

    public static function __221() { # 神圣火焰

        parent::AlterStatus(parent::$def, 'BRN', 50);

    }

    public static function __222() { # 震级变化

        $randkey            = substr(str_shuffle('01122223333334444556'), 0, 1);
        $tmp                = [10, 30, 50, 70, 90, 110, 150];
        parent::$m['power'] = $tmp[$randkey];
        parent::$report .= '震级' . ($randkey + 4) . '，' . (($randkey < 6) ? '没什么感觉。' : ($randkey < 9) ? '感觉大地在震动……' : '山崩地裂！');

    }

    public static function __223() { # 爆裂拳

        parent::AlterSubStatus(parent::$def, 'CFS', 50);

    }

    public static function __231() { # 铁尾

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 1, 30);

    }

    public static function __232() { # 金属爪

        parent::AlterStatLevel(parent::$atk, 'ATK-INC', 1, 10);

    }

    public static function __237() { # 觉醒力量

        if(parent::$moveflag === 'CALTYPE') {

            $iv                 = explode(',', parent::$atk[0]['ind_value']);
            $typearr            = ['6', '7', '9', '11', '10', '8', '16', '12', '1', '2', '3', '4', '14', '13', '17', '15'];
            parent::$m['type']  = $typearr[floor((($iv[0] & 1) + 2 * ($iv[1] & 1) + 4 * ($iv[2] & 1) + 8 * ($iv[5] & 1) + 16 * ($iv[3] & 1) + 32 * ($iv[4] & 1)) * 15 / 63)];
            parent::$m['power'] = floor(((in_array($iv[0] % 4, [2, 3]) ? 1 : 0) + 2 * (in_array($iv[1] % 4, [2, 3]) ? 1 : 0) + 4 * (in_array($iv[2] % 4, [2, 3]) ? 1 : 0) + 8 * (in_array($iv[5] % 4, [2, 3]) ? 1 : 0) + 16 * (in_array($iv[3] % 4, [2, 3]) ? 1 : 0) + 32 * (in_array($iv[4] % 4, [2, 3]) ? 1 : 0)) * 40 / 63 + 30);

        }
    }

    public static function __239() { # 龙卷风

        parent::AlterInstantStatus(parent::$def, 1, 1, 20);

    }

    public static function __242() { # 咬碎

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 1, 20);

    }

    public static function __249() { # 碎岩

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 1, 50);

    }

    public static function __250() { # 漩涡

        parent::__MoveTrap(5);

    }

    public static function __252() { # 下马威、In process...

        if(parent::$atk[1][2][9] !== '1')
            return parent::FailMove();

    }

    public static function __254() { # 能量储存

        if(parent::$atk[1][2][26] >= 3) {

            return parent::FailMove();

        } else {

            parent::$report .= parent::$atk[0]['name'] . '把能量存储在了体内！<br>';

            parent::$atk[1][2][26] ? ++parent::$atk[1][2][26] : (parent::$atk[1][2][26] = 1);

            parent::AlterStatLevel(parent::$atk, 'DEF-INC', 1, 100, FALSE);
            parent::AlterStatLevel(parent::$atk, 'SPDEF-INC', 1, 100, FALSE);

        }

    }

    public static function __255() { # 能量释放

        if(!parent::$atk[1][2][26]) {

            return parent::FailMove();

        } else {

            parent::$m['power']    = parent::$atk[1][2][26] * 100;
            parent::$atk[1][2][26] = FALSE;
            parent::$report .= parent::$atk[0]['name'] . '体内的能量造成了冲击波！<br>';

            parent::AlterStatLevel(parent::$atk, 'DEF-EMP');
            parent::AlterStatLevel(parent::$atk, 'SPDEF-EMP');

        }

    }

    public static function __256() { # 能量吸入
        return;
        if(!parent::$atk[1][2][26]) {

            return parent::FailMove();

        } else {

            parent::Hp('INC', parent::$atk[0]['max_hp'], parent::$atk[0]['hp'], 25 / 2 * (pow(parent::$atk[1][2][26], 2) - parent::$atk[1][2][26] + 2));
            parent::AlterStatLevel(parent::$atk, 'DEF-EMP');
            parent::AlterStatLevel(parent::$atk, 'SPDEF-EMP');

            parent::$atk[1][2][26] = FALSE;
            parent::$report .= parent::$atk[0]['name'] . '用体内的能量回复了！';

        }

    }

    public static function __257() { # 热风

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __260() { # 煽动

        parent::AlterSubStatus(parent::$def, 'CFS', 100, '2-5', TRUE);
        parent::AlterStatLevel(parent::$def, 'SPATK-INC');

    }

    public static function __261() { # 鬼火

        parent::AlterStatus(parent::$def, 'BRN');

    }

    public static function __268() { # 充电

        parent::$atk[1][2][28] = 1;

        parent::AlterStatLevel(parent::$atk, 'SPDEF-INC');

    }

    public static function __276() { # 蛮力

        parent::AlterStatLevel(parent::$atk, 'ATK-DEC');
        parent::AlterStatLevel(parent::$atk, 'DEF-DEC');

    }

    public static function __283() { # 莽撞

        // parent::$stabledamage = parent::$atk[0]['hp'];

        if(parent::$def[0]['hp'] <= parent::$atk[0]['hp']) {

            return parent::FailMove();

        } else {

            parent::$def[0]['hp'] = parent::$atk[0]['hp'];

        }

    }

    public static function __291() { # 潜水

        parent::__MoveCharge(parent::$atk[0]['name'] . '潜入了水中！');

        parent::$charged || parent::__MoveCurrentPlace(parent::$atk, 2);

    }

    public static function __294() { # 萤火

        parent::AlterStatLevel(parent::$atk, 'SPATK-INC', 3);

    }

    public static function __297() { # 羽毛舞

        parent::AlterStatLevel(parent::$def, 'ATK-DEC', 2);

    }

    public static function __298() { # 草裙舞

        parent::AlterSubStatus(parent::$def, 'CFS');

    }

    public static function __299() { # 火花踢

        parent::AlterStatus(parent::$def, 'BRN', 10);

    }

    public static function __302() { # 针刺臂膀

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __305() { # 剧毒之牙

        parent::AlterStatus(parent::$def, 'TXC', 30);

    }

    public static function __306() { # 崩击之爪

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 1, 50);

    }

    public static function __309() { # 彗星拳

        parent::AlterStatLevel(parent::$atk, 'ATK-INC', 1, 20);

    }

    public static function __310() { # 恐吓

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __313() { # 假哭

        parent::AlterStatLevel(parent::$def, 'SPDEF-DEC', 2);

    }

    public static function __315() { # 燃烧殆尽

        parent::AlterStatLevel(parent::$atk, 'SPATK-DEC', 2);

    }

    public static function __318() { # 银色之风

        if(rand(0, 100) < 10) {

            parent::AlterStatLevel(parent::$atk, 'ATK-INC');
            parent::AlterStatLevel(parent::$atk, 'DEF-INC');
            parent::AlterStatLevel(parent::$atk, 'SPATK-INC');
            parent::AlterStatLevel(parent::$atk, 'SPDEF-INC');
            parent::AlterStatLevel(parent::$atk, 'SPD-INC');

        }

    }

    public static function __320() { # 草笛

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __321() { # 挠痒

        parent::AlterStatLevel(parent::$def, 'ATK-DEC');
        parent::AlterStatLevel(parent::$def, 'DEF-DEC');

    }

    public static function __322() { # 宇宙力量

        parent::AlterStatLevel(parent::$atk, 'DEF-INC');
        parent::AlterStatLevel(parent::$atk, 'SPDEF-INC');

    }

    public static function __324() { # 信号光线

        parent::AlterSubStatus(parent::$def, 'CFS', 10);

    }

    public static function __326() { # 神通力

        parent::AlterInstantStatus(parent::$def, 1, 1, 10);

    }

    public static function __328() { # 沙地狱

        parent::__MoveTrap(6);

    }

    public static function __334() { # 铁壁

        parent::AlterStatLevel(parent::$atk, 'DEF-INC', 2);

    }

    public static function __336() { # 远吠

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');

    }

    public static function __339() { # 巨大化

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');
        parent::AlterStatLevel(parent::$atk, 'DEF-INC');

    }

    public static function __340() { # 飞跃

        parent::__MoveCharge(parent::$atk[0]['name'] . '跳得很高！');

        parent::$charged ? parent::AlterStatus(parent::$def, 'PAR', 30) : parent::__MoveCurrentPlace(parent::$atk, 1);

    }

    public static function __342() { # 毒尾

        parent::AlterStatus(parent::$def, 'PSN', 10);

    }

    public static function __344() { # 高压电击

        parent::AlterStatus(parent::$def, 'PAR', 10);
        parent::$m['recoilper'] = 1 / 3;

    }

    public static function __347() { # 冥想

        parent::AlterStatLevel(parent::$atk, 'SPATK-INC');
        parent::AlterStatLevel(parent::$atk, 'SPDEF-INC');

    }

    public static function __349() { # 龙之舞

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');
        parent::AlterStatLevel(parent::$atk, 'SPD-INC');

    }

    public static function __352() { # 水之波动

        parent::AlterSubStatus(parent::$def, 'CFS', 20);

    }

    public static function __354() { # 精神增压

        parent::AlterStatLevel(parent::$atk, 'SPATK-DEC', 2);

    }

    public static function __363() { # 自然恩惠

        if(empty(parent::$atk[0]['item_holding']) || !in_array('0', [parent::$field['other']{2}, parent::$def[1][2][7]])) {

            return parent::FailMove();

        } else {

            list(parent::$atkmove['type'], parent::$m['power']) = explode(',', DB::result_first('SELECT ngiftpwr FROM pkm_itemdata WHERE item_id = ' . parent::$atk[0]['item_holding']));

        }

    }

    public static function __370() { # 近战

        parent::AlterStatLevel(parent::$atk, 'DEF-DEC');
        parent::AlterStatLevel(parent::$atk, 'SPDEF-DEC');

    }

    public static function __374() { # 投掷、in process...

    }

    public static function __387() { # 最终手段

        return;

        $arr = [];

        foreach(parent::$atk[0]['moves'] as $val) {

            if($val[0] === 387) {

                continue;

            } else {

                $arr[] = $val[0];

            }

        }

        if(empty($arr) || array_diff($arr, parent::$atk[1][9]))
            return parent::FailMove();

    }

    public static function __389() { # 偷袭

        if(parent::$defmove['class'] === '0' || $i === 1)
            return parent::FailMove();

    }

    public static function __394() { # 火焰躯进

        parent::AlterStatus(parent::$def, 'BRN', 10);
        parent::$m['recoilper'] = 1 / 3;

    }

    public static function __397() { # 岩切

        parent::AlterStatLevel(parent::$atk, 'SPD-INC', 2);

    }

    public static function __398() { # 毒突

        parent::AlterStatus(parent::$def, 'PSN', 30);

    }

    public static function __399() { # 恶之波动

        parent::AlterInstantStatus(parent::$def, 1, 1, 20);

    }

    public static function __403() { # 空气切割

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __407() { # 龙之冲锋

        parent::AlterInstantStatus(parent::$def, 1, 1, 20);

    }

    public static function __413() { # 勇鸟

        parent::$m['recoilper'] = 1 / 3;

    }

    public static function __417() { # 阴谋

        parent::AlterStatLevel(parent::$atk, 'SPATK-INC', 2);

    }

    public static function __422() { # 雷之牙

        parent::AlterStatus(parent::$def, 'PAR', 10);
        parent::AlterInstantStatus(parent::$def, 1, 1, 10);

    }

    public static function __423() { # 冰之牙

        parent::AlterStatus(parent::$def, 'FRZ', 10);
        parent::AlterInstantStatus(parent::$def, 1, 1, 10);

    }

    public static function __424() { # 火之牙

        parent::AlterStatus(parent::$def, 'BRN', 10);
        parent::AlterInstantStatus(parent::$def, 1, 1, 10);

    }

    public static function __428() { # 思念头槌

        parent::AlterInstantStatus(parent::$def, 1, 1, 20);

    }

    public static function __431() { # 攀岩

        parent::AlterSubStatus(parent::$def, 'CFS', 20);

    }

    public static function __434() { # 龙星群

        parent::AlterStatLevel(parent::$atk, 'SPATK-DEC', 2);

    }

    public static function __436() { # 喷烟

        parent::AlterStatus(parent::$def, 'BRN', 30);

    }

    public static function __437() { # 飞叶风暴

        parent::AlterStatLevel(parent::$atk, 'SPATK-DEC', 2);

    }

    public static function __440() { # 毒十字

        parent::AlterStatus(parent::$def, 'PSN', 10);

    }

    public static function __441() { # 粉尘射击

        parent::AlterStatus(parent::$def, 'PSN', 30);

    }

    public static function __442() { # 铁头槌

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __445() { # 诱惑

        if(in_array('0', [parent::$atk[0]['gender'], parent::$def[0]['gender']]) || parent::$atk[0]['gender'] | parent::$def[0]['gender'] !== 3 || parent::$def[0]['ability'] === '12') { # 无性、异性、钝感

            return parent::FailMove();

        } else {

            parent::AlterStatLevel(parent::$def, 'SPATK-DEC', 2);

        }

    }

    public static function __448() { # 喋喋不休

        parent::AlterSubStatus(parent::$def, 'CFS', 100);

    }

    public static function __449() { # 制裁之砾

        switch(parent::$atk[0]['item_holding']) {
            case '火球石板':
                parent::$m['type'] = '1';
                break;
            case '水珠石板':
                parent::$m['type'] = '2';
                break;
            case '碧绿石板':
                parent::$m['type'] = '3';
                break;
            case '雷电石板':
                parent::$m['type'] = '4';
                break;
            case '拳击石板':
                parent::$m['type'] = '6';
                break;
            case '青空石板':
                parent::$m['type'] = '7';
                break;
            case '昆虫石板':
                parent::$m['type'] = '8';
                break;
            case '猛毒石板':
                parent::$m['type'] = '9';
                break;
            case '岩石石板':
                parent::$m['type'] = '10';
                break;
            case '大地石板':
                parent::$m['type'] = '11';
                break;
            case '钢铁石板':
                parent::$m['type'] = '12';
                break;
            case '冰柱石板':
                parent::$m['type'] = '13';
                break;
            case '神秘石板':
                parent::$m['type'] = '14';
                break;
            case '恐惧石板':
                parent::$m['type'] = '15';
                break;
            case '阴魂石板':
                parent::$m['type'] = '16';
                break;
            case '龙之石板':
                parent::$m['type'] = '17';
                break;
        }

    }

    public static function __451() { # 充电光线

        parent::AlterStatLevel(parent::$atk, 'SPATK-INC', 1, 70);

    }

    public static function __452() { # 木锤

        parent::$m['recoilper'] = 1 / 3;

    }

    public static function __455() { # 防御指令

        parent::AlterStatLevel(parent::$atk, 'DEF-INC');
        parent::AlterStatLevel(parent::$atk, 'SPDEF-INC');

    }

    public static function __457() { # 双刃头槌

        parent::$m['recoilper'] = 1 / 2;

    }

    public static function __463() { # 熔岩风暴

        parent::__MoveTrap(7);

    }

    public static function __464() { # 黑洞

        parent::AlterStatus(parent::$def, 'SLP', 100, 0, TRUE);

    }

    public static function __467() { # 影袭

        parent::__MoveCharge(parent::$atk[0]['name'] . '消失了！');

        parent::$charged ? (parent::$def[1]['insstatus'] = 0) : parent::__MoveCurrentPlace(parent::$atk, 4);

    }

    public static function __468() { # 磨爪

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');
        parent::AlterStatLevel(parent::$atk, 'ACC-INC');

    }

    public static function __482() { # 淤泥波

        parent::AlterStatus(parent::$def, 'PSN', 10);

    }

    public static function __483() { # 蝶之舞

        parent::AlterStatLevel(parent::$atk, 'SPATK-INC');
        parent::AlterStatLevel(parent::$atk, 'SPDEF-INC');
        parent::AlterStatLevel(parent::$atk, 'SPD-INC');

    }

    public static function __488() { # 硝化冲锋

        parent::AlterStatLevel(parent::$atk, 'SPD-INC');

    }

    public static function __489() { # 盘蜷

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');
        parent::AlterStatLevel(parent::$atk, 'DEF-INC');
        parent::AlterStatLevel(parent::$atk, 'ACC-INC');

    }

    public static function __503() { # 沸水

        parent::AlterStatus(parent::$def, 'BRN', 30);

    }

    public static function __504() { # 破壳而出

        parent::AlterStatLevel(parent::$atk, 'ATK-INC', 2);
        parent::AlterStatLevel(parent::$atk, 'DEF-DEC');
        parent::AlterStatLevel(parent::$atk, 'SPATK-INC', 2);
        parent::AlterStatLevel(parent::$atk, 'SPDEF-DEC');
        parent::AlterStatLevel(parent::$atk, 'SPD-INC', 2);

    }

    public static function __508() { # 齿轮变换

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');
        parent::AlterStatLevel(parent::$atk, 'SPD-INC', 2);

    }

    public static function __517() { # 炼狱

        parent::AlterStatus(parent::$def, 'BRN');

    }

    public static function __526() { # 鼓舞士气

        parent::AlterStatLevel(parent::$atk, 'ATK-INC');
        parent::AlterStatLevel(parent::$atk, 'SPATK-INC');

    }

    public static function __528() { # 野性电击

        parent::$m['recoilper'] = 1 / 4;

    }

    public static function __531() { # 心灵压迫

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __534() { # 贝壳刃

        parent::AlterStatLevel(parent::$def, 'DEF-DEC', 1, 50);

    }

    public static function __537() { # 坚硬滚动

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __538() { # 棉花防御

        parent::AlterStatLevel(parent::$atk, 'DEF-INC', 3);

    }

    public static function __542() { # 暴风

        parent::AlterSubStatus(parent::$def, 'CFS', 30);

    }

    public static function __543() { # 爆爆头突击

        parent::$m['recoilper'] = 1 / 4;

    }

    public static function __545() { # 火焰弹

        parent::AlterStatus(parent::$def, 'BRN', 30);

    }

    public static function __546() { # 科技爆破

        switch(parent::$atk[0]['item_holding']) {
            case '火焰卡带':
                parent::$m['type'] = '1';
                break;
            case '海洋卡带':
                parent::$m['type'] = '2';
                break;
            case '雷电卡带':
                parent::$m['type'] = '4';
                break;
            case '冰冻卡带':
                parent::$m['type'] = '13';
                break;
        }

    }

    public static function __547() { # 古代之歌

        parent::AlterStatus(parent::$def, 'SLP', 10);

    }

    public static function __551() { # 青色火焰

        parent::AlterStatus(parent::$def, 'BRN', 20);

    }

    public static function __552() { # 火焰之舞

        parent::AlterStatLevel(parent::$atk, 'SPATK-INC', 1, 50);

    }

    public static function __553() { # 冰结电击

        parent::__MoveCharge(parent::$atk[0]['name'] . '的身上被蓝黄色的寒气所笼盖！');

        parent::$charged && parent::AlterStatus(parent::$def, 'PAR', 30);

    }

    public static function __554() { # 冰冷闪光

        parent::__MoveCharge(parent::$atk[0]['name'] . '的身上被红蓝色的寒气所笼盖！');

        parent::$charged && parent::AlterStatus(parent::$def, 'BRN', 30);

    }

    public static function __556() { # 冰柱坠落

        parent::AlterInstantStatus(parent::$def, 1, 1, 30);

    }

    public static function __557() { # 创造胜利

        parent::AlterStatLevel(parent::$atk, 'DEF-DEC');
        parent::AlterStatLevel(parent::$atk, 'SPDEF-DEC');
        parent::AlterStatLevel(parent::$atk, 'SPD-DEC');

    }

}