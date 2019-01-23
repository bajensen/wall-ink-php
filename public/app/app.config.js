angular
    .module('winkApp')
    .config(['$routeProvider', '$locationProvider',
        function config($routeProvider) {
            $routeProvider
                .when('/devices', {
                    template: '<device-list></device-list>',
                    activetab: 'devices',
                    has_device: false
                })
                .when('/device/:deviceId', {
                    template: '<device-detail></device-detail>',
                    activetab: 'device',
                    has_device: true
                })
                .when('/device_history/:deviceId', {
                    template: '<device-history></device-history>',
                    activetab: 'device_history',
                    has_device: true
                })
                .otherwise('/devices');
        }
    ])
    .directive('ngSlowSrc', function($timeout){
        return{
            restrict: 'A',
            link: function(scope, element, attrs){
                let promise = null;

                function svgPlaceHolder() {
                    let w = element.attr('data-placeholder-width');
                    let h = element.attr('data-placeholder-height');

                    let xml = '<svg width="' + w + '" height="' + h + '" xmlns="http://www.w3.org/2000/svg">' +
                        '<rect x="2" y="2" width="' + (w-4) + '" height="' + (h-4) + '" style="fill:#DEDEDE;stroke:#555555;stroke-width:2"/>' +
                        '<text x="50%" y="50%" font-size="18" text-anchor="middle" alignment-baseline="middle" font-family="monospace, sans-serif" fill="#555555">' + w + '&#215;' + h + '</text>' +
                        '</svg>';
                    return 'data:image/svg+xml;base64,' + btoa(xml);
                }

                attrs.$observe('ngSlowSrc',function(){
                    element.attr('src', svgPlaceHolder());

                    if (promise !== null) {
                        $timeout.cancel(promise);
                    }

                    promise = $timeout(function() {
                        element.attr('src', element.attr('ng-slow-src'));
                    }, 1500);
                });
            }
        }
    })
    .run(function ($rootScope, $route) {
        $rootScope.$route = $route;
    })
    .filter('uriEscape', function() {
        return function (input) {
            if (input) {
                return window.encodeURIComponent(input);
            }
            return "";
        }
    })
    .filter('jsonEncode', function () {
        return function (input) {
            if (input) {
                return angular.toJson(input);
            }
            return "";
        }
    })
    .filter('isPast', function () {
        return function (input) {
            if (input) {
                let then = new Date(input);
                let now = new Date();
                return now > then;
            }

            return false;
        }
    })
;