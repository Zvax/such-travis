<?php
declare(strict_types = 1);
namespace Zvax;
use Auryn\Injector;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Http\Request;
use Http\Response;
use Zvax\Stepping\InjectionParams;
use Zvax\Stepping\Step;
function getRoutes(): \Generator
{
    foreach (require __DIR__ . '/../lib/Berilium/src/routes.php' as $route) {
        yield $route;
    }
}

function getDispatcher(): Dispatcher
{
    $routesCallback = function (RouteCollector $collector) {
        foreach (getRoutes() as $route) {
            $collector->addRoute($route[0], $route[1], $route[2]);
        }
    };
    return \FastRoute\simpleDispatcher($routesCallback);
}

function routeRequest(Request $request, Response $response): Step
{
    $dispatcher = getDispatcher();
    $routeInfo = $dispatcher->dispatch($request->getMethod(),$request->getPath());
    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $response->setStatusCode(404);
            $response->setContent('404 - not found');
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $response->setStatusCode(403);
            $response->setContent('403 - not allowed');
            break;
        case Dispatcher::FOUND:
            $response->setStatusCode(200);
            $className = $routeInfo[1][0];
            $method = $routeInfo[1][1];
            $vars = $routeInfo[2];
            return new Step("$className::$method",InjectionParams::fromRouteParams($vars));
    }
    return new step(function() {echo "something went wrong";});
}

function getInjector(): Injector
{
    $injector = new Injector;
    $aliases = [
        'Http\Request' => 'Http\HttpRequest',
        'Http\Response' => 'Http\HttpResponse',
    ];
    $definitions = [
        'Http\Request' => [
            ':get' => $_GET,
            ':post' => $_POST,
            ':files' => $_FILES,
            ':cookies' => $_COOKIE,
            ':server' => $_SERVER,
        ],
    ];
    $params = new InjectionParams([], $aliases, $definitions);
    $params->addToInjector($injector);
    return $injector;
}
