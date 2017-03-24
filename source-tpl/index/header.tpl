<!DOCTYPE html>
<html>
<head>
    <title ng-bind="_LANG.app_name">{$lang['app_name']}</title>
    <meta charset="UTF-8">
    <meta name="description" content="{$lang['app_description']}">
    <meta name="keywords" content="{$lang['app_keywords']}">
    <meta name="robots" content="index, nofollow">
    <link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="{$path['css']}?{rand(1, 222)}" type="text/css">
    <script src="{ROOT_RELATIVE}/source-tpl/javascript/library.js"></script>
    <script src="{ROOT_RELATIVE}/source-tpl/javascript/angular.min.js"></script>
    <script src="{ROOT_RELATIVE}/source-tpl/javascript/angular-sanitize.min.js"></script>
    <script src="{ROOT_RELATIVE}/source-tpl/javascript/angular-sortable.js"></script>
    <script src="{ROOT_RELATIVE}/source-tpl/javascript/angular-dragdrop.js"></script>
    <script src="{ROOT_RELATIVE}/source-tpl/javascript/library-new.js"></script>
</head>
<body ng-app="pokemon-app" ng-controller="main">

<header>
    <ul id="menu" class="corrected-center">
        <li><a href="?index=home" ng-bind="_LANG.home">&nbsp;</a></li>
        <li><a href="?index=memcp" ng-bind="_LANG.memcp" ng-class="_TNR.has_new_message > 0 && 'highlight'"></a></li>
        <li><a href="?index=shop" ng-bind="_LANG.shop"></a></li>
        <li><a href="?index=pc" ng-bind="_LANG.pc"></a></li>
        <li><a href="?index=daycare" ng-bind="_LANG.daycare"></a></li>
        <li><a href="?index=shelter" ng-bind="_LANG.shelter"></a></li>
        <li><a href="?index=map" ng-bind="_LANG.adventure"></a></li>
        <li ng-if="_TNR.user_id > 0"><a ng-href="%%_TNR.user_id > 0 && '../bbs/member.php?mod=logging&action=login' || '../bbs/forum.php'%%" target="_blank" ng-bind="_LANG.forum"></a></li>
    </ul>
    <div class="decoration-bar"></div>
</header>

<main>