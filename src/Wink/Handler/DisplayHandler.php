<?php
namespace Wink\Handler;

use Wink\DB\Dao;
use Wink\DB\PDO;
use Wink\Handler\Abstracts\AbstractImageHandler;
use Wink\Layout\Interfaces\LayoutScheduleInterface;
use Wink\Layout\Interfaces\LayoutTimelineInterface;
use Wink\Service\WinkEncoder;

class DisplayHandler extends AbstractImageHandler {

    public function display () {
        $config = $this->config;
        $response = $this->response;

        $device = $this->handleDevice();

        if (! $device) {
            $body = $response->getBody();
            $body->write('Unable to create device record.');

            return $response
                ->withBody($body)
                ->withStatus(500);
        }

        $sourcePlugin = $device['source_plugin'];
        $sourceOptions = $device['source_options'];
        $layoutType = $device['layout_type'];
        $layoutOptions = $device['layout_options'];
        $width = $device['width'];
        $height = $device['height'];

        if ($sourcePlugin || $layoutType) {
            $sourcePlugin = $this->createSourcePlugin($config, $sourcePlugin, $sourceOptions);
            $agr = $this->processSchedule($config, $sourcePlugin->getSchedule());
            $resourceDetail = $sourcePlugin->getResource();
            $layout = $this->createLayout($config, $layoutType, $width, $height, $layoutOptions);

            if ($layout instanceof LayoutScheduleInterface) {
                $layout->setResourceAndSchedule($resourceDetail, $agr->getFinalSchedule());

                if ($layout instanceof LayoutTimelineInterface) {
                    $layout->setTimeline($agr->getTimeline());
                }
            }
        }
        else {
            $layout = $this->createLayout($config, 'setup', $width, $height);
        }

        $image = $layout->render();

        $image->quantizeImage(2, \Imagick::COLORSPACE_GRAY, false, $this->config['dither'], false);
        $image->rotateImage(new \ImagickPixel('white'), 180);

        $encoder = new WinkEncoder();
        $imgByteStream = $encoder->fromImagick($image);
        $packed = $encoder->packImage($imgByteStream, $device['mac_address'], $this->config['image_key'], $this->config['refresh_seconds']);

        $body = $response->getBody();
        $body->write($packed);

        return $response
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . time() . '.wink"')
            ->withHeader('Expires', 0)
            ->withHeader('Cache-Control', ['no-store, no-cache, must-revalidate, max-age=0','post-check=0, pre-check=0'])
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Length', strlen($packed))
            ->withBody($body)
            ->withStatus(200);
    }

    /**
     * @return array|false
     */
    protected function handleDevice () {
        $request = $this->request;

        $macAddress = $request->getQueryParam('mac_address');
        $firmware = $request->getQueryParam('firmware');
        $width = $request->getQueryParam('width');
        $height = $request->getQueryParam('height');
        $voltage = $request->getQueryParam('voltage');
        $errorCode = $request->getQueryParam('error');
        $remoteAddress = $request->getAttribute('ip_address');

        $winkPdo = new PDO($this->config['db']['dsn'], $this->config['db']['user'], $this->config['db']['pass']);
        $winkDao = new Dao($winkPdo);

        $device = $winkDao->getDevice($macAddress);

        if (! $device) {
            $device = $winkDao->createDevice($macAddress, $width, $height);
        }

        $nextCheckInTs = strtotime('+30 min');
        $nextCheckIn = date('Y-m-d H:i:s', $nextCheckInTs);

        $winkDao->insertCheckIn($macAddress, $nextCheckIn, $voltage, $firmware, $errorCode, $remoteAddress);

        return $device;
    }
}