<?php
namespace Wink\Handler\Abstracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHandler {
    protected $config;

    /** @var \Psr\Http\Message\ServerRequestInterface|\Slim\Http\Request  */
    protected $request;

    /** @var \Psr\Http\Message\ResponseInterface|\Slim\Http\Response */
    protected $response;

    /** @var \Slim\App */
    protected $app;

    /**
     * AbstractHandler constructor.
     *
     * @param array $config
     * @param \Slim\App $app
     * @param \Psr\Http\Message\ServerRequestInterface|\Slim\Http\Request $request
     * @param \Psr\Http\Message\ResponseInterface|\Slim\Http\Response $response
     */
    public function __construct ($config, $app, ServerRequestInterface $request,  ResponseInterface $response) {
        $this->config = $config;
        $this->app = $app;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return \Slim\Views\PhpRenderer
     */
    protected function getView () {
        return $this->app->getContainer()->get('view');
    }

    protected function withJsonResponse($data, $status = 200) {
        return $this->response->withJson($data, $status, JSON_NUMERIC_CHECK);
    }

}