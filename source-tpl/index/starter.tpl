{include file='header.tpl'}

<div class="main" ng-controller="page-starter">
    <div class="starter-description" ng-bind="_LANG.starter_welcome"></div>

    <ul class="starter-list">
        <li ng-repeat="p in pokemon">
            <img ng-src="%%p.pkm_sprite%%" ng-click="claimPokemon(p.nat_id)">
            <div class="info">
                <div ng-bind="'No.'+ p.nat_id + ' ' + p.name"></div>
                <span ng-class="'type t' + p.type"></span>
                <span ng-if="p.type_b" ng-class="'type t' + p.type_b"></span>
            </div>
        </li>
    </ul>
</div>

{include file='footer.tpl'}