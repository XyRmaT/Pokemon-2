<!--{if !INAJAX}-->
{template index/header}

<table id="my">
    <tr>
        <td class="left">
            <!-- due to IE rowspan height bug, have to use this to solve it, have to figure out a better way -->
            <table>
                <tr>
                    <td class="userinfo">
                        <img class="float-left avatar" src="$trainer[avatar]"></div>
                        <strong>$trainer[username]</strong><br>
                        成就：0<br>
                        排名：#$rank
                    </td>
                </tr>
                <tr>
                    <td class="userstat">
                        <ul>
                            <li><em title="经验：$trainer[exp]/$reqexp">$trainer[level]</em><br>等级</li>
                            <li><em>$dexclt</em><br>精灵</li>
                            <li><em title="$trainer[currency]"><!--{echo Kit::NumberFormat($trainer['currency'])}--></em><br>$system[currency_name]</li>
                        </ul>
                    </td>
                </tr>
                <tr><td class="nav{if $_GET['section'] === ''} current{/if}"><a href="?index=my">队伍</a></td></tr>
                <tr><td class="nav{if $_GET['section'] === 'inbox'} current{/if}"><a href="?index=my&section=inbox"{if $trainer['has_new_message'] === '1'} class="hl"{/if}>消息</a></td></tr>
                <tr><td class="nav{if $_GET['section'] === 'inventory'} current{/if}"><a href="?index=my&section=inventory">背包</a></td></tr>
                <tr><td class="nav{if $_GET['section'] === 'pokedex'} current{/if}"><a href="?index=my&section=pokedex">图鉴</a></td></tr>
                <tr><td class="nav{if $_GET['section'] === 'achievement'} current{/if}"><a href="?index=my&section=achievement">成就</a></td></tr>
                <tr><td class="nav{if $_GET['section'] === 'setting'} current{/if}"><a href="?index=my&section=setting">设置</a></td></tr>
                <tr><td class="border-bottom-none">&nbsp;</td></tr>
            </table>
        </td>
        <td width="758" class="my-info">
<!--{/if}-->

