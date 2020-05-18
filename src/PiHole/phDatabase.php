<?php

//use PiHoleDatabase;


class phDatabase {

    public $database;

    public $pdo;
    public $users;



    function __construct(){

        $gravitylocation = "/etc/pihole/gravity.db";
        $ftllocation     = "/etc/pihole/pihole-FTL.db";
        $maclocation     = "/etc/pihole/macvendor.db";


        // gravity
        try {
            $pdo_gravity = new \PDO("sqlite:" . $gravitylocation );
            $pdo_gravity->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo_gravity->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            //$this->pdo_gravity = $pdo_gravity;

        } catch (\PDOException $e) {
            // handle the exception here
            die($e->getMessage());
        }

        // ftl
        try {
            $pdo_ftl = new \PDO("sqlite:" . $ftllocation );
            $pdo_ftl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo_ftl->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            //$this->pdo_ftl = $pdo_ftl;

        } catch (\PDOException $e) {
            // handle the exception here
            die($e->getMessage());
        }


        $this->client = new PiHoleDatabase\Client($pdo_gravity);
        $this->client_by_group = new PiHoleDatabase\ClientByGroup($pdo_gravity);

        $this->queries = new PiHoleDatabase\Queries($pdo_ftl);

    }






    public function moveIpToGroup( $ip,$togroup){

        $clients = $this->client->getByIp($ip);

        if( count($clients)==0 ){

            $theClient = $this->client->setByIp($ip);
        }else{
            $theClient = $clients[0]['id'];
        }
        #print_r($r);
        #die();
        #print_r($clients[0]['id']);

        // delete all records.
        $this->client_by_group->removeClientFromGroups($theClient);
        // and add a record.
        $this->client_by_group->addClientToGroup($theClient,$togroup);


        return ;//$client;
    }


}
