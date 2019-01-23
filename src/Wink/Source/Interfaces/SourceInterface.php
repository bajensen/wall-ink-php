<?php

namespace Wink\Source\Interfaces;

interface SourceInterface {

    /**
     * SourceInterface constructor.
     *
     * @param $staticConfig
     * @param $runtimeConfig
     */
    public function __construct ($staticConfig, $runtimeConfig);

    /**
     * @return string
     */
    public static function getName ();

    /**
     * @return array
     *  [
     *      'ical_url' => [
     *          'required' => true,
     *          'type' => 'text'
     *      ],
     *      ...
     *  ]
     */
    public function getOptions ();

    /**
     * @return array [['title' => '...', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), [...], ...]
     */
    public function getSchedule ();

    /**
     * @return array ['title' => '...', 'subtitle' => '...', ...]
     */
    public function getResource ();
}