{include file='header.tpl'}

{* Since there's no need to update any data in index page, we don't need to bind any data *}

<section class="index-banner">
    <img src="{ROOT_IMAGE}/other/banner-index.jpg">
</section>

<section class="index-trainer box" ng-if="trainer.uid > 0" ng-cloak>
    <div class="info">
        <img class="avatar float-left" ng-src="%%trainer.avatar%%">
        <span class="ranking float-right"># %%trainer.rank%%</span>
        <span class="username">%%trainer.username%%</span><br>
        <span class="signature" ng-bind="_LANG.no_signature"></span>
    </div>
    <div class="other">
        <span class="stat inline-block" ng-bind-html="trainer.level + '<br>' + _LANG.level"></span>
        <span class="stat inline-block" ng-bind-html="trainer.dex_collected + '<br>' + _LANG.pokedex"></span>
        <span class="stat inline-block" ng-bind-html="numberFormat(trainer.currency) + '<br>' + _LANG.currency"></span>
        <div class="party border"><pokemon-icon ng-repeat="i in party" nat-id="i"></pokemon-icon></div>
    </div>
</section>
<section class="index-login box" ng-if="trainer.uid < 1">
    <div class="title" ng-bind="_LANG.trainer_entrance"></div>
    <div class="content" ng-cloak>
        <input type="text" name="username" ng-placeholder="%%_LANG.username%%">
        <input type="text" name="password" ng-placeholder="%%_LANG.password%%">
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
        <ul><li ng-repeat="(k, t) in top_trainers"><div>%%k + 1%%</div><img class="avatar" ng-src="%%t.avatar%%"><br>%%t.username%%</li></ul>
    </div>
</section>

{include file='footer.tpl'}