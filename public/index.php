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
use Evista\Perform\Service;


date_default_timezone_set('Europe/Budapest');

$router = new League\Route\RouteCollection;
$loader = new Twig_Loader_Filesystem('../src/views');
$crawler = new Crawler();
// Initialize form transpilation service (dependency injection friendly interface)
$formService = new Service($crawler);

$twig = new Twig_Environment(
    $loader, array(
    'cache' => '../var/cache',
)
);
$twig->clearCacheFiles();

$router->addRoute(
    'GET',
    '/form',
    function (Request $request, Response $response) use ($twig) {
        // do something clever
        $response = new Response($twig->render('form.twig.html', []));

        return $response;
    }
);

$router->addRoute(
    'POST',
    '/displayform',
    function (Request $request, Response $response) use ($twig, $crawler, $formService) {

        $formMarkup = $request->request->get('serform');
        $form = $formService->transpileForm($formMarkup);

        // We just dump the object to enable
        $response = new JsonResponse(['dump' => (var_export($form, true))]);

        return $response;
    }
);

// Use form data
$router->addRoute(
    'POST',
    '/loginform',
    function (Request $request, Response $response) use ($twig, $crawler, $formService) {

        $formMarkup = $request->request->get('serform');
        $form = $formService->transpileForm($formMarkup);

        // Get fields:
        $fields = $form->getFields();

        // Use email
        $emailField = $form->getField('email');

        // Get attributes, eg. placeholder:
        $placeholder = $emailField->getAttribute('placeholder');

        // Get selected option:
        $selectField = $form->getField('test-select');
        $selected = $selectField->getValue();

        // Get defaultly selected option (that was set selected in markup)
        $defaultSelected = $selectField->getDefaultSelectedOption();

        // Check validity
        if (!$form->isValid()) {
            // All errors can be spotted in the fields
            foreach ($form->getFields() as $field) {
                if (!$field->isValid()) {
                    $validationErrors[] = $field->getErrors();
                }
            }
        }

        // We just dump the object to enable
        $response = new JsonResponse(['dump' => (var_export($form, true))]);

        return $response;
    }
);

$dispatcher = $router->getDispatcher();
$request = Request::createFromGlobals();

$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
$response->send();
