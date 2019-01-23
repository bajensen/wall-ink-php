<?php
namespace Wink\Layout\Core;

use Wink\Layout\Abstracts\AbstractLayout;

class Error extends AbstractLayout {

    const FONT_TITLE = 'core/standard/Lato-Bold.ttf';
    const FONT_TITLE_SIZE = 46;

    const FONT_SUBTITLE = 'core/standard/Lato-Regular.ttf';
    const FONT_SUBTITLE_SIZE = 24;

    public function render () {
        $image = $this->createBaseImage();

        $y = $this->getHeight() / 2;

        $y += $this->annotation->drawText($image,
            $this->getWidth() / 2, $y,
            null, null, false,
            'Error', 'black',
            self::FONT_TITLE, self::FONT_TITLE_SIZE,
            \Imagick::ALIGN_CENTER
        );

        $this->annotation->drawText($image,
            $this->getWidth() / 2, $y,
            $this->getWidth(), null, false,
            'Something went wrong...', 'black',
            self::FONT_SUBTITLE, self::FONT_SUBTITLE_SIZE,
            \Imagick::ALIGN_CENTER
        );

        $image->quantizeImage(2, \Imagick::COLORSPACE_GRAY, false, false, false);

        return $image;
    }

    /**
     * Get the readable name for this layout.
     *
     * @return string
     */
    public static function getName () {
        return 'Error';
    }

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
    public static function getOptions () {
        return [];
    }
}