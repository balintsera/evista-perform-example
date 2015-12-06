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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DomCrawler\Crawler;
use Evista\Perform\FormMarkupTranspiler;


date_default_timezone_set('Europe/Budapest');

$router = new League\Route\RouteCollection;
$loader = new Twig_Loader_Filesystem('../src/views');
$crawler = new Crawler();

$twig = new Twig_Environment($loader, array(
    'cache' => '../var/cache',
));
$twig->clearCacheFiles();

$router->addRoute('GET', '/form', function (Request $request, Response $response) use($twig) {
    // do something clever
    $response = new Response($twig->render('form.twig.html', []));
    return $response;
});

$router->addRoute('POST', '/loginform', function (Request $request, Response $response) use($twig, $crawler) {
    $formMarkup = $request->request->get('serform');

    $transpiler = new FormMarkupTranspiler($crawler, $formMarkup);
    $fields = $transpiler->findFields();

    $response = new JsonResponse(['dump'=>$fields]);
    return $response;
});

$dispatcher = $router->getDispatcher();
$request = Request::createFromGlobals();

$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
$response->send();