<!--{if $_GET['section'] === ''}-->
    <div class="title">队伍</div>
    <div class="infobar">
        <span class="star on"></span>小贴士：拖拽精灵所在的框可以改变精灵的顺序哟~（仅限电脑）
    </div>
    <ul id="pm-grid">
        <!--{loop $pokemon $val}-->
            <li data-pkm_id="$val[pkm_id]" data-nickname="$val[nickname]">
                <input type="hidden" name="order[]" value="$val[pkm_id]">
                <span class="float-left">
                    $val[nickname]{$val[gender]}
                    <a href="javascript:void(0);" class="pmabandon">[丢]</a>
                    <a href="javascript:void(0);" class="pmnickname">[改]</a>
                    <!--{if !empty($val['moves_new'])}--> <a href="javascript:void(0);" class="pmmove">[技]</a><!--{/if}-->
                </span>
                <span class="flt-r">
                    <img src="$val[item_captured]"><br>
                    <!--{if !empty($val['itemimgpath'])}--><img src="$val[itemimgpath]"><!--{/if}-->
                </span>
                <br class="cl">
                <div class="txt-c">
                    <img src="$val[pkmimgpath]" alt="点我查看数据"><br>
                    Lv. $val[level] $val[status]
                </div>
                <div class="bar" title="$val[hp]/$val[maxhp]">HP<div class="ctn"><div class="hp" style="width:$val[hpper]%"></div></div></div>
                <div class="bar" title="$val[exp]/$val[maxexp]">EXP<div class="ctn"><div class="exp" style="width:$val[expper]%"></div></div></div>
                <div id="info-$val[pkm_id]" class="pm-info h">
                    <table>
                        <tr>
                            <td class="left">
                                <img src="$val[pkmimgpath]"><br>
                                <div class="txt-c">
                                    Lv.$val[level]<br>
                                    $val[type]<br>
                                </div>
                                <div class="stat">
                                    HP<em>$val[hp]</em></br>
                                    攻击<em>$val[atk]</em></br>
                                    防御<em>$val[def]</em></br>
                                    特攻<em>$val[spatk]</em></br>
                                    特防<em>$val[spdef]</em></br>
                                    速度<em>$val[spd]</em>
                                </div>
                            </td>
                            <td class="right">
                                No.$val[nat_id] $val[name] {$val[gender]}<br>
                                昵称：<em>$val[nickname]</em><br>
                                性格：<em>$val[nature]</em><br>
                                特性：<em>$val[ability]</em><br>
                                最初主人：<em>$val[username]</em><br>
                                获得途径：<span title="相遇时$val[met_level]级。">$val[met_location]</span><br>
                                获得时间：$val[met_time]<br>
                                亲密状态：$val[hpnsstatus]<br>
                                <!--{loop $val['moves'] $valb}-->
                                    <div class="move t{$moves[$valb['move_id']]['type']}" title="{if $moves[$valb['move_id']]['power']}威力：{if $moves[$valb['move_id']]['power'] == 1}不定{else}$moves[$valb['move_id']]['power']{/if}<br>{/if}属性：$moves[$valb['move_id']]['type_name']<br>类型：$moves[$valb['move_id']]['class_name']">$moves[$valb['move_id']]['name']<br>PP $valb['pp']/$valb['pp_total']</div>
                                <!--{/loop}--><br>
                            </td>
                        </tr>
                    </table>
                </div>
            </li>
        <!--{/loop}-->
    </ul>

    <form id="my-reorder" onsubmit="return false;" style="display:none">
        <ul>
            <!--{loop $pokemon $key $val}-->
                <!--{if isset($val['nat_id'])}-->
                    <!--{if !empty($val['nat_id'])}-->
                        <li data-pkm_id="$val[pkm_id]" data-nickname="$val[nickname]">
                            <input type="hidden" name="order[]" value="$val[pkm_id]">
                            <!--{if !empty($val['moves_new'])}-->
                                <div class="h move_new">
                                    <b>学习：</b><br>
                                    <!--{loop $val['moves_new'] $keyb $valb}-->
                                        <input type="radio" name="lid" value="$valb[0]"<!--{if $keyb === 0}--> checked<!--{/if}-->>$valb[1]<!--{if ($keyb + 1) % 2 === 0}--><br><!--{/if}-->
                                    <!--{/loop}-->
                                    <!--{if count($val['moves']) > 3}-->
                                        <br><br><b>替换：</b><br>
                                        <!--{loop $val['moves'] $keyb $valb}-->
                                            <input type="radio" name="move_id" value="$valb[0]"<!--{if $keyb === 0}--> checked<!--{/if}-->>$valb[2]<!--{if ($keyb + 1) % 2 === 0}--><br><!--{/if}-->
                                        <!--{/loop}-->
                                    <!--{/if}-->
                                </div>
                            <!--{/if}-->
                        </li>
                    <!--{else}-->
                        <li data-pkm_id="$val[pkm_id]" data-nickname="$val[nickname]" class="egg">
                            <span class="float-left">$val[nickname] <a href="javascript:void(0);" class="pmabandon">[丢]</a> <a href="javascript:void(0);" class="pmnickname">[改]</a></span>
                            <span class="flt-r"><img src="$val[item_captured]"></span><br class="cl">
                            <div class="move_id"><img src="$val[pkmimgpath]" class="egg" alt="点我查看数据"><br>$val[eggstatus]<!--{if $trainer['gm']}--><br>$val[maturity]<!--{/if}--></div>
                            <div id="info-$val[pkm_id]" class="h txt-c">
                                <img src="$val[pkmimgpath]"><p>
                                No.$val[nat_id] 蛋<br>
                                昵称：$val[nickname]<br>
                                $val[met_location]
                            </div>
                            <input type="hidden" name="order[]" value="$val[pkm_id]">
                        </li>
                    <!--{/if}-->
                <!--{/if}-->
            <!--{/loop}-->
        </ul>
    </form>


    <div id="layer-abandon" class="h">确定要狠下心抛弃它么……？</div>
    <div id="layer-nickname" class="h">
        叫它什么好呢？（最多6个字）<br>
        <input name="nickname" class="text ui-widget-content ui-corner-all">
    </div>
    <div id="layer-move" class="h"><form id="learnmove" onsubmit="return false;"></form></div>
<!--{elseif $_GET['section'] === 'pokedex'}-->
    <div class="title">图鉴</div>
    <div class="infobar">
        <span class="star on"></span>登记：{$seen}/{$count}
        <span class="star on"></span>收服：{$dexclt}/{$count}
    </div>
    <ul id="my-dex" class="sb">
        <!--{loop $pokemon $key $val}-->
            <li
                {if $val['is_owned'] === 'n'} class="nm"{elseif $val['is_owned'] === '0'} class="nc"{/if}
                {if $val['is_owned'] !== 'n'} title="No.$key<br>精灵：$val[name]<br>属性：$val[type]"{/if}>
            <!--{if $val['is_owned'] !== 'n'}-->
                <img src="{ROOT_IMAGE}/pokemon-icon/{if $val['is_owned'] === 'n'}_{else}$key{/if}.png">
            <!--{else}-->
                <div></div>
            <!--{/if}-->
            <br>$key</li>
        <!--{/loop}-->
    </ul>
