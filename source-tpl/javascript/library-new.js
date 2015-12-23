/* Library based on AngularJS */
var app = angular.module('pokemon-app', ['ngSanitize']);

app
    .config(function($interpolateProvider){
        $interpolateProvider.startSymbol('%%').endSymbol('%%');
    })
    .controller('main', ['$scope', '_LANG', function($scope, _LANG) {
        $scope._LANG = _LANG;
    }])
    .factory('generalQueue', ['$q', '$timeout', function ($q, $timeout) {
        var _fact = {};
        var _intvalue = 1;
        var waitPromise = $q.when(true);

        var _asyncTask = function (fn, time, successFn, failFn) {
            waitPromise = waitPromise.then(function () {
                return $timeout(fn, time);
            });
            return waitPromise;
        };

        _fact.asyncTask = _asyncTask;
        return _fact;
    }])
    .directive('ngPlaceholder', function() {
        return {
            restrict: 'A',
            link: function($scope, $element, $attr) {
                $element.attr('placeholder', $attr.ngPlaceholder)
            }
        };
    })
    .directive('pokemonOverflow', function() {
        return {
            restrict: 'A',
            link: function($scope, $element) {
                $element.on('load', function() {
                    $element.css({
                        'left': (($element.parent().outerWidth() - $element.width()) / 2) + 'px',
                        'top' : (($element.parent().parent().outerHeight() - $element.parent().prev().outerHeight() - $element.height()) / 2 - 15) + 'px'
                    }).show();
                });
            }
        };
    });

/*app.directive('popUp', function($scope) {
    return {
        restrict: 'E',
        replace: true,
        scope: {
            text*/


window.ondragstart = function() { return false };

function pushState(title, href) {
    history.pushState({}, title, href);
    document.title = title;
}

/* Developer controller display message */
if(typeof console == "object") {
    console.log('%cI\'m new to AngularJS, please don\'t punch me.\n\n', 'font-size: 14px; ');
    console.log('%cSam Ye ♂', 'font-size: 15px; font-weight: bold; font-family: monospace,monospace;');
    console.log('%c    Pokémon Researcher, Protector of the Realm at ANU\n', 'font-weight: bold; font-family: monospace,monospace;');
    console.log('%c| QQ:       306732418\n' +
        '| Email:    pokeuniv@gmail.com\n' +
        '| Mobile:   +61 (450) 816 266\n' +
        '| Facebook: https://www.facebook.com/samyeeeeee\n', 'font-family: monospace,monospace;');
    console.log('%c| "Sometimes it\'s the very people who no one imagines anything of who do the things that no one can imagine." —— Alan Turing (The Imitation Game)',
        'font-family: monospace,monospace;');
}