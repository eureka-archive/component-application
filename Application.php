<?php

/**
 * Copyright (c) 2010-2016 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Application;

use Eureka\Component\Controller\ControllerInterface;
use Eureka\Component\Debug\Debug;
use Eureka\Component\Routing\RouteInterface;
use Eureka\Component\Response;

/**
 * Application class
 *
 * @author Romain Cottard
 * @version 2.1.0
 */
class Application implements ApplicationInterface
{

    /**
     * Route object.
     *
     * @var RouteInterface $route
     */
    protected $route = null;

    /**
     * Application constructor.
     *
     * @param RouteInterface $route
     * @return Application
     */
    public function __construct(RouteInterface $route)
    {
        $this->route = $route;
    }

    /**
     * Run application based on the route.
     *
     * @return   string
     * @throws   \Exception
     */
    public function run()
    {
        try {

            $controller = $this->route->getControllerName();
            $action     = $this->route->getActionName();

            if (!class_exists($controller)) {
                throw new \DomainException('Controller does not exists! (controller: ' . $controller . ')');
            }

            $controller = new $controller($this->route);

            if (!($controller instanceof ControllerInterface)) {
                throw new \LogicException('Controller does not implement Controller Interface! (controller: ' . get_class($controller) . ')');
            }

            if (!method_exists($controller, $action)) {
                throw new \DomainException('Action controller does not exists! (' . get_class($controller) . '::' . $action);
            }

        } catch (\DomainException $exception) {
            $this->handleException($exception, 404);
            return;
        } catch (\Exception $exception) {
            $this->handleException($exception, 500);
            return;
        }

        try {

            $controller->runBefore();
            $response = $controller->$action();
            $controller->runAfter();

            if (!($response instanceof Response\ResponseInterface)) {
                throw new \Exception('Controller does not return a template object !');
            }

            $response->send();

        } catch (\Exception $exception) {
            $controller->handleException($exception);
        }
    }

    /**
     * Handle exception
     *
     * @param \Exception $exception
     * @param  int $httpCode
     * @return void
     * @throws \Exception
     */
    protected function handleException(\Exception $exception, $httpCode)
    {
        $isAjax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        if ($isAjax) {
            $sEngine = Response\Factory::ENGINE_API;
            $sFormat = Response\Factory::FORMAT_JSON;
            $content = json_encode($exception->getTraceAsString());
        } else {
            $sEngine = Response\Factory::ENGINE_NONE;
            $sFormat = Response\Factory::FORMAT_HTML;
            $content = '<b>Exception[' . $exception->getCode(). ']: ' . $exception->getMessage() . '</b>
            <pre>' . $exception->getTraceAsString() . '</pre>';
        }

        $response = Response\Factory::create($sFormat, $sEngine);
        $response->setHttpCode($httpCode)
            ->setContent($content)
            ->send();
    }
}
