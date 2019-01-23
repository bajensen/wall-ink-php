<?php
namespace Wink\Layout\Abstracts;

use Wink\ImageUtil\Annotation;
use Wink\Layout\BuiltIn\Reservable;
use Wink\Layout\Interfaces\LayoutInterface;

abstract class AbstractLayout implements LayoutInterface {
    protected $config;

    protected $width;
    protected $height;

    /** @var Annotation */
    protected $annotation;

    public function __construct ($width, $height, $options) {
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setConfig($options);

        $this->annotation = new Annotation();
    }

    public function createBaseImage () {
        $image = new \Imagick();
        $image->newImage($this->getWidth(), $this->getHeight(), new \ImagickPixel('white'));
        $image->setImageFormat('png');

        return $image;
    }

    /**
     * @param string $option
     * @return mixed|null
     */
    public function getConfigOption ($option) {
        $options = $this->getOptions();

        if (
            is_array($this->config) &&
            array_key_exists($option, $this->config)
        ) {
            return $this->config[$option];
        }
        elseif (
            is_array($options) &&
            array_key_exists($option, $options) &&
            array_key_exists('default', $options[$option])
        ) {
            return $options[$option]['default'];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getConfig () {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig ($config) {
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function getWidth () {
        return $this->width;
    }

    /**
     * @param int $width
     * @throws \Exception
     */
    public function setWidth ($width) {
        if (! is_numeric($width)) {
            throw new \Exception('Invalid width.');
        }

        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight () {
        return $this->height;
    }

    /**
     * @param int $height
     * @throws \Exception
     */
    public function setHeight ($height) {
        if (! is_numeric($height)) {
            throw new \Exception('Invalid height.');
        }

        $this->height = $height;
    }

    /**
     * @param $endTime
     * @param $startTime
     * @return float|int|string
     */
    public function calculateLength ($endTime, $startTime) {
        $length = ($endTime - $startTime) / 60 + 1;

        $length = round($length / 5, 0) * 5;

        $min = $length % 60;

        $hr = intdiv($length, 60);

        $length = ($hr ? $hr . ' hr' : '') . ($hr && $min ? ' ' : '') . ($min ? $min . ' min' : '');

        return $length;
    }

    /**
     * @param $item
     * @return string
     */
    public function calculateSubtitle ($item) {
        $startTime = strtotime($item['start_time']);
        $endTime = strtotime($item['end_time']);
        $length = $this->calculateLength($endTime, $startTime);
        $diffMeridiem = (date('a', $startTime) != date('a', $endTime));
        $subtitle = date('g:i' . ($diffMeridiem ? ' a' : ''), $startTime) . '–' . date('g:i a', $endTime) . ' (' . $length . ')';

        return $subtitle;
    }


}