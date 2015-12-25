{include file='header.tpl'}

<table class="main" ng-controller="page-daycare">
    <tr>
        <td class="header">
            <span ng-bind="_LANG.daycare_welcome"></span>
            <span class="currency" ng-bind="trainer.currency"></span>
        </td>
        <td rowspan="2">

        </td>
    </tr>
    <tr>
        <td>
            {if !empty($pokemon[0]['has_egg']) && !empty($pokemon[1]['has_egg'])}<div class="hint" ng-bind="_LANG.daycare_has_egg"></div>{/if}
            <div class="step"><span class="num">1</span><span class="procedure" ng-bind="_LANG.daycare_step_1"></span></div>
            <div class="step"><span class="num">2</span><span class="procedure" ng-bind="_LANG.daycare_step_2"></span></div>
        </td>
    </tr>
</table>

<script>
    app.factory('party', function () { return {json_encode($party)}; });
</script>

{include file='footer.tpl'}