</main>

<br clear="both">

<div id="layer-alert"></div>

<footer>
    <span ng-bind="_LANG.current_time"></span>: {date('Y-m-d H:i:s', $smarty.server.REQUEST_TIME)}
    <span ng-bind="_LANG.memory_usage"></span>: {Kit::Memory(memory_get_usage(TRUE))}<br>
    {if $user['uid'] == 8}Processed in {round(microtime(TRUE) - $start_time, 6)} second(s), {DB::get_query_num()} queries.<br>{/if}
    <div ng-bind-html="_LANG.browser_advice"></div>
    Copyright &copy; 2013-{YEAR} PokeUniv (Pet). Version {$system['version']}.
</footer>

<script>
    app.factory('_LANG', function () { return {json_encode($lang)}; });

    {literal}
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
        a = s.createElement(o), m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src   = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-42789438-2', 'pokeuniv.com');
    ga('send', 'pageview');
    {/literal}
</script>
{$synclogin}
</html>