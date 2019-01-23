<?php
namespace Wink\Layout\Interfaces;

interface LayoutInterface {
    const OPTION_TYPE_SELECT = 'select';
    const OPTION_TYPE_SHORT_TEXT = 'short_text';

    /**
     * LayoutInterface constructor.
     *
     * @param int $width
     * @param int $height
     * @param array $options the options chosen by the user.
     */
    public function __construct ($width, $height, $options);

    /**
     * @return array
     *  [
     *      'title_align' => [
     *          'default' => 'center',
     *          'type' => 'text',
     *          'options' => [
     *              'center' => 'Center',
     *              'left' => 'Left',
     *              'right' => 'Right',
     *          ]
     *      ],
     *      ...
     *  ]
     */
    public static function getOptions ();

    /**
     * Get the readable name for this layout.
     *
     * @return string
     */
    public static function getName ();

    /**
     * Render the layout to the image.
     *
     * @return \Imagick
     */
    public function render ();

    /**
     * Get the width of the layout.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Get the height of the layout.
     *
     * @return int
     */
    public function getHeight();
}