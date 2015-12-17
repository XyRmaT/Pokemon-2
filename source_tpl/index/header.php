<!DOCTYPE html>
<head>
	<title>口袋大冒险</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="Doduo">
	<meta name="copyright" content="口袋大学城">
	<meta name="description" content="口袋妖怪在线养成冒险系统">
	<meta name="keywords" content="口袋妖怪,神奇宝贝,精灵宝可梦,宠物小精灵,口袋大冒险,养成,冒险,新养成,口袋大学城">
	<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="$path[css]?{echo rand(1,222);}" type="text/css">
	<script src="{ROOT_RELATIVE}/source_tpl/js/library.js"></script>
    <script src="{ROOT_RELATIVE}/source_tpl/js/angular.min.js"></script>
    <script src="{ROOT_RELATIVE}/source_tpl/js/library-new.js"></script>
</head>
<body>

<header>
    <ul id="menu" class="corrected-center">
        <li><a href="index.php">首页</a>
        <!--{if $index === 'admincp'}-->
            <li><a href="?index=admincp&action=award">奖惩</a></li>
        <!--{else}-->
            <li><a href="?index=my"<!--{if !empty($trainer['has_new_message'])}--> class="hl"<!--{/if}-->>个人</a></li>
            <li><a href="?index=shop">商店</a></li>
            <li><a href="?index=pc">PC</a></li>
            <li><a href="?index=daycare">饲育屋</a></li>
            <li><a href="?index=shelter">孤儿院</a></li>
            <li><a href="?index=map">冒险</a></li>
            <li><a href="?index=ranking">排行榜</a></li>
            <li><a href="{if empty($trainer['uid'])}../bbs/member.php?mod=logging&action=login{else}../bbs/forum.php{/if}" target="_blank">论坛</a></li>
        <!--{/if}-->
    </ul>
    <div class="decoration-bar"></div>
</header>


<div id="main">
	