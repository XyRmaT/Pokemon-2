/* Library based on AngularJS */
var app = angular.module('application', []);

app.factory('generalQueue', ['$q', '$timeout', function ($q, $timeout) {
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
}]);