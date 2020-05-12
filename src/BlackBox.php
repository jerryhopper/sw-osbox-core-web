<?php


class BlackBox
{
    /**
     * Configuration information
     * @var array osboxini
     */
    public $osboxini;

    /**
     * the pihole Setupvars.
     * @var SetupVars
     */
    public $setupVars;

    /**
     * @var bool|false|string
     */
    public $webversion;


    /**
     * @var bool|mixed
     */
    public $setupstate;

    /**
     * @var bbDatabase
     */
    public $database;

    /**
     * @var bool
     */
    public $owner;







    /**
     * the UserObject
     * @var userObj
     */
    public $userObject;



    public $isConfigured;
    public $isRegistered;



    private $loginurl;



    /**
     * BlackBox constructor.
     *
     */
    function __construct()
    {

        /**
         * Init & get the Config objects.
         */
        $init = new bbInit();

        // osbox.ini
        $this->osboxini = $init->get_OsboxIni();
        #echo "<pre>";
        #var_dump($this->osboxini);

        // setupVars.cfg
        $this->setupVars = $init->get_PiholeVars();
        #var_dump($this->setupVars);

        // webversion
        $this->webversion = $init->get_WebVersion();
        #var_dump($this->webversion);


        // setupstate
        $this->setupstate = $init->get_SetupState();
        #var_dump($this->setupstate);
        #die();

        //
        try{
            $this->database = new bbDatabase();
        } catch(Exception $e){
            echo $e->getMessage();
        }


        $this->owner = $this->database->users->getOwner();


        //
        //$this->database->users->dropTable();

//var_dump($this->owner);


        if( $this->hasOwner() ){
            #die("Owner");
        }else{
           #die("NoOwner");
        }

#die();

        #var_dump($this->database->users->getOwner());
        #die();






        /**
         * Create the auth object
         */
        $this->auth = new bbAuth($this->osboxini);



        #var_dump( $this->auth->oAuthloginUrl());
        //var_dump( $this->auth->oAuthlogoutUrl());
        //die();



        //$this->config = new bbConfig();





        /**
         * Create the setupvars
         */
        //$vars = new SetupVars();
        //$this->setupVars = $vars->get();





        /**
         * Get the login/out url
         */
        $this->loginurl = $this->auth->oAuthloginUrl();
        $this->logouturl = $this->auth->oAuthlogoutUrl();

#        error_log($this->loginurl);

        /**
         * ....
         */
        $this->isConfigured = true;//$this->config->networkConfigured();
        $this->isRegistered = true;//$this->config->registeredToAccount();








        $this->piholeNativeAuth = new PiholeNativeAuth($this->setupVars);

        //var_dump($this->setupVars);
        #die();

    }






    public function debug(){

        echo "<pre>";
        //print_r();
        echo "</pre>";

    }


    function exec($command){

        return new bbExec($command);
        print_r($res);
        $res->getResult();
        $res->getCommand();
        $res->getReturnvar();
        $res->getOutput();

        die();
    }

    function test(){


       // $gravitydb = new bbGravityDb();
        $piholedb = new bbPiholeDb();

//        $result = $gravitydb->getRegexWhite();
        #$gravitydb->flushRegex();
        #$gravitydb->importRegex();

        #$result = $gravitydb->getExactBlack();
        #$result = $gravitydb->getRegexBlack();
        //$result = $gravitydb->getGroups();
        //$result = $gravitydb->getClients();
        $result = $piholedb->getNetwork();


        echo "<pre>";
        print_r($result);
        die();
        //echo "Drop tables ";
        try{
            //$this->database->dropTable("users");
        }catch(Exception $e){

            echo $e->getMessage();
            die();
        }

        echo "createUsersTable ";


        if( !$this->database->tableExists("users") ){
            echo "Table 'usersx' doesnt exist.";
            $this->database->createUsersTable();
        }


        //$this->database->createUser($this->userObject);
        //$bbdb->setAdmin($this->userObject);


        $this->database->setOwner($this->userObject,true);

        $this->database->setAdmin($this->userObject,true);


        var_dump( $this->database->getUser($this->userObject) );
    die();//"6ab331fb-e654-4de3-aa29-b403fcd557e1"
        echo "getusers";
        var_dump( $this->database->getUsers() );

        echo "getadmin";
        var_dump( $this->database->getAdmins() );


        echo "getowner";
        var_dump( $this->database->getOwner() );

        die();


        //var_dump( $this->userObject );
        //if( $x->tableExists("usersx") ){
        //    echo "Table 'usersx' doesnt exist.";
        //}

        #if( $x->tableExists("users") ){
         #   echo "Table 'users' doesnt exist.";
        #}

        die();


        // SELECT name FROM sqlite_master WHERE type='table' AND name='{table_name}';

        //$results = $db->query('SELECT bar FROM foo');

        //while ($row = $results->fetchArray()) {
        //    var_dump($row);
        //}

        //print_r($x->database);

        die("__!__");

        return $x;
    }

