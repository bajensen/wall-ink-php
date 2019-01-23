<?php
namespace Wink\Layout\BuiltIn;

use Wink\Layout\Abstracts\AbstractTimelineLayout;

abstract class AbstractBuiltIn extends AbstractTimelineLayout {

    const FONT_TINY_SIZE = 16;
    const FONT_TINY = 'core/pixel/EnterCommand.ttf';
    const FONT_HEADER_TITLE = 'core/standard/Lato-Bold.ttf';
    const FONT_HEADER_SUBTITLE = 'core/pixel/MotorolaScreentype.ttf';
    const FONT_LARGE_TITLE = 'core/standard/Lato-Bold.ttf';
    const STROKE_WIDTH = 4;
    const FONT_HEADER_TITLE_SIZE = 46;
    const FONT_LARGE_TITLE_SIZE = 34;
    const SIDE_MARGIN_CONTENT = 10;
    const FONT_HEADER_SUBTITLE_SIZE = 16;
    const FONT_LARGE_SUBTITLE = 'core/standard/Lato-Bold.ttf';
    const SIDE_MARGIN_HEADER = 10;
    const FONT_FOOTER_SIZE = 16;
    const STROKE_WIDTH_CLOCK = 6;
    const FONT_SMALL_TITLE = 'core/standard/Lato-Regular.ttf';
    const FONT_LARGE_SUBTITLE_SIZE = 24;
    const FONT_SMALL_TITLE_SIZE = 24;
    const FONT_FOOTER = 'core/pixel/MotorolaScreentype.ttf';
    const FONT_SMALL_SUBTITLE_SIZE = 16;
    const FONT_SMALL_SUBTITLE = 'core/standard/Lato-Regular.ttf';

    /**
     * @return array|false
     */
    protected static function getBackgroundOptions () {
        $dirIter = new \RecursiveDirectoryIterator(\Wink\Path::BACKGROUND_PATH);
        $iter = new \RecursiveIteratorIterator($dirIter, \RecursiveIteratorIterator::SELF_FIRST);

        $backgroundOptions = [];

        /** @var \SplFileInfo $file */
        foreach ($iter as $file) {
            if ($file->isFile()) {
                $image = new \Imagick();

                try {
                    if ($image->pingImage($file->getRealPath())) {
                        $backgroundOptions[] = str_replace('\\', '/', $file->getRealPath());
                    }
                }
                catch (\ImagickException $e) {
                    // Could not decode, s
                }
            }
        }

        $backgroundOptions = array_map(function ($e) {
            return str_replace(\Wink\Path::BACKGROUND_PATH, '', $e);
        }, $backgroundOptions);

        return $backgroundOptions;
    }

