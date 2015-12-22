<!DOCTYPE html>
<head>
	<title>{$lang['app_name']}</title>
	<meta charset="UTF-8">
	<meta name="description" content="{$lang['app_description']}">
	<meta name="keywords" content="{$lang['app_keywords']}">
	<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="{$path['css']}?{rand(1, 222)}" type="text/css">
    <script src="{$smarty.const.ROOT_RELATIVE}/source-tpl/js/library.js"></script>
    <script src="{$smarty.const.ROOT_RELATIVE}/source-tpl/js/angular.min.js"></script>
    <script src="{$smarty.const.ROOT_RELATIVE}/source-tpl/js/angular-sanitize.min.js"></script>
    <script src="{$smarty.const.ROOT_RELATIVE}/source-tpl/js/library-new.js"></script>
</head>
<body ng-app="pokemon-app" ng-controller="main">

<header>
    <ul id="menu" class="corrected-center">
        <li><a href="index.tpl" ng-bind="_LANG.home">&nbsp;</a></li>
        {if $index === 'admincp'}
            <li><a href="?index=admincp&action=award" ng-bind="_LANG.home"></a></li>
        {else}
            <li><a href="?index=my"{if !empty($trainer['has_new_message'])} class="hl"{/if} ng-bind="_LANG.home"></a></li>
            <li><a href="?index=shop" ng-bind="_LANG.shop"></a></li>
            <li><a href="?index=pc" ng-bind="_LANG.pc"></a></li>
            <li><a href="?index=daycare" ng-bind="_LANG.daycare"></a></li>
            <li><a href="?index=shelter" ng-bind="_LANG.shelter"></a></li>
            <li><a href="?index=map" ng-bind="_LANG.adventure"></a></li>
            <li><a href="?index=ranking" ng-bind="_LANG.ranking"></a></li>
            <li><a href="{if empty($trainer['uid'])}../bbs/member.php?mod=logging&action=login{else}../bbs/forum.php{/if}" target="_blank" ng-bind="_LANG.forum"></a></li>
        {/if}
    </ul>
    <div class="decoration-bar"></div>
</header>

<main>
	