<?php
namespace Wink\Service;

class WinkEncoder {

    /**
     * Take an Imagick image and convert it into the proper image byte stream.
     * @param \Imagick $image
     * @return string
     * @throws \ImagickPixelException
     */
    public function fromImagick (\Imagick $image) {
        $height = $image->getImageHeight();
        $width = $image->getImageWidth();
        $numBytes = $width * $height / 8;

        $bytes = [];

        for ($i = 0; $i < $numBytes; $i++) {
            $bytes[] = 0;
        }

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = $image->getImagePixelColor($x, $y)->getColor();
                $isBlack = ! ($color['r'] || $color['g'] || $color['b']);

                $offset = intdiv($x, 8) + intdiv($width, 8) * $y;

                $bitmask = 0x80 >> ($x % 8);

                if ($isBlack) {
                    $bytes[$offset] = $bytes[$offset] | $bitmask;
                }
                else {
                    $bytes[$offset] = $bytes[$offset] & (($bitmask) ^ 0xff);
                }
            }
        }

        return pack('C' . $numBytes, ...$bytes);
    }

    /**
     * Take the image byte stream and convert them to an Imagick object.
     *
     * @param string $imageBytes
     * @param int $width
     * @param int $height
     * @return \Imagick
     * @throws \ImagickException
     */
    public function toImagick ($imageBytes, $width, $height) {
        $image = new \Imagick();
        $image->newImage($width, $height, new \ImagickPixel('white'));
        $image->setImageFormat("png");

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $offset = intdiv($x, 8) + intdiv($width, 8) * $y;

                $bitmask = 0x80 >> ($x % 8);

                if (ord($imageBytes[$offset]) & $bitmask) {
                    $draw = new \ImagickDraw();
                    $draw->setStrokeWidth(1);
                    $draw->point($x, $y);
                    $image->drawImage($draw);
                }
            }
        }

        return $image;
    }

    /**
     * Take the image byte stream and prepare it for sending.
     *
     * @param string $imageBytes the image byte stream.
     * @param string $macAddress The MAC address of the destination device.
     * @param string $imageKey The Image Key of the destination device.
     * @param int $sleepSeconds Number of seconds for the display to sleep before loading an new image.
     * @return string the final byte stream to be sent as a response to the display.
     */
    public function packImage ($imageBytes, $macAddress, $imageKey, $sleepSeconds) {
        $currentTimeBin = pack('V', time());
        $wakeUpTimeBin = pack('V', time() + $sleepSeconds);

        $timePack = $currentTimeBin . $wakeUpTimeBin;
        $timeSha = sha1($timePack, true);
        $macSha = sha1($macAddress, true);
        $imageKeySha = sha1($imageKey, true);
        $imageSha = sha1($imageBytes, true);

        // Chunk 1 Complete SHA1 (20 bytes)
        // sha1( sha1(chunk2 . chunk3) . sha1(mac_address) . sha1(image_key) )
        $chunk1 = sha1($timeSha . $macSha . $imageKeySha, true);

        // Chunk 2 Current Unix time (4 bytes)
        $chunk2 = $currentTimeBin;

        // Chunk 3 Wake up unix time (4 bytes)
        $chunk3 = $wakeUpTimeBin;

        // Chunk 4 Image SHA1 (20 bytes)
        // sha1( sha1(mac_address) . sha1(raw_image) . sha1(image_key) )
        $chunk4 = sha1($macSha . $imageSha . $imageKeySha, true);

        // Chunk 5 Image & Time SHA1 (20 bytes)
        // WRONG: sha1( sha1(chunk 1 . chunk 2) . sha1(mac_address) . sha1(raw_image) . sha1(image_key) )
        // CORRECT: sha1( sha1(currentTS . wakeupTS) . sha1(mac_address) . sha1(raw_image) . sha1(image_key) )
        $chunk5 = sha1($timeSha . $macSha . $imageSha . $imageKeySha, true);

        $header = $chunk1 . $chunk2 . $chunk3 . $chunk4 . $chunk5;

        return $header . $imageBytes;
    }
}
