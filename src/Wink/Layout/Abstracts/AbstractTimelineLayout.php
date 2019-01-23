<?php
namespace Wink\Layout\Abstracts;

use Wink\Layout\Interfaces\LayoutTimelineInterface;

abstract class AbstractTimelineLayout extends AbstractScheduleLayout implements LayoutTimelineInterface {
    protected $timeline;

    /**
     * @param array $timeline
     */
    public function setTimeline (array $timeline) {
        $this->timeline = $timeline;
    }

    /**
     * @return array
     */
    public function getTimeline () {
        return $this->timeline;
    }
}