<?php
namespace Wink\Layout\Abstracts;

use Wink\Layout\Interfaces\LayoutScheduleInterface;

abstract class AbstractScheduleLayout extends AbstractLayout implements LayoutScheduleInterface {
    protected $resource;
    protected $schedule;

    public function setResourceAndSchedule ($resource, $schedule) {
        $this->resource = $resource;
        $this->schedule = $schedule;
    }

    /**
     * @return array
     */
    public function getResource () {
        return $this->resource;
    }

    /**
     * @param array $resource
     */
    public function setResource ($resource) {
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function getSchedule () {
        return $this->schedule;
    }

    /**
     * @param array $schedule
     */
    public function setSchedule ($schedule) {
        $this->schedule = $schedule;
    }
}