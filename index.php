<?php

require 'vendor/autoload.php';

require 'src/BlackBox.php';
require 'src/blackbox/bbConfig.php';
require 'src/blackbox/bbAuth.php';
#require 'src/SetupVars.php';
require 'src/blackbox/bbExec.php';
require 'src/blackbox/bbDatabase.php';
require 'src/OsboxDatabase/Users.php';


require 'src/PiHole/PiholeNativeAuth.php';

require 'src/PiHole/phDatabase.php';
require 'src/PiHole/Database/Client.php';
require 'src/PiHole/Database/ClientByGroup.php';

require 'src/blackbox/OauthUserObj.php';
require 'src/blackbox/bbInit.php';

/*

require 'src/blackbox/bbCommand.php';

require 'src/blackbox/bbState.php';

require 'src/blackbox/bbGravityDb.php';
require 'src/blackbox/bbPiholeDb.php';



require 'src/FTL.php';
require 'src/Gravity.php';
require 'src/BbPiholeApi.php';
require 'src/BbPiholeApiDb.php';
require 'src/SQLite3.php';

*/

session_start();
// Create Slim app
$app = new \Slim\App();




// Fetch DI Container
$container = $app->getContainer();

$container['cookie'] = function($c){
    $request = $c->get('request');
    return new \Slim\Http\Cookies($request->getCookieParams());
};


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





$app->add(function ($request, $response, $next) {

    //$response->getBody()->write('BEFORE');

    if ( !is_null( $this->cookie->get("auth") ) OR  $this->cookie->get("auth")!="" ){
        $this->BlackBox->validateToken( $this->cookie->get("auth") );
        #var_dump($this->cookie->get("auth") );
        #print_r($this->cookie->get("persistentlogin"));
        //die();
    }

    //print_r($this->BlackBox);
    //die();

    $response = $next($request, $response);
#    $response->getBody()->write('AFTER');
    return $response;
});


// Generate CSRF token
if(empty($_SESSION['token'])) {
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
}
$token = $_SESSION['token'];


/**
 *
 *
 *
 *
 *
 **/

$app->get('/osbox/test', function ($request, $response, $args) {

    return $response->withJson("/osbox/test");
    return $this->view->render("register/index.html");

    //return $this->view->render( $response, $this->BlackBox->showpage( "default/dashboard.html", $request ), $this->BlackBox->UiParameters(["PAGE"=>".page_dashboard"]));
})->setName('page_dashboard');



$app->get('/test', function ($request, $response, $args) {
    return $this->view->render("unregistered/index.html");
//    return $this->view->render( $response, $this->BlackBox->showpage( "default/dashboard.html", $request ), $this->BlackBox->UiParameters(["PAGE"=>".page_dashboard"]));
})->setName('page_dashboard');


$app->get('/devices', function ($request, $response, $args) {
    return $this->view->render( $response, $this->BlackBox->showpage( "devices.html", $request ), $this->BlackBox->UiParameters(["CSRFTOKEN"=>$_SESSION['token'],"PAGE"=>".page_uwapparaten"]));
})->setName('page_uwapparaten');

$app->get('/users', function ($request, $response, $args) {
    return $this->view->render( $response, $this->BlackBox->showpage( "users.html", $request ), $this->BlackBox->UiParameters(["CSRFTOKEN"=>$_SESSION['token'],"PAGE"=>".page_users"]));
})->setName('page_users');

$app->get('/groups', function ($request, $response, $args) {
    return $this->view->render( $response, $this->BlackBox->showpage( "groups.html", $request ), $this->BlackBox->UiParameters(["CSRFTOKEN"=>$_SESSION['token'],"PAGE"=>".page_groups"]));
})->setName('page_groups');




// Define home route
$app->get('/', function ($request, $response, $args) {
//    return $this->view->render($response,"register/index.html");
    #[authenticated] => 1
    #[user][userId] => 6ab331fb-e654-4de3-aa29-b403fcd557e1
    #[user][userEmail] => hopper.jerry@gmail.com
    #print_r($request->getAttribute("AUTH"));
    #die();

    #die();

    //return $this->view->render( $response, "default/dashboard.html", $this->BlackBox->UiParameters(["PAGE"=>".page_dashboard"]));
    //return $response->withJson("whooo");
    return $this->view->render( $response, $this->BlackBox->showpage( "default/dashboard.html", $request ), $this->BlackBox->UiParameters(["CSRFTOKEN"=>$_SESSION['token'],"PAGE"=>".page_dashboard"]));
})->setName('page_dashboard');


$app->get('/api/users', function ($request, $response, $args) {

    return $response->withJson([]);
});

$app->get('/api/move/{ip}/togroup/{piholegroup}', function ($request, $response, $args) {


    $done = $this->BlackBox->PiHole->moveIpToGroup( $args['ip'], $args['piholegroup'] );

    //$done = array("IP"=>$args['ip'],"GROUP"=>$args['piholegroup']);

    return $response->withJson($done);
});


/*******************************************************************************************
 *
 *  ..
 *
 *******************************************************************************************/


$app->get('/login', function ($request, $response, $args) {
    return $response->withRedirect( $this->BlackBox->getLoginUrl() );
});

$app->get('/logout', function ($request, $response, $args) {
    // set the cookie
    $setcookies = new Slim\Http\Cookies();
    $setcookies->set('auth',[
        'value' => "",
        'expires' => "yesterday",
        'path' => '/',
        'domain' => 'blackbox.surfwijzer.nl',
        'httponly' => true,
        'hostonly' => false,
        'secure' => true,
        'samesite' => 'lax'
    ]);
    $setcookies->set('persistentlogin',[
        'value' => "",
        'expires' => "yesterday",
        'path' => '/admin',
        'domain' => 'blackbox.surfwijzer.nl',
        'httponly' => true,
        'hostonly' => false,
        'secure' => true,
        'samesite' => 'lax'
    ]);
    /*
     *
     $setcookies->set('persistentlogin',[
        'value' => "",
        'expires' => "yesterday",
        'path' => '/',
        'domain' => 'blackbox.surfwijzer.nl',
        'httponly' => true,
        'hostonly' => false,
        'secure' => true,
        'samesite' => 'lax'
    ]);
     */
    $response = $response->withHeader('Set-Cookie', $setcookies->toHeaders());

    return $response->withRedirect( '/');
})->setName('page_logout');

/**
 *  Callback function.
 */
$app->get('/callback', function ($request, $response, $args) {

    $allGetVars = $request->getQueryParams();



    $expires=3590;

    $allGetVars = $request->getQueryParams();
    $token = $allGetVars["token"];

    $isAuthenticated = $this->BlackBox->validateToken($token);
    //return $response->withJson($isAuthenticated);




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
            'path' => '/admin',
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



