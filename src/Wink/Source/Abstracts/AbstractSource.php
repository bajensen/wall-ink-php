<?php
namespace Wink\Source\Abstracts;

use Wink\Source\Interfaces\SourceInterface;

abstract class AbstractSource implements SourceInterface {
    protected $config;
    protected $runtimeConfig;

    public function __construct ($staticConfig, $runtimeConfig) {
        $this->config = $staticConfig;
        $this->runtimeConfig = $runtimeConfig;
    }
}