<?php

require 'vendor/autoload.php';

require 'src/BlackBox.php';
require 'src/blackbox/bbConfig.php';
require 'src/blackbox/bbAuth.php';
require 'src/SetupVars.php';
require 'src/blackbox/bbExec.php';
require 'src/blackbox/bbDatabase.php';
require 'src/PiholeNativeAuth.php';

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



/**
 *  Callback function.
 */
$app->get('/callback', function ($request, $response, $args) {
    return $response->withJson("whooo");


    $expires=3590;

    $allGetVars = $request->getQueryParams();
    $token = $allGetVars["token"];

    $isAuthenticated = $this->BlackBox->validateToken($token);




    /**
     *  If user is not authenticated, redirect to error page.
     */
    if( ! $isAuthenticated ){
        /**
         *  If user is not authenticated, redirect to error page.
         */
        return $response->withRedirect( '/error');

    }elseif( ! $this->BlackBox->hasOwner()  ){
        /**
         *  If user is authenticated, but the device has no owner.
         */
        //print_r($this->BlackBox->userObject);

        $this->BlackBox->setOwner(  $this->BlackBox->userObject );

        //die();
        // set the cookie
        $setcookies = new Slim\Http\Cookies();
        $setcookies->set('auth', [
            'value' => $token,
            'expires' => $this->BlackBox->getUserinfo('expires'),
            'path' => '/',
            'domain' => 'blackbox.surfwijzer.nl',
            'httponly' => true,
            'hostonly' => false,
            'secure' => true,
            'samesite' => 'lax'
        ]);
        $response = $response->withHeader('Set-Cookie', $setcookies->toHeaders());

        return $response->withRedirect( '/firstrun');

    }else{
        /**
         *  If user is authenticated, and the device has a owner.
         */

        // set the cookie
        $setcookies = new Slim\Http\Cookies();
        $setcookies->set('auth',[
            'value' => $token,
            'expires' => $this->BlackBox->getUserinfo('expires'),
            'path' => '/',
            'domain' => 'blackbox.surfwijzer.nl',
            'httponly' => true,
            'hostonly' => false,
            'secure' => true,
            'samesite' => 'lax'
        ]);
        $setcookies->set('persistentlogin',[
            'value' => $this->BlackBox->setupVars["WEBPASSWORD"],
            'expires' => $this->BlackBox->getUserinfo('expires'),
            'path' => '/',
            'domain' => 'blackbox.surfwijzer.nl',
            'httponly' => true,
            'hostonly' => false,
            'secure' => true,
            'samesite' => 'lax'
        ]);
        $response = $response->withHeader('Set-Cookie', $setcookies->toHeaders());

        return $response->withRedirect( '/');

    }
});



$app->run();