<!--{elseif $_GET['section'] === 'achievement'}-->
    <h3>成就列表</h3>
    <hr>
    <p>注：点击相应的成就即可完成；成就名目前只是临时的，并未特意取名</p>
    <ul id="my-achv">
        <!--{loop $achievement $val}-->
            <li<!--{if !empty($val['time_obtained'])}--> class="done"<!--{/if}--> data-achv_id="$val[achv_id]"><div class="name">$val[name]</div><div class="description">$val[description]</div><div class="achieved"><!--{if !empty($val['time_obtained'])}-->完成<!--{else}-->未完成<!--{/if}--></div></li>
        <!--{/loop}-->
    </ul>
<!--{elseif $_GET['section'] === 'inbox'}-->
    <div class="title">邮箱</div>
    <div class="infobar">
        <span class="star on"></span>未读：<span class="unread">$unread</span>
        <span class="flt-r"><span class="star off"></span>每周定时清理已读超过7天的邮件</span>
    </div>
    <!--{if !empty($message)}-->
        <table id="my-inbox">
            <!--{loop $message $key $val}-->
                <tr>
                    <td class="status"><div class="{if $val['time_read']}read{else}unread{/if}"></div></td>
                    <td class="avatar"><img src="$val[avatar]"></td>
                    <td>$val[title]<br>$val[content]</td>
                    <td class="date">$val[time_sent]</td>
                    <td><span class="del" data-msg_id="$val[msg_id]"></span></td>
                </tr>
            <!--{/loop}-->
            <tr><td colspan="5" class="border-bottom-none">$multi[display]</td></tr>
        </table>
    <!--{/if}-->
<!--{elseif $_GET['section'] === 'inventory'}-->
    <div class="title">背包</div>
    <div class="infobar">
        <span class="star on" data-type="0"></span>全部
        <!--{loop $types $key $val}-->
            <span class="star off" data-type="{echo $key + 1}"></span>$val
        <!--{/loop}-->
        <span class="flt-r"><span class="star on not"></span>通过将道具拖拽到精灵身上可以使精灵携带道具哦~</span>
    </div>
    <ul id="my-invt"></ul>
    <script>
        item = $item;
        if(typeof initiateItemList !== "undefined") { initiateItemList(); }
    </script>
    <div id="my-invtp">
        <ul>
            <!--{loop $pokemon $val}-->
                <li data-pkm_id="$val[pkm_id]">
                    $val[nickname]<em class="flt-r">$val[gender]&nbsp;</em><br>
                    <img src="$val[pkmimgpathi]"> <em class="level">Lv.$val[level]</em><span class="item flt-r"><!--{if $val[itemimgpath]}--><img src="$val[itemimgpath]" {if $val['item_carrying']}data-item_id="$val[item_carrying]{/if}"><!--{/if}--></span>
                </li>
            <!--{/loop}-->
        </ul>
    </div>
    <!--Put here for ajax operation return data replacement-->
    <div id="layer-useitem" class="h">
        对谁使用<span class="name"></span>呢？<br>
        <table class="pmchoose">
            <tr>
                <td><div class="ui-icon ui-icon-triangle-1-w arrow" data-direction="left"></div></td>
                <td width="80%">
                    <ul class="pmtarget">
                        <!--{eval $i = 0;}-->
                        <!--{loop $pokemon $key $val}-->
                            <!--{if !empty($val['nat_id'])}-->
                                <li{if $i !== 0} class="h"{/if} data-index="$i" data-pkm_id="$val[pkm_id]">
                                    <img src="$val[pkmimgpath]"><br>
                                    $val[nickname]{$val[gender]} Lv.$val[level]
                                </li>
                                <!--{eval ++$i;}-->
                            <!--{/if}-->
                        <!--{/loop}-->
                    </ul>
                </td>
                <td><div class="ui-icon ui-icon-triangle-1-e arrow" data-direction="right"></div></td>
            </tr>
        </table>
    </div>

<!--{/if}-->

<!--{if !INAJAX}-->
        </td>
    </tr>
</table>

{template index/footer}
<!--{/if}-->