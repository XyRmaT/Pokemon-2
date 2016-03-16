<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if(!$_G['uid']) {
    showmessage('to_login', '', '', array('login' => 1));
}

@include DISCUZ_ROOT . './source/plugin/kd8c/kd8c.lang.php';
require_once DISCUZ_ROOT . './source/plugin/kd8c/kd8c_common.inc.php';

function replace($str) {
    $str = str_replace("'", "\'", $str);
    $str = str_replace("\"", "\\\"", $str);
    return $str;
}

//判断act操作，1为修改宠物资料 - X2
if($_G['gp_act'] == 1) {
    //将编号参数强制转换成整型
    $cmd        = intval($_G['gp_cmd']);
    $selectlist = intval($_G['gp_selectlist']);
    $pmname     = htmlspecialchars($_G['gp_pmname']);
    //保存修改宠物的资料
    DB::query("UPDATE cdb_kd8c_my_new_pm SET pmname='" . replace($pmname) . "',pic='" . $selectlist . "' WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
    //提示宠物资料修改成功
    showmessage($kd8c_kd8c_showmsg['msg1'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
} //判断act操作，2为卸下道具 - X2
else if($_G['gp_act'] == 2) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //查询宠物身上原本有没有道具
    $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
    $my    = DB::fetch($query);
    if($my['daoju'] != 0) {
        //如果有道具，卸下道具并放入包中
        DB::query("INSERT INTO cdb_kd8c_my_tool ( `mytoolid` , `toolid` , `uid` ) VALUES ('', '$my[daoju]', '$_G[uid]')");
        DB::query("UPDATE cdb_kd8c_my_new_pm SET daoju=0 WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
        if($my['id_quanguo'] >= 10000 && $my['id_quanguo'] < 20000) {
            //如果队长本为mega形态，进行mega退化
            //如为分XY的mega进化，编号-10001，否则-10000；
            if($my['id_quanguo'] == 10007 || $my['id_quanguo'] == 10151) {
                $update = $my['id_quanguo'] - 10001;
            } else {
                $update = $my['id_quanguo'] - 10000;
            }
            //进行退化操作
            DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update . " WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
            showmessage($kd8c_kd8c_showmsg['msg31'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
        } else {
            //提示卸下道具成功
            showmessage($kd8c_kd8c_showmsg['msg2'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
        }
    } else {
        //如果没有道具或宠物信息非法，进行提示
        showmessage($kd8c_kd8c_showmsg['msg4'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    }
} //判断act操作，3为提升到队首 - X2
else if($_G['gp_act'] == 3) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //将队首宠物宠物放下，将选择的宠物提到队首
    //PS：如果提升的宠物数据非法，则会全部放下，不过提示成功
    $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm where uid='" . $_G[uid] . "' AND showtype='1' ORDER BY showtype,pmid");
    $my    = DB::fetch($query);

    //如果队首宠物不存在，进行提升
    if($my['uid'] <= 0) {
        DB::query("UPDATE cdb_kd8c_my_new_pm SET showtype=1 WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
        //新队长判定是否Mega进化
        $query  = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE pmid='" . $cmd . "'");
        $my2    = DB::fetch($query);
        $toolid = $my2['daoju'];
        if($toolid >= 54 && $toolid <= 101) {
            //Mega进化
            //判断道具ID，54为妙蛙花结晶
            if($toolid == 54) {
                $update['3'] = 10003;
            } else if($toolid == 55) {
                $update['6'] = 10006;
            } else if($toolid == 56) {
                $update['6'] = 10007;
            } else if($toolid == 57) {
                $update['9'] = 10009;
            } else if($toolid == 58) {
                $update['65'] = 10065;
            } else if($toolid == 59) {
                $update['94'] = 10094;
            } else if($toolid == 60) {
                $update['115'] = 10115;
            } else if($toolid == 61) {
                $update['127'] = 10127;
            } else if($toolid == 62) {
                $update['130'] = 10130;
            } else if($toolid == 63) {
                $update['142'] = 10142;
            } else if($toolid == 64) {
                $update['150'] = 10150;
            } else if($toolid == 65) {
                $update['150'] = 10151;
            } else if($toolid == 66) {
                $update['181'] = 10181;
            } else if($toolid == 67) {
                $update['212'] = 10212;
            } else if($toolid == 68) {
                $update['214'] = 10214;
            } else if($toolid == 69) {
                $update['229'] = 10229;
            } else if($toolid == 70) {
                $update['248'] = 10248;
            } else if($toolid == 71) {
                $update['257'] = 10257;
            } else if($toolid == 72) {
                $update['282'] = 10282;
            } else if($toolid == 73) {
                $update['303'] = 10303;
            } else if($toolid == 74) {
                $update['306'] = 10306;
            } else if($toolid == 75) {
                $update['308'] = 10308;
            } else if($toolid == 76) {
                $update['310'] = 10310;
            } else if($toolid == 77) {
                $update['354'] = 10354;
            } else if($toolid == 78) {
                $update['359'] = 10359;
            } else if($toolid == 79) {
                $update['445'] = 10445;
            } else if($toolid == 80) {
                $update['448'] = 10448;
            } else if($toolid == 81) {
                $update['460'] = 10460;
            } else if($toolid == 82) {
                $update['15'] = 10015;
            } else if($toolid == 83) {
                $update['18'] = 10018;
            } else if($toolid == 84) {
                $update['80'] = 10080;
            } else if($toolid == 85) {
                $update['208'] = 10208;
            } else if($toolid == 86) {
                $update['254'] = 10254;
            } else if($toolid == 87) {
                $update['260'] = 10260;
            } else if($toolid == 88) {
                $update['302'] = 10302;
            } else if($toolid == 89) {
                $update['319'] = 10319;
            } else if($toolid == 90) {
                $update['323'] = 10323;
            } else if($toolid == 91) {
                $update['334'] = 10334;
            } else if($toolid == 92) {
                $update['362'] = 10362;
            } else if($toolid == 93) {
                $update['373'] = 10373;
            } else if($toolid == 94) {
                $update['376'] = 10376;
            } else if($toolid == 95) {
                $update['380'] = 10380;
            } else if($toolid == 96) {
                $update['381'] = 10381;
            } else if($toolid == 97) {
                $update['384'] = 10384;
            } else if($toolid == 98) {
                $update['428'] = 10428;
            } else if($toolid == 99) {
                $update['475'] = 10475;
            } else if($toolid == 100) {
                $update['531'] = 10531;
            } else if($toolid == 101) {
                $update['719'] = 10719;
            }
            //判断Mega石是否正确
            if($update[$my2['id_quanguo']]) {
                //是的话进行进化操作
                DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update[$my2['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' and pmid='" . $my2['pmid'] . "'");
            }
        }
    } //如果队首宠物存在且正经验放下队首宠物，并进行提升
    else if($my['exp'] > 0 && $my['uid'] > 0) {
        if($my['id_quanguo'] >= 10000 && $my['id_quanguo'] < 20000) {
            //如果原队长本为mega形态，进行mega退化
            //如为分XY的mega进化，编号-10001，否则-10000；
            if($my['id_quanguo'] == 10007 || $my['id_quanguo'] == 10151) {
                $update = $my['id_quanguo'] - 10001;
            } else {
                $update = $my['id_quanguo'] - 10000;
            }
            //进行退化操作
            DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update . " WHERE uid='" . $_G[uid] . "' and pmid='" . $my['pmid'] . "'");
        }
        //新队长判定是否Mega进化
        $query  = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE pmid='" . $cmd . "'");
        $my     = DB::fetch($query);
        $toolid = $my['daoju'];
        if($toolid >= 54 && $toolid <= 101) {
            //Mega进化
            //判断道具ID，54为妙蛙花结晶
            if($toolid == 54) {
                $update['3'] = 10003;
            } else if($toolid == 55) {
                $update['6'] = 10006;
            } else if($toolid == 56) {
                $update['6'] = 10007;
            } else if($toolid == 57) {
                $update['9'] = 10009;
            } else if($toolid == 58) {
                $update['65'] = 10065;
            } else if($toolid == 59) {
                $update['94'] = 10094;
            } else if($toolid == 60) {
                $update['115'] = 10115;
            } else if($toolid == 61) {
                $update['127'] = 10127;
            } else if($toolid == 62) {
                $update['130'] = 10130;
            } else if($toolid == 63) {
                $update['142'] = 10142;
            } else if($toolid == 64) {
                $update['150'] = 10150;
            } else if($toolid == 65) {
                $update['150'] = 10151;
            } else if($toolid == 66) {
                $update['181'] = 10181;
            } else if($toolid == 67) {
                $update['212'] = 10212;
            } else if($toolid == 68) {
                $update['214'] = 10214;
            } else if($toolid == 69) {
                $update['229'] = 10229;
            } else if($toolid == 70) {
                $update['248'] = 10248;
            } else if($toolid == 71) {
                $update['257'] = 10257;
            } else if($toolid == 72) {
                $update['282'] = 10282;
            } else if($toolid == 73) {
                $update['303'] = 10303;
            } else if($toolid == 74) {
                $update['306'] = 10306;
            } else if($toolid == 75) {
                $update['308'] = 10308;
            } else if($toolid == 76) {
                $update['310'] = 10310;
            } else if($toolid == 77) {
                $update['354'] = 10354;
            } else if($toolid == 78) {
                $update['359'] = 10359;
            } else if($toolid == 79) {
                $update['445'] = 10445;
            } else if($toolid == 80) {
                $update['448'] = 10448;
            } else if($toolid == 81) {
                $update['460'] = 10460;
            } else if($toolid == 82) {
                $update['15'] = 10015;
            } else if($toolid == 83) {
                $update['18'] = 10018;
            } else if($toolid == 84) {
                $update['80'] = 10080;
            } else if($toolid == 85) {
                $update['208'] = 10208;
            } else if($toolid == 86) {
                $update['254'] = 10254;
            } else if($toolid == 87) {
                $update['260'] = 10260;
            } else if($toolid == 88) {
                $update['302'] = 10302;
            } else if($toolid == 89) {
                $update['319'] = 10319;
            } else if($toolid == 90) {
                $update['323'] = 10323;
            } else if($toolid == 91) {
                $update['334'] = 10334;
            } else if($toolid == 92) {
                $update['362'] = 10362;
            } else if($toolid == 93) {
                $update['373'] = 10373;
            } else if($toolid == 94) {
                $update['376'] = 10376;
            } else if($toolid == 95) {
                $update['380'] = 10380;
            } else if($toolid == 96) {
                $update['381'] = 10381;
            } else if($toolid == 97) {
                $update['384'] = 10384;
            } else if($toolid == 98) {
                $update['428'] = 10428;
            } else if($toolid == 99) {
                $update['475'] = 10475;
            } else if($toolid == 100) {
                $update['531'] = 10531;
            } else if($toolid == 101) {
                $update['719'] = 10719;
            }
            //判断Mega石是否正确
            if($update[$my['id_quanguo']]) {
                //是的话进行进化操作
                DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' and pmid='" . $my['pmid'] . "'");
            }
        }
        DB::query("UPDATE cdb_kd8c_my_new_pm SET showtype=2 WHERE uid='" . $_G[uid] . "' and showtype=1");
        DB::query("UPDATE cdb_kd8c_my_new_pm SET showtype=1 WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
    }
    //如果队首宠物存在并为负经验，不进行提升

    //提升后无论是否成功都进行提示成功
    showmessage($kd8c_kd8c_showmsg['msg3'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
} //判断act操作，4为放生宠物 - X2
else if($_G['gp_act'] == 4) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //根据参数直接删除宠物
    //PS：警告提示在前台JS上，如果放生宠物数据非法，会提示放生成功，但不会进行有效操作
    $my = DB::fetch(DB::query("SELECT * FROM cdb_kd8c_my_new_pm where uid='" . $_G[uid] . "' AND pmid='$cmd'"));
    if($my['lock'] == 1)
        showmessage("该宠物已经锁定，不能放生。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    else {
        if(rand(0, 5) == 1)
            DB::query("Insert into `cdb_kd8c_wondertrade` SET `luid`='" . $my['luid'] . "', `id_quanguo`='" . $my['id_quanguo'] . "',`pmname`='" . $my['pmname'] . "',`hp_now`='" . $my['hp_now'] . "',`hp_gt`='" . $my['hp_gt'] . "',`gongji_gt`='" . $my['gongji_gt'] . "',`fangyu_gt`='" . $my['fangyu_gt'] . "',`minjie_gt`='" . $my['minjie_gt'] . "',`tegong_gt`='" . $my['tegong_gt'] . "',`tefang_gt`='" . $my['tefang_gt'] . "',`hp_nl`='" . $my['hp_nl'] . "',`gongji_nl`='" . $my['gongji_nl'] . "',`fangyu_nl`='" . $my['fangyu_nl'] . "',`minjie_nl`='" . $my['minjie_nl'] . "',`tegong_nl`='" . $my['tegong_nl'] . "',`tefang_nl`='" . $my['tefang_nl'] . "',`daoju`='" . $my['daoju'] . "',`pmball`='" . $my['pmball'] . "',`exp`='" . $my['exp'] . "',`lv`='" . $my['lv'] . "',`pic`='" . $my['pic'] . "',`sex`='" . $my['sex'] . "',`shine`='" . $my['shine'] . "'");

        DB::query("DELETE FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
    }
    //放生后无论是否成功都进行提示成功
    showmessage($kd8c_kd8c_showmsg['msg6'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
} //判断act操作，5为使用、装备道具 - X2
else if($_G['gp_act'] == 5) {
    //将编号参数强制转换成整型
    $cmd        = intval($_G['gp_cmd']);
    $selectlist = intval($_G['gp_selectlist']);
    //查询选择的宠物数据
    $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE pmid='" . $selectlist . "'");
    $mypm  = DB::fetch($query);
    //根据我的道具ID查询道具ID
    $query  = DB::query("SELECT m.toolid,m.parameter,t.toolorder,t.tooluseobject FROM cdb_kd8c_my_tool m LEFT JOIN cdb_kd8c_tool_toollist t ON m.toolid=t.toolid WHERE m.uid='" . $_G[uid] . "' and m.mytoolid='" . $cmd . "'");
    $my     = DB::fetch($query);
    $toolid = $my['toolid'];
    //判断使用对象，1为宠物
    if($my['tooluseobject'] == 1) {
        //判断使用类型，1为使用型
        if($my['toolorder'] == 1) {
            //判断道具ID，1为水之石
            if($toolid == 1) {
                $update['61']  = 62;
                $update['90']  = 91;
                $update['120'] = 121;
                $update['133'] = 134;
                $update['271'] = 272;
                $update['515'] = 516;
                $my['update']  = 1;
            } //判断道具ID，2为雷之石
            else if($toolid == 2) {
                $update['25']  = 26;
                $update['133'] = 135;
                $update['603'] = 604;
                $my['update']  = 1;
            } //判断道具ID，3为火之石
            else if($toolid == 3) {
                $update['37']  = 38;
                $update['58']  = 59;
                $update['133'] = 136;
                $update['513'] = 514;
                $my['update']  = 1;
            } //判断道具ID，4为叶之石
            else if($toolid == 4) {
                $update['44']  = 45;
                $update['70']  = 71;
                $update['102'] = 103;
                $update['274'] = 275;
                $update['511'] = 512;
                $my['update']  = 1;
            } //判断道具ID，5为太阳石
            else if($toolid == 5) {
                $update['44']  = 182;
                $update['191'] = 192;
                $update['546'] = 547;
                $update['548'] = 549;
                $update['694'] = 695;
                $my['update']  = 1;
            } //判断道具ID，6为月亮石
            else if($toolid == 6) {
                $update['30']  = 31;
                $update['33']  = 34;
                $update['35']  = 36;
                $update['39']  = 40;
                $update['300'] = 301;
                $update['517'] = 518;
                $my['update']  = 1;
            } //判断道具ID，7为龙鳞
            else if($toolid == 7) {
                $update['117'] = 230;
                $my['update']  = 1;
            } //判断道具ID，8为升级卡片
            else if($toolid == 8) {
                $update['137'] = 233;
                $my['update']  = 1;
            } //判断道具ID，9为深海之牙
            else if($toolid == 9) {
                $update['366'] = 367;
                $my['update']  = 1;
            } //判断道具ID，10为深海之鳞
            else if($toolid == 10) {
                $update['366'] = 368;
                $my['update']  = 1;
            } //判断道具ID，11为王者之证
            else if($toolid == 11) {
                $update['61'] = 186;
                $update['79'] = 199;
                $my['update'] = 1;
            } //判断道具ID，12为金属外套
            else if($toolid == 12) {
                $update['95']  = 208;
                $update['123'] = 212;
                $my['update']  = 1;
            } //判断道具ID，26为神奇糖果
            else if($toolid == 26) {
                $my['expadd'] = 100;
                $my['upid']   = $selectlist;
                $my['update'] = 2;
            } //判断道具ID，27为糖果碎屑
            else if($toolid == 27) {
                $my['expadd'] = floor(rand(1, 5));
                $my['upid']   = $selectlist;
                $my['update'] = 2;
            } //判断道具ID，29为光石
            else if($toolid == 29) {
                $update['315'] = 407;
                $update['176'] = 468;
                $update['572'] = 573;
                $update['670'] = 671;
                $my['update']  = 1;
            } //判断道具ID，30为暗石
            else if($toolid == 30) {
                $update['200'] = 429;
                $update['198'] = 430;
                $update['608'] = 609;
                $update['680'] = 681;
                $my['update']  = 1;
            } //判断道具ID，31为觉醒石
            else if($toolid == 31) {
                //echo $mypm['sex'];
                if($mypm['sex'] == "♀") {
                    $update['361'] = 478;
                }
                if($mypm['sex'] == "♂") {
                    $update['281'] = 475;
                }
                $my['update'] = 1;
            } //判断道具ID，32为幸运蛋
            else if($toolid == 32) {
                $update['440'] = 113;
                $my['update']  = 1;
            } //判断道具ID，33为保护装置
            else if($toolid == 33) {
                $update['112'] = 464;
                $my['update']  = 1;
            } //判断道具ID，34为电气变压器
            else if($toolid == 34) {
                $update['125'] = 466;
                $my['update']  = 1;
            } //判断道具ID，35为岩浆变压器
            else if($toolid == 35) {
                $update['126'] = 467;
                $my['update']  = 1;
            } //判断道具ID，36为阴森补丁
            else if($toolid == 36) {
                $update['233'] = 474;
                $my['update']  = 1;
            } //判断道具ID，37为神奇斗篷
            else if($toolid == 37) {
                $update['356'] = 477;
                $my['update']  = 1;
            } //判断道具ID，106为HP努力药剂
            else if($toolid == 106) {
                $my['nladd']  = 'hp_nl';
                $my['upid']   = $selectlist;
                $my['update'] = 3;
            } //判断道具ID，107为攻击努力药剂
            else if($toolid == 107) {
                $my['nladd']  = 'gongji_nl';
                $my['upid']   = $selectlist;
                $my['update'] = 3;
            } //判断道具ID，108为防御努力药剂
            else if($toolid == 108) {
                $my['nladd']  = 'fangyu_nl';
                $my['upid']   = $selectlist;
                $my['update'] = 3;
            } //判断道具ID，109为速度努力药剂
            else if($toolid == 109) {
                $my['nladd']  = 'minjie_nl';
                $my['upid']   = $selectlist;
                $my['update'] = 3;
            } //判断道具ID，110为特攻努力药剂
            else if($toolid == 110) {
                $my['nladd']  = 'tegong_nl';
                $my['upid']   = $selectlist;
                $my['update'] = 3;
            } //判断道具ID，111为特防努力药剂
            else if($toolid == 111) {
                $my['nladd']  = 'tefang_nl';
                $my['upid']   = $selectlist;
                $my['update'] = 3;
            } //判断道具ID，113为黄金糖果
            else if($toolid == 113) {
                $exp          = DB::fetch(DB::query("select exp,lv from `cdb_kd8c_my_new_pm` where uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'"));
                $my['expadd'] = ($exp['lv'] + 1) * ($exp['lv'] + 1) - $exp['exp'];
                $my['upid']   = $selectlist;
                $my['update'] = 2;
            } else {
                //如果道具判断有问题，提示道具非法
                showmessage($kd8c_kd8c_showmsg['msg20'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
            }
            //判断升级种类，1为物品特殊升级
            if($my['update'] == 1) {
                //查询携带的道具，判断是否带有不变石
                $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                $my    = DB::fetch($query);
                if($my['daoju'] == 13) {
                    //带有不变石头进行提示
                    showmessage($kd8c_kd8c_showmsg['msg13'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                } else {
                    //判断宠物是否属于该种特殊进化
                    if($update[$my['id_quanguo']]) {
                        //是的话进行进化操作
                        DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                        DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
                        //更新图鉴信息
                        if($update[$my['id_quanguo']] > 0) add_pet_history($_G['uid'], $_G['username'], $update[$my['id_quanguo']], $my['shine']);
                        //进化成功，进行提示
                        showmessage($kd8c_kd8c_showmsg['msg14'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                    } else {
                        //不属于该类特殊进化，进行提示
                        showmessage($kd8c_kd8c_showmsg['msg15'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                    }
                }
            } //判断升级种类，2为经验普通升级
            else if($my['update'] == 2) {
                require_once DISCUZ_ROOT . './source/plugin/kd8c/kd8c_jinhua.sub.inc.php';
                DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
                showmessage($kd8c_kd8c_showmsg['msg21'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
            } //判断升级种类，3为努力值
            else if($my['update'] == 3) {
                $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                $myy   = DB::fetch($query);
                if($myy[$my['nladd']] >= 252 || ($myy['hp_nl'] + $myy['gongji_nl'] + $myy['fangyu_nl'] + $myy['minjie_nl'] + $myy['tegong_nl'] + $myy['tefang_nl']) >= 510)
                    showmessage("您的精灵努力值不能再增加了！", 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                else {
                    $myy[$my['nladd']] += 10;
                    if($myy[$my['nladd']] >= 252)
                        $myy[$my['nladd']] = 252;
                    DB::query("UPDATE cdb_kd8c_my_new_pm SET " . $my['nladd'] . "=" . $myy[$my['nladd']] . " WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                    DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
                    showmessage("使用成功！您的精灵的努力值增加了！", 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                }
            }
        } //判断使用类型，2为装备型
        else if($my['toolorder'] == 2) {
            //判断道具ID，13为不变石
            if(TRUE) {
                //54-101为Mega石
                //判断身上是否已携带道具
                $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                $my    = DB::fetch($query);
                if($my['daoju'] != 0) {
                    //如果携带，将道具卸下放入包中
                    if($my['id_quanguo'] >= 10000 && $my['id_quanguo'] < 20000) {
                        //如果该PM本为mega形态，进行mega退化
                        //如为分XY的mega进化，编号-10001，否则-10000；
                        if($my['id_quanguo'] == 10007 || $my['id_quanguo'] == 10151) {
                            $update = $my['id_quanguo'] - 10001;
                        } else {
                            $update = $my['id_quanguo'] - 10000;
                        }
                        //进行退化操作
                        DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update . " WHERE uid='" . $_G[uid] . "' and pmid='" . $cmd . "'");
                    }
                    DB::query("INSERT INTO cdb_kd8c_my_tool ( `mytoolid` , `toolid` , `uid` ) VALUES ('', '$my[daoju]', '$_G[uid]')");
                    DB::query("UPDATE cdb_kd8c_my_new_pm SET daoju=0 WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                }
                //将新道具带上，从包中删除
                DB::query("UPDATE cdb_kd8c_my_new_pm SET daoju=" . $toolid . " WHERE uid='" . $_G[uid] . "' and daoju='0' and pmid='" . $selectlist . "'");
                DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
                if($toolid >= 54 && $toolid <= 101) {
                    //Mega进化
                    //判断道具ID，54为妙蛙花结晶
                    if($toolid == 54) {
                        $update['3'] = 10003;
                    } else if($toolid == 55) {
                        $update['6'] = 10006;
                    } else if($toolid == 56) {
                        $update['6'] = 10007;
                    } else if($toolid == 57) {
                        $update['9'] = 10009;
                    } else if($toolid == 58) {
                        $update['65'] = 10065;
                    } else if($toolid == 59) {
                        $update['94'] = 10094;
                    } else if($toolid == 60) {
                        $update['115'] = 10115;
                    } else if($toolid == 61) {
                        $update['127'] = 10127;
                    } else if($toolid == 62) {
                        $update['130'] = 10130;
                    } else if($toolid == 63) {
                        $update['142'] = 10142;
                    } else if($toolid == 64) {
                        $update['150'] = 10150;
                    } else if($toolid == 65) {
                        $update['150'] = 10151;
                    } else if($toolid == 66) {
                        $update['181'] = 10181;
                    } else if($toolid == 67) {
                        $update['212'] = 10212;
                    } else if($toolid == 68) {
                        $update['214'] = 10214;
                    } else if($toolid == 69) {
                        $update['229'] = 10229;
                    } else if($toolid == 70) {
                        $update['248'] = 10248;
                    } else if($toolid == 71) {
                        $update['257'] = 10257;
                    } else if($toolid == 72) {
                        $update['282'] = 10282;
                    } else if($toolid == 73) {
                        $update['303'] = 10303;
                    } else if($toolid == 74) {
                        $update['306'] = 10306;
                    } else if($toolid == 75) {
                        $update['308'] = 10308;
                    } else if($toolid == 76) {
                        $update['310'] = 10310;
                    } else if($toolid == 77) {
                        $update['354'] = 10354;
                    } else if($toolid == 78) {
                        $update['359'] = 10359;
                    } else if($toolid == 79) {
                        $update['445'] = 10445;
                    } else if($toolid == 80) {
                        $update['448'] = 10448;
                    } else if($toolid == 81) {
                        $update['460'] = 10460;
                    } else if($toolid == 82) {
                        $update['15'] = 10015;
                    } else if($toolid == 83) {
                        $update['18'] = 10018;
                    } else if($toolid == 84) {
                        $update['80'] = 10080;
                    } else if($toolid == 85) {
                        $update['208'] = 10208;
                    } else if($toolid == 86) {
                        $update['254'] = 10254;
                    } else if($toolid == 87) {
                        $update['260'] = 10260;
                    } else if($toolid == 88) {
                        $update['302'] = 10302;
                    } else if($toolid == 89) {
                        $update['319'] = 10319;
                    } else if($toolid == 90) {
                        $update['323'] = 10323;
                    } else if($toolid == 91) {
                        $update['334'] = 10334;
                    } else if($toolid == 92) {
                        $update['362'] = 10362;
                    } else if($toolid == 93) {
                        $update['373'] = 10373;
                    } else if($toolid == 94) {
                        $update['376'] = 10376;
                    } else if($toolid == 95) {
                        $update['380'] = 10380;
                    } else if($toolid == 96) {
                        $update['381'] = 10381;
                    } else if($toolid == 97) {
                        $update['384'] = 10384;
                    } else if($toolid == 98) {
                        $update['428'] = 10428;
                    } else if($toolid == 99) {
                        $update['475'] = 10475;
                    } else if($toolid == 100) {
                        $update['531'] = 10531;
                    } else if($toolid == 101) {
                        $update['719'] = 10719;
                    }
                    //判断Mega石是否正确并且PM放在队首
                    if($update[$my['id_quanguo']] && $my['showtype'] == 1) {
                        //是的话进行进化操作
                        DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
                        //Mega进化成功，进行提示
                        showmessage($kd8c_kd8c_showmsg['msg29'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                    } else {
                        //队首/道具种类问题不进行Mega进化，进行提示
                        showmessage($kd8c_kd8c_showmsg['msg30'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                    }
                } else {
                    //无论是否成功都进行成功提示，不过非法不进行有效操作
                    showmessage($kd8c_kd8c_showmsg['msg11'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                }
            } else {
                //如果道具判断有问题，提示道具非法
                showmessage($kd8c_kd8c_showmsg['msg20'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
            }
        } //判断使用类型，3为一次装备型
        else if($my['toolorder'] == 3) {
            //判断道具ID，14为精灵球
            if($toolid == 14) {
            } //判断道具ID，15为超级球
            else if($toolid == 15) {
            } //判断道具ID，16为高级球
            else if($toolid == 16) {
            } //判断道具ID，17为触网球
            else if($toolid == 17) {
            } //判断道具ID，18为大师球
            else if($toolid == 18) {
            } //判断道具ID，19为亲密球
            else if($toolid == 19) {
            } //判断道具ID，20为时间球
            else if($toolid == 20) {
            } //判断道具ID，21为深水球
            else if($toolid == 21) {
            } //判断道具ID，22为狩猎球
            else if($toolid == 22) {
            } //判断道具ID，23为珍惜球
            else if($toolid == 23) {
            } //判断道具ID，24为重复球
            else if($toolid == 24) {
            } //判断道具ID，25为巢窝球
            else if($toolid == 25) {
            } //判断道具ID，28为透明球
            else if($toolid == 28) {
            } //判断道具ID，44为黑暗球
            else if($toolid == 44) {
            } //判断道具ID，45为治疗球
            else if($toolid == 45) {
            } //判断道具ID，46为珍宝球
            else if($toolid == 46) {
            } //判断道具ID，47为快速球
            else if($toolid == 47) {
            } else {
                //如果道具判断有问题，提示道具非法
                showmessage($kd8c_kd8c_showmsg['msg20'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
            }
            //进行装备，然后从包中将道具删除
            DB::query("UPDATE cdb_kd8c_my_new_pm SET pmball=" . $toolid . " WHERE uid='" . $_G[uid] . "' and pmid='" . $selectlist . "'");
            DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
            //无论是否成功都进行成功提示，不过非法不进行有效操作
            showmessage($kd8c_kd8c_showmsg['msg12'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
        } else {
            //如果道具判断有问题，提示道具非法
            showmessage($kd8c_kd8c_showmsg['msg20'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
        }
    } //判断使用对象，2为人
    else if($my['tooluseobject'] == 2) {
        //判断道具ID，43为2006圣诞礼品包裹
        if($toolid == 43) {
            $tempdppm[1]  = 387;
            $tempdppm[2]  = 390;
            $tempdppm[3]  = 393;
            $tempdppm[4]  = 396;
            $tempdppm[5]  = 399;
            $tempdppm[6]  = 401;
            $tempdppm[7]  = 403;
            $tempdppm[8]  = 406;
            $tempdppm[9]  = 408;
            $tempdppm[10] = 410;
            $tempdppm[11] = 412;
            $tempdppm[12] = 415;
            $tempdppm[13] = 417;
            $tempdppm[14] = 418;
            $tempdppm[15] = 420;
            $tempdppm[16] = 422;
            $tempdppm[17] = 425;
            $tempdppm[18] = 427;
            $tempdppm[19] = 431;
            $tempdppm[20] = 433;
            $tempdppm[21] = 434;
            $tempdppm[22] = 436;
            $tempdppm[23] = 438;
            $tempdppm[24] = 439;
            $tempdppm[25] = 440;
            $tempdppm[26] = 441;
            $tempdppm[27] = 442;
            $tempdppm[28] = 443;
            $tempdppm[29] = 446;
            $tempdppm[30] = 447;
            $tempdppm[31] = 449;
            $tempdppm[32] = 451;
            $tempdppm[33] = 453;
            $tempdppm[34] = 455;
            $tempdppm[35] = 456;
            $tempdppm[36] = 458;
            $tempdppm[37] = 459;
            $tempdppmrand = rand(1, 37);
            DB::query("INSERT INTO cdb_kd8c_my_box ( uid,ptid,ptname,boxtype,fromname ) VALUES ('$_G[uid]', '$tempdppm[$tempdppmrand]', '2006年圣诞礼物', '宠物', '逍遥朽木')");
            DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
            showmessage('包裹打开了，快去箱子里看看礼物是什么吧！', 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
        }
        //判断道具ID，48为宠物箱
        if($toolid == 48) {
            DB::query("UPDATE " . DB::table('common_member_count') . " SET $pmboxcredits=$pmboxcredits+1 WHERE uid='" . $_G[uid] . "'");
            DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
            showmessage('您的宠物箱空间增加了！', 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
        } //判断道具ID，112为扭蛋券
        else if($toolid == 112) {
            //前往兑换大厅
            showmessage("正在前往扭蛋机...", 'plugin.php?id=kd8c:kd8c_pet&act=0&cmd=2');
        }
        //判断道具ID，49为2007年元旦礼物
        if($toolid == 49) {
            $temp['1']      = 10;
            $temp['2']      = 10;
            $temp['3']      = 10;
            $temp['4']      = 10;
            $temp['5']      = 10;
            $temp['6']      = 10;
            $temp['7']      = 5;
            $temp['8']      = 5;
            $temp['9']      = 5;
            $temp['10']     = 5;
            $temp['11']     = 5;
            $temp['12']     = 5;
            $temp['13']     = 10;
            $temp['14']     = 40;
            $temp['15']     = 10;
            $temp['16']     = 10;
            $temp['17']     = 10;
            $temp['18']     = 5;
            $temp['19']     = 5;
            $temp['20']     = 10;
            $temp['21']     = 5;
            $temp['22']     = 5;
            $temp['23']     = 5;
            $temp['24']     = 10;
            $temp['25']     = 10;
            $temp['26']     = 1;
            $temp['27']     = 40;
            $temp['28']     = 1;
            $temp['29']     = 10;
            $temp['30']     = 5;
            $temp['31']     = 10;
            $temp['32']     = 5;
            $temp['33']     = 5;
            $temp['34']     = 5;
            $temp['35']     = 5;
            $temp['36']     = 5;
            $temp['37']     = 5;
            $temp['38']     = 5;
            $temp['39']     = 5;
            $temp['40']     = 5;
            $temp['41']     = 1;
            $temp['42']     = 1;
            $temp['43']     = 0;
            $temp['44']     = 10;
            $temp['45']     = 10;
            $temp['46']     = 10;
            $temp['47']     = 10;
            $tempcode['1']  = "rewtrwe";
            $tempcode['2']  = "wtwete1";
            $tempcode['3']  = "1rwtjyt";
            $tempcode['4']  = "1gktrre";
            $tempcode['5']  = "1erhger";
            $tempcode['6']  = "10ouyre";
            $tempcode['7']  = "587kjuy";
            $tempcode['8']  = "5rwbtre";
            $tempcode['9']  = "5cqwerv";
            $tempcode['10'] = "treyyy5";
            $tempcode['11'] = "54213dd";
            $tempcode['12'] = "234rew5";
            $tempcode['13'] = "qqere10";
            $tempcode['14'] = "20trett";
            $tempcode['15'] = "13dfcc0";
            $tempcode['16'] = "1524320";
            $tempcode['17'] = "1rw3444";
            $tempcode['18'] = "56588hh";
            $tempcode['19'] = "o88n7i5";
            $tempcode['20'] = "1trytr0";
            $tempcode['21'] = "tttjii5";
            $tempcode['22'] = "5sadfbb";
            $tempcode['23'] = "5i8oljk";
            $tempcode['24'] = "10trttt";
            $tempcode['25'] = "10wqqww";
            $tempcode['26'] = "56i8995";
            $tempcode['27'] = "10vvrvw";
            $tempcode['28'] = "590vvyo";
            $tempcode['29'] = "1uyli90";
            $tempcode['30'] = "eecrey5";
            $tempcode['31'] = "168jo80";
            $tempcode['32'] = "532rgtg";
            $tempcode['33'] = "57jkkoo";
            $tempcode['34'] = "5i3g5wq";
            $tempcode['35'] = "52fbyml";
            $tempcode['36'] = "588ok90";
            $tempcode['37'] = "f5485g5";
            $tempcode['38'] = "3463gy5";
            $tempcode['39'] = "55436w5";
            $tempcode['40'] = "2225vre";
            $tempcode['41'] = "5utio85";
            $tempcode['42'] = "53rw54f";
            $tempcode['43'] = "0t2dtb5";
            $tempcode['44'] = "1rwfas0";
            $tempcode['45'] = "10qyrqw";
            $tempcode['46'] = "1qfweqf";
            $tempcode['47'] = "1we4rtw";
            if(!$whichrand) {
                for($i = 1; $i <= 47; $i++) {
                    $randmax += $temp[$i];
                }
                $temptoolid = rand(1, $randmax);
                for($i = 1; $i <= 47; $i++) {
                    $randsum += $temp[$i];
                    if($randsum >= $temptoolid) {
                        $randid = $i;
                        break;
                    }
                }
                $query = DB::query("SELECT * FROM cdb_kd8c_tool_toollist WHERE toolid='$randid'");
                $tool  = DB::fetch($query);
                if($tool['toolorder'] == 1) {
                    $src  = "xiaohao/";
                    $type = $kd8c_tool_name['xiaohao'];
                } else if($tool['toolorder'] == 2) {
                    $src  = "zhuangbei/";
                    $type = $kd8c_tool_name['zhuangbei'];
                } else if($tool['toolorder'] == 3) {
                    $src  = "ball/";
                    $type = $kd8c_tool_name['zhuangbei1'];
                }
                $tool_selllist = "<img src=\"images/kd8c/tool/" . $src . $tool['toolid'] . ".gif\">";
                include template('kd8c:kd8c_kd8c_tool49');
            } else {
                $whichrand   = htmlspecialchars($whichrand);
                $tempbiaozhi = FALSE;
                for($i = 1; $i <= 47; $i++) {
                    if($tempcode[$i] == $whichrand) {
                        $tempbiaozhi = TRUE;
                        break;
                    }
                }
                if($tempbiaozhi) {
                    DB::query("INSERT INTO cdb_kd8c_my_box ( uid,ptid,ptname,boxtype,fromname ) VALUES ('$_G[uid]', '$i', '2007年元旦礼物', '道具', '逍遥朽木')");
                    DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
                    showmessage('您的奖卷兑换成功', 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
                } else {
                    showmessage('您的奖卷兑换有问题。', 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
                }
            }
        }
        //判断道具ID，50为2008新春红包
        if($toolid == 50) {
            $parameter = split(";;", $my['parameter']);
            for($i = 0; $i < count($parameter); $i++) {
                $subparameter = split(",", $parameter[$i]);
                $newparameter = "";
                for($j = 3; $j < count($subparameter); $j++) {
                    if($newparameter == "") {
                        $newparameter = $subparameter[$j];
                    } else {
                        $newparameter .= "," . $subparameter[$j];
                    }

                }
                DB::query("INSERT INTO cdb_kd8c_my_box ( uid,ptid,ptname,boxtype,fromname,parameter ) VALUES ('$_G[uid]', '$subparameter[0]', '$subparameter[1]', '道具', '逍遥朽木','$newparameter')");
            }
            DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
            showmessage('红包打开了，快去箱子里看看礼物是什么吧！', 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
        }
        //判断道具ID，51为信
        if($toolid == 51) {
            $parameter = split(",", $my['parameter']);
            echo "
<html>

<head>
<meta http-equiv=\"Content-Language\" content=\"zh-cn\">
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\">
<style type=\"text/css\">
<!--
td { font-size: 22pt; font-family:隶书}

-->
</style>
<title>信~</title>
</head>

<body topmargin=\"0\" leftmargin=\"0\">
<center>
<table border=\"0\" width=\"800\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
		<td width=\"800\" colspan=\"2\">
		<img border=\"0\" src=\".\\images\\kd8c\\tool\\other\\0001.jpg\" width=\"800\" height=\"424\"></td>
	</tr>
	<tr>
		<td bgcolor=\"#FCF5EB\" background=\".\\images\\kd8c\\tool\\other\\0004.jpg\" width=\"800\" colspan=\"2\">
		To:你^^<br>
		<img border=\"0\" src=\".\\images\\kd8c\\tool\\other\\0002.jpg\" width=\"800\" height=\"11\"><br>
		" . $parameter[1] . "
		<img border=\"0\" src=\".\\images\\kd8c\\tool\\other\\0002.jpg\" width=\"800\" height=\"11\"></td>
	</tr>
	<tr>
		<td bgcolor=\"#FCF5EB\" background=\".\\images\\kd8c\\tool\\other\\0004.jpg\" width=\"302\">
		<img border=\"0\" src=\".\\images\\kd8c\\tool\\other\\0003.jpg\" width=\"302\" height=\"97\"></td>
		<td bgcolor=\"#FCF5EB\" background=\".\\images\\kd8c\\tool\\other\\0004.jpg\" width=\"498\" valign=\"top\">
		<p align=\"right\">From:" . $parameter[0] . "</td>
	</tr>
</table>
</center>
</body>

</html>
			";
        }
    } else {
        //如果道具判断有问题，提示道具非法
        showmessage($kd8c_kd8c_showmsg['msg20'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
    }
} //判断act操作，6为丢弃道具
else if($_G['gp_act'] == 6) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //根据参数直接删除道具
    //PS：警告提示在前台JS上，如果丢弃的道具数据非法，会提示放生成功，但不会进行有效操作
    DB::query("DELETE FROM cdb_kd8c_my_tool WHERE uid='" . $_G[uid] . "' and mytoolid='" . $cmd . "'");
    //无论是否成功，进行成功提示
    showmessage($kd8c_kd8c_showmsg['msg8'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=1');
} //判断act操作，7为取出箱内物品 - X2
else if($_G['gp_act'] == 7) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //查询要取出的物品信息
    $query = DB::query("SELECT * FROM cdb_kd8c_my_box WHERE uid='" . $_G[uid] . "' and boxid='" . $cmd . "'");
    $my    = DB::fetch($query);
    //判断物品类型
    if($my['boxtype'] == "宠物" || $my['boxtype'] == "闪光") {
        //查询身上PM数量
        $query     = DB::query("SELECT COUNT(*) FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' and showtype in ('1','2')");
        $kd8c_user = DB::result($query, 0);
        if($kd8c_user >= 6) {
            //如果数量超过6只，弹出错误提示
            showmessage($kd8c_pet_showmsg['msg18'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
        } else {
            //如果未达到上限
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
            $pmsex['29']  = "♀";
            $pmsex['30']  = "♀";
            $pmsex['31']  = "♀";
            $pmsex['113'] = "♀";
            $pmsex['115'] = "♀";
            $pmsex['124'] = "♀";
            $pmsex['238'] = "♀";
            $pmsex['241'] = "♀";
            $pmsex['242'] = "♀";
            $pmsex['314'] = "♀";
            $pmsex['380'] = "♀";
            $pmsex['413'] = "♀";
            $pmsex['416'] = "♀";
            $pmsex['440'] = "♀";
            $pmsex['478'] = "♀";
            $pmsex['488'] = "♀";
            $pmsex['32']  = "♂";
            $pmsex['33']  = "♂";
            $pmsex['34']  = "♂";
            $pmsex['106'] = "♂";
            $pmsex['107'] = "♂";
            $pmsex['128'] = "♂";
            $pmsex['236'] = "♂";
            $pmsex['237'] = "♂";
            $pmsex['313'] = "♂";
            $pmsex['381'] = "♂";
            $pmsex['414'] = "♂";
            $pmsex['475'] = "♂";
            $pmsex['81']  = "无";
            $pmsex['82']  = "无";
            $pmsex['100'] = "无";
            $pmsex['101'] = "无";
            $pmsex['120'] = "无";
            $pmsex['121'] = "无";
            $pmsex['132'] = "无";
            $pmsex['137'] = "无";
            $pmsex['144'] = "无";
            $pmsex['145'] = "无";
            $pmsex['146'] = "无";
            $pmsex['150'] = "无";
            $pmsex['151'] = "无";
            $pmsex['201'] = "无";
            $pmsex['233'] = "无";
            $pmsex['243'] = "无";
            $pmsex['244'] = "无";
            $pmsex['245'] = "无";
            $pmsex['249'] = "无";
            $pmsex['250'] = "无";
            $pmsex['251'] = "无";
            $pmsex['292'] = "无";
            $pmsex['337'] = "无";
            $pmsex['338'] = "无";
            $pmsex['343'] = "无";
            $pmsex['344'] = "无";
            $pmsex['374'] = "无";
            $pmsex['375'] = "无";
            $pmsex['376'] = "无";
            $pmsex['377'] = "无";
            $pmsex['378'] = "无";
            $pmsex['379'] = "无";
            $pmsex['382'] = "无";
            $pmsex['383'] = "无";
            $pmsex['384'] = "无";
            $pmsex['385'] = "无";
            $pmsex['386'] = "无";
            $pmsex['436'] = "无";
            $pmsex['437'] = "无";
            $pmsex['462'] = "无";
            $pmsex['474'] = "无";
            $pmsex['479'] = "无";
            $pmsex['480'] = "无";
            $pmsex['481'] = "无";
            $pmsex['482'] = "无";
            $pmsex['483'] = "无";
            $pmsex['484'] = "无";
            $pmsex['486'] = "无";
            $pmsex['487'] = "无";
            $pmsex['489'] = "无";
            $pmsex['490'] = "无";
            $pmsex['491'] = "无";
            $pmsex['492'] = "无";
            $pmsex['493'] = "无";

            $pmsex['494'] = "无";
            $pmsex['538'] = "♂";
            $pmsex['539'] = "♂";
            $pmsex['548'] = "♀";
            $pmsex['549'] = "♀";
            $pmsex['599'] = "无";
            $pmsex['600'] = "无";
            $pmsex['601'] = "无";
            $pmsex['615'] = "无";
            $pmsex['622'] = "无";
            $pmsex['623'] = "无";
            $pmsex['627'] = "♂";
            $pmsex['628'] = "♂";
            $pmsex['629'] = "♀";
            $pmsex['630'] = "♀";
            $pmsex['638'] = "无";
            $pmsex['639'] = "无";
            $pmsex['640'] = "无";
            $pmsex['641'] = "♂";
            $pmsex['642'] = "♂";
            $pmsex['643'] = "无";
            $pmsex['644'] = "无";
            $pmsex['645'] = "♂";
            $pmsex['646'] = "无";
            $pmsex['647'] = "无";
            $pmsex['648'] = "无";
            $pmsex['649'] = "无";

            $pmsex['669'] = "♀";
            $pmsex['703'] = "无";
            $pmsex['716'] = "无";
            $pmsex['717'] = "无";
            $pmsex['718'] = "无";

            if($pmsex[$my[ptid]]) {
                $pmsex = $pmsex[$my[ptid]];
            } else {
                $luckly = rand(1, 2);
                if($luckly == 1) {
                    $pmsex = "♀";
                } else {
                    $pmsex = "♂";
                }
            }
            //计算当前等级的HP值
            $hp_nlz = hpjisuan($pet['hp_zz'], $hp_gt, $hp_nl, $hp_lv);
            //查询宠物显示方式，制定新购买的PM的显示方式
            $query   = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' AND showtype=1");
            $firstpm = DB::fetch($query);
            if($firstpm) {
                $showtype = 2;
            } else {
                $showtype = 1;
            }
            //闪光判定
            $shineluck = rand(1, $shinenum);
            if($shineluck == 1)
                $shine = 1;
            else $shine = 0;
            if($my['boxtype'] == "闪光")
                $shine = 1;
            $queryyy = DB::query("SELECT maxpm FROM cdb_kd8c_pet_status WHERE user_id='" . $_G[uid] . "'");
            $maxnum2 = DB::fetch($queryyy);
            if(!$maxnum2) {
                DB::query("INSERT INTO `cdb_kd8c_pet_status`(`id`, `user_id`, `user_name`, `total`, `maxpm`) VALUES ('','" . $_G[uid] . "','" . $_G[username] . "','0','0')");
                $maxnum2['maxpm'] = 0;
            }
            if($my['fromname'] == "扭蛋机")
                $get = 4;
            else $get = 5;
            DB::query("INSERT INTO cdb_kd8c_my_new_pm ( pmid , uid ,luid, id_quanguo , pmname , hp_now , hp_gt , gongji_gt , fangyu_gt , minjie_gt , tegong_gt , tefang_gt , hp_nl , gongji_nl , fangyu_nl , minjie_nl , tegong_nl , tefang_nl , daoju , pmball , showtype , exp , lv , pic ,sex,orderlist,shine,get,getdate ) VALUES ('', '$_G[uid]','$_G[uid]', '$my[ptid]', '$my[ptname]', '$hp_nlz', '$hp_gt', '$gongji_gt', '$fangyu_gt', '$minjie_gt', '$tegong_gt', '$tefang_gt', '0', '0', '0', '0', '0', '0', '0', '0', '$showtype', '1', '1', '1', '$pmsex',$maxnum2[maxpm],'$shine','$get',now())");
            DB::query("DELETE FROM cdb_kd8c_my_box WHERE uid='" . $_G[uid] . "' and boxid='" . $cmd . "'");
            //更新图鉴信息
            if($my['ptid'] > 0) add_pet_history($_G['uid'], $_G['username'], $my['ptid'], $shine);
            //取出成功，进行提示
            showmessage($kd8c_kd8c_showmsg['msg17'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
        }
    } else if($my['boxtype'] == "道具") {
        //查询身上道具数量
        $query     = DB::query("SELECT count(*) FROM cdb_kd8c_my_tool where uid='$_G[uid]'");
        $kd8c_user = DB::result($query, 0);
        if($kd8c_user < 30) {
            //如果未达到上限
            DB::query("INSERT INTO cdb_kd8c_my_tool ( `mytoolid` , `toolid` , `uid` , `parameter` ) VALUES ('', '$my[ptid]', '$_G[uid]', '$my[parameter]')");
            DB::query("DELETE FROM cdb_kd8c_my_box WHERE uid='" . $_G[uid] . "' and boxid='" . $cmd . "'");
            //取出成功，进行提示
            showmessage($kd8c_kd8c_showmsg['msg18'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
        } else {
            //大于30个道具，弹出错误提示
            showmessage($kd8c_tool_showmsg['msg4'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
        }
    } else {
        //取出物品非法，进行提示
        showmessage($kd8c_kd8c_showmsg['msg19'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
    }
} //判断act操作，8为丢弃箱内物品 - X2
else if($_G['gp_act'] == 8) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    //根据参数直接删除道具
    //PS：警告提示在前台JS上，如果丢弃的道具数据非法，会提示放生成功，但不会进行有效操作
    DB::query("DELETE FROM cdb_kd8c_my_box WHERE uid='" . $_G[uid] . "' and boxid='" . $cmd . "'");
    //无论是否成功，进行成功提示
    showmessage($kd8c_kd8c_showmsg['msg16'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=2');
} //判断act操作，9为修改家族资料 - X2
else if($_G['gp_act'] == 9) {
    //将编号参数强制转换成整型
    $cmd           = intval($_G['gp_cmd']);
    $mininame      = htmlspecialchars($_G['gp_mininame']);
    $addclanamount = intval($_G['gp_addclanamount']);
    if($addclanamount < 0) {
        $addclanamount = 0;
    }
    $clanintroduce = htmlspecialchars($_G['gp_clanintroduce']);
    $master1name   = htmlspecialchars($_G['gp_master1name']);
    $master2name   = htmlspecialchars($_G['gp_master2name']);
    if($kd8c_user['groupid'] == 1) {
        DB::query("UPDATE cdb_kd8c_clan_clanlist SET mininame='" . $mininame . "',clanamount='" . $addclanamount . "',clanintroduce='" . $clanintroduce . "',master1name='" . $master1name . "',master2name='" . $master2name . "' WHERE clanid='" . $kd8c_user['clanid'] . "'");
        showmessage($kd8c_kd8c_showmsg['msg28'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    } else {
        showmessage($kd8c_kd8c_showmsg['msg27'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    }
} //判断act操作，10为族长让位 - X2
else if($_G['gp_act'] == 10) {
    //将编号参数强制转换成整型
    $cmd      = intval($_G['gp_cmd']);
    $tomaster = htmlspecialchars($_G['gp_tomaster']);
    if($kd8c_user['groupid'] == 1) {
        $query = DB::query("SELECT * FROM cdb_kd8c_my_clan mc LEFT JOIN " . DB::table('common_member') . " mb ON mc.uid=mb.uid WHERE mb.username='" . $tomaster . "' and mc.clanid='" . $kd8c_user['clanid'] . "'");
        $my    = DB::fetch($query);
        if($my) {
            if($my['uid'] != $_G[uid]) {
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=1 WHERE mc.uid='" . $my['uid'] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "'");
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=3 WHERE mc.uid='" . $_G[uid] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "'");
            }
            showmessage("族长让位成功，" . $tomaster . "成为新族长。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
        } else {
            showmessage("该会员不是家族成员，不能对其进行族长让位。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
        }
    } else {
        showmessage($kd8c_kd8c_showmsg['msg27'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    }
} //判断act操作，11为任命长老 - X2
else if($_G['gp_act'] == 11) {
    //将编号参数强制转换成整型
    $cmd      = intval($_G['gp_cmd']);
    $master21 = htmlspecialchars($_G['gp_master21']);
    $master22 = htmlspecialchars($_G['gp_master22']);
    $master23 = htmlspecialchars($_G['gp_master23']);
    $master24 = htmlspecialchars($_G['gp_master24']);
    $master25 = htmlspecialchars($_G['gp_master25']);
    if($kd8c_user['groupid'] == 1) {
        DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=3 WHERE mc.group='2' AND mc.clanid='" . $kd8c_user['clanid'] . "'");
        if($master21) {
            $query = DB::query("SELECT * FROM cdb_kd8c_my_clan mc LEFT JOIN " . DB::table('common_member') . " mb ON mc.uid=mb.uid WHERE mb.username='" . $master21 . "' and mc.clanid='" . $kd8c_user['clanid'] . "'");
            $my    = DB::fetch($query);
            if($my) {
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=2 WHERE mc.uid='" . $my['uid'] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "' AND mc.group<>1");
            }
        }
        if($master22) {
            $query = DB::query("SELECT * FROM cdb_kd8c_my_clan mc LEFT JOIN " . DB::table('common_member') . " mb ON mc.uid=mb.uid WHERE mb.username='" . $master22 . "' and mc.clanid='" . $kd8c_user['clanid'] . "'");
            $my    = DB::fetch($query);
            if($my) {
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=2 WHERE mc.uid='" . $my['uid'] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "' AND mc.group<>1");
            }
        }
        if($master23) {
            $query = DB::query("SELECT * FROM cdb_kd8c_my_clan mc LEFT JOIN " . DB::table('common_member') . " mb ON mc.uid=mb.uid WHERE mb.username='" . $master23 . "' and mc.clanid='" . $kd8c_user['clanid'] . "'");
            $my    = DB::fetch($query);
            if($my) {
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=2 WHERE mc.uid='" . $my['uid'] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "' AND mc.group<>1");
            }
        }
        if($master24) {
            $query = DB::query("SELECT * FROM cdb_kd8c_my_clan mc LEFT JOIN " . DB::table('common_member') . " mb ON mc.uid=mb.uid WHERE mb.username='" . $master24 . "' and mc.clanid='" . $kd8c_user['clanid'] . "'");
            $my    = DB::fetch($query);
            if($my) {
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=2 WHERE mc.uid='" . $my['uid'] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "' AND mc.group<>1");
            }
        }
        if($master25) {
            $query = DB::query("SELECT * FROM cdb_kd8c_my_clan mc LEFT JOIN " . DB::table('common_member') . " mb ON mc.uid=mb.uid WHERE mb.username='" . $master25 . "' and mc.clanid='" . $kd8c_user['clanid'] . "'");
            $my    = DB::fetch($query);
            if($my) {
                DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=2 WHERE mc.uid='" . $my['uid'] . "' AND mc.clanid='" . $kd8c_user['clanid'] . "' AND mc.group<>1");
            }
        }
        showmessage($kd8c_user['master2name'] . "任命完毕。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    } else {
        showmessage($kd8c_kd8c_showmsg['msg27'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    }
} //判断act操作，12为家族投资 - X2
else if($_G['gp_act'] == 12) {
    //将编号参数强制转换成整型
    $cmd       = intval($_G['gp_cmd']);
    $addamount = intval($_G['gp_addamount']);
    if($addamount < 0) {
        $addamount = 0;
    }
    if($user_extcredits[$moneycredits] > $addamount) {
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $moneycredits . "=" . $moneycredits . "-" . $addamount . " WHERE uid='" . $_G[uid] . "'");
        DB::query("UPDATE cdb_kd8c_clan_clanlist SET clanbank=clanbank+" . $addamount . " WHERE clanid='" . $kd8c_user['clanid'] . "'");
        showmessage("家族投资成功。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    } else {
        showmessage("您的现金不足。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    }
} //判断act操作，13为退出家族 - X2
else if($_G['gp_act'] == 13) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    if($kd8c_user['groupid'] == 1) {
        DB::query("DELETE FROM cdb_kd8c_my_clan WHERE clanid='" . $kd8c_user['clanid'] . "'");
        DB::query("DELETE FROM cdb_kd8c_clan_clanlist WHERE clanid='" . $kd8c_user['clanid'] . "'");
        showmessage("您的家族已经解散。", 'plugin.php?id=kd8c:kd8c_clan&act=0&cmd=0');
    } else {
        DB::query("DELETE FROM cdb_kd8c_my_clan WHERE uid='" . $_G[uid] . "' AND clanid='" . $kd8c_user['clanid'] . "'");
        DB::query("UPDATE cdb_kd8c_clan_clanlist SET membernum=membernum-1 WHERE clanid='" . $kd8c_user['clanid'] . "'");
        showmessage("您已退出家族。", 'plugin.php?id=kd8c:kd8c_clan&act=0&cmd=0');
    }
} //判断act操作，14为审核成员 - X2
else if($_G['gp_act'] == 14) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    if($kd8c_user['groupid'] == 1 || $kd8c_user['groupid'] == 2) {
        DB::query("UPDATE cdb_kd8c_my_clan mc SET mc.group=3 WHERE mc.uid='" . $cmd . "' AND mc.clanid='" . $kd8c_user['clanid'] . "' AND mc.group='0'");
        showmessage("成员审核成功", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    } else {
        showmessage($kd8c_kd8c_showmsg['msg27'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    }
} //判断act操作，15为开除成员 - X2
else if($_G['gp_act'] == 15) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    if($kd8c_user['groupid'] == 1 || $kd8c_user['groupid'] == 2) {
        DB::query("DELETE FROM cdb_kd8c_my_clan WHERE uid='" . $cmd . "' AND clanid='" . $kd8c_user['clanid'] . "'");
        DB::query("UPDATE cdb_kd8c_clan_clanlist SET membernum=membernum-1 WHERE clanid='" . $kd8c_user['clanid'] . "'");
        showmessage("您已将成员开除。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    } else {
        showmessage($kd8c_kd8c_showmsg['msg27'], 'plugin.php?id=kd8c:kd8c&act=0&cmd=3');
    }
} //判断act操作，16为寄存宠物 - X2
else if($_G['gp_act'] == 16) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);
    /*
    $query = DB::query("SELECT boxsize FROM ".DB::table('common_member_count')." WHERE uid='".$_G[uid]."'");
    $kd8c_user = $db->result($query, 0);
    $boxsize=explode(",",$kd8c_user);
    $petboxsize=$boxsize[0];
    */
    $petboxsize = $user_extcredits[$pmboxcredits];
    if(!$petboxsize > 0) {
        showmessage("您还没有宠物箱。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    }
    $toolboxsize = $boxsize[1];
    $query       = DB::query("SELECT count(*) FROM cdb_kd8c_my_new_pm WHERE showtype IN ('3') AND uid='" . $_G[uid] . "'");
    $kd8c_user   = DB::result($query, 0);
    if($petboxsize > $kd8c_user) {
        $query = DB::query("SELECT * FROM cdb_kd8c_my_new_pm where uid='" . $_G[uid] . "' AND pmid='$cmd'");
        $my    = DB::fetch($query);
        if($my['exp'] > 0)
            DB::query("UPDATE cdb_kd8c_my_new_pm SET showtype=3 WHERE pmid='$cmd'");
        if($my['id_quanguo'] >= 10000 && $my['id_quanguo'] < 20000) {
            //如果该宠物本为mega形态，进行mega退化
            //如为分XY的mega进化，编号-10001，否则-10000；
            if($my['id_quanguo'] == 10007 || $my['id_quanguo'] == 10151) {
                $update = $my['id_quanguo'] - 10001;
            } else {
                $update = $my['id_quanguo'] - 10000;
            }
            //进行退化操作
            DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update . " WHERE uid='" . $_G[uid] . "' and pmid='" . $my['pmid'] . "'");
        }
        showmessage("您已将宠物存放到了宠物箱中。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    } else {
        showmessage("您的箱子已满，不能再存放了。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    }
} //判断act操作，17为取出宠物 - X2
else if($_G['gp_act'] == 17) {
    //将编号参数强制转换成整型
    $cmd = intval($_G['gp_cmd']);

    //查询身上PM数量
    //备注：由于购买PET的时候是放在身上，所以只查在身上的PET
    $query     = DB::query("SELECT COUNT(*) FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' AND showtype IN ('1','2')");
    $kd8c_user = DB::result($query, 0);
    if($kd8c_user >= 6) {
        //如果数量超过6只，弹出错误提示
        showmessage("您身上已有6只宠物，不能再取出了。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=4');
    } else {
        //查询宠物显示方式，制定新购买的PM的显示方式
        //备注：由于是判断所购买的PET是放在队首还是身上其他位置，所以只查在队首的PET
        $query   = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $_G[uid] . "' AND showtype=1");
        $firstpm = DB::fetch($query);
        if($firstpm) {
            $showtype = 2;
        } else {
            $showtype = 1;
            //新队长判定是否Mega进化
            $query  = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE pmid='" . $cmd . "'");
            $my     = DB::fetch($query);
            $toolid = $my['daoju'];
            if($toolid >= 54 && $toolid <= 101) {
                //Mega进化
                //判断道具ID，54为妙蛙花结晶
                if($toolid == 54) {
                    $update['3'] = 10003;
                } else if($toolid == 55) {
                    $update['6'] = 10006;
                } else if($toolid == 56) {
                    $update['6'] = 10007;
                } else if($toolid == 57) {
                    $update['9'] = 10009;
                } else if($toolid == 58) {
                    $update['65'] = 10065;
                } else if($toolid == 59) {
                    $update['94'] = 10094;
                } else if($toolid == 60) {
                    $update['115'] = 10115;
                } else if($toolid == 61) {
                    $update['127'] = 10127;
                } else if($toolid == 62) {
                    $update['130'] = 10130;
                } else if($toolid == 63) {
                    $update['142'] = 10142;
                } else if($toolid == 64) {
                    $update['150'] = 10150;
                } else if($toolid == 65) {
                    $update['150'] = 10151;
                } else if($toolid == 66) {
                    $update['181'] = 10181;
                } else if($toolid == 67) {
                    $update['212'] = 10212;
                } else if($toolid == 68) {
                    $update['214'] = 10214;
                } else if($toolid == 69) {
                    $update['229'] = 10229;
                } else if($toolid == 70) {
                    $update['248'] = 10248;
                } else if($toolid == 71) {
                    $update['257'] = 10257;
                } else if($toolid == 72) {
                    $update['282'] = 10282;
                } else if($toolid == 73) {
                    $update['303'] = 10303;
                } else if($toolid == 74) {
                    $update['306'] = 10306;
                } else if($toolid == 75) {
                    $update['308'] = 10308;
                } else if($toolid == 76) {
                    $update['310'] = 10310;
                } else if($toolid == 77) {
                    $update['354'] = 10354;
                } else if($toolid == 78) {
                    $update['359'] = 10359;
                } else if($toolid == 79) {
                    $update['445'] = 10445;
                } else if($toolid == 80) {
                    $update['448'] = 10448;
                } else if($toolid == 81) {
                    $update['460'] = 10460;
                }
                //判断Mega石是否正确
                if($update[$my['id_quanguo']]) {
                    //是的话进行进化操作
                    DB::query("UPDATE cdb_kd8c_my_new_pm SET id_quanguo=" . $update[$my['id_quanguo']] . " WHERE uid='" . $_G[uid] . "' and pmid='" . $my['pmid'] . "'");
                }
            }
        }
        DB::query("UPDATE cdb_kd8c_my_new_pm SET showtype=$showtype WHERE pmid='$cmd'");
        $query  = DB::query("select * from cdb_kd8c_my_new_pm  WHERE pmid='$cmd'");
        $pet_id = DB::fetch($query);
        if($pet_id['id_quanguo'] > 0) add_pet_history($_G['uid'], $_G['username'], $pet_id['id_quanguo'], $pet_id['shine']);
        showmessage("宠物取出成功。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=4');
    }
} //判断act操作，18为箱子顺序
else if($_G['gp_act'] == 18) {
    $seta = intval($_G['gp_seta']);
    $setb = intval($_G['gp_setb']);
    if($seta != 0 && $setb != 0) {
        $qb = DB::query("SELECT orderlist FROM cdb_kd8c_my_new_pm where pmid='" . $seta . "' and uid='" . $_G[uid] . "'");
        $qa = DB::fetch($qb);
        $qc = DB::query("SELECT orderlist FROM cdb_kd8c_my_new_pm where pmid='" . $setb . "' and uid='" . $_G[uid] . "'");
        $qd = DB::fetch($qc);
        if($qa != '' && $qb != '') {
            DB::query("Update cdb_kd8c_my_new_pm SET orderlist=" . $qd['orderlist'] . " where pmid='" . $seta . "'");
            DB::query("Update cdb_kd8c_my_new_pm SET orderlist=" . $qa['orderlist'] . " where pmid='" . $setb . "'");
            showmessage("调整完毕。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=4');
        } else showmessage("无法继续移动。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=4');
    } else showmessage("无法继续移动。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=4');
} //判断act操作，19为队伍顺序
else if($_G['gp_act'] == 19) {
    $seta = intval($_G['gp_seta']);
    $setb = intval($_G['gp_setb']);
    if($seta != 0 && $setb != 0) {
        $qb = DB::query("SELECT orderlist FROM cdb_kd8c_my_new_pm where pmid='" . $seta . "' and uid='" . $_G[uid] . "'");
        $qa = DB::fetch($qb);
        $qc = DB::query("SELECT orderlist FROM cdb_kd8c_my_new_pm where pmid='" . $setb . "' and uid='" . $_G[uid] . "'");
        $qd = DB::fetch($qc);
        if($qa != '' && $qb != '') {
            DB::query("Update cdb_kd8c_my_new_pm SET orderlist=" . $qd['orderlist'] . " where pmid='" . $seta . "'");
            DB::query("Update cdb_kd8c_my_new_pm SET orderlist=" . $qa['orderlist'] . " where pmid='" . $setb . "'");
            showmessage("调整完毕。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
        } else showmessage("无法继续移动。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
    } else showmessage("无法继续移动。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
} //判断act操作，20为锁定
else if($_G['gp_act'] == 20) {
    $cmd = intval($_G['gp_cmd']);
    DB::query("UPDATE `cdb_kd8c_my_new_pm` SET `lock`=`lock`*-1+1 where pmid=" . $cmd);
    showmessage("操作成功。", 'plugin.php?id=kd8c:kd8c&act=0&cmd=0');
} //判断act操作，默认为显示界面
else {
    //设置标题
    $navigation = $navigation . $kd8c['system_name'] . " - " . $kd8c_kd8c['system_name'];
    //判断参数，1为显示道具背包
    if($_G['gp_cmd'] == 1) {
        $kd8c_kd8c_title['navigation'] = $kd8c_kd8c_title['title_name2'];
        $query                         = DB::query("SELECT * FROM cdb_kd8c_my_tool m LEFT JOIN cdb_kd8c_tool_toollist t ON t.toolid=m.toolid where m.uid='" . $_G[uid] . "'ORDER BY m.mytoolid");
        $i                             = 1;
        while($my = DB::fetch($query)) {
            if($my['toolorder'] == 1) {
                $src             = "xiaohao/";
                $type            = $kd8c_tool_name['xiaohao'];
                $my['toolorder'] = "使用";
            } else if($my['toolorder'] == 2) {
                $src             = "zhuangbei/";
                $type            = $kd8c_tool_name['zhuangbei'];
                $my['toolorder'] = "装备";
            } else if($my['toolorder'] == 3) {
                $src             = "ball/";
                $type            = $kd8c_tool_name['zhuangbei1'];
                $my['toolorder'] = "装备";
            }
            $selectlist = "<select style=\"width=120\" name=\"selectlist\">";
            if($my['tooluseobject'] == 1) {
                for($j = 1; ; $j++) {
                    if($kd8c_user[$j]['pmid']) {
                        $selectlist .= "<option value=\"" . $kd8c_user[$j]['pmid'] . "\">" . $kd8c_user[$j]['pmname'] . "</option>";
                    } else {
                        break;
                    }
                }
            } else if($my['tooluseobject'] == 2) {
                $selectlist .= "<option value=\"\">$_G[username]</option>";
            }
            $selectlist .= "</select>";
            if($i % 3 == 1) {
                $kd8c_selllist .= "<tr class=\"fl_row\" valign=\"top\"><td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/tool/" . $src . $my['toolid'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\">" . $kd8c_tool_name['toolname'] . ":" . $my['toolname'] . "</td></tr><tr><td class=\"fl_row\">" . $my['toolintroduce'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c\" name=\"mytool" . $i . "\"><td class=\"category\" width=\"80%\">给" . $selectlist . "&nbsp;&nbsp;<a onclick=\"mytool" . $i . ".submit();\" >" . $my['toolorder'] . "</a><input type=\"hidden\" value=\"5\" name=\"act\"><input type=\"hidden\" value=\"" . $my['mytoolid'] . "\" name=\"cmd\"></td><td class=\"category\" width=\"20%\" align=\"center\" valign=\"bottom\"><a href='plugin.php?id=kd8c:kd8c&act=6&cmd=" . $my['mytoolid'] . "' onclick=\"return confirm('" . $kd8c_kd8c_showmsg['msg7'] . "');\">丢弃</a></td></form></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td>";
            } else if($i % 3 == 2) {
                $kd8c_selllist .= "<td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/tool/" . $src . $my['toolid'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\">" . $kd8c_tool_name['toolname'] . ":" . $my['toolname'] . "</td></tr><tr><td class=\"fl_row\">" . $my['toolintroduce'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c\" name=\"mytool" . $i . "\"><td class=\"category\" width=\"80%\">给" . $selectlist . "&nbsp;&nbsp;<a onclick=\"mytool" . $i . ".submit();\" >" . $my['toolorder'] . "</a><input type=\"hidden\" value=\"5\" name=\"act\"><input type=\"hidden\" value=\"" . $my['mytoolid'] . "\" name=\"cmd\"></td><td class=\"category\" width=\"20%\" align=\"center\" valign=\"bottom\"><a href='plugin.php?id=kd8c:kd8c&act=6&cmd=" . $my['mytoolid'] . "' onclick=\"return confirm('" . $kd8c_kd8c_showmsg['msg7'] . "');\">丢弃</a></td></form></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td>";
            } else if($i % 3 == 0) {
                $kd8c_selllist .= "<td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/tool/" . $src . $my['toolid'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\">" . $kd8c_tool_name['toolname'] . ":" . $my['toolname'] . "</td></tr><tr><td class=\"fl_row\">" . $my['toolintroduce'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c\" name=\"mytool" . $i . "\"><td class=\"category\" width=\"80%\">给" . $selectlist . "&nbsp;&nbsp;<a onclick=\"mytool" . $i . ".submit();\" >" . $my['toolorder'] . "</a><input type=\"hidden\" value=\"5\" name=\"act\"><input type=\"hidden\" value=\"" . $my['mytoolid'] . "\" name=\"cmd\"></td><td class=\"category\" width=\"20%\" align=\"center\" valign=\"bottom\"><a href='plugin.php?id=kd8c:kd8c&act=6&cmd=" . $my['mytoolid'] . "' onclick=\"return confirm('" . $kd8c_kd8c_showmsg['msg7'] . "');\">丢弃</a></td></form></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td></tr>
				";
            }
            $i++;
        }
        if($i % 3 == 2) {
            $kd8c_selllist .= "<td width=\"33%\"> </td><td width=\"33%\"> </td></tr>";
        }
        if($i % 3 == 0) {
            $kd8c_selllist .= "<td width=\"33%\"> </td></tr>";
        }
        if(!$kd8c_selllist) {
            $kd8c_selllist = "<tr class=\"fl_row\" valign=\"top\"><td width=\"100%\" colspan=\"3\" align=\"center\">" . $kd8c_kd8c_showmsg['msg10'] . "</td></tr>";
        }
        //导入模板
        include template('kd8c:kd8c_kd8c_mybag');
    } //判断参数，2为显示箱子
    else if($_G['gp_cmd'] == 2) {
        $kd8c_kd8c_title['navigation'] = $kd8c_kd8c_title['title_name3'];
        $query                         = DB::query("SELECT * FROM cdb_kd8c_my_box m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.ptid where m.uid='" . $_G[uid] . "' AND (m.boxtype='宠物' or m.boxtype='闪光') ORDER BY m.boxid");
        $i                             = 1;
        while($my = DB::fetch($query)) {
            if($i % 3 == 1) {
                $pet_selllist .= "<tr class=\"fl_row\" valign=\"top\"><td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/pet/icon/" . $src . $my['id_quanguo'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\" width=\"25%\">" . $kd8c_pet_name['id_quanguo'] . $my['id_quanguo'] . "</td><td class=\"altbg1\" width=\"75%\">" . $kd8c_tool_name['petname'] . ":" . $my['name_cn'] . "</td></tr><tr><td class=\"altbg1\" colspan=\"2\">" . $kd8c_kd8c_name['fromname'] . $my['fromname'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"category\" width=\"50%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=7&cmd=" . $my['boxid'] . "\">取出</a></td><td class=\"category\" width=\"50%\" align=\"center\" valign=\"bottom\"><a href=\"plugin.php?id=kd8c:kd8c&act=0&cmd=2\"  onclick=\"if(confirm('" . $kd8c_kd8c_showmsg['msg7'] . "')){location.href='plugin.php?id=kd8c:kd8c&act=8&cmd=" . $my['boxid'] . "';}\">丢弃</a></td></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td>";
            } else if($i % 3 == 2) {
                $pet_selllist .= "<td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/pet/icon/" . $src . $my['id_quanguo'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\" width=\"25%\">" . $kd8c_pet_name['id_quanguo'] . $my['id_quanguo'] . "</td><td class=\"altbg1\" width=\"75%\">" . $kd8c_tool_name['petname'] . ":" . $my['name_cn'] . "</td></tr><tr><td class=\"altbg1\" colspan=\"2\">" . $kd8c_kd8c_name['fromname'] . $my['fromname'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"category\" width=\"50%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=7&cmd=" . $my['boxid'] . "\">取出</a></td><td class=\"category\" width=\"50%\" align=\"center\" valign=\"bottom\"><a href=\"plugin.php?id=kd8c:kd8c&act=0&cmd=2\"   onclick=\"if(confirm('" . $kd8c_kd8c_showmsg['msg7'] . "')){location.href='plugin.php?id=kd8c:kd8c&act=8&cmd=" . $my['boxid'] . "';}\">丢弃</a></td></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td>";
            } else if($i % 3 == 0) {
                $pet_selllist .= "<td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/pet/icon/" . $src . $my['id_quanguo'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\" width=\"25%\">" . $kd8c_pet_name['id_quanguo'] . $my['id_quanguo'] . "</td><td class=\"altbg1\" width=\"75%\">" . $kd8c_tool_name['petname'] . ":" . $my['name_cn'] . "</td></tr><tr><td class=\"altbg1\" colspan=\"2\">" . $kd8c_kd8c_name['fromname'] . $my['fromname'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"category\" width=\"50%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=7&cmd=" . $my['boxid'] . "\">取出</a></td><td class=\"category\" width=\"50%\" align=\"center\" valign=\"bottom\"><a

href=\"plugin.php?id=kd8c:kd8c&act=0&cmd=2\"  onclick=\"if(confirm('" . $kd8c_kd8c_showmsg['msg7'] . "')){location.href='plugin.php?id=kd8c:kd8c&act=8&cmd=" . $my['boxid'] . "';}\">丢弃</a></td></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td></tr>";
            }
            $i++;
        }
        if($i % 3 == 2) {
            $pet_selllist .= "<td width=\"33%\"> </td><td width=\"33%\"> </td></tr>";
        }
        if($i % 3 == 0) {
            $pet_selllist .= "<td width=\"33%\"> </td></tr>";
        }
        $query = DB::query("SELECT * FROM cdb_kd8c_my_box m LEFT JOIN cdb_kd8c_tool_toollist t ON t.toolid=m.ptid where m.uid='" . $_G[uid] . "' AND m.boxtype='道具' ORDER BY m.boxid");
        $i     = 1;
        while($my = DB::fetch($query)) {
            if($my['toolorder'] == 1) {
                $src = "xiaohao/";
            } else if($my['toolorder'] == 2) {
                $src = "zhuangbei/";
            } else if($my['toolorder'] == 3) {
                $src = "ball/";
            }
            if($i % 3 == 1) {
                $tool_selllist .= "<tr class=\"fl_row\" valign=\"top\"><td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/tool/" . $src . $my['toolid'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\" width=\"25%\">" . $kd8c_pet_name['id_quanguo'] . $my['toolid'] . "</td><td class=\"altbg1\" width=\"75%\">" . $kd8c_tool_name['toolname'] . ":" . $my['toolname'] . "</td></tr><tr><td class=\"altbg1\" colspan=\"2\">" . $kd8c_kd8c_name['fromname'] . $my['fromname'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"category\" width=\"50%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=7&cmd=" . $my['boxid'] . "\">取出</a></td><td class=\"category\" width=\"50%\" align=\"center\" valign=\"bottom\"><a href=\"plugin.php?id=kd8c:kd8c&act=0&cmd=2\" onclick=\"if(confirm('" . $kd8c_kd8c_showmsg['msg7'] . "')){location.href='plugin.php?id=kd8c:kd8c&act=8&cmd=" . $my['boxid'] . "';}\">丢弃</a></td></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td>";
            } else if($i % 3 == 2) {
                $tool_selllist .= "<td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/tool/" . $src . $my['toolid'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\" width=\"25%\">" . $kd8c_pet_name['id_quanguo'] . $my['toolid'] . "</td><td class=\"altbg1\" width=\"75%\">" . $kd8c_tool_name['toolname'] . ":" . $my['toolname'] . "</td></tr><tr><td class=\"altbg1\" colspan=\"2\">" . $kd8c_kd8c_name['fromname'] . $my['fromname'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"category\" width=\"50%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=7&cmd=" . $my['boxid'] . "\">取出</a></td><td class=\"category\" width=\"50%\" align=\"center\" valign=\"bottom\"><a href=\"plugin.php?id=kd8c:kd8c&act=0&cmd=2\" onclick=\"if(confirm('" . $kd8c_kd8c_showmsg['msg7'] . "')){location.href='plugin.php?id=kd8c:kd8c&act=8&cmd=" . $my['boxid'] . "';}\">丢弃</a></td></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td>";
            } else if($i % 3 == 0) {
                $tool_selllist .= "<td width=\"33%\" align=\"center\"><table border=\"0\" width=\"95%\" cellspacing=\"0\" cellpadding=\"0\"><tr height=\"5\"><td colspan=\"2\"></td></tr><tr valign=\"top\"><td width=\"37\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"37\"><tr><td><img src=\"images/kd8c/tool/" . $src . $my['toolid'] . ".gif\"></td></tr></table></td><td width=\"100%\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"altbg1\" width=\"25%\">" . $kd8c_pet_name['id_quanguo'] . $my['toolid'] . "</td><td class=\"altbg1\" width=\"75%\">" . $kd8c_tool_name['toolname'] . ":" . $my['toolname'] . "</td></tr><tr><td class=\"altbg1\" colspan=\"2\">" . $kd8c_kd8c_name['fromname'] . $my['fromname'] . "</td></tr></table></td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr><td class=\"category\" width=\"50%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=7&cmd=" . $my['boxid'] . "\">取出</a></td><td class=\"category\" width=\"50%\" align=\"center\" valign=\"bottom\"><a href=\"plugin.php?id=kd8c:kd8c&act=0&cmd=2\" onclick=\"if(confirm('" . $kd8c_kd8c_showmsg['msg7'] . "')){location.href='plugin.php?id=kd8c:kd8c&act=8&cmd=" . $my['boxid'] . "';}\">丢弃</a></td></tr></table></td></tr><tr height=\"5\"><td colspan=\"2\"></td></tr></table></td></tr>";
            }
            $i++;
        }
        if($i % 3 == 2) {
            $tool_selllist .= "<td width=\"33%\"> </td><td width=\"33%\"> </td></tr>";
        }
        if($i % 3 == 0) {
            $tool_selllist .= "<td width=\"33%\"> </td></tr>";
        }
        //导入模板
        include template('kd8c:kd8c_kd8c_mybox');
    } //判断参数，3为显示家族
    else if($_G['gp_cmd'] == 3) {
        $kd8c_kd8c_title['navigation'] = $kd8c_kd8c_title['title_name4'];
        if($kd8c_user['clanid']) {
            $query      = DB::query("SELECT * FROM cdb_kd8c_my_clan mc
								LEFT JOIN " . DB::table('common_member') . " mb ON mb.uid=mc.uid
				                LEFT JOIN " . DB::table('common_member_count') . " cmc ON cmc.uid=mc.uid
								WHERE mc.clanid='" . $kd8c_user['clanid'] . "' ORDER BY mc.group,myclanid");
            $master2num = 1;
            while($my = DB::fetch($query)) {
                $query1 = DB::query("SELECT * FROM cdb_kd8c_my_new_pm WHERE uid='" . $my['uid'] . "' AND showtype IN ('1','2')");
                $pmshow = "";
                while($mypm = DB::fetch($query1)) {
                    if($mypm['pmball'] == 28) {
                        $pmshow .= "<img src=\"images/kd8c/pet/icon/" . $mypm['id_quanguo'] . ".gif\">";
                    } else {
                        $pmshow .= "<img src=\"images/kd8c/tool/ball/" . $mypm['pmball'] . ".gif\">";
                    }
                }
                if($kd8c_user['groupid'] == 1 || $kd8c_user['groupid'] == 2) {
                    $audit   = "[<a href=\"plugin.php?id=kd8c:kd8c&act=14&cmd=" . $my['uid'] . "\">审核</a>] [<a href=\"plugin.php?id=kd8c:kd8c&act=15&cmd=" . $my['uid'] . "\">开除</a>]";
                    $unaudit = "[<a href=\"plugin.php?id=kd8c:kd8c&act=15&cmd=" . $my['uid'] . "\">开除</a>]";
                }
                if($my['group'] == 1) {
                    $master1list .= "<tr class=\"fl_row\" align=\"center\"><td>" . $my['uid'] . "</td><td>" . $my['username'] . "</td><td align=\"right\">" . $my['credits'] . "</td><td align=\"right\">" . $my[$moneycredits] . "</td><td align=\"right\">" . $my[$bankcredits] . "</td><td>" . $pmshow . "</td><td align=\"center\">" . $kd8c_user['master1name'] . "</td></tr>";
                } else if($my['group'] == 2) {
                    $kd8c_user['master2'][$master2num] = $my['username'];
                    $master2num++;
                    $master2list .= "<tr class=\"fl_row\"><td>" . $my['uid'] . "</td><td>" . $my['username'] . "</td><td align=\"right\">" . $my['credits'] . "</td><td align=\"right\">" . $my[$moneycredits] . "</td><td align=\"right\">" . $my[$bankcredits] . "</td><td>" . $pmshow . "</td><td align=\"center\">" . $kd8c_user['master2name'] . "</td></tr>";
                } else if($my['group'] == 3) {
                    $memberlist .= "<tr class=\"fl_row\"><td>" . $my['uid'] . "</td><td>" . $my['username'] . "</td><td align=\"right\">" . $my['credits'] . "</td><td align=\"right\">" . $my[$moneycredits] . "</td><td align=\"right\">" . $my[$bankcredits] . "</td><td>" . $pmshow . "</td><td align=\"center\">" . $kd8c_clan_name['membername1'] . $unaudit . "</td></tr>";
                } else {
                    $memberlist .= "<tr class=\"fl_row\"><td>" . $my['uid'] . "</td><td>" . $my['username'] . "</td><td align=\"right\">" . $my['credits'] . "</td><td align=\"right\">" . $my[$moneycredits] . "</td><td align=\"right\">" . $my[$bankcredits] . "</td><td>" . $pmshow . "</td><td align=\"center\">" . $kd8c_clan_name['membername2'] . $audit . "</td></tr>";
                }
            }
            $memberlist = $master1list . $master2list . $memberlist;
            //导入模板
            include template('kd8c:kd8c_kd8c_myclan');
        } else {
            showmessage($kd8c_kd8c_showmsg['msg26'], 'plugin.php?id=kd8c:kd8c_clan&act=0&cmd=0');
        }
    } //判断参数，3-9为显示宠物箱排序
    else if($_G['gp_cmd'] >= 4 && $_G['gp_cmd'] <= 9) {
        $cmd                           = $_G['gp_cmd'];
        $kd8c_kd8c_title['navigation'] = $kd8c_kd8c_title['title_name5'];
        /*
        $query = DB::query("SELECT boxsize FROM ".DB::table('common_member_count')." WHERE uid='".$_G[uid]."'");
        $kd8c_user2 = $db->result($query, 0);
        $boxsize=explode(",",$kd8c_user2);
        $petboxsize=$boxsize[0];
        */
        $petboxsize = $user_extcredits[$pmboxcredits];
        if(!$petboxsize) {
            $petboxsize = "未开通，目前空间为0。";
        }
        switch($cmd) {
            case 5:
                $kd8c_box_order['navigation'] = $kd8c_box_order['title_name2'];
                $query                        = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY vid ASC ");
                break;
            case 6:
                $kd8c_box_order['navigation'] = $kd8c_box_order['title_name3'];
                $query                        = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY vid DESC ");
                break;
            case 7:
                $kd8c_box_order['navigation'] = $kd8c_box_order['title_name4'];
                $query                        = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY lv ASC ");
                break;
            case 8:
                $kd8c_box_order['navigation'] = $kd8c_box_order['title_name5'];
                $query                        = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY lv DESC ");
                break;
            case 9:
                $kd8c_box_order['navigation'] = $kd8c_box_order['title_name6'];
                $query                        = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY sex DESC ");
                break;
            default:
                $kd8c_box_order['navigation'] = $kd8c_box_order['title_name1'];
                $query                        = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid,m.orderlist FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY orderlist");
                $que                          = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid,m.orderlist FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY orderlist");
                break;
        }

        $i      = 1;
        $k      = 1;
        $set    = array();
        $set[0] = 0;
        while($me = DB::fetch($que)) {
            $set[$k] = $me['pmid'];
            $k++;
        }
        $set[$k] = 0;
        $k       = 1;
        while($my = DB::fetch($query)) {
            //制定图片列表
            //$set[$k]=$my['pmid'];
            $my['selected'][$my['pic']] = " selected";
            $piclist                    = "<select style=\"width=156\" name=\"selectlist\" onchange=\"document.getElementById('petimg" . $i . "').innerHTML='<img src=images/kd8c/pet/pm/" . $my['id_quanguo'] . "_'+mypet" . $i . ".selectlist.value+'.jpg>';\">";
            for($j = 1; $j <= $my['maxpic']; $j++) {
                $piclist .= "<option value=\"" . $j . "\" " . $my['selected'][$j] . ">" . $my['name_cn'] . "图片" . $j . "</option>";
            }
            $piclist .= "</select>";

            $my['shuxing'] = $my['shuxing1'] . ($my['shuxing2'] != "-" ? " + " . $my['shuxing2'] : ''); // 制定属性显示格式
            $my['exp'] = floor(($my['exp'] - pow($my['lv'], 2)) * 100 / (pow($my['lv'] + 1, 2) - pow($my['lv'], 2)));// 计算经验百分比
            $my['hp_zz'] = hpjisuan($my['hp_zz'], $my['hp_gt'], $my['hp_nl'], $my['lv']);//计算能力
            $my['hp_now'] = $my['hp_now'] > $my['hp_zz'] ? $my['hp_zz'] : $my['hp_now'];

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
				<td width=\"25\"><img src=\"images/kd8c/tool/zhuangbei/" . $my['daoju'] . ".gif\"></td>";
            if($cmd == 4)
                $kd8c_selllist .= "<td width=\"30\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=18&seta=" . $set[$k - 1] . "&setb=" . $my['pmid'] . "\">上移</a></td>
				<td width=\"30\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=18&seta=" . $my['pmid'] . "&setb=" . $set[$k + 1] . "\">下移</a></td>";
            $kd8c_selllist .= "<td width=\"30\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=17&cmd=" . $my['pmid'] . "\">取出</a></td>
			</tr>
			";
            $k = $k + 1;
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
        include template('kd8c:kd8c_kd8c_pcpmbox');
    } //判断参数，10为显示属性筛选
    else if($_G['gp_cmd'] == 10) {
        $cmd                           = $_G['gp_cmd'];
        $selectnum                     = intval($_G['gp_selectlist']);
        $kd8c_kd8c_title['navigation'] = $kd8c_kd8c_title['title_name5'];

        $petboxsize = $user_extcredits[$pmboxcredits];
        if(!$petboxsize) {
            $petboxsize = "未开通，目前空间为0。";
        }


        $selectlist = "<form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c&act=0&cmd=10\" name=\"typeselect\">";
        $selectlist .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select style=\"width=120\" name=\"selectlist\">";
        $selectlist .= "<option value=\"0\">全部</option>";
        $selectlist .= "<option value=\"19\">火</option>";
        $selectlist .= "<option value=\"1\">水</option>";
        $selectlist .= "<option value=\"2\">草</option>";
        $selectlist .= "<option value=\"3\">电</option>";
        $selectlist .= "<option value=\"4\">冰</option>";
        $selectlist .= "<option value=\"5\">龙</option>";
        $selectlist .= "<option value=\"6\">虫</option>";
        $selectlist .= "<option value=\"7\">岩</option>";
        $selectlist .= "<option value=\"8\">钢</option>";
        $selectlist .= "<option value=\"9\">地</option>";
        $selectlist .= "<option value=\"10\">超</option>";
        $selectlist .= "<option value=\"11\">鬼</option>";
        $selectlist .= "<option value=\"12\">恶</option>";
        $selectlist .= "<option value=\"13\">毒</option>";
        $selectlist .= "<option value=\"14\">飞</option>";
        $selectlist .= "<option value=\"15\">斗</option>";
        $selectlist .= "<option value=\"16\">普</option>";
        $selectlist .= "<option value=\"17\">妖</option>";
        $selectlist .= "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $selectlist .= "<button onclick=\"document.typeselect.submit();\" >筛选</button></form> ";
        //$kd8c_selllist.=$_POST['selectlist'].$selectnum;
        //$selectnum=5;
        switch($selectnum) {
            case 19:
                $st = '火';
                break;
            case 1:
                $st = '水';
                break;
            case 2:
                $st = '草';
                break;
            case 3:
                $st = '电';
                break;
            case 4:
                $st = '冰';
                break;
            case 5:
                $st = '龙';
                break;
            case 6:
                $st = '虫';
                break;
            case 7:
                $st = '岩';
                break;
            case 8:
                $st = '钢';
                break;
            case 9:
                $st = '地';
                break;
            case 10:
                $st = '超';
                break;
            case 11:
                $st = '鬼';
                break;
            case 12:
                $st = '恶';
                break;
            case 13:
                $st = '毒';
                break;
            case 14:
                $st = '飞';
                break;
            case 15:
                $st = '斗';
                break;
            case 16:
                $st = '普';
                break;
            case 17:
                $st = '妖';
                break;
            default:
                break;
        }
        $kd8c_box_order['navigation'] = $kd8c_box_order['title_name7'] . " " . $st;
        if($st) {
            $query = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid,m.orderlist FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') and (shuxing1='" . $st . "' or shuxing2='" . $st . "')ORDER BY shuxing1,shuxing2");

        } else {
            $query = DB::query("SELECT m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic,p.vid,m.orderlist FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('3') ORDER BY shuxing1,shuxing2");
        }
        $i = 1;
        $kd8c_selllist .= $selectlist;
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
				<td width=\"30\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=17&cmd=" . $my['pmid'] . "\">取出</a></td>
			</tr>
			";
        }
        $kd8c_selllist = "
		<tr class=\"fl_row\" valign=\"top\">
			<td width=\"100%\">
					<table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\">
			" . $kd8c_selllist . "
					</table>
			</td>
		</tr>";
        if(!$kd8c_selllist) {
            $kd8c_selllist = "<tr class=\"fl_row\" valign=\"top\"><td width=\"100%\" colspan=\"3\" align=\"center\">" . $kd8c_kd8c_showmsg['msg9'] . "</td></tr>";
        }
        //导入模板
        include template('kd8c:kd8c_kd8c_pcpmbox');
    } //判断参数，默认为显示我的宠物小精灵
    else {
        $kd8c_kd8c_title['navigation'] = $kd8c_kd8c_title['title_name1'];
        $query                         = DB::query("SELECT m.lock,m.shine ,m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,m.showtype,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('1','2') ORDER BY showtype,orderlist");
        $que                           = DB::query("SELECT m.lock,m.shine ,m.pmid,m.id_quanguo,m.pmname,m.lv,m.exp,m.hp_now,m.pmball,m.daoju,m.hp_gt,m.gongji_gt,m.fangyu_gt,m.minjie_gt,m.tegong_gt,m.tefang_gt,m.hp_nl,m.gongji_nl,m.fangyu_nl,m.minjie_nl,m.tegong_nl,m.tefang_nl,m.pic,m.sex,p.name_cn,p.hp_zz,p.gongji_zz,p.fangyu_zz,p.minjie_zz,p.tegong_zz,p.tefang_zz,p.shuxing1,p.shuxing2,p.maxpic FROM cdb_kd8c_my_new_pm m LEFT JOIN cdb_kd8c_pet_pmlist p ON p.id_quanguo=m.id_quanguo where uid='" . $_G[uid] . "' and showtype in ('2') ORDER BY showtype,orderlist");
        $i                             = 1;
        $k                             = 1;
        $set                           = array();
        $set[0]                        = 0;
        while($me = DB::fetch($que)) {
            $set[$k] = $me['pmid'];
            $k++;
        }
        $set[$k] = 0;
        $k       = 0;
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
            $hpamount        = $my['hp_zz'] - $my['hp_now'];
            $my['gongji_zz'] = pm5vjisuan($my['gongji_zz'], $my['gongji_gt'], $my['gongji_nl'], $my['lv']);
            $my['fangyu_zz'] = pm5vjisuan($my['fangyu_zz'], $my['fangyu_gt'], $my['fangyu_nl'], $my['lv']);
            $my['minjie_zz'] = pm5vjisuan($my['minjie_zz'], $my['minjie_gt'], $my['minjie_nl'], $my['lv']);
            $my['tegong_zz'] = pm5vjisuan($my['tegong_zz'], $my['tegong_gt'], $my['tegong_nl'], $my['lv']);
            $my['tefang_zz'] = pm5vjisuan($my['tefang_zz'], $my['tefang_gt'], $my['tefang_nl'], $my['lv']);
            //制定输出
            if($i % 2 == 1) {
                $kd8c_selllist .= "<tr  valign=\"top\"><td width=\"50%\"><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c&act=1\" name=\"mypet" . $i . "\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr valign=\"top\"><td width=\"161\"><div id='petimg" . $i . "' style=\"width: 156px; height: 120px\"><img src=\"images/kd8c/pet/pm/" . $my['id_quanguo'] . "_" . $my['pic'] . ".jpg\"></div><br>$piclist</td><td><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\"><td width=\"33%\">" . $kd8c_pet_name['id_quanguo'] . $my['id_quanguo'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['name_cn'] . "<input type=\"text\" name=\"pmname\" size=\"14\" value=\"" . $my['pmname'] . "\"><input type=\"hidden\" value=\"1\" name=\"act\"><input type=\"hidden\" value=\"" . $my['pmid'] . "\" name=\"cmd\">";
                if($my['shine'] == 1)
                    $kd8c_selllist .= "&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"#E1E100\">★★★</font>";
                $kd8c_selllist .= "</td></tr><tr ><td>性别:" . $my['sex'] . "</td><td colspan=\"2\">" . $kd8c_pet_name['shuxing'] . $my['shuxing'] . "</td></tr><tr ><td width=\"33%\">" . $kd8c_pet_name['lv'] . $my['lv'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['exp'] . $my['exp'] . "%</td></tr><tr ><td colspan=\"3\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"50%\">" . $kd8c_pet_name['hp_zz'] . $my['hp_now'] . "/" . $my['hp_zz'] . "</td><td width=\"50%\">" . $kd8c_pet_name['tegong_zz'] . $my['tegong_zz'] . "</td></tr><tr><td width=\"50%\">" . $kd8c_pet_name['gongji_zz'] . $my['gongji_zz'] . "</td><td width=\"50%\">" . $kd8c_pet_name['tefang_zz'] . $my['tefang_zz'] . "</td></tr><tr><td width=\"50%\">" . $kd8c_pet_name['fangyu_zz'] . $my['fangyu_zz'] . "</td><td width=\"50%\">" . $kd8c_pet_name['minjie_zz'] . $my['minjie_zz'] . "</td></tr></table></td></tr><tr ><td width=\"25%\">" . $kd8c_pet_name['zhuangbei'] . "</td><td width=\"25%\"><img src=\"images/kd8c/tool/ball/" . $my['pmball'] . ".gif\"></td><td width=\"25%\"><img src=\"images/kd8c/tool/zhuangbei/" . $my['daoju'] . ".gif\"></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c&act=20&cmd=" . $my['pmid'] . "\"><img src=\"images/kd8c/pet/images/lock_" . $my['lock'] . ".gif\"></a></td></tr></table></td></tr><tr><td height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\" align=\"center\"><td width=\"20%\"><button onclick=\"document.mypet" . $i . ".submit();\" >保存设置</button></td><td width=\"20%\"><a href=\"plugin.php?id=kd8c:kd8c&act=2&cmd=" . $my['pmid'] . "\">卸下道具</a></td><td width=\"20%\"><a href=\"plugin.php?id=kd8c:kd8c&act=3&cmd=" . $my['pmid'] . "\">升到队首</a></td>";
                if($my['showtype'] == 2)
                    $kd8c_selllist .= "<td width=\"10%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=19&seta=" . $set[$k - 1] . "&setb=" . $my['pmid'] . "\">上移</a></td>
				<td width=\"10%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=19&seta=" . $my['pmid'] . "&setb=" . $set[$k + 1] . "\">下移</a></td>";
                $kd8c_selllist .= "<td width=\"10%\"><a href=\"plugin.php?id=kd8c:kd8c&act=16&cmd=" . $my['pmid'] . "\">寄存</a></td><td width=\"20%\"><a href='plugin.php?id=kd8c:kd8c&act=4&cmd=" . $my['pmid'] . "' onclick=\"return confirm('" . $kd8c_kd8c_showmsg['msg5'] . "');\">放生</a></td></tr></table></td></tr></table></form></td>";
            } else {
                $kd8c_selllist .= "<td width=\"50%\"><form method=\"POST\" action=\"plugin.php?id=kd8c:kd8c&act=1\" name=\"mypet" . $i . "\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr valign=\"top\">><td width=\"161\"><div id='petimg" . $i . "' style=\"width: 156px; height: 120px\"><img src=\"images/kd8c/pet/pm/" . $my['id_quanguo'] . "_" . $my['pic'] . ".jpg\"></div><br>$piclist</td><td><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\"><td width=\"33%\">" . $kd8c_pet_name['id_quanguo'] . $my['id_quanguo'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['name_cn'] . "<input type=\"text\" name=\"pmname\" size=\"14\" value=\"" . $my['pmname'] . "\"><input type=\"hidden\" value=\"1\" name=\"act\"><input type=\"hidden\" value=\"" . $my['pmid'] . "\" name=\"cmd\">";
                if($my['shine'] == 1)
                    $kd8c_selllist .= "&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"#E1E100\">★★★</font>";
                $kd8c_selllist .= "</td></tr><tr ><td>性别:" . $my['sex'] . "</td><td colspan=\"2\">" . $kd8c_pet_name['shuxing'] . $my['shuxing'] . "</td></tr><tr ><td width=\"33%\">" . $kd8c_pet_name['lv'] . $my['lv'] . "</td><td width=\"66%\" colspan=\"2\">" . $kd8c_pet_name['exp'] . $my['exp'] . "%</td></tr><tr ><td colspan=\"3\"><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"50%\">" . $kd8c_pet_name['hp_zz'] . $my['hp_now'] . "/" . $my['hp_zz'] . "</td><td width=\"50%\">" . $kd8c_pet_name['tegong_zz'] . $my['tegong_zz'] . "</td></tr><tr><td width=\"50%\">" . $kd8c_pet_name['gongji_zz'] . $my['gongji_zz'] . "</td><td width=\"50%\">" . $kd8c_pet_name['tefang_zz'] . $my['tefang_zz'] . "</td></tr><tr><td width=\"50%\">" . $kd8c_pet_name['fangyu_zz'] . $my['fangyu_zz'] . "</td><td width=\"50%\">" . $kd8c_pet_name['minjie_zz'] . $my['minjie_zz'] . "</td></tr></table></td></tr><tr ><td width=\"25%\">" . $kd8c_pet_name['zhuangbei'] . "</td><td width=\"25%\"><img src=\"images/kd8c/tool/ball/" . $my['pmball'] . ".gif\"></td><td width=\"25%\"><img src=\"images/kd8c/tool/zhuangbei/" . $my['daoju'] . ".gif\"></td><td width=\"25%\"><a href=\"plugin.php?id=kd8c:kd8c&act=20&cmd=" . $my['pmid'] . "\"><img src=\"images/kd8c/pet/images/lock_" . $my['lock'] . ".gif\"></a></td></tr></table></td></tr><tr><td height=\"5\"></td></tr><tr><td colspan=\"2\"><table cellspacing=\"1\" cellpadding=\"4\" width=\"100%\" class=\"tableborder\"><tr class=\"category\" align=\"center\"><td width=\"20%\"><button onclick=\"document.mypet" . $i . ".submit();\" >保存设置</button></td><td width=\"20%\"><a href=\"plugin.php?id=kd8c:kd8c&act=2&cmd=" . $my['pmid'] . "\">卸下道具</a></td><td width=\"20%\"><a href=\"plugin.php?id=kd8c:kd8c&act=3&cmd=" . $my['pmid'] . "\">升到队首</a></td>";
                if($my['showtype'] == 2)
                    $kd8c_selllist .= "<td width=\"10%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=19&seta=" . $set[$k - 1] . "&setb=" . $my['pmid'] . "\">上移</a></td>
				<td width=\"10%\" align=\"center\"><a href=\"plugin.php?id=kd8c:kd8c&act=19&seta=" . $my['pmid'] . "&setb=" . $set[$k + 1] . "\">下移</a></td>";
                $kd8c_selllist .= "<td width=\"10%\"><a href=\"plugin.php?id=kd8c:kd8c&act=16&cmd=" . $my['pmid'] . "\">寄存</a></td><td width=\"20%\"><a href='plugin.php?id=kd8c:kd8c&act=4&cmd=" . $my['pmid'] . "' onclick=\"return confirm('" . $kd8c_kd8c_showmsg['msg5'] . "');\">放生</a></td></tr></table></td></tr></table></form></td></tr>";
            }
            $i++;
            $k++;
        }
        if($i % 2 != 1) {
            $kd8c_selllist .= "<td width=\"50%\"> </td></tr>";
        }
        if(!$kd8c_selllist) {
            $kd8c_selllist = "<tr class=\"fl_row\" valign=\"top\"><td width=\"100%\" colspan=\"3\" align=\"center\">" . $kd8c_kd8c_showmsg['msg9'] . "</td></tr>";
        }
        //导入模板
        include template('kd8c:kd8c_kd8c_mypet');
    }
}
?>