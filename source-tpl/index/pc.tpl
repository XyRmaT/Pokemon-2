{include file='header.tpl'}

<table class="main" ng-controller="page-pc">
    <tr>
        <td class="header">
            <span ng-bind-html="_LANG.pc_welcome"></span>
            <span class="currency" ng-bind="_TNR.currency"></span>
        </td>
        <td rowspan="2" class="info">
            <div ng-if="section === 'heal'" ng-cloak>
                <div class="title"><h3>%%_LANG.heal%%</h3></div>
                <div class="bar"><span class="star yellow"></span> %%_LANG.hint_heal%%</div>
                <ul>
                    <li ng-repeat="(k, p) in heal" class="pokemon">
                        <span class="float-left" ng-bind-html="p.nickname + ' ' + p.gender_sign + ' Lv.' + p.level"></span>
                        <img class="pokemon-sprite" ng-src="%%p.pkm_sprite%%" ng-click="healPokemon(p.pkm_id, true)" pokemon-overflow>
                        <div class="bottom">%%p.remain_time > 0 && lang(_LANG.heal_need_time, [Math.floor(p.remain_time / 60)]) || _LANG.recovered%%</div>
                    </li>
                    <li ng-repeat="i in array(_SYS.pkm_limits.pc_heal - heal.length) track by $index" class="pokemon blank">
                        <button class="button-heal" pop-up="party">%%_LANG.put%%</button>
                    </li>
                </ul>
            </div>
            <div ng-if="section === 'storage'" ng-cloak>
                <div class="title"><h3>%%_LANG.storage%%</h3></div>
                <div class="bar"><span class="star yellow"></span> %%_LANG.hint_storage%%</div>
                <div ng-repeat="(i, b) in boxes" class="pc-box" ng-click="moveToStorage(i)">
                    <div class="box-title">BOX %%i - 100%%</div>
                    <div class="box-content">
                        <span ng-repeat="p in b | orderObjectBy : 'nat_id'" ng-class="picon(p.nat_id) + (storageObject.selectedBox === null || storageObject.selectedBox !== null && storageObject.selectedPokemon[p.pkm_id] ? '' : ' not-selected')" ng-click="storageSelectPokemon($event, p.pkm_id, i)"></span>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="no-padding no-top-border">
            <div class="side-menu"><a data-section="heal" ng-bind="_LANG.heal"></a></div>
            <div class="side-menu"><a data-section="trade" ng-bind="_LANG.trade"></a></div>
            <div class="side-menu"><a data-section="storage" ng-bind="_LANG.storage"></a></div>
        </td>
    </tr>
</table>

<div class="pop-up party hide">
    <div class="title" draggable><span>%%_LANG.pc_which_to_put%%</span><span class="close">×</span></div>
    <div class="content">
        <div ng-repeat="(k, p) in party" class="pokemon-b" ng-class="{ even: k % 2 !== 0 }" ng-click="healPokemon(p.pkm_id); pop.closeAll()">
            %%p.nickname%% <span ng-bind-html="p.gender_sign"></span> Lv.%%p.level%%
            <span class="float-right">
                <img ng-src="%%p.capture_item_sprite%%"><br>
                <img ng-if="p.item_holding" ng-src="%%p.hold_item_sprite%%">
            </span><br>
            <span ng-class="picon(p.nat_id)"></span>
            <hr>
        </div>
        <div ng-if="!party.length">%%_LANG.no_party_pokemon%%</div>
    </div>
</div>

{include file='footer.tpl'}