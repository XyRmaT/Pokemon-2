{include file='header.tpl'}

<section class="index-banner">
    <img src="{ROOT_IMAGE}/other/banner-index.jpg">
</section>

<section class="index-trainer box" ng-if="trainer.user_id > 0" ng-cloak>
    <div class="info">
        <img class="avatar float-left" ng-src="%%trainer.avatar%%">
        <span class="ranking float-right"># %%trainer.rank%%</span>
        <span class="trainer_name">%%trainer.trainer_name%%</span><br>
        <span class="signature" ng-bind="_LANG.no_signature"></span>
    </div>
    <div class="other">
        <span class="stat inline-block" ng-bind-html="trainer.level + '<br>' + _LANG.level"></span>
        <span class="stat inline-block" ng-bind-html="trainer.dex_collected + '<br>' + _LANG.pokedex"></span>
        <span class="stat inline-block" ng-bind-html="numberFormat(trainer.currency) + '<br>' + _LANG.currency"></span>
        <div class="party border"><pokemon-icon ng-repeat="i in party" nat-id="i"></pokemon-icon></div>
    </div>
</section>
<section class="index-login box" ng-if="!trainer.user_id" ng-controller="process-register">
    <div class="title" ng-bind="_LANG.trainer_entrance"></div>
    <div class="content" ng-cloak>
        <div ng-if="regProcess === 1">
            <input type="text" ng-model="reg.email" ng-placeholder="%%_LANG.email%%">
            <input type="text" ng-model="reg.trainer_name" ng-placeholder="%%_LANG.trainer_name%%">
        </div>
        <div ng-if="regProcess === 2">
            <input type="text" ng-model="reg.password" ng-placeholder="%%_LANG.password%%">
            <input type="text" ng-model="reg.password_retype" ng-placeholder="%%_LANG.password_retype%%">
        </div>
        <button id="sign-in" class="float-left" ng-bind="_LANG.sign_in"></button>
        <button id="sign-up" class="float-right" ng-bind="_LANG.sign_up"></button>
    </div>
</section>

<section class="index-log box">
    <div class="title" ng-bind="_LANG.newest_updates"></div>
    <div class="content">
        <ul><li><span class="category ann"></span> 口袋大冒险2016年正式公测<span class="float-right">2015-12-24</span></li></ul>
    </div>
</section>

<section class="index-stats box">
    <div class="title" ng-bind="_LANG.world_stats"></div>
    <div class="content" ng-cloak>
        <table>
            <tr><td ng-bind="_LANG.online_total"></td><td>%%world_stat.online_total%%</td></tr>
            <tr><td ng-bind="_LANG.trainer_total"></td><td>%%world_stat.trainer_total%%</td></tr>
            <tr><td ng-bind="_LANG.pokemon_total"></td><td>%%world_stat.pokemon_total%%</td></tr>
            <tr><td ng-bind="_LANG.shiny_total"></td><td>%%world_stat.shiny_total%%</td></tr>
        </table>
    </div>
</section>

<section class="index-horde box">
    <div class="title" ng-bind="_LANG.random_pokemon"></div>
    <div class="content" ng-cloak>
        <img ng-src="%%rand_pkm.pkm_sprite%%" pokemon-overflow>
        <div>%%rand_pkm.nickname%% <span ng-bind-html="rand_pkm.gender"></span> Lv.%%rand_pkm.level%%</div>
    </div>
</section>

<section class="index-trainers box">
    <div class="title" ng-bind="_LANG.outstanding_trainers"></div>
    <div class="content" ng-cloak>
        <ul><li ng-repeat="(k, t) in top_trainers"><div>%%k + 1%%</div><img class="avatar" ng-src="%%t.avatar%%"><br>%%t.trainer_name%%</li></ul>
    </div>
</section>

{include file='footer.tpl'}