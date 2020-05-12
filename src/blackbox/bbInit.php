<?php


/*
 * /etc/osbox/setup.state
 * /etc/osbox/ssl_enabled
 * /etc/osbox/core-web.v
 *
 * /etc/osbox/osbox.ini
 * /etc/osbox/osbox.db
 *
 */



class bbInit
{

    protected $osboxini;
    protected $webversion;
    protected $setupstate;
    protected $piholesetupvars;


    function __construct()
    {
        // check if master or slave.
        $ssl=$this->_SSLEnabled();

        if ( $ssl===false){
            // No SSL file found.
            throw new \Exception("Fatal: No SSL config found.");

        }elseif( trim($ssl)=="blackbox.surfwijzer.nl" ){
            // This is a osbox master
            $this->osboxini= $this->_OsboxIni();
            $this->webversion= $this->_webVersion();
            $this->setupstate= $this->_SetupState();
            $this->piholesetupvars = $this->_Pihole_setupVars_conf();

        }else{
            // this is something thats not build yet.
            throw new \Exception("Fatal: Unknown bbInit error .");
        }

    }

    public function get_OsboxIni(){
        return $this->osboxini;
    }
    public function get_WebVersion(){
        return $this->webversion;
    }
    public function get_SetupState(){
        return $this->setupstate;
    }
    public function get_PiholeVars(){
        return $this->piholesetupvars;
    }

/*
    public function pihole_gravity_list(){

    }
    public function pihole_ftl_branch(){

    }
    public function pihole_dnsservers_conf(){

    }
    public function pihole_ftl_conf(){

    }


    public function pihole_adlists_list(){

    }
    public function pihole_custom_list(){

    }
    public function pihole_local_list(){

    }

*/

    private function _Pihole_setupVars_conf(){
        try{
            return $this->readIni("/etc/pihole/setupVars.conf");
        }catch (Exception $e){
            return false;
        }
    }

    private function _SetupState(){
        try{
            $r=explode(",",$this->readFile("setup.state"));
            $state=$r[0];
            $stateInt=$r[1];
            $stateMsg=$r[2];
            return $state;
        }catch (Exception $e){
            return false;
        }
    }


    private function _webVersion(){
        try{
            return $this->readFile("core-web.v");
        }catch (Exception $e){
            return false;
        }
    }



    private  function _OsboxIni(){
        try{
            return $this->readIni("/etc/osbox/osbox.ini");
        }catch (Exception $e){
            return $e->getMessage();
            return false;
        }
    }

    private function _SSLEnabled(){
        try{
            return $this->readFile("ssl_enabled");
        }catch (Exception $e){
            return false;
        }

    }





    private function readIni($file){
        if(file_exists($file)){
            return parse_ini_file($file);
        }else{
            throw new Exception("File not found! ($file)");
        }
    }

    private function readFile ($file){

        $filename="/etc/osbox/".$file;

        if(file_exists($filename)){
            $handle = fopen($filename, "rb");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            return $contents;
        }else{
            throw new \Exception("File does not exist (".$filename.")");
        }

    }

}
