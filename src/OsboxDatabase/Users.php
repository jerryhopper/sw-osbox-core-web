<?php


namespace OsboxDatabase;


class Users
{

    protected $tableName="users";

    function __construct($pdo)
    {
        $this->pdo = $pdo;

    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getOwner(){

        $sql="SELECT * FROM users WHERE owner=true";

        try{
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result =  $stmt->fetch();

            if($result===false){
                return array();
            }else{
                return $result;
            }




        }catch( \PDOException $e){
            error_log("PDOException ".$e->getMessage() );

            if( strpos($e->getMessage(),"no such table:") ){
                $this->createTable();
                return array();

            }else{
                throw new \Exception("Unknown error! ".$e->getMessage() );
            }

        }catch(\Exception $e){
            error_log("Exception");

            echo $e->getMessage();
        }

    }




    function get($id=false){
        if($id===false){
            $sql = "SELECT * FROM ".$this->tableName." ";
        }else{
            $sql = "SELECT * FROM ".$this->tableName." WHERE uuid=:uuid";

        }
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->fetchAll();

        return $result;
    }

    function set($arr){

        $uuid=$arr[0];
        $email=$arr[1];
        $name=$arr[2];
        $owner=$arr[3];
        $admin=$arr[4];

        $sql="INSERT INTO ".$this->tableName."  (uuid,email,name,owner,admin) VALUES (:uuid,:email,:name,:owner,:admin) ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bind_param(":uuid",$uuid);
        $stmt->bind_param(":email",$email);
        $stmt->bind_param(":name",$name);
        $stmt->bind_param(":owner",$owner);
        $stmt->bind_param(":admin",$admin);

        $stmt->exec($sql);


        var_dump($result);
        //$stmt->execute();
        // prepare and bind


    }

    function setOwner( $userObject ){
        error_log("setOwner");

        $userObject->id;
        $userObject->email;
        $userObject->name;
        $userObject->roles;

#print_r(        $userObject->id );
//print_r(        $userObject->name );
//        die();

//        6ab331fb-e654-4de3-aa29-b403fcd557e1

        $sql="INSERT INTO ".$this->tableName." (uuid,email,name,owner,admin) VALUES ( :uuid, :email,:name,true,true) ";

//
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':uuid', (string)$userObject->id);
        $stmt->bindValue(':email',(string) $userObject->email);
        $stmt->bindValue(':name',(string) $userObject->name);


        $data = array(
            "uuid"=>$userObject->id,
            "email"=>$userObject->email,
            "name"=>$userObject->name
            );


        return $stmt->execute();

    }

    function dropTable(){
        $sql = "DROP TABLE ".$this->tableName;
        $stmt = $this->pdo->exec($sql);
        var_dump($stmt);
    }

    function createTable(){
        $sql = "CREATE TABLE ".$this->tableName." (
                uuid VARCHAR(40)  UNIQUE NOT NULL,
                email VARCHAR(80)  NOT NULL,
                name VARCHAR(120)  NOT NULL,
                owner BOOLEAN DEFAULT 'false' NOT NULL,
                admin BOOLEAN DEFAULT 'false' NOT NULL);";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute([]);
        //$stmt = $this->pdo->execute($sql);
        //var_dump($res);
        //$stmt->execute();

    }


}
