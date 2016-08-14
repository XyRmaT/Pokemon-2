{include file='header.tpl'}

<table class="main" ng-controller="page-memcp">
    <tr>
        <td class="header no-bottom-border">
            <div ng-cloak>
                <img class="avatar float-left" ng-src="%%trainer.avatar%%">
                <span class="ranking float-right" ng-bind="'#' + trainer.rank"></span>
                <span class="trainer_name" ng-bind="trainer.trainer_name"></span><br>
                <span class="signature" ng-bind="_LANG.no_signature"></span>
                <hr>
                <span class="stat inline-block" ng-bind-html="trainer.level + '<br><span>' + _LANG.level + '</span>'" tooltip="%%trainer.exp + ' / ' + trainer.exp_required%%"></span>
                <span class="stat inline-block" ng-bind-html="trainer.dex_collected + '<br><span>' + _LANG.pokedex + '</span>'"></span>
                <span class="stat inline-block" ng-bind-html="numberFormat(trainer.currency) + '<br><span>' + _LANG.currency + '</span>'"></span>
            </div>
        </td>
        <td rowspan="2" class="info">
            <div ng-if="section === 'info'" ng-cloak>
                <div class="title"><h3>%%_LANG.info%%</h3></div>
                <div class="title"><h3>%%_LANG.trainer_card%%</h3></div>
                <div class="box-b">
                    <img class="trainer-card float-left" ng-src="%%trainer.card%%">
                    <textarea id="clipboard-trainer-card">[url=%%location.origin + location.pathname%%][img]%%trainer.card | toAbsoluteLink%%[/img][/url]</textarea>
                    <button copieable="trainer-card">%%_LANG.copy%%</button>
                </div>
            </div>
            <div ng-if="section === 'party'" ng-cloak>
                <div class="title"><h3>%%_LANG.party%%</h3></div>
                <div class="bar"><span class="star yellow"></span> %%_LANG.hint_party%%</div>
                <ul data-as-sortable="orderListener" data-ng-model="pokemon">
                    <li ng-repeat="(k, p) in pokemon" class="pokemon" data-as-sortable-item>
                        <div data-as-sortable-item-handle>
                            <span class="float-left" ng-bind-html="p.nickname + ' ' + p.gender_sign + ' Lv.' + p.level + (p.is_shiny > 0 && ' <span class=shiny></span> ' || '')"></span>
                            <span class="float-right">
                                <img ng-src="%%p.capture_item_sprite%%"><br>
                                <img ng-if="p.item_holding > 0" ng-src="%%p.hold_item_sprite%%">
                            </span>
                            <img class="pokemon-sprite" ng-src="%%p.pkm_sprite%%" ng-click="$root.view_index = k" tooltip="%%_LANG.click_for_more%%" pop-up="pokemon-info" pokemon-overflow>
                            <div class="bottom" ng-if="p.nat_id > 0">
                                <vbar options="{ type: 'hp', value: %%p.hp%%, max: %%p.max_hp%% }"></vbar>
                                <vbar options="{ type: 'exp', value: %%p.exp - p.exp_this_level%%, max: %%p.exp_required%% }"></vbar>
                            </div>
                            <div class="bottom" ng-if="p.nat_id == 0">%%p.egg_phase%%<p></div>
                        </div>
                    </li>
                </ul>
            </div>
            <div ng-if="section === 'inventory'" data-drop="true" jqyoui-droppable="{ onDrop: 'returnItem' }" data-jqyoui-options="{ accept: 'img.to' }" ng-cloak>
                <div class="title"><h3>%%_LANG.inventory%%</h3></div>
                <div class="bar"><span class="star yellow"></span> %%_LANG.hint_inventory_hold%% %%_LANG.hint_inventory_use%%</div>
                <div class="tab-group" ng-init="$root.type = 0">
                    <div ng-repeat="(k, t) in _LANG.data_item_types" ng-click="$root.type = k" ng-class="{ current: $root.type == k }">%%t%%</div>
                </div>
                <ul class="item-list clear">
                    <li ng-repeat="(k, i) in items" ng-if="(type === 0 || i.type == type) && i.quantity > 0" tooltip="%%i.name%% x%%i.quantity%%<br>%%i.description + (i.is_usable <= 0 && '<br>' + _LANG.not_usable || '')%%">
                        <img class="from" ng-src="%%i.item_sprite%%" ng-click="$root.currentItem = i" pop-up="party" data-item-id="%%i.item_id%%" data-drag="true" jqyoui-draggable data-jqyoui-options="{ revert: 'invalid', zIndex: 12, helper: 'clone' }">
                    </li>
                </ul>
            </div>
            <div ng-if="section === 'pokedex'" ng-cloak>
                <div class="title">
                    <h3>%%_LANG.pokedex%%</h3>
                    <span class="star yellow"></span> %%_LANG.seen_number%% %%dex_seen%% (%%(dex_seen / pokemon_total * 100 | number : 1) + '%'%%)
                    <span class="star red"></span> %%_LANG.dex_collected%% %%trainer.dex_collected%% (%%(trainer.dex_collected / pokemon_total * 100 | number : 1) + '%'%%)
                </div>
                <div class="bar">
                    <span ng-repeat="(k, r) in system.regions" ng-if="k > 0">
                        <span class="star"></span>%%_LANG[r[0]]%% %%((count_generations[k] / (r[2] - r[1]) * 100 | number : 1) || 0) + '%'%%
                    </span>
                </div>
                <ul class="pokedex">
                    <li ng-repeat="(k, p) in pokemon" ng-class="{ 'not-met': !p.nat_id, 'not-collected': !p.is_owned }" tooltip="%%p.name%%">
                        <pokemon-icon nat-id="p.nat_id"></pokemon-icon><br>
                        %%k%%
                    </li>
                </ul>
            </div>
            <div ng-if="section === 'inbox'" ng-cloak>
                <div class="title"><h3>%%_LANG.inbox%%</h3></div>
                <div class="bar">
                    <span class="star"></span>%%(_LANG.unread | semiColumn) + unread_total%%
                    <span class="star"></span>%%(_LANG.read | semiColumn) + messages.length%%
                </div>
                <ul class="inbox">
                    <li ng-repeat="(k, m) in messages">
                        <img class="avatar float-left" ng-src="%%m.avatar%%">
                        %%m.title%%<br><span ng-bind-html="m.content"></span>
                        <span class="deletion" ng-click="deleteMessage(m.msg_id)"></span>
                        <span class="dateline">%%m.time_sent * 1000 | date : 'yyyy-MM-dd HH:mm:ss' : '+0800'%%</span>
                    </li>
                </ul>
            </div>
        </td>
        <td ng-if="section === 'inventory'" rowspan="2" class="inventory-party info" ng-cloak>
            <div class="title"><h3>%%_LANG.party%%</h3></div>
            <div ng-repeat="(k, p) in party" class="pokemon-b" data-key="%%k%%" data-drop="true" jqyoui-droppable="{ onDrop: 'giveItem' }" data-jqyoui-options="{ accept: 'img.from' }">
                %%p.nickname%% <span ng-bind-html="p.gender_sign"></span> Lv.%%p.level%%
                <span class="float-right">
                    <img ng-src="%%p.capture_item_sprite%%"><br>
                    <span><img class="to" ng-if="p.item_holding > 0" ng-src="%%p.hold_item_sprite%%" data-item-id="%%p.item_holding%%" data-drag="true" jqyoui-draggable data-jqyoui-options="{ revert: 'invalid', zIndex: 12, helper: 'clone' }"></span>
                </span><br>
                <pokemon-icon nat-id="p.nat_id"></pokemon-icon>
                <hr>
            </div>
        </td>
    </tr>
    <tr>
        <td class="no-padding no-top-border">
            <div class="side-menu"><a data-section="info" ng-bind="_LANG.info"></a></div>
            <div class="side-menu"><a data-section="party" ng-bind="_LANG.party"></a></div>
            <div class="side-menu"><a data-section="inventory"  ng-bind="_LANG.inventory"></a></div>
            <div class="side-menu"><a data-section="inbox"  ng-bind="_LANG.inbox"></a></div>
            <div class="side-menu"><a data-section="pokedex"  ng-bind="_LANG.pokedex"></a></div>
            <div class="side-menu hide"><a data-section="achievement"  ng-bind="_LANG.achievement"></a></div>
            <div class="side-menu hide"><a data-section="setting"  ng-bind="_LANG.setting"></a></div>
        </td>
    </tr>
