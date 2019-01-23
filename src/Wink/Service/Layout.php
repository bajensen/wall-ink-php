<?php
namespace Wink\Service;

use ReflectionClass;
use Wink\Layout\Interfaces\LayoutScheduleInterface;

class Layout {
    protected $config = [];

    public function __construct (array $config) {
        $this->config = $config;
    }

    /**
     * @return array ['ClassName' => ['name' => 'Readable Name', 'uses_source' => true], ...]
     */
    public function getLayouts () {
        $layouts = [];

        foreach ($this->config['layouts'] as $layout => $layoutConfig) {
            if ($this->getClassName($layout)) {
                $layouts [$layout] = [
                    'name' => $this->getName($layout),
                    'uses_source' => $this->getUsesSource($layout)
                ];
            }
        }

        return $layouts;
    }

    /**
     * @param string $layout
     * @param int $width
     * @param int $height
     * @param array $options
     * @return \Wink\Layout\Interfaces\LayoutInterface
     * @throws \Exception
     */
    public function create ($layout, $width, $height, array $options = []) {
        $config = $this->config;

        $layoutOptions = $this->calculateOptionValues($layout, $options);

        $layoutClass = $this->getClassName($layout);

        if (! $layoutClass) {
            throw new \Exception('Cannot find Layout class for ' . $layout);
        }

        /** @var \Wink\Layout\Interfaces\LayoutInterface $layoutEngine */
        $layoutEngine = new $layoutClass($width, $height, $layoutOptions);

        return $layoutEngine;
    }

    /**
     * @param string $layout
     * @return string
     */
    public function getName ($layout) {
        $layoutClass = $this->getClassName($layout);

        return $layoutClass ? call_user_func($layoutClass . '::getName') : 'Cannot find ' . $layout;
    }

    /**
     * @param string $layout .
     * @return array
     */
    public function getOptions ($layout) {
        $layoutClass = $this->getClassName($layout);

        return $layoutClass ? call_user_func($layoutClass . '::getOptions') : [];
    }

    /**
     * @param string $layout
     * @return bool
     */
    public function getUsesSource ($layout) {
        $layoutClass = $this->getClassName($layout);
        $rc = new \ReflectionClass($layoutClass);

        return $rc->implementsInterface(LayoutScheduleInterface::class);
    }

    /**
     * @param string $layout
     * @param array $options
     * @return array
     */
    protected function calculateOptionValues($layout, array $options) {
        $config = $this->config;

        $layoutOptions = [];

        $layoutConfigOptions = (isset($config['layouts'][$layout]) ? $config['layouts'][$layout] : []);

        $layoutOptions = array_replace_recursive($layoutOptions, $layoutConfigOptions);

        if (is_array($options)) {
            $options = array_filter($options);
            $layoutOptions = array_replace_recursive($layoutOptions, $options);
        }

        return $layoutOptions;
    }

    /**
     * @param string $layout
     * @return string|null
     */
    protected function getClassName ($layout) {
        if (! array_key_exists($layout, $this->config['layouts'])) {
            return null;
        }

        $className = $this->config['layouts'][$layout]['class'];

        if (class_exists($className)) {
            return $className;
        }
        else {
            return null;
        }
    }
}