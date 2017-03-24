{include file='header.tpl'}

<table class="main" ng-controller="page-shelter">
    <tr>
        <td class="header">
            <span ng-bind-html="_LANG.shelter_welcome"></span>
            <span class="currency" ng-bind="_TNR.currency"></span>
        </td>
        <td rowspan="2" class="info">
            <div class="pokemon-pool" ng-cloak>
                <ul ng-if="pokemon.length > 0">
                    <li ng-repeat="(k, p) in pokemon">
                        <img ng-src="%%p.pkm_sprite%%" ng-click="claimPokemon(p.pkm_id)" tooltip="No.%%p.nat_id%% %%p.name%% %%p.gender_sign%%  Lv.%%p.level%%" pokemon-overflow>
                    </li>
                </ul>
                <div ng-if="pokemon.length <= 0">%%_LANG.no_pokemon%%</div>
            </div>
            <div class="egg-pool" ng-cloak>
                <ul ng-if="eggs.length > 0">
                    <li ng-repeat="(k, e) in eggs">
                        <img ng-src="%%e.pkm_sprite%%" ng-click="claimPokemon(e.pkm_id)" tooltip="%%e.name%%">
                    </li>
                </ul>
                <div ng-if="eggs.length <= 0">%%_LANG.no_egg%%</div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="step"><span class="num">1</span><span class="procedure" ng-bind="lang(_LANG.shelter_step_1, [_SYS.shelter_cost, _SYS.currency_name])"></span></div>
        </td>
    </tr>
</table>

{include file='footer.tpl'}