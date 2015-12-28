/* Library based on AngularJS */

var app     = angular.module('pokemon-app', ['ngSanitize']),
    $       = angular.element,
    $window = $(window);

app
    .config(['$httpProvider', '$interpolateProvider', function ($httpProvider, $interpolateProvider) {
        $interpolateProvider.startSymbol('%%').endSymbol('%%');
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        $httpProvider.interceptors.push(function ($q, $rootScope) {
            var timer;
            return {
                'request' : function (config) {
                    $('header .decoration-bar').removeClass('loading active');
                    timer = setTimeout(function () {
                        $('header .decoration-bar').addClass('loading');
                    }, 666);
                    return config;
                },
                'response': function (response) {
                    timer = null;
                    $('header .decoration-bar').removeClass('loading').addClass('active');
                    if (response.data.msg) {
                        $('.pop-up.message > .content').html(response.data.msg);
                        pop.open('message');
                    }
                    if (response.data.data) {
                        for (var i in response.data.data) {
                            $rootScope[i] = response.data.data[i];
                        }
                    }
                }
            }
        });
    }])
    .controller('main', ['$rootScope', '$scope', 'external', function ($rootScope, $scope, external) {
        for (var i in external)
            $rootScope[i] = external[i];
        $scope.Math         = Math;
        $scope.numberFormat = function (num) {
            return (num > 999999) ? Math.round(num / 1000000) + 'm' : ((num > 999) ? Math.round(num / 1000) + 'k' : num);
        };
        $scope.$watch('trainer.currency', function (newValue, oldValue) {
            if (newValue == oldValue) return;
            var timer       = null,
                changeValue = Math.ceil(Math.abs(newValue - oldValue) / 10) * (newValue > oldValue ? 1 : -1),
                elem        = $('table.main .header .currency');
            oldValue        = parseInt(oldValue);
            timer           = setInterval(function () {
                if (newValue == oldValue) {
                    clearInterval(timer);
                    return;
                }
                oldValue = (changeValue > 0 ? Math.min : Math.max)(oldValue + changeValue, newValue);
                elem.html(oldValue);
            }, 22);
        });

        $('#pop-up-mask, .pop-up .close, [pop-up-close]').bind('click', pop.closeAll);
        $('header .decoration-bar').addClass('active');
    }])
    .controller('page-daycare', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        $rootScope.fnPutPokemon  = function (pkmId) {
            $http.get('?index=daycare&process=pokemon-put&pkm_id=' + pkmId);
        };
        $rootScope.fnTakePokemon = function (pkmId) {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=daycare&process=pokemon-take&pkm_id=' + pkmId);
        };
        $rootScope.fnTakeEgg     = function () {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=daycare&process=egg-take');
        };
    }])
    .factory('generalQueue', ['$q', '$timeout', function ($q, $timeout) {
        var _fact       = {};
        var _intvalue   = 1;
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
    .directive('ngPlaceholder', function () {
        return {
            restrict: 'A',
            link    : function ($scope, $element, $attr) {
                $element.attr('placeholder', $attr.ngPlaceholder)
            }
        };
    })
    .directive('pokemonOverflow', function () {
        return {
            restrict: 'A',
            link    : function ($scope, $element) {
                $element.bind('load', function () {
                    $element.css({
                        'left': (($element.parent().outerWidth() - $element.width()) / 2) + 'px',
                        'top' : (($element.parent().outerHeight() - $element.height()) / 2) + 'px'
                    }).show();
                });
            }
        };
    })
    .directive('pokemonIcon', function () {
        return {
            restrict: 'E',
            scope   : {
                natId: '='
            },
            link    : function ($scope, $element) {
                $element.replaceWith('<span class="pokemon-icon" style="background-position:' +
                    '-' + ((parseInt($scope.natId) % 12) * 32) + 'px ' +
                    '-' + (Math.floor(parseInt($scope.natId) / 12) * 32) + 'px"></span>');
            }
        }
    })
    .directive('popUp', function () {
        return {
            restrict: 'A',
            link    : function ($scope, $element, $attr) {
                $element.bind('click', function () {
                    pop.open($attr.popUp);
                });
            }
        }
    })
    .directive('draggable', function () {
        return {
            trstrict: 'A',
            link    : function ($scope, $element) {
                var elem, x_elem, y_elem, x_pos, y_pos = 0;
                $element
                    .on('mousedown', function () {
                        elem   = $element.hasClass('title') ? $element.parent() : $element;
                        x_elem = x_pos - elem.offset().left;
                        y_elem = y_pos - elem.offset().top;
                    })
                    .on('mousemove', function (e) {
                        x_pos = document.all ? window.event.clientX : e.pageX;
                        y_pos = document.all ? window.event.clientY : e.pageY;
                        if (elem)
                            elem.css({
                                top : (y_pos - y_elem) + 'px',
                                left: (x_pos - x_elem) + 'px'
                            });
                    })
                    .on('mouseup', function () {
                        elem = null;
                    });
            }
        };
    });


window.ondragstart = function () {
    return false;
};

var pop = {
    open    : function (name) {
        this.closeAll();
        var popUp = $('.pop-up.' + name);
        popUp.show().css({
            top : (($window.height() - popUp.height()) / 3) + 'px',
            left: (($window.width() - popUp.width()) / 2) + 'px'
        });
        $('#pop-up-mask').show();
    },
    closeAll: function () {
        $('.pop-up, #pop-up-mask').hide();
    }
};

function pushState(title, href) {
    history.pushState({}, title, href);
    document.title = title;
}

/* Developer controller display message */
if (typeof console == "object") {
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