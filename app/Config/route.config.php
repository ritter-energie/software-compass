<?php

declare(strict_types=1);

use Tempest\Router\RouteConfig;

/*
 * In the web application we want middleware-generated auth responses
 * (redirects/forbidden) to be sent to the browser rather than being converted
 * into debug exceptions. Tests can still opt into exception throwing
 * independently through Tempest's HTTP tester.
 */
return new RouteConfig(
    throwHttpExceptions: false,
);

