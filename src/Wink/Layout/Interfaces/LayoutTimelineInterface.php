<?php
namespace Wink\Layout\Interfaces;

interface LayoutTimelineInterface extends LayoutScheduleInterface {

    /**
     * @param array $timeline
     */
    public function setTimeline (array $timeline);
}