<?php
    require_once("./page.php");
    require_once("./pdocon.php");
    //NOTE:  IF REDO, CLEAR FLOW, FLIGHT_SCHEDULE AND SERVICE_ORDER TO RESET THE SYSTEM
    $db = new PDOCON();
    $userlogin = "yangpaothao";
    $apiKey = "967a59a2413a23840e6c83fbdc57a08b67923d88";
    $thiscustomer = "Qantas Airways Limited";
    $thisicao = "QFA";
    $origindestination = "KLAX";
    $destination = "KLAX";
    $thisurl = "http://flightxml.flightaware.com/soap/FlightXML2/wsdl";
    $pageno = 15;
    $date1 = "";
    $date2 = "";
    $shift = 0;
 
    //AeroAPI
    //https://flightaware.com/commercial/aeroapi/explorer/#op_SearchBirdseyeInFlight
    ////
    //URL: http://flightxml.flightaware.com/json/FlightXML2/Search?query=-idents%20{UAL*%20AAL*}

    //We will want to get flights with no depature time for yesterday and today ONLY.  We don't care abou the future.
    //We want to select from flow.
    $curtime = date('Y-m-d H:i:s');
    $starttime = date('Y-m-d H:i:s', strtotime("-1 hours", strtotime($curtime)));
    $endtime = date('Y-m-d H:i:s', strtotime("+1 hours", strtotime($curtime)));
  
    $sql = "SELECT * FROM flow WHERE isdeparted = false and isdeleted = false and ";
    $sql .= "((scheduledeparture BETWEEN '$starttime' AND '$endtime' AND actualdeparture IS NULL) OR (schedulearrival BETWEEN '$starttime' AND '$endtime' AND actualarrival IS NULL)) ";
    $sql .= "ORDER BY scheduledeparture, schedulearrival";
    $result = $db->PDOMiniquery($sql);
    file_put_contents("../dodebug/debug.txt", "apiupdate sql: ".$sql, FILE_APPEND);
    
    $indentstr = "";
    $flightarray = [];
    if($db->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $flightarray[] = "QFA".$rs['flightnumber'];
        }
    }
    else
    {
        exit();
    }   
    //getAPIscheduleupdate($db, $starttime, $endtime, $pageno, $userlogin, $apiKey, $thisurl, $thiscustomer, $thisicao, $flightarray);
    function getAPIscheduleupdate($db, $starttime, $endtime, $pageno, $userlogin, $apiKey, $thisurl, $thiscustomer, $thisicao, &$flightarray)
    {
        //curl -X GET "https://aeroapi.flightaware.com/aeroapi/schedules/2022-12-29/2023-01-19?origin=KLAX&airline=QFA&max_pages=2"
        //http://flightxml.flightaware.com/json/FlightXML2/Search?query=-idents%20{UAL*%20AAL*}
        //$url = "https://flightxml.flightaware.com/json/FlightXML3?query=-idents{QAS}";
        //https://flightxml.flightaware.com/json/FlightXML3/FlightInfoStatus?ident=SWA35@1504888800&include_ex_data=true&filter&howMany=10&offset=0 
        
        $theseidents = implode(" ", $flightarray);
        echo $theseidents."<br>";
        $options = array(
                 'trace' => true,
                 'exceptions' => 0,
                 'login' => $userlogin,
                 'password' => $apiKey,
                 );
        //{in orig{KLAX}} {in dest{KLAX}} 
        $client = new SoapClient($thisurl, $options);
        //$query = "{match ident QFA*}";{match ident QFA*}
        $query = "{match ident QFA*} {orig_or_dest {KLAX} {in orig{KCLT}}} "; //Looks like I will need to use the ident for looking up flights
        $params = array("query" => $query, "howMany" => $pageno, "offset" => 0);
        $result = $client->SearchBirdseyeInFlight($params);
        print_r($result);
    }
    
?>
    

