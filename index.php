<?php

require 'vendor/autoload.php';

require 'src/BlackBox.php';
require 'src/blackbox/bbConfig.php';
require 'src/blackbox/bbAuth.php';
require 'src/SetupVars.php';
require 'src/blackbox/bbExec.php';
require 'src/blackbox/bbDatabase.php';


/*

require 'src/blackbox/bbCommand.php';

require 'src/blackbox/bbState.php';

require 'src/blackbox/bbGravityDb.php';
require 'src/blackbox/bbPiholeDb.php';

require 'src/blackbox/userObj.php';

require 'src/FTL.php';
require 'src/Gravity.php';
require 'src/BbPiholeApi.php';
require 'src/BbPiholeApiDb.php';
require 'src/SQLite3.php';
require 'src/PiholeNativeAuth.php';
*/


// Create Slim app
$app = new \Slim\App();




// Fetch DI Container
$container = $app->getContainer();

$container['BlackBox'] = function($c){
    return new BlackBox();
};


// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('osbox/templates', [
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

    //return $response->withJson("whooo");
    return $this->view->render( $response, $this->BlackBox->showpage( "default/dashboard.html", $request ), $this->BlackBox->UiParameters(["PAGE"=>".page_dashboard"]));
})->setName('page_dashboard');


$app->run();
