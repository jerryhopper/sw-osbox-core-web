<?php

require 'vendor/autoload.php';


// Create Slim app
$app = new \Slim\App();




// Fetch DI Container
$container = $app->getContainer();




// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false /*'path/to/cache'*/
    ]);

    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};


/**
 *
 *
 *
 *
 *
 **/
// Define home route
$app->get('/', function ($request, $response, $args) {


    #[authenticated] => 1
    #[user][userId] => 6ab331fb-e654-4de3-aa29-b403fcd557e1
    #[user][userEmail] => hopper.jerry@gmail.com
    #print_r($request->getAttribute("AUTH"));
    #die();

    return $response->withJson("whooo");
    //return $this->view->render( $response, $this->BlackBox->showpage( "default/dashboard.html", $request ), $this->BlackBox->UiParameters(["PAGE"=>".page_dashboard"]));
})->setName('page_dashboard');


$app->run();
