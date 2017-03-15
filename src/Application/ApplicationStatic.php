<?php

/**
 * Copyright (c) 2010-2016 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Application;

use Eureka\Component\Config\Config;
use Eureka\Component\Container\Container;
use Eureka\Middleware;
use Eureka\Component\Http\Message as HttpMessage;
use Eureka\Component\Http\Middleware as HttpMiddleware;

/**
 * Application class
 *
 * @author Romain Cottard
 */
class ApplicationStatic implements ApplicationInterface
{
    /**
     * @var HttpMiddleware\MiddlewareInterface[] $middleware
     */
    protected $middleware = [];

    /**
     * @var string $type Static content type.
     */
    protected $type = '';

    /**
     * Run application based on the route.
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function run()
    {
        $this->loadMiddleware();

        //~ Default response
        $response = new HttpMessage\Response();
        //$response->getBody()->write('-- static --');

        //~ Get response
        $stack    = new HttpMiddleware\Stack($response, $this->middleware);
        $response = $stack->process(HttpMessage\ServerRequest::createFromGlobal());

        //~ Send response
        (new HttpMessage\ResponseSender($response))->send();
    }

    /**
     * Load middlewares
     *
     * @return void
     */
    private function loadMiddleware()
    {
        $config = Container::getInstance()->get('config');

        $this->middleware[] = new Middleware\ExceptionMiddleware\ExceptionMiddleware($config);

        //~ Request
        $request = HttpMessage\ServerRequest::createFromGlobal();
        $query   = $request->getQueryParams();

        $this->type = $query['type'];

        switch ($this->type) {
            case 'css':
                $this->middleware[] = new Middleware\StaticMiddleware\CssMiddleware($config);
                break;
            case 'js':
                $this->middleware[] = new Middleware\StaticMiddleware\JsMiddleware($config);
                break;
            case 'image':
                $this->middleware[] = new Middleware\StaticMiddleware\ImageMiddleware($config);
                break;
            case 'font':
                $this->middleware[] = new Middleware\StaticMiddleware\FontMiddleware($config);
                break;
        }

    }
}
