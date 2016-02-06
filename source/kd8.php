<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if(!$_G['uid']) {
    showmessage('to_login', '', '', ['login' => 1]);
}

@include DISCUZ_ROOT . './source/plugin/kd8c/kd8c.lang.php';
require_once DISCUZ_ROOT . './source/plugin/kd8c/kd8c_common.inc.php';

function dayornight() {
    $hour = date(H, $_SERVER['REQUEST_TIME']);
    return $hour >= 7 && $hour < 19 ? 'day' : 'night';
}

//判断act操作，1为购买宠物
if($_G['gp_act'] == 1) {
    //将编号参数强制转换成整型
    $cmd  = intval($_G['gp_cmd']);
    $page = intval($_G['gp_page']);
    //查询身上PM数量
    //备注：由于购买PET的时候是放在身上，所以只查在身上的PET
    $query     = DB::query('SELECT COUNT(*) FROM cdb_kd8c_my_pm WHERE uid=' . $_G['uid'] . ' AND showtype IN (1, 2)');
    $kd8c_user = DB::result($query, 0);
    if($kd8c_user >= 6) {
        //如果数量超过6只，弹出错误提示
        showmessage($kd8c_pet_showmsg['msg4'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    } else {
        //查询所购买的宠物是否开放
        $query = DB::query("SELECT * FROM cdb_kd8c_pet_pmlist WHERE buy='开放' AND id_quanguo=" . $cmd);
        $pet   = DB::fetch($query);
        if($pet) {
            //查询现金是否足够购买该宠物

            if($user_extcredits[$moneycredits] >= $pet['price']) {
                //HP等级
                $hp_lv = 1;
                //HP努力度
                $hp_nl = 0;
                //计算个体值
                $hp_gt     = rand(0, 31);
                $gongji_gt = rand(0, 31);
                $fangyu_gt = rand(0, 31);
                $minjie_gt = rand(0, 31);
                $tegong_gt = rand(0, 31);
                $tefang_gt = rand(0, 31);
                //计算性别

                $female_ids  = array(29, 30, 31, 113, 115, 124, 238, 241, 242, 314, 380, 413, 416, 440, 478, 488, 548, 549, 629, 630, 669);
                $male_ids    = array(32, 33, 34, 106, 107, 128, 236, 237, 313, 381, 414, 475, 538, 539, 627, 628, 641, 642, 645);
                $neutral_ids = array(81, 82, 100, 101, 120, 121, 132, 137, 144, 145, 146, 150, 151, 201, 233, 243, 244, 245, 249, 250, 251, 292, 337, 338, 343, 344, 374, 375, 376, 377, 378, 379, 382, 383, 384, 385, 386, 436, 437, 462, 474, 479, 480, 481, 482, 483, 484, 486, 487, 489, 490, 491, 492, 493, 494, 599, 600, 601, 615, 622, 623, 638, 639, 640, 643, 644, 646, 647, 648, 649, 703, 716, 717, 718);
                $sex_sign    = array('无', '♀', '♂');
                $pmsex       = $sex_sign[in_array($cmd, $female_ids) ? 1 : (in_array($cmd, $male_ids) ? 2 : (in_array($cmd, $neutral_ids) ? 0 : rand(1, 2)))];

                //计算当前等级的HP值
                $hp_nlz = hpjisuan($pet['hp_zz'], $hp_gt, $hp_nl, $hp_lv);

                //查询宠物显示方式，制定新购买的PM的显示方式
                //备注：由于是判断所购买的PET是放在队首还是身上其他位置，所以只查在队首的PET
                $query    = DB::query('SELECT * FROM cdb_kd8c_my_pm WHERE uid=' . $_G['uid'] . ' AND showtype = 1');
                $firstpm  = DB::fetch($query);
                $showtype = $firstpm ? 2 : 1;

                //扣钱
                DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "-" . $pet['price'] . " WHERE uid='" . $_G[uid] . "'");
                DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "+" . $pet['price'] . " WHERE uid='70490'");
                //给宠物
                DB::query("INSERT INTO cdb_kd8c_my_pm ( pmid , uid , id_quanguo , pmname , hp_now , hp_gt , gongji_gt , fangyu_gt , minjie_gt , tegong_gt , tefang_gt , hp_nl , gongji_nl , fangyu_nl , minjie_nl , tegong_nl , tefang_nl , daoju , pmball , showtype , exp , lv , pic , sex ) VALUES ('', '$_G[uid]', '$pet[id_quanguo]', '$pet[name_cn]', '$hp_nlz', '$hp_gt', '$gongji_gt', '$fangyu_gt', '$minjie_gt', '$tegong_gt', '$tefang_gt', '0', '0', '0', '0', '0', '0', '0', '0', '$showtype', '1', '1', '1','$pmsex')");
                //更新图鉴信息
                if($pet['id_quanguo'] > 0) add_pet_history($_G['uid'], $_G['username'], $pet['id_quanguo']);
                showmessage($kd8c_pet_showmsg['msg3'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=1&page=' . $page);
            } else {
                //如果现金不足，弹出错误提示
                showmessage($kd8c_pet_showmsg['msg2'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=1&page=' . $page);
            }
        } else {
            //如果不开放或不是贩卖的宠物，弹出错误提示
            showmessage($kd8c_pet_showmsg['msg1'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=1&page=' . $page);
        }
    }
} //判断act操作，2为恢复HP - X2
else if($_G['gp_act'] == 2) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //查找所要恢复的这只PM
    //备注：由于恢复HP的PET都要带在身上，所以只查在身上的PET
    $query = DB::query("SELECT * FROM cdb_kd8c_my_pm LEFT JOIN cdb_kd8c_pet_pmlist ON cdb_kd8c_my_pm.id_quanguo=cdb_kd8c_pet_pmlist.id_quanguo WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "' AND showtype IN ('1','2')");
    //获取该PM的数据集
    $pet = DB::fetch($query);
    //如果数据集为真，即该PM存在
    if($pet) {
        //计算该PM的HP最大值
        $max_hp = hpjisuan($pet['hp_zz'], $pet['hp_gt'], $pet['hp_nl'], $pet['lv']);
        //如果现在的HP小于等于0，即死亡
        if($pet['hp_now'] <= 0) {
            //把HP归为0
            $pet['hp_now'] = 0;
            //计算需要恢复多少HP，并计算恢复价钱
            $hpmoney = $max_hp - $pet['hp_now'];
            //如果价钱为负的话，把价钱定为0
            if($hpmoney < 0) {
                $hpmoney = 0;
            }
            //计算复活并恢复的钱
            $hpmoney = $hpmoney;
        } //否则，即没有死亡
        else {
            //计算需要恢复多少HP，并计算恢复价钱
            $hpmoney = $max_hp - $pet['hp_now'];
            //如果价钱为负的话，把价钱定为0
            if($hpmoney < 0) {
                $hpmoney = 0;
            }
        }
        //如果用户的钱少于恢复HP应付的价钱
        if($user_extcredits[$moneycredits] < $hpmoney) {
            //提示身上现金不足
            showmessage($kd8c_pet_showmsg['msg5'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
        } //否则，即有足够现金
        else {
            //恢复PM的HP
            DB::query("UPDATE cdb_kd8c_my_pm SET hp_now=" . $max_hp . " WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "'");
            //扣除应付的价钱
            DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "-" . $hpmoney . " WHERE uid='" . $_G[uid] . "'");
            DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "+" . $hpmoney . " WHERE uid='70490'");
            //提示恢复HP成功和扣除的钱
            showmessage($pet['pmname'] . '已经恢复体力，花费' . $hpmoney . '元', 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
        }
    } //否则，即该PM不存在
    else {
        //提示宠物不存在
        showmessage($kd8c_pet_showmsg['msg6'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
    }
} //判断act操作，3为按摩（亲密度进化） - X2
else if($_G['gp_act'] == 3) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //备注：由于按摩要将PET带在身上，所以只查在身上的PET
    $query = DB::query("SELECT * FROM cdb_kd8c_my_pm WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "' AND showtype IN ('1','2')");
    $my    = DB::fetch($query);
    //如果用户的钱少于按摩应付的价钱
    if($user_extcredits[$moneycredits] < $anmoamount) {
        //提示身上现金不足
        showmessage($kd8c_pet_showmsg['msg5'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
    } else {
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "-" . $anmoamount . " WHERE uid='" . $_G[uid] . "'");
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "+" . $anmoamount . " WHERE uid='70490'");
        if($my['daoju'] == 13) {
            showmessage($kd8c_pet_showmsg['msg11'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
        } else {
            //皮丘进化皮卡丘
            $update['172'] = 25;
            //皮进化皮皮
            $update['173'] = 35;
            //胖丁进化胖可丁
            $update['174'] = 39;
            //大嘴蝠进化叉子蝠
            $update['42'] = 169;
            //吉利蛋进化快乐蛋
            $update['113'] = 242;
            //伊布进化光布
            $update['133_1'] = 196;
            //伊布进化影布
            $update['133_2'] = 197;
            //波克比进化飞翼兽
            $update['175'] = 176;
            //露莉莉进化玛利露
            $update['298'] = 183;
            //玫瑰花苞进化双色玫瑰
            $update['406_1'] = 315;
            //棉花兔进化女郎兔
            $update['427'] = 428;
            //金铃进化奇美铃
            $update['433_1'] = 358;
            //刚比兽进化卡比兽
            $update['446'] = 143;
            //鲁力欧进化鲁卡力欧
            $update['447_1'] = 448;

            //球蝙蝠进化
            $update['527'] = 528;
            //护子虫进化
            $update['541'] = 542;

            //亲密度特殊进化判断
            //依布
            if($my['id_quanguo'] == 133) {
                if(date(H, time()) >= 7 && date(H, time()) < 19) {
                    $my['id_quanguo'] = $my['id_quanguo'] . "_1";
                } else {
                    $my['id_quanguo'] = $my['id_quanguo'] . "_2";
                }
                //$luckly=floor(rand(1,2));
                //$my['id_quanguo']=$my['id_quanguo']."_".$luckly;
            }
            //玫瑰花苞
            if($my['id_quanguo'] == 406) {
                if(dayornight() == "day") {
                    $my['id_quanguo'] = $my['id_quanguo'] . "_1";
                }
            }
            //金铃
            if($my['id_quanguo'] == 433) {
                if(dayornight() == "night") {
                    $my['id_quanguo'] = $my['id_quanguo'] . "_1";
                }
            }
            //鲁力欧
            if($my['id_quanguo'] == 447) {
                if(dayornight() == "day") {
                    $my['id_quanguo'] = $my['id_quanguo'] . "_1";
                }
            }


            //进行进化
            if($update[$my['id_quanguo']]) {
                $luckly = floor(rand(1, 20));
                if($luckly == 10) {
                    DB::query("UPDATE cdb_kd8c_my_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "'");
                    //更新图鉴信息
                    if($update[$my['id_quanguo']] > 0) add_pet_history($_G['uid'], $_G['username'], $update[$my['id_quanguo']]);
                    showmessage($kd8c_pet_showmsg['msg13'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
                } else {
                    showmessage($kd8c_pet_showmsg['msg12'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
                }
            } else {
                showmessage($kd8c_pet_showmsg['msg14'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
            }
        }
    }
} //判断act操作，4为美容（美丽度进化） - X2
else if($_G['gp_act'] == 4) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //备注：由于美容需要把PET带在身上，所以只查在身上的PET
    $query = DB::query("SELECT * FROM cdb_kd8c_my_pm WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "' AND showtype IN ('1','2')");
    $my    = DB::fetch($query);
    //如果用户的钱少于美容应付的价钱
    if($user_extcredits[$moneycredits] < $meirongamount) {
        //提示身上现金不足
        showmessage($kd8c_pet_showmsg['msg5'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
    } else {
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "-" . $meirongamount . " WHERE uid='" . $_G[uid] . "'");
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "+" . $meirongamount . " WHERE uid='70490'");
        if($my['daoju'] == 13) {
            showmessage($kd8c_pet_showmsg['msg7'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
        } else {
            //丑鲤鱼进化美丽龙
            $update['349'] = 350;
            //伊布进化仙布
            $update['133'] = 700;
            if($update[$my['id_quanguo']]) {
                $luckly = floor(rand(1, 20));
                if($luckly == 10) {
                    DB::query("UPDATE cdb_kd8c_my_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "'");
                    //更新图鉴信息
                    if($update[$my['id_quanguo']] > 0) add_pet_history($_G['uid'], $_G['username'], $update[$my['id_quanguo']]);
                    showmessage($kd8c_pet_showmsg['msg9'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
                } else {
                    showmessage($kd8c_pet_showmsg['msg8'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
                }
            } else {
                showmessage($kd8c_pet_showmsg['msg10'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
            }
        }
    }
} //判断act操作，5为通信（通信进化） - X2
else if($_G['gp_act'] == 5) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //备注：由于通信需要把PET带在身上，所以在此查在身上的PET
    $query = DB::query("SELECT * FROM cdb_kd8c_my_pm WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "' AND showtype IN ('1','2')");
    $my    = DB::fetch($query);
    //如果用户的钱少于美容应付的价钱
    if($user_extcredits[$moneycredits] < $tongxinamount) {
        //提示身上现金不足
        showmessage($kd8c_pet_showmsg['msg5'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
    } else {
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "-" . $tongxinamount . " WHERE uid='" . $_G[uid] . "'");
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "+" . $tongxinamount . " WHERE uid='70490'");
        if($my['daoju'] == 13) {
            showmessage($kd8c_pet_showmsg['msg15'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
        } else {
            //勇吉拉进化胡地
            $update['64'] = 65;
            //豪力化怪力
            $update['67'] = 68;
            //隆隆石进化隆隆岩
            $update['75'] = 76;
            //鬼斯通进化耿鬼
            $update['93'] = 94;

            //
            $update['525'] = 526;
            //
            $update['533'] = 534;
            //
            $update['588'] = 589;
            //
            $update['616'] = 617;

            if($my['daoju'] == 52) {
                $update['682'] = 683;
            } else if($my['daoju'] == 53) {
                $update['684'] = 685;
            }
            //
            $update['708'] = 709;
            //
            $update['710'] = 711;
            if($update[$my['id_quanguo']]) {
                DB::query("UPDATE cdb_kd8c_my_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' AND pmid='" . $cmd . "'");
                //更新图鉴信息
                if($update[$my['id_quanguo']] > 0) add_pet_history($_G['uid'], $_G['username'], $update[$my['id_quanguo']]);
                showmessage($kd8c_pet_showmsg['msg16'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
            } else {
                showmessage($kd8c_pet_showmsg['msg17'], 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=0');
            }
        }
    }
} //判断act操作，6为转移
else if($_G['gp_act'] == 6) {
    showmessage('暂无信息。', 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=3');
} //判断act操作，默认为显示界面
else {
    //设置标题
    $navigation = $navigation . $kd8c['system_name'] . " - " . $kd8c_pet['system_name'];
    //设置版本信息
    $kd8c['hack_name']       = $kd8c_pet['system_name'];
    $kd8c['kd8chackVersion'] = $kd8c_pet['hackVersion'];
    $kd8c['copyright']       = $kd8c_tool['copyright'];
    //判断参数，1为显示宠物小精灵商店
    if($_G['gp_cmd'] == 1 || !isset($_G['gp_cmd'])) {
        $kd8c_pet_title['navigation'] = $kd8c_pet_title['title_name2'];
        $page                         = $_G['gp_page'];
        //分页设置开始
        $query       = DB::query("SELECT count(*) FROM cdb_kd8c_pet_pmlist WHERE buy='开放' ORDER BY id_quanguo");
        $threadcount = DB::result($query, 0);

        $page        = intval($page) ? intval($page) : 1;
        $start_limit = ($page - 1) * 20;
        $multipage   = multi($threadcount, 20, $page, "plugin.php?id=kd8c:kd8c_pet&act=0&cmd=1");

        //显示分页结果
        $query = DB::query("SELECT * FROM cdb_kd8c_pet_pmlist WHERE buy='开放' ORDER BY id_quanguo LIMIT $start_limit, 20");
        $i     = 1;
        while($pet = DB::fetch($query)) {
            //制定属性显示格式
            $pet['shuxing'] = $pet['shuxing1'];
            if($pet['shuxing2'] != "-") {
                $pet['shuxing'] .= " + " . $pet['shuxing2'];
            }
            //制定输出
            if($i % 2 == 1) {
                $pet_selllist .= "<tr class=\"altbg2\" valign=\"top\"><td width=\"50%\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr valign=\"top\"><td width=\"161\"><img src=\"images/kd8c/pet/pm/" . $pet['id_quanguo'] . "_1.jpg\"><br><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c_pet\"><input type=\"submit\" style=\"width=156;height=27\" value=\"" . $kd8c_pet_name['buy'] . $pet['name_cn'] . "\" name=\"button\"><input type=\"hidden\" value=\"1\" name=\"act\"><input type=\"hidden\" value=\"" . $page . "\" name=\"page\"><input type=\"hidden\" value=\"" . $pet['id_quanguo'] . "\" name=\"cmd\"></form></td><td><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\"><td>" . $kd8c_pet_name['id_quanguo'] . $pet['id_quanguo'] . "</td><td colspan=\"2\">" . $kd8c_pet_name['name_cn'] . $pet['name_cn'] . "</td></tr><tr class=\"altbg2\"><td colspan=\"3\">" . $kd8c_pet_name['shuxing'] . $pet['shuxing'] . "</td></tr><tr class=\"altbg2\"><td colspan=\"3\">" . $kd8c_pet_name['zz'] . "</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['hp_zz'] . $pet['hp_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['gongji_zz'] . $pet['gongji_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['fangyu_zz'] . $pet['fangyu_zz'] . "</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['minjie_zz'] . $pet['minjie_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['tegong_zz'] . $pet['tegong_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['tefang_zz'] . $pet['tefang_zz'] . "</td></tr><tr class=\"altbg2\"><td colspan=\"3\"><font color=red><b>" . $kd8c_pet_name['price'] . $pet['price'] . "</b></font></td></tr></table></td></tr></table></td>";
            } else {
                $pet_selllist .= "<td width=\"50%\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr valign=\"top\"><td width=\"161\"><img src=\"images/kd8c/pet/pm/" . $pet['id_quanguo'] . "_1.jpg\"><br><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c_pet\"><input type=\"submit\" style=\"width=156;height=27\" value=\"" . $kd8c_pet_name['buy'] . $pet['name_cn'] . "\" name=\"button\"><input type=\"hidden\" value=\"1\" name=\"act\"><input type=\"hidden\" value=\"" . $page . "\" name=\"page\"><input type=\"hidden\" value=\"" . $pet['id_quanguo'] . "\" name=\"cmd\"></form></td><td><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\"><td>" . $kd8c_pet_name['id_quanguo'] . $pet['id_quanguo'] . "</td><td colspan=\"2\">" . $kd8c_pet_name['name_cn'] . $pet['name_cn'] . "</td></tr><tr class=\"altbg2\"><td colspan=\"3\">" . $kd8c_pet_name['shuxing'] . $pet['shuxing'] . "</td></tr><tr class=\"altbg2\"><td colspan=\"3\">" . $kd8c_pet_name['zz'] . "</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['hp_zz'] . $pet['hp_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['gongji_zz'] . $pet['gongji_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['fangyu_zz'] . $pet['fangyu_zz'] . "</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['minjie_zz'] . $pet['minjie_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['tegong_zz'] . $pet['tegong_zz'] . "</td><td width=\"33%\">" . $kd8c_pet_name['tefang_zz'] . $pet['tefang_zz'] . "</td></tr><tr class=\"altbg2\"><td colspan=\"3\"><font color=red><b>" . $kd8c_pet_name['price'] . $pet['price'] . "</b></font></td></tr></table></td></tr></table></td></tr>";
            }
            $i++;
        }
        if($i % 2 != 1) {
            $pet_selllist .= "<td width=\"50%\"> </td></tr>";
        }
        //导入模板
        include template('kd8c:kd8c_pet_pmshop');
    } else
        //判断参数，2为显示兑换中心
        if($_G['gp_cmd'] == 2) {
        } else
            //判断参数，3为显示旧箱子入口
            if($_G['gp_cmd'] == 3) {
                $kd8c_pet_title['navigation'] = $kd8c_pet_title['title_name4'];
                /*
                $query = DB::query("SELECT boxsize FROM ".DB::table('common_member_count')." WHERE uid='".$_G[uid]."'");
                $kd8c_user2 = $db->result($query, 0);
                $boxsize=explode(",",$kd8c_user2);
                $petboxsize=$boxsize[0];
                */
                $query = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic FROM cdb_kd8c_my_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo WHERE uid='" . $_G[uid] . "' ORDER BY showtype,pmid");
                $i     = 1;
                while($my = DB::fetch($query)) {
                    //制定图片列表
                    $my['selected'][$my['pic']] = " selected";
                    $piclist                    = "<select style=\"width=156\" name=\"selectlist\" onchange=\"document.getElementById('petimg" . $i . "').innerHTML='<img src=images/kd8c/pet/pm/" . $my['id_quanguo'] . "_'+mypet" . $i . ".selectlist.value+'.jpg>';\">";
                    for($j = 1; $j <= $my['maxpic']; $j++) {
                        $piclist .= "<option value=\"" . $j . "\" " . $my['selected'][$j] . ">" . $my['name_cn'] . "图片" . $j . "</option>";
                    }
                    $piclist .= "</select>";
                    //制定属性显示格式
                    $my['shuxing'] = $my['shuxing1'];
                    if($my['shuxing2'] != "-") {
                        $my['shuxing'] .= " + " . $my['shuxing2'];
                    }
                    //计算经验百分比
                    $my['exp'] = floor(($my['exp'] - pow($my['lv'], 2)) * 100 / (pow($my['lv'] + 1, 2) - pow($my['lv'], 2)));
                    //计算能力
                    $my['hp_zz'] = hpjisuan($my['hp_zz'], $my['hp_gt'], $my['hp_nl'], $my['lv']);
                    if($my['hp_now'] > $my['hp_zz']) {
                        $my['hp_now'] = $my['hp_zz'];
                    }

                    //制定输出
                    $kd8c_selllist .= "
			<tr class=\"fl_row\">
				<td width=\"1\"><img src=\"images/kd8c/pet/icon/" . $my['id_quanguo'] . ".gif\"></td>
				<td width=\"40\">" . $kd8c_pet_name['id_quanguo'] . $my['id_quanguo'] . "</td>
				<td>" . $kd8c_pet_name['name_cn'] . $my['pmname'] . "<input type=\"hidden\" value=\"1\" name=\"act\"><input type=\"hidden\" value=\"" . $my['pmid'] . "\" name=\"cmd\"></td>
				<td width=\"15\">" . $my['sex'] . "</td>
				<td width=\"100\">" . $kd8c_pet_name['shuxing'] . $my['shuxing'] . "</td>
				<td width=\"50\">" . $kd8c_pet_name['lv'] . $my['lv'] . "</td>
				<td width=\"70\">" . $kd8c_pet_name['exp'] . $my['exp'] . "%</td>
				<td width=\"25\"><img src=\"images/kd8c/tool/ball/" . $my['pmball'] . ".gif\"></td>
				<td width=\"25\"><img src=\"images/kd8c/tool/zhuangbei/" . $my['daoju'] . ".gif\"></td>
				<td width=\"30\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=6&cmd=" . $my['pmid'] . "\">转移</a></td>
			</tr>
			";
                }
                $kd8c_selllist = "
		<tr class=\"fl_row\" valign=\"top\">
			<td width=\"100%\">
				<form method=\"POST\" action=\"kd8c.php\" name=\"mypet" . $i . "\">
					<table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\">
			" . $kd8c_selllist . "
					</table>
				</form>
			</td>
		</tr>";
                if(!$kd8c_selllist) {
                    $kd8c_selllist = "<tr class=\"fl_row\" valign=\"top\"><td width=\"100%\" colspan=\"3\" align=\"center\">" . $kd8c_kd8c_showmsg['msg9'] . "</td></tr>";
                }
                //导入模板
                include template('kd8c:kd8c_kd8c_old_pcpmbox');
            } else //判断参数，0为显示精灵中心
            {
                $kd8c_pet_title['navigation'] = $kd8c_pet_title['title_name1'];
                //备注：由于显示身上现有的PET，所以只查身上现有的PET
                $query = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2 FROM cdb_kd8c_my_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo WHERE uid='" . $_G[uid] . "' AND showtype IN ('1','2') ORDER BY showtype,pmid");
                $i     = 1;
                while($pet = DB::fetch($query)) {
                    //制定属性显示格式
                    $pet['shuxing'] = $pet['shuxing1'];
                    if($pet['shuxing2'] != "-") {
                        $pet['shuxing'] .= " + " . $pet['shuxing2'];
                    }
                    //计算经验百分比
                    $pet['exp'] = floor(($pet['exp'] - pow($pet['lv'], 2)) * 100 / (pow($pet['lv'] + 1, 2) - pow($pet['lv'], 2)));
                    //计算能力
                    $pet['hp_zz'] = hpjisuan($pet['hp_zz'], $pet['hp_gt'], $pet['hp_nl'], $pet['lv']);
                    if($pet['hp_now'] > $pet['hp_zz']) {
                        $pet['hp_now'] = $pet['hp_zz'];
                    }
                    $hpamount         = $pet['hp_zz'] - $pet['hp_now'];
                    $pet['gongji_zz'] = pm5vjisuan($pet['gongji_zz'], $pet['gongji_gt'], $pet['gongji_nl'], $pet['lv']);
                    $pet['fangyu_zz'] = pm5vjisuan($pet['fangyu_zz'], $pet['fangyu_gt'], $pet['fangyu_nl'], $pet['lv']);
                    $pet['minjie_zz'] = pm5vjisuan($pet['minjie_zz'], $pet['minjie_gt'], $pet['minjie_nl'], $pet['lv']);
                    $pet['tegong_zz'] = pm5vjisuan($pet['tegong_zz'], $pet['tegong_gt'], $pet['tegong_nl'], $pet['lv']);
                    $pet['tefang_zz'] = pm5vjisuan($pet['tefang_zz'], $pet['tefang_gt'], $pet['tefang_nl'], $pet['lv']);
                    //制定输出
                    if($i % 2 == 1) {
                        $pet_selllist .= "<tr class=\"altbg2\" valign=\"top\"><td width=\"50%\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr valign=\"top\"><td width=\"161\"><img src=\"images/kd8c/pet/pm/" . $pet['id_quanguo'] . "_" . $pet['pic'] . ".jpg\"><br></td><td><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\"><td width=\"33%\">" . $kd8c_pet_name['id_quanguo'] . $pet['id_quanguo'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['name_cn'] . $pet['pmname'] . "</td></tr><tr class=\"altbg2\"><td>性别:" . $pet['sex'] . "</td><td colspan=\"2\">" . $kd8c_pet_name['shuxing'] . $pet['shuxing'] . "</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['lv'] . $pet['lv'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['exp'] . $pet['exp'] . "%</td></tr><tr class=\"altbg2\"><td colspan=\"3\">" . $kd8c_pet_name['hp_zz'] . $pet['hp_now'] . "/" . $pet['hp_zz'] . "[恢复需要" . $hpamount . "口袋元]</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['zhuangbei'] . "</td><td width=\"33%\"><img src=\"images/kd8c/tool/ball/" . $pet['pmball'] . ".gif\"></td><td width=\"33%\"><img src=\"images/kd8c/tool/zhuangbei/" . $pet['daoju'] . ".gif\"></td></tr></table></td></tr><tr><td height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\" align=\"center\"><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=2&cmd=" . $pet['pmid'] . "\">恢复[" . $hpamount . $moneyunit . "]</a></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=3&cmd=" . $pet['pmid'] . "\">按摩[" . $anmoamount . $moneyunit . "]</a></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=4&cmd=" . $pet['pmid'] . "\">美容[" . $meirongamount . $moneyunit . "]</a></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=5&cmd=" . $pet['pmid'] . "\">通信[" . $tongxinamount . $moneyunit . "]</a></td></tr></table></td></tr></table></td>";
                    } else {
                        $pet_selllist .= "<td width=\"50%\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr valign=\"top\"><td width=\"161\"><img src=\"images/kd8c/pet/pm/" . $pet['id_quanguo'] . "_" . $pet['pic'] . ".jpg\"><br></td><td><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\"><td width=\"33%\">" . $kd8c_pet_name['id_quanguo'] . $pet['id_quanguo'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['name_cn'] . $pet['pmname'] . "</td></tr><tr class=\"altbg2\"><td>性别:" . $pet['sex'] . "</td><td colspan=\"2\">" . $kd8c_pet_name['shuxing'] . $pet['shuxing'] . "</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['lv'] . $pet['lv'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['exp'] . $pet['exp'] . "%</td></tr><tr class=\"altbg2\"><td colspan=\"3\">" . $kd8c_pet_name['hp_zz'] . $pet['hp_now'] . "/" . $pet['hp_zz'] . "[恢复需要" . $hpamount . "口袋元]</td></tr><tr class=\"altbg2\"><td width=\"33%\">" . $kd8c_pet_name['zhuangbei'] . "</td><td width=\"33%\"><img src=\"images/kd8c/tool/ball/" . $pet['pmball'] . ".gif\"></td><td width=\"33%\"><img src=\"images/kd8c/tool/zhuangbei/" . $pet['daoju'] . ".gif\"></td></tr></table></td></tr><tr><td height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\" align=\"center\"><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=2&cmd=" . $pet['pmid'] . "\">恢复[" . $hpamount . $moneyunit . "]</a></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=3&cmd=" . $pet['pmid'] . "\">按摩[" . $anmoamount . $moneyunit . "]</a></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=4&cmd=" . $pet['pmid'] . "\">美容[" . $meirongamount . $moneyunit . "]</a></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c_pet&act=5&cmd=" . $pet['pmid'] . "\">通信[" . $tongxinamount . $moneyunit . "]</a></td></tr></table></td></tr></table></td></tr>";
                    }
                    $i++;
                }
                if($i % 2 != 1) {
                    $pet_selllist .= "<td width=\"50%\"> </td></tr>";
                }
                if(!$pet_selllist) {
                    $pet_selllist = "<tr class=\"altbg2\" valign=\"top\"><td width=\"100%\" colspan=\"2\" align=\"center\">" . $kd8c_kd8c_showmsg['msg9'] . "</td></tr>";
                }
                //导入模板
                include template('kd8c:kd8c_pet_pmcenter');
            }

}
?>