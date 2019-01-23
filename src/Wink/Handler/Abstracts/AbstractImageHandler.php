<?php
namespace Wink\Handler\Abstracts;

use Wink\Service\Layout;
use Wink\Service\Source;
use Wink\Service\Schedule;
use Wink\Service\Time;

class AbstractImageHandler extends AbstractHandler {


    /**
     * @param array $config
     * @param string $layout
     * @param int $width
     * @param int $height
     * @param array $options
     * @return \Wink\Layout\Interfaces\LayoutInterface
     * @throws \Exception
     */
    protected function createLayout ($config, $layout, $width, $height,  array $options = []) {
        $layoutService = new Layout($config);
        $layoutEngine = $layoutService->create($layout, $width, $height, $options);

        return $layoutEngine;
    }

    /**
     * @param array $config
     * @param string $plugin
     * @param array $runtimeOptions
     * @return \Wink\Source\SourceInterface
     */
    protected function createSourcePlugin ($config, $plugin, $runtimeOptions) {
        $pluginService = new Source($config);

        return $pluginService->getSource($plugin, $runtimeOptions);
    }

    /**
     * @param array $config
     * @param array $schedule
     * @return Schedule
     */
    protected function processSchedule ($config, $schedule) {
        $operationSchedule = $config['operation_schedule'];

        $startTime = $operationSchedule['open'];
        $endTime = $operationSchedule['close'];
        $days = $operationSchedule['days'];

        $aggregator = new Schedule(Time::getCurrentDate(), Time::getCurrentTime(), $startTime, $endTime, $days);
        $aggregator->setAvailableMessage($operationSchedule['available_message']);
        $aggregator->setUnavailableMessage($operationSchedule['unavailable_message']);
        $aggregator->process($schedule);

        return $aggregator;
    }
}