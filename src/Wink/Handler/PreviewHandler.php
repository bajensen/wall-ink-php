<?php
namespace Wink\Handler;

use Wink\Handler\Abstracts\AbstractImageHandler;
use Wink\Layout\Interfaces\LayoutScheduleInterface;
use Wink\Layout\Interfaces\LayoutTimelineInterface;

class PreviewHandler extends AbstractImageHandler {
    public function preview () {
        $request = $this->request;
        $response = $this->response;
        $config = $this->config;

        $width = $request->getQueryParam('width');
        $height = $request->getQueryParam('height');

        if (! $width || ! $height) {
            throw new \Exception('Required parameters width and/or height are missing.');
        }

        $sourcePlugin = $request->getQueryParam('source_plugin');
        $sourceOptions = json_decode($request->getQueryParam('source_options'), true);

        $layout = $request->getQueryParam('layout_type');
        $layoutOptions = json_decode($request->getQueryParam('layout_options'), true);

        $layout = $this->createLayout($config, $layout, $width, $height, $layoutOptions);

        if ($layout instanceof LayoutScheduleInterface) {
            $sourcePlugin = $this->createSourcePlugin($config, $sourcePlugin, $sourceOptions);
            $agr = $this->processSchedule($config, $sourcePlugin->getSchedule());
            $resourceDetail = $sourcePlugin->getResource();
            $layout->setResourceAndSchedule($resourceDetail, $agr->getFinalSchedule());

            if ($layout instanceof LayoutTimelineInterface) {
                $layout->setTimeline($agr->getTimeline());
            }
        }

        $image = $layout->render();
        $image->quantizeImage(2, \Imagick::COLORSPACE_GRAY, false, $this->config['dither'], false);

        $blob = $image->getImageBlob();

        $body = $response->getBody();
        $body->write($blob);

        return $response
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Content-Length', strlen($blob))
            ->withBody($body)
            ->withStatus(200);
    }

}