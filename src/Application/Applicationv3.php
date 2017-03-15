<?php

/**
 * Copyright (c) 2010-2016 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Application;

use Eureka\Component\Container\Container;
use Eureka\Component\Http\Message as HttpMessage;
use Eureka\Component\Http\Middleware as HttpMiddleware;

/**
 * Application class
 *
 * @author Romain Cottard
 */
class Applicationv3
{
    /**
     * @var HttpMiddleware\MiddlewareInterface[] $middleware
     */
    protected $middleware = [];

    /**
     * Run application based on the route.
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function run()
    {
        $this->loadConfigPackages();
        $this->loadMiddleware();

        //~ Default response
        $response = new HttpMessage\Response();

        //~ Get response
        $stack    = new HttpMiddleware\Stack($response, $this->middleware);
        $response = $stack->process(HttpMessage\ServerRequest::createFromGlobal());

        //~ Send response
        (new HttpMessage\ResponseSender($response))->send();
    }

    /**
     * Load configs from packages.
     *
     * @return void
     */
    private function loadConfigPackages()
    {
        $config = Container::getInstance()->get('config');
        $list   = $config->get('global.package');

        if (empty($list) || !is_array($list)) {
            return;
        }

        foreach ($list as $name => $data) {
            if (!isset($data['config'])) {
                continue;
            }

            $config->loadYamlFromDirectory($data['config']);
        }
    }

    /**
     * Load middlewares
     *
     * @return void
     */
    private function loadMiddleware()
    {
        $this->middleware = [];

        $config = Container::getInstance()->get('config');
        $list   = $config->get('global.middleware');

        foreach ($list as $name => $conf) {
            $services = $conf['services'];
            foreach ($services as $service) {
                // todo
            }
            $this->middleware[] = new $conf['class']($config);
        }
    }
}
