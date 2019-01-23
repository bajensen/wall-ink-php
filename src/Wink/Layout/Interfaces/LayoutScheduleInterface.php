<?php
namespace Wink\Layout\Interfaces;

interface LayoutScheduleInterface extends LayoutInterface {

    /**
     * @param array $resource
     * @param array $schedule
     */
    public function setResourceAndSchedule ($resource, $schedule);
}