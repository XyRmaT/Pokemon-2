/* Library based on AngularJS */

'use strict';

var app               = angular.module('pokemon-app', ['ngSanitize', 'as.sortable', 'ngDragDrop']),
    $                 = angular.element,
    $window           = $(window),
    isTooltipDisabled = false;

app
    .config(['$httpProvider', '$interpolateProvider', function ($httpProvider, $interpolateProvider) {
        $interpolateProvider.startSymbol('%%').endSymbol('%%');
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        $httpProvider.interceptors.push(function ($q, $rootScope) {
            var timer;
            return {
                'request': function (config) {
                    if (config.pushState) pushState('', config.url);
                    config.url += '&t=' + Math.random();
                    $('header .decoration-bar').removeClass('loading active');
                    timer = setTimeout(function () {
                        $('header .decoration-bar').addClass('loading');
                    }, 666);
                    return config;
                },
                'response': function (response) {
                    timer = null;
                    $('header .decoration-bar').removeClass('loading').addClass('active');
                    if (response.data.js) {
                        eval(response.data.js);
                    }
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
        $scope.location     = window.location;
        $scope.pop          = pop;
        $scope.array        = function (max) {
            return new Array(max);
        };
        $scope.numberFormat = function (num) {
            return (num > 999999) ? Math.round(num / 1000000) + 'm' : ((num > 999) ? Math.round(num / 1000) + 'k' : num);
        };
        $scope.$watch('trainer.currency', function (newValue, oldValue) {
            if (newValue == oldValue) return;

            var timer       = null,
                changeValue = Math.ceil(Math.abs(newValue - oldValue) / 10) * (newValue > oldValue ? 1 : -1),
                elem        = $('table.main .header .currency');

            oldValue = parseInt(oldValue);
            timer    = setInterval(function () {
                if (newValue == oldValue) {
                    clearInterval(timer);
                    return;
                }
                oldValue = (changeValue > 0 ? Math.min : Math.max)(oldValue + changeValue, newValue);
                elem.html(oldValue);
            }, 22);
        });
        $scope.lang = function (text, varriables) {
            for (var i in varriables) {
                text = text.replace(/%[a-z]/, varriables[i]);
            }
            return text;
        };

        $('#pop-up-mask, .pop-up .close, [pop-up-close]').bind('click', pop.closeAll);
        $('header .decoration-bar').addClass('active');
    }])
    .controller('process-register', ['$scope', '$http', function ($scope, $http) {
        $scope.regProcess = 1;
        $scope.reg        = {
            email: '',
            password: '',
            trainer_name: '',
            password_retype: ''
        };
    }])
    .controller('page-daycare', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        $rootScope.putPokemon  = function (pkmId) {
            $http.get('?index=daycare&process=put-pokemon&pkm_id=' + pkmId);
        };
        $rootScope.takePokemon = function (pkmId) {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=daycare&process=take-pokemon&pkm_id=' + pkmId);
        };
        $rootScope.takeEgg     = function () {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=daycare&process=take-egg');
        };
    }])
    .controller('page-shelter', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        $rootScope.claimPokemon = function (pkmId) {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=shelter&process=claim-pokemon&pkm_id=' + pkmId);
        };
    }])
    .controller('page-shop', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        $rootScope.buyItem = function (itemId, quantity) {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=shop&process=buy-item&item_id=' + itemId + '&quantity=' + quantity);
            pop.closeAll();
        };
    }])
    .controller('page-starter', ['$scope', '$http', function($scope, $http) {
        $scope.claimPokemon = function(natId) {
            confirm($scope._LANG.are_you_sure) && $http.get('?index=starter&process=claim-pokemon&nat_id=' + natId);
        }
    }])
    .controller('page-pc', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        var sideMenu = $('.side-menu');
        sideMenu.find('[data-section="' + $rootScope.section + '"]').addClass('current');
        sideMenu.find('[data-section]').on('click', function (event) {
            event.preventDefault();
            if ($(this).hasClass('current')) return;
            $http.get('?index=pc&section=' + $(this).data('section'), {pushState: true});
            sideMenu.find('.current').removeClass('current');
            $(this).addClass('current');
            sideMenu.find('[data-section]');
        });
        $rootScope.healPokemon = function (pkmId, isTake) {
            (isTake && confirm($scope._LANG.are_you_sure) || !isTake) && $http.get('?index=pc&process=heal-pokemon&pkm_id=' + pkmId + '&action=' + (isTake ? 'take' : ''));
        };
    }])
    .controller('page-memcp', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        var sideMenu = $('.side-menu');
        sideMenu.find('[data-section="' + $rootScope.section + '"]').addClass('current');
        sideMenu.find('[data-section]').on('click', function (event) {
            event.preventDefault();
            if ($(this).hasClass('current')) return;
            $http.get('?index=memcp&section=' + $(this).data('section'), {pushState: true});
            sideMenu.find('.current').removeClass('current');
            $(this).addClass('current');
            sideMenu.find('[data-section]');
        });
        $scope.orderListener = {
            dragStart: function () {
                isTooltipDisabled = true;
            },
            dragEnd: function () {
                isTooltipDisabled = false;
            },
            orderChanged: function (event) {
                var orders  = '',
                    pokemon = event.source.itemScope.pokemon;
                for (var i in pokemon)
                    orders += '&orders[]=' + pokemon[i].pkm_id;
                $http.get('?index=memcp&process=pokemon-reorder' + orders);
            }
        };
        $scope.giveItem      = function (event, ui) {
            var target       = $(event.target),
                pokemon      = $rootScope.party[target.data('key')],
                itemId       = ui.helper.data('item-id'),
                targetItemId = target.find('img.to').attr('data-item-id');
            /* Be careful here, data() generates a cached id */
            pokemon.item_holding     = ui.helper.data('item-id');
            pokemon.hold_item_sprite = ui.helper.attr('src');
            ui.helper.remove();
            $rootScope.items[itemId].quantity--;
            if (targetItemId) $rootScope.items[targetItemId].quantity = parseInt($rootScope.items[targetItemId].quantity) + 1;
            $http.get('?index=memcp&process=give-item&pkm_id=' + pokemon.pkm_id + '&item_id=' + itemId);
        };
        $scope.returnItem    = function (event, ui) {
            var pokemon          = $rootScope.party[ui.draggable.parentsUntil('[data-key]').parent().data('key')];
            pokemon.item_holding = 0;
            ui.helper.remove();
            $rootScope.items[ui.helper.data('item-id')].quantity++;
            $http.get('?index=memcp&process=give-item&pkm_id=' + pokemon.pkm_id);
        };
        $rootScope.useItem   = function (itemId, pkmId) {
            if (!confirm($rootScope._LANG.are_you_sure)) return;
            $http.get('?index=memcp&process=use-item&pkm_id=' + pkmId + '&item_id=' + itemId);
        };
        $scope.deleteMessage = function (msg_id) {
            event.preventDefault();
            if (!confirm($scope._LANG.are_you_sure)) return;
            $http.get('?index=memcp&process=delete-message&msg_id=' + msg_id);
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
            link: function ($scope, $element, $attr) {
                $element.attr('placeholder', $attr.ngPlaceholder)
            }
        };
    })
    .directive('pokemonOverflow', function () {
        return {
            restrict: 'A',
            link: function ($scope, $element) {
                $element.bind('load', function () {
                    $element.css({
                        'left': (($element.parent().outerWidth() - $element.width()) / 2) + 'px',
                        'top': (($element.parent().outerHeight() - $element.height()) / 2) + 'px'
                    }).show();
                });
            }
        };
    })
    .directive('pokemonIcon', function () {
        return {
            restrict: 'E',
            scope: {
                natId: '='
            },
            link: function ($scope, $element) {
                $element.replaceWith('<span class="pokemon-icon" ' + ($scope.natId ? 'style="background-position:' +
                    '-' + ((parseInt($scope.natId) % 12) * 32) + 'px ' +
                    '-' + (Math.floor(parseInt($scope.natId) / 12) * 32) + 'px"' : '') + '></span>');
            }
        }
    })
    .directive('popUp', function () {
        return {
            priority: 1001,
            restrict: 'A',
            link: function ($scope, $element, $attr) {
                $element.bind('click', function () {
                    pop.open($attr.popUp);
                });
            }
        }
    })
    .directive('draggable', function () {
        return {
            restrict: 'A',
            link: function ($scope, $element) {
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
                                top: (y_pos - y_elem) + 'px',
                                left: (x_pos - x_elem) + 'px'
                            });
                    })
                    .on('mouseup', function () {
                        elem = null;
                    });
            }
        };
    })
    .directive('tooltip', ['$rootScope', function ($rootScope) {
        return {
            restrict: 'A',
            link: function ($scope, $element, $attr) {
                if (!$attr.tooltip) return;
                var tooltip         = $('#tooltip'),
                    decorationWidth = tooltip.find('.triangle').outerWidth();
                $element.bind('mouseover', function (event) {
                    if (isTooltipDisabled) return;
                    var dis = $(this), offset = dis.offset();
                    event.preventDefault();
                    $rootScope.tooltipMessage = $attr.tooltip;
                    $rootScope.$apply();
                    tooltip.css({
                        top: (offset.top + dis.outerHeight() / 2 - 7) + 'px',
                        left: (offset.left + dis.outerWidth() + decorationWidth) + 'px'
                    }).show();
                }).bind('mouseout', function (event) {
                    event.preventDefault();
                    $('#tooltip').hide();
                });
            }
        }
    }])
    .directive('copieable', ['$rootScope', function ($rootScope) {
        return {
            restrict: 'A',
            link: function ($scope, $element, $attr) {
                $element.on('click', function (event) {
                    event.preventDefault();
                    var clipboard = $('#clipboard-' + $attr.copieable).select(),
                        content   = clipboard.html();
                    try {
                        document.execCommand('copy');
                    } catch (e) {
                        if (navigator.userAgent.toLowerCase().match(/(msie|trident)/)) window.clipboardData.setData('Text', content);
                        else window.prompt($rootScope._LANG.copy_prompt, content);
                    }
                });
            }
        };
    }])
    .directive('vbar', ['$rootScope', '$parse', '$compile', function ($rootScope, $parse, $compile) {
        return {
            restrict: 'E',
            link: function ($scope, $element, $attr) {
                var options = $parse($attr.options)();
                $element.replaceWith($compile('<div class="vbar ' + options.type + '">' +
                    '<div class="outer" tooltip="' + options.value + ' / ' + options.max + '">' +
                    '<div class="inner" style="width:' + (options.value / options.max * 100) + '%"></div>' +
                    '<em>' + options.value + ' / ' + options.max + '</em></div></div>')($rootScope));
            }
        }
    }])
    .filter('toAbsoluteLink', function () {
        return function (input) {
            return location.origin + location.pathname + input.replace(/^(\.+\/)/, '');
        }
    })
    .filter('semiColumn', ['$rootScope', function ($rootScope) {
        return function (input) {
            return input + $rootScope._LANG.semi_column;
        }
    }]);

var pop = {
    open: function (name) {
        this.closeAll();
        var popUp = $('.pop-up.' + name);
        popUp.fadeIn().css({
            top: (($window.height() - popUp.height()) / 2.5) + 'px',
            left: (($window.width() - popUp.width()) / 2) + 'px'
        });
        $('#pop-up-mask').fadeIn();
    },
    closeAll: function () {
        $('.pop-up, #pop-up-mask').fadeOut();
    }
};

window.ondrag = function () {
    $('#tooltip').hide();
};

window.ondragstart = function () {
    isTooltipDisabled = true;
};

window.ondragstop = function () {
    isTooltipDisabled = false;
};

function pushState(title, href) {
    history.pushState({}, title, href);
    document.title = title;
}

/* Developer controller display message */
if (typeof console == 'object') {
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