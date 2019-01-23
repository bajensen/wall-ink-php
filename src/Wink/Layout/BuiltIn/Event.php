<?php
namespace Wink\Layout\BuiltIn;

class Event extends AbstractBuiltIn {
    const BACKGROUND_PATH_BLOB = \Wink\Path::BACKGROUND_PATH . '*/*.png';

    public function render () {
        $image = $this->createBaseImage();

        $this->drawBackground($image);
        $this->drawCurrentEvent($image);
        $this->drawHeader($image);
        $this->drawFooter($image);

        return $image;
    }

    /**
     * @param \Imagick $image
     */
    protected function drawCurrentEvent ($image) {
        $schedule = $this->getSchedule();

        $currentEvent = null;

        if (count($schedule) > 0 && $schedule[0]) {
            $currentEvent = $schedule[0];
        }

        list($align, $x) = $this->getAlignFromOption('content_align');

        $bgType = $this->getConfigOption('content_text_bg_type');
        $hasBg = in_array($bgType, ['stroke', 'highlight']);
        $useBox = ($bgType == 'highlight');

        $yInitial = $this->getHeight() / 2 - 25;

        if ($currentEvent && $currentEvent['reserved']) {
            $title = $currentEvent['title'];
            $subtitle = $this->calculateSubtitle($currentEvent);

            $options = [
                'max_width' => $this->getWidth(),
                'align' => $align,
                'align_top' => true
            ];

            $titleOptions = $options + [
                'font' => self::FONT_LARGE_TITLE,
                'size' => self::FONT_LARGE_TITLE_SIZE
            ];

            $subtitleOptions = $options + [
                'font' => self::FONT_LARGE_SUBTITLE,
                'size' => self::FONT_LARGE_SUBTITLE_SIZE
            ];

            if ($hasBg) {
                $y = $yInitial;
                $y += $this->annotation->drawTextWithBg($image, $x, $yInitial, $title, 'white', 5, $useBox, $titleOptions);
                $this->annotation->drawTextWithBg($image, $x, $y, $subtitle, 'white', 5, $useBox, $subtitleOptions);
            }
            else {
                $y = $yInitial;
                $y += $this->annotation->drawTextWithOptions($image, $x, $yInitial, $title, $titleOptions);
                $this->annotation->drawTextWithOptions($image, $x, $y, $subtitle, $subtitleOptions);
            }
        }
        else {
            $noEventText = 'No Current Event';

            $options = [
                'max_width' => $this->getWidth(),
                'font' => self::FONT_LARGE_TITLE,
                'size' => self::FONT_LARGE_TITLE_SIZE,
                'align' => $align,
            ];

            $textHeight = $this->annotation->drawTextWithOptions($image,
                $x, $yInitial,
                $noEventText,
                $options + ['dry' => true]
            );

            $yInitial = $this->getHeight() / 2 + $textHeight / 2;

            if ($hasBg) {
                $this->annotation->drawTextWithBg($image,
                    $x, $yInitial,
                    $noEventText,
                    'white',
                    5,
                    $useBox,
                    $options
                );
            }
            else {
                $this->annotation->drawTextWithOptions($image,
                    $x, $yInitial,
                    $noEventText,
                    $options
                );
            }
        }

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
            $bgImg = new \Imagick(\Wink\Path::BACKGROUND_PATH . $backgrounds[$idx]);
            $image->compositeImageGravity($bgImg, \Imagick::COMPOSITE_DEFAULT, \Imagick::GRAVITY_CENTER);
        }
        elseif ($background != 'none' && in_array($background, $backgrounds)) {
            $bgImg = new \Imagick(\Wink\Path::BACKGROUND_PATH . $background);
            $image->compositeImageGravity($bgImg, \Imagick::COMPOSITE_DEFAULT, \Imagick::GRAVITY_CENTER);
        }
    }

    /**
     * @return array
     *  [
     *      'title_align' => [
     *          'default' => 'center',
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
        $options = self::getBuiltInOptions();

        return $options;
    }

    /**
     * Get the readable name for this layout.
     *
     * @return string
     */
    public static function getName () {
        return 'Event';
    }

    /**
     * @return array|false
     */
    protected static function getBackgroundOptions () {
        $backgroundOptions = glob(self::BACKGROUND_PATH_BLOB);

        $backgroundOptions = array_map(function ($e) {
            return str_replace(\Wink\Path::BACKGROUND_PATH, '', $e);
        }, $backgroundOptions);

        return $backgroundOptions;
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