    /**
     * @return string
     */
    public function getLoginUrl(){
        return $this->loginurl;
    }


    public function getLogoutUrl(){
        return $this->loginout;
    }

    /**
     *
     * @param array $array
     * @return array
     */
    public function UiParameters(array $array){
        $log = "Current installation state: ".$this->setupstate."  - ";
        //$log .= "isRegistered = ".(int)$this->isRegistered." - ";

        if( $this->hasOwner() ){
            $log .= "Device has owner. ";
        }else{
            $log .= "device has no owner. ";
        }

        $theData = array(
            "SERVER_ADDR"=>$_SERVER['SERVER_ADDR'],
            "AUTH_LOGINURL"=>$this->getLoginUrl(),
            "STATE"=>$this->getState(),
            "AUTH"=>$this->getUserinfo(),
            "log"=>$log
        );
        return array_merge($theData,$array);
    }


    /**
     * Gets the installation state
     * sets the readablestate
     * @return int
     */
    public function getState(){
        //$state = $this->config->getState();

       // $readablestate = new bbState($state);
        //$this->readablestate = $readablestate->state;

        return $this->setupstate;
    }



    /**
     * @param bool $property
     * @return array|bool|mixed
     */
    public function getUserinfo($property=false){


        if( !$this->auth->isAuthenticated() ){
            if($property!=false){
                return false;
            }
            return array(
                "authenticated"=>false,
            );
        }else{

            $result =  array(
                "authenticated"=>true,

                "user"=>array(
                    "id"=>$this->userObject->id,
                    "email"=>$this->userObject->email,
                    "name"=>$this->userObject->name,
                    "roles"=>$this->userObject->roles,
                    "expires"=>$this->userObject->expires
                )
            );
            if($property!=false){
                return $result['user'][$property];
            }
            return $result;
        }

    }






    public function showPage( $templatename , $request ){

        /*
            "10"=>"Ready for shipping",
            "11"=>"static network configured",
            "12"=>"namebased host reachable",
            "13"=>"device registered to user"
        */

        if( $request->getUri()->getHost()=="blackbox.surfwijzer.nl" &&
            $request->getUri()->getScheme()=="https" ){

        }

        if( $this->getstate()!="staticnetwork"){
            // something is wrong, this shouldnt happen.

        }


        if( ! $this->hasOwner() ){
            return "register/index.html";
        }


        return $templatename;


        var_dump( $this->hasOwner() );
        var_dump( $this->owner );


        die();

        /*
         if ( $this->getstate()==10 ) {
            return "setup/index.html";
        }
        if ( $this->getstate()==11 ) {
            return "setup/index.html";
        }
        if ( $this->getstate()==12 ) {
            return "register/index.html";
        }

        if ( $this->getstate()==13 ) {
            return $templatename;
        }
*/
        // if the network is configured, and device has a owner we can show the requested template
        //if($this->config->networkConfigured() && $this->config->registeredToAccount() ){
            //return $templatename;
        //}

        //&& !$this->config->registeredToAccount()
        //if( !$this->config->networkConfigured() ){
          //  return "setup/index.html";
        //}

        return "register/index.html";


    }






    /**
     * @return bool
     */
    public function hasOwner(){

        if(count($this->owner)>0){
            return true;
        }
        return false;
    }


    /**
     * @param $userObject
     * @return bool
     * @throws Exception
     */
    public function setOwner( OauthUserObj $userObject ){

        $userObject->id;
        $userObject->email;
        $userObject->name;
        $userObject->roles;


        $res = $this->database->users->setOwner($userObject);
        #$var_dump($res);
        #$res = exec("sudo osbox owner set $uid");
        error_log("setOwner ".$userObject->id);
        #die();
        //$res = $this->exec("osbox owner set ".$userObject->id);

        return res;

        #return $this->config->setOwner($userObject /* $uid,$email*/);
    }


    /**
     * Validates token, and populates the userObject property
     * @param $token
     * @return bool
     */
    public function validateToken($token){

        try {
            $validation = $this->auth->validate($token);
        }catch(Exception $e){
            die("token error! ".$e->getMessage());
        }

        if(!$validation){
            return false;
        }else{
            $this->userObject = new OauthUserObj($this->auth->token);

            return true;
        }

    }



}
