<?php
namespace Wink\ImageUtil;

class Annotation {

    const DEFAULT_FONT = 'core/standard/Lato-Regular.ttf';

    public function drawTextWithBg ($image, $x, $y, $text, $strokeColor, $strokeSize, $useBg, array $options = array()) {
        $bgOptions = $options
            + [
                'stroke_color' => $strokeColor,
                'stroke_size' => $strokeSize,
                'use_bg' => $useBg
            ];

        $this->drawTextWithOptions($image, $x, $y, $text, $bgOptions);

        return $this->drawTextWithOptions($image, $x, $y, $text, $options);
    }

    /**
     * @param \Imagick $image
     * @param int $x
     * @param int $y
     * @param string $text
     * @param array $options
     * @return float|int
     */
    public function drawTextWithOptions ($image, $x, $y, $text, array $options = array()) {
        $maxWidth = isset($options['max_width']) ? $options['max_width'] : null;
        $maxHeight = isset($options['max_height']) ? $options['max_height'] : null;
        $alignTop = isset($options['align_top']) ? $options['align_top'] : false;
        $color = isset($options['color']) ? $options['color'] : 'black';
        $font = isset($options['font']) ? $options['font'] : self::DEFAULT_FONT;
        $size = isset($options['size']) ? $options['size'] : 36;
        $align = isset($options['align']) ? $options['align'] : \Imagick::ALIGN_LEFT;
        $strokeColor = isset($options['stroke_color']) ? $options['stroke_color'] : null;
        $strokeSize = isset($options['stroke_size']) ? $options['stroke_size'] : null;
        $useBg = isset($options['use_bg']) ? $options['use_bg'] : null;
        $dry = isset($options['dry']) ? $options['dry'] : false;

        if (strpos($font, 'assets') === false) {
            $font = \Wink\Path::FONT_PATH . $font;
        }

        $draw = new \ImagickDraw();

        if ($font !== null) {
            $draw->setFont($font);
        }

        $draw->setTextAlignment($align);
        $draw->setTextAntialias(true);
        $draw->setFillColor(new \ImagickPixel($color));
        $draw->setTextInterLineSpacing(0);
        $draw->setFontSize($size);

        if ($strokeColor !== null && ! $useBg) {
            $draw->setStrokeColor(new \ImagickPixel($strokeColor));
            $draw->setStrokeWidth($strokeSize);
        }

        if ($maxWidth != null) {
            list($lines, $lineHeight) = $this->wordWrapAnnotation($image, $draw, $text, $maxWidth);
        }
        else {
            $lines = [$text];
            $metrics = $image->queryFontMetrics($draw, $text);
            $lineHeight = $metrics['textHeight'];
        }

        $height = $lineHeight * count($lines);

        $yPos = ($alignTop ? $y + $lineHeight : $y);

        for ($i = 0; $i < count($lines); $i++) {
            $curLine = $lines[$i];

            if (! $useBg) {
                if (! $dry) {
                    $image->annotateImage($draw, $x, $yPos + $i * $lineHeight, 0, $curLine);
                }
            }
            else {
                $boundingBox = ['y1' => 0, 'y2' => 0];

                foreach (str_split($curLine) as $char) {
                    $metrics = $image->queryFontMetrics($draw, $char);

                    $boundingBox['y1'] = min($boundingBox['y1'], $metrics['boundingBox']['y1']);
                    $boundingBox['y2'] = max($boundingBox['y2'], $metrics['boundingBox']['y2']);
                }

                $boundingBox['y1'] = floor($boundingBox['y1']);
                $boundingBox['y2'] = ceil($boundingBox['y2']);

                $metrics = $image->queryFontMetrics($draw, $curLine);
                $lineWidth = $metrics['textWidth'];

                $y1 = $yPos + $i * $lineHeight - $boundingBox['y2'];
                $y2 = $yPos + $i * $lineHeight - $boundingBox['y1'];

                if ($align == \Imagick::ALIGN_LEFT) {
                    $x1 = $x;
                    $x2 = $x + $lineWidth;
                }
                elseif ($align == \Imagick::ALIGN_RIGHT) {
                    $x1 = $x - $lineWidth;
                    $x2 = $x;
                }
                elseif ($align == \Imagick::ALIGN_CENTER) {
                    $x1 = $x - $lineWidth / 2;
                    $x2 = $x + $lineWidth / 2;
                }
                else {
                    $x1 = $x;
                    $x2 = $x + $lineWidth;
                }

                $x1 -= $strokeSize;
                $x2 += $strokeSize;
                $y1 -= $strokeSize;
                $y2 += $strokeSize;

                if (! $dry) {
                    $drawRect = new \ImagickDraw();
                    $drawRect->setFillColor(new \ImagickPixel($strokeColor));
                    $drawRect->rectangle($x1, $y1, $x2, $y2);
                    $image->drawImage($drawRect);
                }
            }
        }

        return $height;
    }

    /**
     * @param \Imagick $image
     * @param int $x
     * @param int $y
     * @param $maxWidth
     * @param $maxHeight
     * @param bool $alignTop
     * @param string $text
     * @param string $color
     * @param string $font
     * @param int $size
     * @param int $align
     * @param string $strokeColor
     * @param int $strokeWidth
     * @param bool $useBg
     * @return int textHeight
     */
    public function drawText ($image, $x, $y, $maxWidth, $maxHeight, $alignTop,
                              $text, $color = 'black', $font = null, $size = 36,
                              $align = \Imagick::ALIGN_LEFT,
                              $strokeColor = null, $strokeWidth = null, $useBg = false) {

        return $this->drawTextWithOptions($image, $x, $y, $text, [
            'max_width' => $maxWidth,
            'max_height' => $maxHeight,
            'align_top' => $alignTop,
            'color' => $color,
            'font' => $font,
            'size' => $size,
            'align' => $align,
            'stroke_color' => $strokeColor,
            'storke_size' => $strokeWidth,
            'use_bg' => $useBg,
        ]);
    }

    /**
     * @param \Imagick $image the Imagick Image Object
     * @param \ImagickDraw $draw the ImagickDraw Object
     * @param $text
     * @param int $maxWidth the maximum width in pixels for your wrapped "virtual" text box
     * @return array of lines and line heights
     */
    function wordWrapAnnotation ($image, $draw, $text, $maxWidth) {
        $text = trim($text);

        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $lines = array();
        $i = 0;
        $lineHeight = 0;

        while (count($words) > 0) {
            $metrics = $image->queryFontMetrics($draw, implode(' ', array_slice($words, 0, ++$i)));
            $lineHeight = max($metrics['textHeight'], $lineHeight);

            // check if we have found the word that exceeds the line width
            if ($metrics['textWidth'] > $maxWidth || count($words) < $i) {
                // handle case where a single word is longer than the allowed line width (just add this as a word on its own line?)
                if ($i == 1) {
                    $i++;
                }

                $lines[] = implode(' ', array_slice($words, 0, --$i));
                $words = array_slice($words, $i);
                $i = 0;
            }
        }

        return array($lines, $lineHeight);
    }

    public function ellipse ($text, $maxLength) {
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3);
            $text = trim($text);
            $text .= '...';
        }

        return $text;
    }
}