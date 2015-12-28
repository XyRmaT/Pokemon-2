{include file='header.tpl'}

<table class="main" ng-controller="page-daycare">
    <tr>
        <td class="header">
            <span ng-bind="_LANG.daycare_welcome"></span>
            <span class="currency" ng-bind="trainer.currency"></span>
        </td>
        <td rowspan="2" class="info">
            <div ng-cloak>
                <div class="pokemon" ng-if="pokemon[0]">
                    <span class="float-left" ng-bind-html="pokemon[0].nickname + ' ' + pokemon[0].gender_sign + ' Lv.' + pokemon[0].level"></span>
                    <span class="float-right">
                        <img ng-src="%%pokemon[0].capture_item_sprite%%"><br>
                        <img ng-if="pokemon[0].item_carrying" ng-src="%%pokemon[0].carry_item_sprite%%">
                    </span>
                    <img ng-src="%%pokemon[0].pkm_sprite%%" ng-click="fnTakePokemon(pokemon[0].pkm_id)" pokemon-overflow>
                    <div class="bottom">
                        %%_LANG.daycare_take_cost.replace('%d', pokemon[0].cost)%%<br>
                        %%_LANG.daycare_take_gain.replace('%d', pokemon[0].exp_increased)%%
                    </div>
                </div>
                <div class="pokemon no" ng-if="!pokemon[0]"><button class="default" ng-bind="_LANG.daycare_put" pop-up="party"></button></div>

                <div class="has_egg" ng-class="{ no: pokemon[0].has_egg < 1 || pokemon[1].has_egg < 1}" ng-click="if(pokemon[0].has_egg > 0 && pokemon[1].has_egg > 0) fnTakeEgg()"></div>

                <div class="pokemon" ng-if="pokemon[1]">
                    <span class="float-left" ng-bind-html="pokemon[1].nickname + ' ' + pokemon[1].gender_sign + ' Lv.' + pokemon[1].level"></span>
                    <span class="float-right">
                        <img ng-src="%%pokemon[1].capture_item_sprite%%"><br>
                        <img ng-if="pokemon[1].item_carrying" ng-src="%%pokemon[1].carry_item_sprite%%">
                    </span>
                    <img ng-src="%%pokemon[1].pkm_sprite%%" ng-click="fnTakePokemon(pokemon[1].pkm_id)" pokemon-overflow>
                    <div class="bottom">
                        %%_LANG.daycare_take_cost.replace('%d', pokemon[1].cost)%%<br>
                        %%_LANG.daycare_take_gain.replace('%d', pokemon[1].exp_increased)%%
                    </div>
                </div>
                <div class="pokemon no" ng-if="!pokemon[1]"><button class="default" ng-bind="_LANG.daycare_put" pop-up="party"></button></div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="hint" ng-bind="_LANG.daycare_has_egg" ng-if="pokemon[0].has_egg > 0 && pokemon[1].has_egg > 0" ng-cloak></div>
            <div class="hint" ng-bind="_LANG.data_daycare_status[egg_chance]" ng-if="pokemon.length > 1" ng-cloak></div>
            <div class="step"><span class="num">1</span><span class="procedure" ng-bind="_LANG.daycare_step_1"></span></div>
            <div class="step"><span class="num">2</span><span class="procedure" ng-bind="_LANG.daycare_step_2"></span></div>
            <div class="step"><span class="num">3</span><span class="procedure" ng-bind="_LANG.daycare_step_3"></span></div>
        </td>
    </tr>
</table>

<div class="pop-up party" class="hide">
    <div class="title" draggable><span>%%_LANG.daycare_which_to_put%%</span><span class="close">×</span></div>
    <div class="content">
        <div ng-repeat="(k, p) in party" class="pokemon-b" ng-class="{literal}{even: k % 2 !== 0}{/literal}" ng-click="fnPutPokemon(p.pkm_id)" pop-up-close>
            %%p.nickname%% <span ng-bind-html="p.gender_sign"></span> Lv.%%p.level%%
            <span class="float-right">
                <img ng-src="%%p.capture_item_sprite%%"><br>
                <img ng-if="p.item_carrying" ng-src="%%p.carry_item_sprite%%">
            </span><br>
            <pokemon-icon nat-id="p.nat_id"></pokemon-icon>
            <hr>
        </div>
        <div ng-if="!party.length">%%_LANG.no_party_pokemon%%</div>
    </div>
</div>

{include file='footer.tpl'}