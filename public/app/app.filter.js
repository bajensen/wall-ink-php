angular
    .module('winkApp')
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
    .filter('errorName', function () {
        return function (input) {
            let map = {
                // Firmware Codes
                1: "WiFi Connection Weak",
                4: "WiFi Connect Failed",
                5: "WiFi SSID Not Found",
                6: "WiFi Connection Lost",
                7: "WiFi Max Attempts",
                2: "File too small",
                3: "Verification Failed",
                8: "Replay Attack Detected",

                // HTTP Codes
                200: 'OK',
                201: 'Created',
                202: 'Accepted',
                203: 'Non-Authoritative Information',
                204: 'No Content',
                205: 'Reset Content',
                206: 'Partial Content',
                300: 'Multiple Choices',
                301: 'Moved Permanently',
                302: 'Found',
                303: 'See Other',
                304: 'Not Modified',
                305: 'Use Proxy',
                307: 'Temporary Redirect',
                400: 'Bad Request',
                401: 'Unauthorized',
                402: 'Payment Required',
                403: 'Forbidden',
                404: 'Not Found',
                405: 'Method Not Allowed',
                406: 'Not Acceptable',
                407: 'Proxy Authentication Required',
                408: 'Request Timeout',
                409: 'Conflict',
                410: 'Gone',
                411: 'Length Required',
                412: 'Precondition Failed',
                413: 'Request Entity Too Large',
                414: 'Request-URI Too Long',
                415: 'Unsupported Media Type',
                416: 'Requested Range Not Satisfiable',
                417: 'Expectation Failed',
                500: 'Internal Server Error',
                501: 'Not Implemented',
                502: 'Bad Gateway',
                503: 'Service Unavailable',
                504: 'Gateway Timeout',
                505: 'HTTP Version Not Supported'
            };

            if (input && map.hasOwnProperty(input)) {
                return map[input] + ' (' + input + ')';
            }

            return '';
        }
    })
;