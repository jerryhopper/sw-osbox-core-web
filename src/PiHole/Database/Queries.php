<?php


namespace PiHoleDatabase;


class Queries
{
    protected $tableName="queries";

    function __construct($pdo)
    {
        $this->pdo = $pdo;

    }

    function resolveHostname($clientip, $printIP)
    {
        global $clients;
        $ipaddr = strtolower($clientip);
        if(array_key_exists($clientip, $clients))
        {
            // Entry already exists
            $clientname = $clients[$ipaddr];
            if($printIP)
                return $clientname."|".$clientip;
            return $clientname;
        }

        else if(filter_var($clientip, FILTER_VALIDATE_IP))
        {
            // Get host name of client and convert to lower case
            $clientname = strtolower(gethostbyaddr($ipaddr));
        }
        else
        {
            // This is already a host name
            $clientname = $ipaddr;
        }
        // Buffer result
        $clients[$ipaddr] = $clientname;

        if($printIP)
            return $clientname."|".$clientip;
        return $clientname;
    }

    function ByIp($ip){



        //$from = intval($from);
        //$until = intval($until);


        $dbquery = "SELECT timestamp, type, domain, client, status FROM queries WHERE client=:client  LIMIT 100";

        //$dbquery = "SELECT timestamp, type, domain, client, status FROM queries WHERE  timestamp >= :from AND timestamp <= :until LIMIT 100";

            /*if(isset($_GET["types"]))
            {
                $types = $_GET["types"];
                if(preg_match("/^[0-9]+(?:,[0-9]+)*$/", $types) === 1)
                {
                    // Append selector to DB query. The used regex ensures
                    // that only numbers, separated by commas are accepted
                    // to avoid code injection and other malicious things
                    // We accept only valid lists like "1,2,3"
                    // We reject ",2,3", "1,2," and similar arguments
                    $dbquery .= "AND status IN (".$types.") ";
                }
                else
                {
                    die("Error. Selector types specified using an invalid format.");
                }
            }*/
            $dbquery .= "ORDER BY timestamp ASC";

        $stmt = $db->prepare($dbquery);

        //$stmt->bindValue(":from", intval($from), SQLITE3_INTEGER);
        //$stmt->bindValue(":until", intval($until), SQLITE3_INTEGER);
        $stmt->bindValue(":client", $ip, SQLITE3_STRING);
        $results = $stmt->execute();

        $data = array();

        $results = $stmt->execute();
        if(!is_bool($results))
            while ($row = $results->fetchArray())
            {
                $c = $this->resolveHostname($row[3],false);

                // Convert query type ID to name
                // Names taken from FTL's query type names
                switch($row[1]) {
                    case 1:
                        $query_type = "A";
                        break;
                    case 2:
                        $query_type = "AAAA";
                        break;
                    case 3:
                        $query_type = "ANY";
                        break;
                    case 4:
                        $query_type = "SRV";
                        break;
                    case 5:
                        $query_type = "SOA";
                        break;
                    case 6:
                        $query_type = "PTR";
                        break;
                    case 7:
                        $query_type = "TXT";
                        break;
                    case 8:
                        $query_type = "NAPTR";
                        break;
                    default:
                        $query_type = "UNKN";
                        break;
                }
                // array:        time     type         domain                client           status
                $allQueries[] = [$row[0], $query_type, utf8_encode($row[2]), utf8_encode($c), $row[4]];
            }
        }
        $result = array('data' => $allQueries);
        $data = array_merge($data, $result);

        return $response->withJson($data);
}


}
