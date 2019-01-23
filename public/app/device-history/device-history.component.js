angular
    .module('deviceHistory')
    .component('deviceHistory', {
        templateUrl: '/app/device-history/device-history.template.html',
        controller: ['$routeParams', '$http', '$q', '$scope', '$location', '$timeout',
            function DeviceHistoryController($routeParams, $http, $q, $scope, $location, $timeout) {
                let $ctrl = this;

                $ctrl.deviceId = $routeParams.deviceId;

                $ctrl.deviceHistory = [];
                $ctrl.data = {};
                $ctrl.ready = false;

                let deviceHistory = $http.get('/admin/device_history/' + $ctrl.deviceId);

                deviceHistory.then(function (response) {
                    $ctrl.ready = true;
                    $ctrl.deviceHistory = response.data;
                });
            }
        ]
    })
;