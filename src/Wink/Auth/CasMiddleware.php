<?php

namespace Wink\Auth;

class CasMiddleware {
    protected $config;

    public function __construct ($config) {
        $this->config = $config;

        // Enable debugging
//        \phpCAS::setDebug();

        // Enable verbose error messages. Disable in production!
//        \phpCAS::setVerbose(true);

        \phpCAS::client(CAS_VERSION_2_0, $config['host'], $config['port'], $config['context']);

        \phpCAS::setNoCasServerValidation();
    }

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke ($request, $response, $next) {
        $path = $request->getUri()->getPath();

        if (preg_match('#/admin.*#', $path)) {
            \phpCAS::forceAuthentication();

            $user = \phpCAS::getUser();

            if (! in_array($user, $this->config['authz_users'])) {
                $response->getBody()->write('<h1>Not Authorized</h1>');

                return $response->withStatus(401);
            }
        }

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $next($request, $response);

        return $response;
    }
}