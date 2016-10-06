<?php

/**
 * Copyright (c) 2010-2016 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Application;

use Eureka\Component\Routing\RouteInterface;

/**
 * Application interface
 *
 * @author Romain Cottard
 */
interface ApplicationInterface
{
    /**
     * ApplicationInterface constructor.
     *
     * @param RouteInterface $route
     */
    public function __construct(RouteInterface $route);

    /**
     * Run Application
     *
     * @return void
     */
    public function run();
}