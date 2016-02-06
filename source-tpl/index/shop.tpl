{include file='header.tpl'}

<table class="main" ng-controller="page-shop">
    <tr>
        <td class="header">
            <span ng-bind-html="_LANG.shop_welcome"></span>
            <span class="currency" ng-bind="trainer.currency"></span>
        </td>
        <td rowspan="2" class="info">
            <div class="tab-group" ng-cloak ng-init="$root.type = 0">
                <div ng-repeat="(k, t) in _LANG.data_item_types" ng-click="$root.type = k" ng-class="{ current: $root.type == k }">%%t%%</div>
            </div>
            <ul class="shop-list clear" ng-cloak>
                <li ng-repeat="(k, i) in items" ng-if="(!type || i.type == type) && i.stock > 0" ng-click="$root.item = i; $root.quantity = 1; pop.open('buy-item')"
                    tooltip="%%(_LANG.stock | semiColumn) + i.stock%%">
                   <span class="item-wrapper"><img ng-src="%%i.item_sprite%%"></span>
                   <span>
                       %%i.name%%<br>
                       <span class="light-text">%%i.description%%</span>
                   </span>
                   <span class="float-right">￥%%i.price%%</span>
                </li>
            </ul>
        </td>
    </tr>
    <tr>
        <td>
            <div class="step"><span class="num">1</span><span class="procedure" ng-bind="lang(_LANG.shop_step_1)"></span></div>
            <div class="step"><span class="num">2</span><span class="procedure" ng-bind="lang(_LANG.shop_step_2)"></span></div>
            <div class="step"><span class="num">3</span><span class="procedure" ng-bind="lang(_LANG.shop_step_3)"></span></div>
        </td>
    </tr>
</table>

<div class="pop-up buy-item hide">
    <div class="content content-center">
        <span class="item-wrapper middle"><img ng-src="%%item.item_sprite%%"></span>
        <input class="inline-block middle" type="number" min="1" ng-model="$root.quantity" ng-placeholder="%%_LANG.enter_quantity%%" required>
        <br clear="both">
        <button ng-click="buyItem(item.item_id, $root.quantity)">￥%%item.price%%</button>
    </div>
</div>

{include file='footer.tpl'}