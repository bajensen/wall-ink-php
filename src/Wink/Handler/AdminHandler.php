<?php
namespace Wink\Handler;

use Wink\Handler\Abstracts\AbstractHandler;
use Wink\Service\Layout;
use Wink\Service\Source;

class AdminHandler extends AbstractHandler {
    /** @var \Wink\DB\Dao */
    protected $dao;

    public function __construct (array $config, \Slim\App $app, $request, $response) {
        parent::__construct($config, $app, $request, $response);

        $pdo = new \Wink\DB\PDO($this->config['db']['dsn'], $this->config['db']['user'], $this->config['db']['pass']);
        $this->dao = new \Wink\DB\Dao($pdo);
    }

    public function admin () {
        $view = $this->getView();

        return $view->render($this->response, 'admin.phtml');
    }

    public function devicesJson () {
        $devices = $this->dao->getDevices();
        return $this->withJsonResponse($devices);
    }

    public function deviceHistoryJson ($deviceId) {
        $device = $this->dao->getDevice($deviceId);
        $deviceHistory = $this->dao->getDeviceHistory($deviceId);

        return $this->withJsonResponse([
            'device' => $device,
            'history' => $deviceHistory
        ]);
    }

    public function deviceJson ($deviceId) {
        $device = $this->dao->getDevice($deviceId);
        return $this->withJsonResponse($device);
    }

    public function deleteDevice ($deviceId) {
        $device = $this->dao->deleteDevice($deviceId);
        return $this->withJsonResponse($device);
    }

    public function saveDevice ($deviceId) {
        $body = $this->request->getBody();
        $deviceJson = $body->getContents();
        $device = json_decode($deviceJson, true);
        $device = $this->dao->updateDevice($deviceId, $device);

        return $this->withJsonResponse($device);
    }

    public function sourcesJson () {
        $pluginService = new Source($this->config);
        $plugins = $pluginService->getSources();
        return $this->withJsonResponse($plugins);
    }

    public function layoutsJson () {
        $layoutService = new Layout($this->config);
        $layouts = $layoutService->getLayouts();
        return $this->withJsonResponse($layouts);
    }

    public function pluginOptionsJson () {
        $pluginService = new Source($this->config);
        $resources = $this->getAllPluginOptions($pluginService);
        return $this->withJsonResponse($resources);
    }

    public function completeOptionsJson () {
        $pluginService = new Source($this->config);
        $sources = $pluginService->getSources();

        $layoutService = new Layout($this->config);
        $layouts = $layoutService->getLayouts();

        $layoutOptions = $this->getAllLayoutOptions($layoutService);
        $pluginOptions = $this->getAllPluginOptions($pluginService);

        return $this->withJsonResponse([
            'sources' => $sources,
            'source_options' => $pluginOptions,
            'layouts' => $layouts,
            'layout_options' => $layoutOptions
        ]);
    }

    /**
     * @param Source|null $pluginService
     * @return array
     */
    protected function getAllPluginOptions ($pluginService) {
        $pluginOptions = [];

        if (! $pluginService) {
            $pluginService = new Source($this->config);
        }

        foreach ($pluginService->getSources() as $plugin => $pluginConfig) {
            $pluginOptions[$plugin] = $pluginService->getOptions($plugin);
        }

        return $pluginOptions;
    }

    public function layoutOptionsJson () {
        $layoutOptions = $this->getAllLayoutOptions();

        return $this->withJsonResponse($layoutOptions);
    }

    /**
     * @param Layout|null $layoutService
     * @return array
     */
    protected function getAllLayoutOptions ($layoutService = null) {
        $layoutOptions = [];

        if (! $layoutService) {
            $layoutService = new Layout($this->config);
        }

        foreach ($layoutService->getLayouts() as $layout => $layoutProps) {
            $layoutOptions[$layout] = $layoutService->getOptions($layout);
        }

        return $layoutOptions;
    }
}