</table>

<div class="pop-up pokemon-info hide">
    <div class="title" draggable>
        <img ng-src="%%pokemon[view_index].capture_item_sprite%%">
        %%pokemon[view_index].nickname%% <span ng-bind-html="pokemon[view_index].gender_sign"></span> Lv.%%pokemon[view_index].level%%
        <span class="close">×</span>
    </div>
    <div class="content">
        <div ng-repeat="(k, p) in pokemon" ng-if="k === view_index">
            <table class="pokemon-info">
                <tr>
                    <td class="border-right">
                        <div class="text-center relative"><img ng-src="%%p.pkm_sprite%%" class="pokemon-sprite" pokemon-overflow></div>
                        <div class="text-center" ng-bind-html="p.types"></div><br>
                        <table class="center">
                            <tr><td width="30">%%_LANG.nature%%</td><td>%%p.nature || '-'%%</td></tr>
                            <tr><td>%%_LANG.ability%%</td><td>%%p.ability || '-'%%</td></tr>
                        </table>
                    </td>
                    <td>
                        <table class="info">
                            <tr><td>%%_LANG.nickname%%</td><td>%%p.nickname%%</td><td>%%_LANG.owner%%</td><td>%%p.trainer_name%%</td></tr>
                            <tr><td colspan="4">&nbsp;</td></tr>
                            <tr><td>%%_LANG.hp%%</td><td>%%p.hp > 0 && p.hp || '-'%%</td><td>%%_LANG.special_attack%%</td><td>%%p.spatk > 0 && p.spatk || '-'%%</td></tr>
                            <tr><td>%%_LANG.attack%%</td><td>%%p.atk > 0 && p.atk || '-'%%</td><td>%%_LANG.special_defense%%</td><td>%%p.spdef > 0 && p.spdef || '-'%%</td></tr>
                            <tr><td>%%_LANG.defense%%</td><td>%%p.def > 0 && p.def || '-'%%</td><td>%%_LANG.speed%%</td><td>%%p.spd > 0 && p.spd || '-'%%</td></tr>
                        </table>
                        <div ng-repeat="m in p.moves" class="move t%%moves[m.move_id].type%%"
                            tooltip="%%moves[m.move_id].power > 0 && (_LANG.power | semiColumn) + moves[m.move_id].power + '<br>' || ''%%
                                %%(_LANG.type | semiColumn) + moves[m.move_id].type_name%%<br>
                                %%(_LANG.class | semiColumn) + moves[m.move_id].class_name%%<br>
                                %%(_LANG.effect | semiColumn) + moves[m.move_id].description%%">
                            %%moves[m.move_id].name%%<br>
                            %%m.pp%% / %%m.pp_total%%
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="border-top text-center" colspan="2">
                        %%p.met_location%% (%%(_LANG.obtain_time | semiColumn) + (p.met_time * 1000 | date : 'yyyy-MM-dd HH:mm:ss' : '+0800')%%)<br>
                        %%p.happiness_phase && ((_LANG.happiness_status | semiColumn) + _LANG.data_happiness_phases[p.happiness_phase]) || ''%%
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="pop-up party hide">
    <div class="title" draggable><span>%%lang(_LANG.item_which_to_use_on, [currentItem.name])%%</span><span class="close">×</span></div>
    <div class="content">
        <div ng-repeat="(k, p) in party" class="pokemon-b" ng-class="{literal}{even: k % 2 !== 0}{/literal}" ng-click="useItem(currentItem.item_id, p.pkm_id)" pop-up-close>
            %%p.nickname%% <span ng-bind-html="p.gender_sign"></span> Lv.%%p.level%%
            <span class="float-right">
                <img ng-src="%%p.capture_item_sprite%%"><br>
                <img ng-if="p.item_holding > 0" ng-src="%%p.hold_item_sprite%%">
            </span><br>
            <pokemon-icon nat-id="p.nat_id"></pokemon-icon>
            <hr>
        </div>
        <div ng-if="!party.length">%%_LANG.no_party_pokemon%%</div>
    </div>
</div>

{include file='footer.tpl'}