<?php
declare(strict_types = 1);
namespace Zvax;
use Zvax\Stepping\Engine;
use Zvax\Stepping\Step;
require __DIR__ . '/../vendor/autoload.php';
$injector = getInjector();
$engine = new Engine($injector, new Step('Zvax\routeRequest'));
$engine->execute();
