<?php
/**
 * Created by PhpStorm.
 * User: balint
 * Date: 2015. 12. 05.
 * Time: 19:43
 */

include_once('../vendor/autoload.php');


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

date_default_timezone_set('Europe/Budapest');

$router = new League\Route\RouteCollection;
$loader = new Twig_Loader_Filesystem('../src/views');
$twig = new Twig_Environment($loader, array(
    'cache' => '../var/cache',
));
$twig->clearCacheFiles();

$router->addRoute('GET', '/form', function (Request $request, Response $response) use($twig) {
    // do something clever
    $response = new Response($twig->render('form.twig.html', []));
    return $response;
});

$dispatcher = $router->getDispatcher();
$request = Request::createFromGlobals();

$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
$response->send();
