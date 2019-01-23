<?php
namespace Wink\Layout\BuiltIn;

class Reservable extends AbstractBuiltIn {

    public function render () {
        $image = $this->createBaseImage();

        $this->drawBackground($image);
        $this->drawHeader($image);

        if ($this->getConfigOption('show_timeline') == 'yes') {
            $this->drawTimeline($image);
        }

        $this->drawContent($image);
        $this->drawFooter($image);

        return $image;
    }

    /**
     * @param \Imagick $image
     */
    public function drawTimeline ($image) {
        $timeline = $this->getTimeline();

        $currentTime = \Wink\Service\Time::getCurrentTimestamp();

        $y = $this->getHeight() - 55;

        $cnt = count($timeline);
        $imageWidth = $this->getWidth();
        $size = ceil($cnt / $imageWidth);
        $chunks = array_chunk($timeline, $size, true);

        $timelineWidth = count($chunks);

        $x = floor($imageWidth / 2) - floor($timelineWidth / 2);

        $black = new \ImagickPixel('black');
        $white = new \ImagickPixel('white');

        $draw = new \ImagickDraw();
        $draw->setFillColor($black);
        $draw->rectangle($x - 2, $y - 2, $x + $timelineWidth, $y + 12);
        $image->drawImage($draw);

        $draw = new \ImagickDraw();
        $draw->setFillColor($white);
        $draw->rectangle($x , $y, $x + $timelineWidth - 2, $y + 10);
        $image->drawImage($draw);

        $prevHr = null;
        $prevReserved = false;

        foreach ($chunks as $chunk) {
            $time = array_keys($chunk)[0];
            $hour = date('ga', round($time / 300) * 300);

            $isReserved = array_sum($chunk) > 0;
            $isPast = $time < $currentTime;

            if ($isPast || $isReserved || $prevReserved != $isReserved) {
                $draw = new \ImagickDraw();
                $draw->setStrokeColor($black);
                $draw->setFillColor($white);

                if (! ($isPast || $hour != $prevHr || $prevReserved != $isReserved)) {
                    $draw->setStrokeWidth(0.5);
                    $draw->setStrokeDashArray([1, 3]);
                    $draw->setStrokeDashOffset(($hour + $x) % 4);
                    $draw->setStrokeAntialias(false);
                }

                $draw->line($x, $y, $x, $y + 10);
                $image->drawImage($draw);
            }

            if ($prevHr != $hour) {
                $this->annotation->drawText($image,
                    $x, $y + 8, null, null, true,
                    $hour, 'black',
                    self::FONT_TINY, self::FONT_TINY_SIZE, \Imagick::ALIGN_CENTER,
                    'white', 1, true
                );

                $this->annotation->drawText($image,
                    $x, $y + 8, null, null, true,
                    $hour, 'black',
                    self::FONT_TINY, self::FONT_TINY_SIZE, \Imagick::ALIGN_CENTER
                );
            }

            $prevHr = $hour;
            $prevReserved = $isReserved;

            $x += 1;
        }
    }

    /**
     * @param \Imagick $image
     */
    public function drawContent ($image) {
        $spacing = 10;
        $startY = 75;
        $y = $startY;
        $maxHeight = $this->getHeight() - 74 - 55;

        $schedule = $this->getSchedule();
        $itemCnt = count($schedule);

        list($align, $x) = $this->getAlignFromOption('content_align');

        for ($i = 0; $i < $itemCnt && $y < $maxHeight; $i++) {
            $item = $schedule[$i];
            $smaller = ($i != 0);

            $subtitle = $this->calculateSubtitle($item);

            $title = $this->annotation->ellipse($item['title'], 70);

            $height = $this->drawItem($image, $x, $y, $title, $subtitle, $align, $smaller);

            $y += $height + $spacing;
        }

        if ($i < $itemCnt) {
            $this->drawItem($image, $x, $y, '', ($itemCnt - $i) . ' more...', $align, true);
        }
    }

    /**
     * @param \Imagick $image
     * @param int $xStart
     * @param int $yStart
     * @param string $title
     * @param string $subtitle
     * @param int $align
     * @param bool $smaller
     * @return int
     */
    public function drawItem ($image, $xStart, $yStart, $title, $subtitle, $align, $smaller = false) {
        $fontT = $smaller ? self::FONT_SMALL_TITLE : self::FONT_LARGE_TITLE;
        $sizeT = $smaller ? self::FONT_SMALL_TITLE_SIZE : self::FONT_LARGE_TITLE_SIZE;

        $fontS = $smaller ? self::FONT_SMALL_SUBTITLE : self::FONT_LARGE_SUBTITLE;
        $sizeS = $smaller ? self::FONT_SMALL_SUBTITLE_SIZE : self::FONT_LARGE_SUBTITLE_SIZE;

        $maxWidth = $this->getWidth() - self::SIDE_MARGIN_CONTENT * 2;

        $bgType = $this->getConfigOption('content_text_bg_type');

        if (in_array($bgType, ['stroke', 'highlight'])) {
            $useBox = ($bgType == 'highlight');
            $height = $this->annotation->drawText($image, $xStart, $yStart, $maxWidth, null, true, $title, 'white', $fontT, $sizeT, $align, 'white', self::STROKE_WIDTH, $useBox);
            $this->annotation->drawText($image, $xStart, $yStart + $height, $maxWidth, null, true, $subtitle, 'white', $fontS, $sizeS, $align, 'white', self::STROKE_WIDTH, $useBox);
        }

        $height = $this->annotation->drawText($image, $xStart, $yStart, $maxWidth, null, true, $title, 'black', $fontT, $sizeT, $align);
        $height += $this->annotation->drawText($image, $xStart, $yStart + $height, $maxWidth, null, true, $subtitle, 'black', $fontS, $sizeS, $align);

        return $height;
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

        $options['show_timeline'] = [
            'name' => 'Show Timeline',
            'default' => 'yes',
            'type' => 'select',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No'
            ]
        ];

        return $options;
    }

    /**
     * Get the readable name for this layout.
     *
     * @return string
     */
    public static function getName () {
        return 'Reservable';
    }

}