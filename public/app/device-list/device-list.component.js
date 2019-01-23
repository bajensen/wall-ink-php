angular
    .module('deviceList')
    .component('deviceList', {
        templateUrl: '/app/device-list/device-list.template.html',
        controller: ['$routeParams', '$http', '$q', '$location',
            function DeviceListController($routeParams, $http, $q, $location) {
                $ctrl = this;
                $ctrl.devices = [];
                $ctrl.ready = false;

                $http.get('/admin/devices').then(function (response) {
                    $ctrl.devices = response.data;
                    $ctrl.ready = true;
                });

                $ctrl.open = function (deviceId) {
                    $location.path("/device/" + deviceId);
                }
            }
        ]
    })
;