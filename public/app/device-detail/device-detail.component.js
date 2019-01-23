angular
    .module('deviceDetail')
    .component('deviceDetail', {
        templateUrl: '/app/device-detail/device-detail.template.html',
        controller: ['$routeParams', '$http', '$q', '$scope', '$location', '$timeout',
            function DeviceDetailController($routeParams, $http, $q, $scope, $location, $timeout) {
                let $ctrl = this;

                $ctrl.deviceId = $routeParams.deviceId;

                $ctrl.device = {};
                $ctrl.data = {};
                $ctrl.ready = false;

                let device = $http.get('/admin/device/' + $ctrl.deviceId);
                let complete_options = $http.get('/admin/complete_options');

                $q.all([device, complete_options]).then(function () {
                    $ctrl.ready = true;

                    $ctrl.device = angular.copy($ctrl.data.device);

                    $ctrl.layouts = $ctrl.data.layouts;
                    $ctrl.layout_options = $ctrl.data.layout_options;
                    $ctrl.sources = $ctrl.data.sources;
                    $ctrl.source_options = $ctrl.data.source_options;

                });

                $scope.$watch('$ctrl.device.layout_type', function (layoutType) {
                    let device = $ctrl.device;

                    if (device) {
                        let layoutType = device.layout_type;

                        if (layoutType) {

                            if (! device.hasOwnProperty('layout_options') || device.layout_options === null) {
                                device.layout_options = {};
                            }

                            let layoutTypeOptions = $ctrl.layout_options[layoutType];

                            for (let i in layoutTypeOptions) {
                                let option = layoutTypeOptions[i];

                                if (option.type !== 'short_text' && (! device.layout_options.hasOwnProperty(i) || ! device.layout_options[i])) {
                                    device.layout_options[i] = option.default;
                                }
                            }
                        }
                    }
                });

                $scope.$watch('$ctrl.device.source_options', function (sourcePlugin) {
                    let device = $ctrl.device;

                    if (device) {
                        let sourcePlugin = device.source_plugin;

                        if (sourcePlugin) {

                            if (! device.hasOwnProperty('source_options') || device.source_options === null) {
                                device.source_options = {};
                            }

                            let sourceOptions = $ctrl.source_options[sourcePlugin];

                            for (let i in sourceOptions) {
                                let option = sourceOptions[i];

                                if (option.type !== 'short_text' && (! device.source_options.hasOwnProperty(i) || ! device.source_options[i])) {
                                    device.source_options[i] = option.default;
                                }
                            }
                        }
                    }
                });

                device.then(function (response) {
                    $ctrl.data.device = response.data;
                });

                complete_options.then(function (response) {
                    $ctrl.data.sources = response.data.sources;
                    $ctrl.data.source_options = response.data.source_options;
                    $ctrl.data.layouts = response.data.layouts;
                    $ctrl.data.layout_options = response.data.layout_options;
                });

                $ctrl.isValid = function () {
                    let theDevice = $ctrl.device;

                    if (! theDevice.mac_address) {
                        return false;
                    }

                    if (
                        ! theDevice.layout_type ||
                        theDevice.layout_type && $ctrl.layouts[theDevice.layout_type].uses_source &&
                        (! theDevice.source_plugin || ! theDevice.source_options)) {
                        return false;
                    }

                    return true;
                };

                $ctrl.previewUrl = function (device) {
                    let layoutOptionsStr = window.encodeURIComponent(angular.toJson(device.layout_options));
                    let sourceOptionsStr = window.encodeURIComponent(angular.toJson(device.source_options));

                    return '/admin/preview?' +
                        'preview=1' +
                        '&width=' + device.width +
                        '&height=' + device.height +
                        '&source_plugin=' + device.source_plugin +
                        '&source_options=' + sourceOptionsStr +
                        '&layout_type=' + device.layout_type +
                        '&layout_options=' + layoutOptionsStr;
                };

                $ctrl.resetBattery = function () {
                    $ctrl.device.batteries_replaced_date = new Date().toISOString();
                };

                $ctrl.canSave = function () {
                    return ! angular.equals($ctrl.device, $ctrl.data.device) && $ctrl.isValid();
                };

                $ctrl.clearAll = function () {
                    $ctrl.device.layout_type = null;
                    $ctrl.device.layout_options = null;
                    $ctrl.device.source_plugin = null;
                    $ctrl.device.source_options = null;
                };

                $ctrl.save = function () {
                    if ($ctrl.canSave()) {
                        $http.post('/admin/device/' + $ctrl.deviceId, $ctrl.device).then(function () {
                            $location.path("/devices/");
                        });
                    }
                };

                $ctrl.delete = function () {
                    if ($ctrl.deleteConfirm) {
                        $http.delete('/admin/device/' + $ctrl.deviceId).then(function () {
                            $location.path("/devices/");
                        });
                    }

                    $ctrl.deleteConfirm = true;

                    $timeout(function () {
                        $ctrl.deleteConfirm = false;
                    }, 500);
                }
            }
        ]
    })
;