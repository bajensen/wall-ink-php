<?php
namespace Wink\Service;

class Source {
    protected $config;

    public function __construct ($config) {
        $this->config = $config;
    }

    public function getSources () {
        $sources = [];

        foreach ($this->config['sources'] as $sourcePlugin => $sourceConfig) {
            $sources[$sourcePlugin] = [
                'name' => $this->getName($sourcePlugin)
            ];
        }

        return $sources;
    }

    /**
     * @param string $source
     * @param array $runtimeOptions
     * @return \Wink\Source\Interfaces\SourceInterface|null
     * @throws \Exception
     */
    public function getSource ($source, array $runtimeOptions) {
        $runtimeOptions = $this->calculateOptionValues($source, $runtimeOptions);

        $sourceClass = $this->getClassName($source);

        if (! $sourceClass) {
            throw new \Exception('Cannot find Plugin class for ' . $source);
        }

        /** @var \Wink\Source\Interfaces\SourceInterface $source */
        $source = new $sourceClass($this->config['sources'][$source], $runtimeOptions);

        return $source;
    }

    /**
     * @param string $source
     * @param array $options
     * @return array
     */
    protected function calculateOptionValues($source, array $options) {
        $config = $this->config;

        $sourceOptions = [];

        $runtimeOptions = (isset($config['sources'][$source]['runtime']) ? $config['sources'][$source]['runtime'] : []);

        $sourceOptions = array_replace_recursive($sourceOptions, $runtimeOptions);

        if (is_array($options)) {
            $options = array_filter($options);
            $sourceOptions = array_replace_recursive($sourceOptions, $options);
        }

        return $sourceOptions;
    }

    /**
     * @param string $source
     * @return string
     */
    public function getName ($source) {
        $layoutClass = $this->getClassName($source);

        return $layoutClass ? call_user_func($layoutClass . '::getName') : 'Cannot find ' . $source;
    }

    /**
     * @param string $source .
     * @return array
     */
    public function getOptions ($source) {
        $source = $this->getSource($source, []);

        return $source ? $source->getOptions() : [];
    }

    /**
     * @param string $source
     * @return string|null
     */
    protected function getClassName ($source) {
        if (! array_key_exists($source, $this->config['sources'])) {
            return null;
        }

        $className = $this->config['sources'][$source]['class'];

        if (class_exists($className)) {
            return $className;
        }
        else {
            return null;
        }
    }
}