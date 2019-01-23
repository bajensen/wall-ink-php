<?php
namespace Wink\Service;

class Schedule {
    protected $currentDate;
    protected $currentTime;
    protected $openTime;
    protected $closeTime;
    protected $operatingDays = [];

    protected $availableMessage = 'Available*';
    protected $unavailableMessage = 'Not Reservable';

    protected $incremental;
    protected $collapsed;
    protected $final;

    const PRECISION = '+1 min';

    /**
     * Schedule constructor.
     *
     * @param string $currentDate
     * @param string $currentTime
     * @param string $openTime
     * @param string $closeTime
     * @param array $operatingDays
     */
    public function __construct ($currentDate, $currentTime, $openTime, $closeTime, $operatingDays) {
        $this->currentDate = $currentDate;
        $this->currentTime = $currentTime;
        $this->openTime = $openTime;
        $this->closeTime = $closeTime;
        $this->operatingDays = $operatingDays;
    }

    /**
     * @param array $scheduleItems [ ['start_time' => '2018-01-01 07:00:00', 'end_time' => '2018-01-01 07:59:00', 'title' => 'Board Meeting'], ... ]
     */
    public function process (array $scheduleItems) {
        uasort($scheduleItems, function ($a, $b) {
            $startTSA = strtotime($a['start_time']);
            $startTSB = strtotime($b['start_time']);

            if ($startTSA < $startTSB) {
                return -1;
            }
            elseif ($startTSA > $startTSB) {
                return 1;
            }

            $endTSA = strtotime($a['end_time']);
            $endTSB = strtotime($b['end_time']);

            if ($endTSA < $endTSB) {
                return -1;
            }
            elseif ($endTSA > $endTSB) {
                return 1;
            }

            return 0;
        });

        $this->incremental = $this->create();
        $this->incremental = $this->populate($scheduleItems, $this->incremental);
        $this->collapsed = $this->collapse($this->incremental);
        $this->final = $this->filter($this->collapsed);
    }

    /**
     * Create the array representing the days schedule with an element for each minute.
     * @return array
     */
    protected function create () {
        $startTime = strtotime($this->currentDate. ' 00:00:00');
        $endTime = strtotime($this->currentDate. ' 23:59:00');

        $increment = strtotime(self::PRECISION, 0);

        $incrementalItems = [];

        // Prepare incremental items
        for ($time = $startTime; $time <= $endTime; $time += $increment) {
            $timeFormatted = date('Y-m-d H:i:s', $time);

            $incrementalItems[$time] = [
                'start_time' => $timeFormatted,
                'end_time' => $timeFormatted,
                'title' => $this->isOperatingAt($time) ? $this->getAvailableMessage() : $this->getUnavailableMessage(),
                'reserved' => false
            ];
        }

        return $incrementalItems;
    }

    /**
     * Populate the per-minute schedule using the given arary of items
     *
     * @param array $scheduleItems
     * @param array $incrementalItems
     * @return array
     */
    protected function populate (array $scheduleItems, array $incrementalItems) {
        foreach ($scheduleItems as $item) {
            $startTime = strtotime($item['start_time']);
            $endTime = strtotime($item['end_time']);
            $increment = strtotime(self::PRECISION, 0);

            for ($time = $startTime; $time <= $endTime; $time += $increment) {
                if (array_key_exists($time, $incrementalItems)) {
                    $incrementalItems[$time]['title'] = $item['title'];
                    $incrementalItems[$time]['reserved'] = true;
                }
            }
        }

        return $incrementalItems;
    }

    /**
     * Collapse the given per-minute daily schedule into a usable per-title schedule.
     *
     * @param array $incrementalItems
     * @return array
     */
    protected function collapse ($incrementalItems) {
        $incrementalItems = array_values($incrementalItems);

        $itemCount = count($incrementalItems);

        if ($itemCount < 2) {
            return $incrementalItems;
        }

        $finalItems = [$incrementalItems[0]];

        for ($i = 1; $i < $itemCount; $i++) {
            $lastAdded = $finalItems[count($finalItems) - 1];
            if ($incrementalItems[$i]['title'] != $lastAdded['title']) {
                $finalItems[] = $incrementalItems[$i];
            }
            else {
                $finalItems[count($finalItems) - 1]['end_time'] = $incrementalItems[$i]['end_time'];
            }
        }

        return $finalItems;
    }

    /**
     * Remove items that ended in the past.
     *
     * @param array $items
     * @return array
     */
    protected function filter (array $items) {
        $currentTime = $this->currentTime;

        $items = array_filter($items, function ($item) use ($currentTime) {
            $itemEndTime = Time::format('H:i', $item['end_time']);
            $currentTime = Time::format('H:i', $currentTime);
            return ($itemEndTime >= $currentTime);
        });

        return array_values($items);
    }

    /**
     * Determine if the resource is operating at the given time.
     *
     * @param $time
     * @return bool
     */
    protected function isOperatingAt ($time) {
        $operatingDays = $this->operatingDays;
        $openTime = strtotime($this->currentDate . ' ' . $this->openTime);
        $closeTime = strtotime($this->currentDate . ' ' . $this->closeTime);

        $weekDay = date('w', $time);

        return (in_array($weekDay, $operatingDays) && $openTime <= $time && $time < $closeTime);
    }

    /**
     * @return mixed
     */
    public function getIncremental () {
        return $this->incremental;
    }

    /**
     * @return mixed
     */
    public function getTimeline () {
        $items = $this->incremental;

        $items = array_filter($items, [$this, 'isOperatingAt'], ARRAY_FILTER_USE_KEY);

        $items = array_map(function ($e) {
            return $e['reserved'];
        }, $items);

        return $items;
    }

    /**
     * @return mixed
     */
    public function getCollapsed () {
        return $this->collapsed;
    }

    /**
     * @return mixed
     */
    public function getFinalSchedule () {
        return $this->final;
    }

    /**
     * @return string
     */
    public function getAvailableMessage () {
        return $this->availableMessage;
    }

    /**
     * @param string $availableMessage
     */
    public function setAvailableMessage ($availableMessage) {
        $this->availableMessage = $availableMessage;
    }

    /**
     * @return string
     */
    public function getUnavailableMessage () {
        return $this->unavailableMessage;
    }

    /**
     * @param string $unavailableMessage
     */
    public function setUnavailableMessage ($unavailableMessage) {
        $this->unavailableMessage = $unavailableMessage;
    }

}