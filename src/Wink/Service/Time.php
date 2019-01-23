<?php
namespace Wink\Service;

class Time {

    public static function getCurrentDate ($format = 'Y-m-d') {
        return self::getCurrent($format);
    }

    public static function getCurrentTime ($format = 'H:i:s') {
        return self::getCurrent($format);
    }

    public static function getCurrentDateTime ($format = 'Y-m-d H:i:s') {
        return self::getCurrent($format);
    }

    public static function getCurrent ($format) {
        return date($format, self::getCurrentTimestamp());
    }

    public static function format ($format, $timeStr) {
        return date($format, strtotime($timeStr));
    }

    public static function getCurrentTimestamp () {
        if (isset($_GET['time'])) {
            return strtotime($_GET['time']);
        }

        return time();
    }

    public static function fromUTCToLocal ($utcDate, $format = DATE_ISO8601) {
        $timezone = date_default_timezone_get();

        $replacedDate = new \DateTime($utcDate, new \DateTimeZone('UTC'));
        $replacedDate->setTimezone(new \DateTimeZone($timezone));

        return $replacedDate->format($format);
    }

    public static function fromLocalToUTC ($date, $format = 'Y-m-d H:i:s') {
        $timezone = date_default_timezone_get();

        $replacedDate = new \DateTime($date, new \DateTimeZone($timezone));
        $replacedDate->setTimezone(new \DateTimeZone('UTC'));

        return $replacedDate->format($format);
    }
}