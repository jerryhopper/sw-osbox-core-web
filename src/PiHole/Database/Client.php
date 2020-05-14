<?php

namespace PiHoleDatabase;

class Client
{
    protected $tableName="client";

    function __construct($pdo)
    {
        $this->pdo = $pdo;

    }



    function setByIp ($ip){
        $sql = "INSERT INTO ".$this->tableName." (ip) values (:ip)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":ip", $ip);
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    function getByIp($ip){
        //$sql = 'SELECT id FROM CLIENT where ip=ip';


        $sql = "SELECT * FROM ".$this->tableName." WHERE ip=:ip";


        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":ip", $ip);
        $stmt->execute();
        $result = $stmt->fetchAll();


        return $result;
    }


}
