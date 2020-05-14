<?php

namespace PiHoleDatabase;

class ClientByGroup{

    protected $tableName="client_by_group";

    function __construct($pdo)
    {
        $this->pdo = $pdo;

    }

    function removeClientFromGroups($clientid){
        $sql = 'DELETE from client_by_group WHERE client_id=:id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $clientid);
        return $stmt->execute();
        //$result = $stmt->fetchAll();
    }

    function addClientToGroup($clientid,$group){
        echo "cid ".$clientid."  g:".$group;

        $sql = 'INSERT INTO client_by_group (group_id,client_id ) VALUES (:group,:clientid)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":clientid", $clientid);
        $stmt->bindValue(":group", $group);

        return $stmt->execute();
    }


}