    protected static function getBuiltInOptions () {
        $backgroundOptions = self::getBackgroundOptions();

        return [
            'theme' => [
                'name' => 'Theme',
                'default' => 'dark',
                'type' => 'select',
                'options' => [
                    'dark' => 'Dark',
                    'light' => 'Light'
                ]
            ],
            'header_align' => [
                'name' => 'Header Align',
                'default' => 'center',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ]
            ],
            'content_text_bg_type' =>[
                'name' => 'Content Text Background',
                'default' => 'highlight',
                'type' => 'select',
                'options' => [
                    'highlight' => 'Highlight (Box)',
                    'stroke' => 'Stroke (Shadow)',
                    'none' => 'None'
                ]
            ],
            'content_align' => [
                'name' => 'Content Align',
                'default' => 'left',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ]
            ],
            'background_image' => [
                'name' => 'Background Image',
                'default' => 'none',
                'type' => 'select',
                'options' => (['none' => 'None', 'random' => 'Random'] + array_combine($backgroundOptions, $backgroundOptions))
            ],
            'footer_left_text' => [
                'name' => 'Footer Left Text',
                'default' => 'Wall Ink Display',
                'type' => 'short_text'
            ],
            'footer_right_text' => [
                'name' => 'Footer Right Text',
                'default' => 'Reserve at example.com',
                'type' => 'short_text'
            ],
        ];
    }

    /**
     * @param \Imagick $image
     */
    public function drawFooter ($image) {
        $theme = $this->getConfigOption('theme');

        if ($theme == 'light') {
            $fgColor = 'black';
            $bgColor = 'white';
        }
        else {
            $fgColor = 'white';
            $bgColor = 'black';
        }

        $this->drawRect($image, $bgColor, 0, $this->getHeight() - 28, $this->getWidth(), $this->getHeight() - 1);

        if ($theme == 'light') {
            $this->drawRect($image, $fgColor, 0, $this->getHeight() - 28, $this->getWidth(), $this->getHeight() - 26);
        }

        $leftText = $this->getConfigOption('footer_left_text');
        $rightText = $this->getConfigOption('footer_right_text');
        $y = 6;
        $this->annotation->drawText($image, self::SIDE_MARGIN_HEADER, $this->getHeight() - $y, null, null, false, $leftText, $fgColor, self::FONT_FOOTER, self::FONT_FOOTER_SIZE, \Imagick::ALIGN_LEFT);
        $this->annotation->drawText($image, $this->getWidth() - self::SIDE_MARGIN_HEADER, $this->getHeight() - $y, null, null, false, $rightText, $fgColor, self::FONT_FOOTER, self::FONT_FOOTER_SIZE, \Imagick::ALIGN_RIGHT);
    }

    /**
     * @param \Imagick $image
     */
    public function drawHeader ($image) {
        $theme = $this->getConfigOption('theme');

        if ($theme == 'light') {
            $fgColor = 'black';
            $bgColor = 'white';
        }
        else {
            $fgColor = 'white';
            $bgColor = 'black';
        }

        $this->drawRect($image, $bgColor, 0, 0, $this->getWidth(), 72);

        if ($theme == 'light') {
            $this->drawRect($image, $fgColor, 0, 72, $this->getWidth(), 74);
        }

        $resource = $this->getResource();
        $subtitle = $resource['subtitle'];
        $firstRow = $resource['title'];
        $secondRow = \Wink\Service\Time::getCurrent('l, j F Y') . ($subtitle ? ' - ' . $subtitle : '');

        list($align, $x) = $this->getAlignFromOption('header_align');

        $this->annotation->drawText($image, $x, 66, null, null, false, $secondRow, $fgColor, self::FONT_HEADER_SUBTITLE, self::FONT_HEADER_SUBTITLE_SIZE, $align);
        $this->annotation->drawText($image, $x, 44, null, null, false, $firstRow, $bgColor, self::FONT_HEADER_TITLE, self::FONT_HEADER_TITLE_SIZE, $align, $bgColor, 1);
        $this->annotation->drawText($image, $x, 44, null, null, false, $firstRow, $fgColor, self::FONT_HEADER_TITLE, self::FONT_HEADER_TITLE_SIZE, $align);
    }

    /**
     * @param \Imagick $image
     */
    public function drawBackground ($image) {
        $background = $this->getConfigOption('background_image');
        $backgrounds = self::getBackgroundOptions();

        if ($background == 'random') {
            $idx = date('s') % count($backgrounds);
//            $image->readImage(\Wink\Path::BACKGROUND_PATH . $backgrounds[$idx]);
            $bgImg = new \Imagick(\Wink\Path::BACKGROUND_PATH . $backgrounds[$idx]);
            $image->compositeImageGravity($bgImg, \Imagick::COMPOSITE_DEFAULT, \Imagick::GRAVITY_CENTER);
        }
        elseif ($background != 'none' && in_array($background, $backgrounds)) {
//            $image->readImage(\Wink\Path::BACKGROUND_PATH . $background);
            $bgImg = new \Imagick(\Wink\Path::BACKGROUND_PATH . $background);
            $image->compositeImageGravity($bgImg, \Imagick::COMPOSITE_DEFAULT, \Imagick::GRAVITY_CENTER);
        }
    }

    /**
     * @param string $option
     * @param int $default
     * @return int
     */
    protected function getAlignFromConfig ($option, $default) {
        $selected = $this->getConfigOption($option);

        if ($selected == 'left') {
            return \Imagick::ALIGN_LEFT;
        }
        elseif ($selected == 'center') {
            return \Imagick::ALIGN_CENTER;
        }
        elseif ($selected == 'right') {
            return \Imagick::ALIGN_RIGHT;
        }

        return $default;
    }

    /**
     * @param $option
     * @return array
     */
    protected function getAlignFromOption ($option) {
        $align = $this->getAlignFromConfig($option, \Imagick::ALIGN_CENTER);

        $x = self::SIDE_MARGIN_HEADER;

        if ($align == \Imagick::ALIGN_LEFT) {
            $x = self::SIDE_MARGIN_HEADER;
        }
        elseif ($align == \Imagick::ALIGN_CENTER) {
            $x = $this->getWidth() / 2;
        }
        elseif ($align == \Imagick::ALIGN_RIGHT) {
            $x = $this->getWidth() - self::SIDE_MARGIN_HEADER;
        }

        return array($align, $x);
    }

    /**
     * @param \Imagick $image
     * @param string $color
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     */
    protected function drawRect ($image, $color, $x1, $y1, $x2, $y2) {
        $draw = new \ImagickDraw();
        $draw->setFillColor(new \ImagickPixel($color));
        $draw->rectangle($x1, $y1, $x2, $y2);
        $image->drawImage($draw);
    }

}