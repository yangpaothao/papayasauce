<?php
    require_once("./page.php");
    require_once("./pdocon.php");
    //NOTE:  IF REDO, CLEAR FLOW, FLIGHT_SCHEDULE AND SERVICE_ORDER TO RESET THE SYSTEM
    $db = new PDOCON();
    $futuredays = 5;
    $apiKey = "4f9kwPe0AoZADaAVjTMTGOqqRhvbn4pN";
    $thiscustomer = "Qantas Airways Limited";
    $thisicao = "QFA";
    $origindestination = "KLAX";
    $pageno = 20;
    $date1 = "";
    $date2 = "";
    $shift = 0;
    //Wer will get 1 record only from this sql.
    $sqldt = "SELECT * FROM date_tracker WHERE isRan = false ORDER BY date, shift limit 1";
    $rsdt = $db->PDOMiniquery($sqldt);

    /*
     //We use this block of code to filled in initial 30 days worth of dates for table date_tracker, disabled after filled
    $insertdate1 = date('Y-m-d');
    $insertdate2 = date('Y-m-d', strtotime("+$futuredays days"));
    while(strtotime($insertdate1) <= strtotime($insertdate2))
    {
        echo "this date: ".$insertdate1."<br>";
       
        $sqlinsert = "INSERT INTO date_tracker (recno, date, shift) ";
        $sqlinsert .= "VALUES (NULL, '$insertdate1', 1, 0), (NULL, '$insertdate1', 2, 0)";
        file_put_contents("../dodebug/debug.txt", $sqlinsert."===", FILE_APPEND);
        $db->PDOMiniinsert($sqlinsert);
        $insertdate1 = date('Y-m-d', strtotime( "+1 day", strtotime($insertdate1)));
        //echo "new day: ".$insertdate1."<br>";
    }
    */
    $thisdate = date('Y-m-d');  //default day to today
    $shift = 1; //shift default to 1
    $dtrecno = 0;
    if($db->PDORowcount($rsdt) > 0)
    {
        foreach($rsdt as $rsdata)
        {
            $thisdate = $rsdata['date'];
            $shift = $rsdata['shift'];
            $dtrecno = $rsdata['recno'];
        }
    } 
    else
    {   
        Checkdates($thisdate, $futuredays, $db);
        $sqldt = "SELECT * FROM date_tracker WHERE isRan = false ORDER BY date, shift limit 1";
        $rsdt = $db->PDOMiniquery($sqldt);
        foreach($rsdt as $rsdata)
        {
            $thisdate = $rsdata['date'];
            $shift = $rsdata['shift'];
            $dtrecno = $rsdata['recno'];
        }
    }
    //echo "doing this date: ".$thisdate." and shift: ".$shift."<BR><BR>";
    $startdate = $thisdate."T00:00:00Z"; //Morning half for, m for morning
    $enddate = $thisdate."T23:59:59Z";
    if($shift == 1)
    {
        getAPIscheduleorigin($startdate, $enddate, $pageno, $apiKey, $thiscustomer, $thisicao, $origindestination);
        $temptable = "date_tracker";
        if($dtrecno != 0)
        {
            
            $tempdata= array("isRan" => true);
            $tempwhere = array("recno" => $dtrecno);
            $db->PDOUpdate($temptable, $tempdata, $tempwhere, $temptable);
        }
        else
        {
            //The databasse is empty so we need to insert the dates into date_tracker table
            $tempdata = array("date" => $thisdate, "shift" => 1, "isRan" => true);
            $db->PDOInsert($temptable, $tempdata);
            $tempdata = array("date" => $thisdate, "shift" => 2, "isRan" => true);
            $db->PDOInsert($temptable, $tempdata);
        }
         
    }
    else if($shift == 2)
    {   
        Checkdates($thisdate, $futuredays, $db);
        getAPIscheduledestination($startdate, $enddate, $pageno, $apiKey, $thiscustomer, $thisicao, $origindestination);
        $temptable = "date_tracker";
        $tempdata= array("isRan" => true);
        $tempwhere = array("recno" => $dtrecno);
        $db->PDOUpdate($temptable, $tempdata, $tempwhere, $temptable);
    }
    function Checkdates($curdate, $futuredays, $db)
    {
        $thisdatel = "";   
        //file_put_contents("../dodebug/debug.txt", "What is this curdate? ".$curdate."\n", FILE_APPEND);
        //Now we need to insert 1 more day to the table
        $sqldtl = "SELECT * FROM date_tracker WHERE isRan = false ORDER BY recno DESC limit 1 ";
        $rsdtl = $db->PDOMiniquery($sqldtl);
        if($db->PDORowcount($rsdtl) > 0)
        {
            foreach($rsdtl as $rsdata)
            {
                $thisdatel = $rsdata['date'];
            }
            file_put_contents("../dodebug/debug.txt", "What is this thisdate1? ".$thisdatel."\n", FILE_APPEND);
            //We will now add the $futuredays days to today's date and then compare to $thisdatel and if
            //$thisdatel is less than the date said, then we add to it until it is the same day otherwise we
            //don't do anything because the date is already added or enough dates in the table.

            $newday = date('Y-m-d', strtotime( "+$futuredays day", strtotime($curdate)));  //29 + 5 
            file_put_contents("../dodebug/debug.txt", "What is this newdays? ".$newday."\n", FILE_APPEND);
            
            //Is $newday > than the last day in this table
            if(strtotime($newday) > strtotime($thisdatel))
            {
                //Now we need to add 1 day to $thisdatel and then insert into the table until the day is equal to $newday
                $tempdate =  strtotime($newday) - strtotime($thisdatel);
                $tempdaytobeadded = number_format(date('d', $tempdate));
                
                $datesql = $datestr = "INSERT INTO date_tracker (recno, date, shift, isRan) VALUES ";
                file_put_contents("../dodebug/debug.txt", "What is this days? ".$tempdaytobeadded."<br>", FILE_APPEND);
                for($i=1; $i<=$tempdaytobeadded; $i++)
                {
                    $realdaytobeadded = date('Y-m-d', strtotime("+$i day", strtotime($thisdatel)));
                    $datesql .= "(NULL, '$realdaytobeadded', 1, 0), (NULL, '$realdaytobeadded', 2, 0),";
                }
                $sqlinsert = rtrim($datesql, ',');
                //file_put_contents("../dodebug/debug.txt", $sqlinsert."===add", FILE_APPEND);
                $db->PDOMiniinsert($sqlinsert);

            }
        }
        else
        {
            $thisdatel = date('Y-m-d');
            $datesql = $datestr = "INSERT INTO date_tracker (recno, date, shift, isRan) VALUES ";
            $datesql .= "(NULL, '$thisdatel', 1, 0), (NULL, '$thisdatel', 2, 0),";
            for($i=1; $i<=$futuredays; $i++)
            {
                //file_put_contents("../dodebug/debug.txt", "$i: ".$thisdatel."<br>", FILE_APPEND);
                $thisdatel = date('Y-m-d', strtotime("+1 day", strtotime($thisdatel)));
                //file_put_contents("../dodebug/debug.txt", $realdaytobeadded."===day? $i", FILE_APPEND);
                $datesql .= "(NULL, '$thisdatel', 1, 0), (NULL, '$thisdatel', 2, 0),";
            }
            $sqlinsert = rtrim($datesql, ',');
            file_put_contents("../dodebug/debug.txt", $sqlinsert."===in empty", FILE_APPEND);
            $db->PDOMiniinsert($sqlinsert);
        }
    }
    function getAPIscheduleorigin($startdate, $enddate, $pageno, $apiKey, $thiscustomer, $thisicao, $origindestination)
    {
        global $db;
        //We will start to get the last availabler allowable date schedule, for now 2 days in advance, that way we
        //do not get the data we already got which would be today and tomorrow.
        $queryParams = array(
            'origin' => $origindestination,
            'airline' => $thisicao,
            'max_pages' => $pageno
        );
        //curl -X GET "https://aeroapi.flightaware.com/aeroapi/schedules/2022-12-29/2023-01-19?origin=KLAX&airline=QFA&max_pages=2"

        $url = "https://aeroapi.flightaware.com/aeroapi/schedules/$startdate/$enddate?".http_build_query($queryParams);
        echo $url."<br>";
        //return(false);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Accept: application/json; charset=UTF-8",
            "x-apikey: $apiKey",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        echo $resp;
        curl_close($curl);

        $thisjson = json_decode($resp, true);
        $bigarrayschedule = [];
        $bigarrayflow = [];
        $temparray = [];
        $i=0;
        $thistable = "flight_schedule";
        foreach($thisjson['scheduled'] as $val)
        {
            if(is_array($val))
            {
                $sqlqry = "SELECT recno, dates FROM $thistable WHERE customer = '$thiscustomer' AND flightnumber= '".substr($thisjson['scheduled'][$i]['ident'], 3)."' ";
                $sqlqry .= "AND fromstation = '".$thisjson['scheduled'][$i]['origin']."' AND schedulearrival LIKE '%".date('Y/m/d H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."%' ";
                $sqlqry .= "AND scheduledeparture LIKE '%".date('Y/m/d H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_out']))."%'";
                //echo $sqlqry;
                $resultqry = $db->PDOMiniquery($sqlqry);
                if($db->PDORowcount($resultqry) == 0)
                {
                    $sqlins = "INSERT INTO $thistable (customer, actype, flightnumber, fromstation, schedulearrival, tostation, scheduledeparture, dates) VALUES(";
                    $sqlins .= "'Qantas Airways Limited', ";
                    $sqlins .= "'".$thisjson['scheduled'][$i]['aircraft_type']."', ";
                    $sqlins .= "'".substr($thisjson['scheduled'][$i]['ident'], 3)."', ";
                    $sqlins .= "'".$thisjson['scheduled'][$i]['origin']."', ";
                    $sqlins .= "'".date('Y/m/d H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."', ";
                    $sqlins .= "'".$thisjson['scheduled'][$i]['destination']."', ";
                    $sqlins .= "'".date('Y/m/d H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_out']))."', ";
                    $sqlins .= "'".date('Y/m/d', strtotime($thisjson['scheduled'][$i]['scheduled_out']))."')";
                    //echo $sqlins.'<br>';
                    $db->PDOMiniquery($sqlins);
                }
                else
                {
                    foreach($resultqry as $rs)
                    {
                        $sqlupdate = "UPDATE $thistable SET dates = CONCAT_WS(',', dates, '".date('Y/m/d', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."')";
                        $sqlupdate .= "WHERE recno = ".$rs['recno']." AND dates NOT LIKE '%".date('Y/m/d', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."%'";
                        $db->PDOMiniquery($sqlupdate);
                    }
                }
                /*
                $temparray['customer'] = "Qantas Airways Limited";
                $thisident = $thisjson['scheduled'][$i]['ident'];
                $temparray['flightnumber'] = substr($thisident, 3);
                $temparray['actype'] =  $thisjson['scheduled'][$i]['aircraft_type'];
                $temparray['dates'] = date('m/d/Y', strtotime($thisjson['scheduled'][$i]['scheduled_in']));            //2022-12-27T13:55:00Z
                $temparray['fromstation'] =  $thisjson['scheduled'][$i]['origin'];
                $temparray['scheduledepature'] = date('H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_out']));                
                $temparray['tostation'] =  $thisjson['scheduled'][$i]['destination'];
                $temparray['schedulearrival'] = date('H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_in']));
                $bigarrayschedule[] = $temparray;
                 */
            }
            $i++;
        }
        /*
        foreach($bigarrayschedule as $key => $val)
        {
            $thisdata = $val;

            foreach($val as $key2 => $val2)
            {
                echo $key2." => ".$val2."<br>";
            }
            echo "<br>";

        }*/
    }
    function getAPIscheduledestination($startdate, $enddate, $pageno, $apiKey, $thiscustomer, $thisicao, $origindestination)
    {
        global $db;
        /*Flight Aware only allows for 2 future days schedule search.  Can probably get farther out dates but will cost more
         */
        //$startdate = date('Y-m-d', strtotime('+10 days'));
        //$enddate = date('Y-m-d', strtotime('+21 days', strtotime($startdate))); 
        //We will start to get the last availabler allowable date schedule, for now 2 days in advance, that way we
        //do not get the data we already got which would be today and tomorrow.
        $queryParams = array(
            'destination' => $origindestination,
            'airline' => $thisicao,
            'max_pages' => $pageno
        );
        //curl -X GET "https://aeroapi.flightaware.com/aeroapi/schedules/2022-12-29/2023-01-19?origin=KLAX&airline=QFA&max_pages=2"

        $url = "https://aeroapi.flightaware.com/aeroapi/schedules/$startdate/$enddate?".http_build_query($queryParams);
        echo $url.'<br>';
        //return(false);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Accept: application/json; charset=UTF-8",
            "x-apikey: $apiKey",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        echo $resp;
        curl_close($curl);

        $thisjson = json_decode($resp, true);
        $bigarrayschedule = [];
        $bigarrayflow = [];
        $temparray = [];
        $i=0;
        $thistable = "flight_schedule";
        foreach($thisjson['scheduled'] as $val)
        {
            if(is_array($val))
            {
                $sqlqry = "SELECT recno, dates FROM $thistable WHERE customer = '$thiscustomer' AND flightnumber= '".substr($thisjson['scheduled'][$i]['ident'], 3)."' ";
                $sqlqry .= "AND tostation = '".$thisjson['scheduled'][$i]['destination']."'";
                //echo $sqlqry;
                $resultqry = $db->PDOMiniquery($sqlqry);
                if($db->PDORowcount($resultqry) == 0)
                {
                    //If we do not have a record where the customer is this and there flightnumber is this and the destination is this, we will insert into it, it is a 
                    //new record for this.
                    $sqlins = "INSERT INTO $thistable (customer, actype, flightnumber, fromstation, schedulearrival, tostation, scheduledeparture, dates) VALUES(";
                    $sqlins .= "'Qantas Airways Limited', ";
                    $sqlins .= "'".$thisjson['scheduled'][$i]['aircraft_type']."', ";
                    $sqlins .= "'".substr($thisjson['scheduled'][$i]['ident'], 3)."', ";
                    $sqlins .= "'".$thisjson['scheduled'][$i]['origin']."', ";
                    $sqlins .= "'".date('Y/m/d H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."', ";
                    $sqlins .= "'".$thisjson['scheduled'][$i]['destination']."', ";
                    $sqlins .= "'".date('Y/m/d H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_out']))."', ";
                    $sqlins .= "'".date('Y/m/d', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."')";
                    //echo $sqlins.'<br>';
                    $db->PDOMiniquery($sqlins);
                }
                else
                {
                    //If we found a record where the customer is this, the flightnumber is this and the destination is this, we just add the date to it.
                    //ex: 2012-12-12,2012-13-12,...
                    foreach($resultqry as $rs)
                    {
                        $sqlupdate = "UPDATE $thistable SET dates = CONCAT_WS(',', dates, '".date('Y/m/d', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."')";
                        $sqlupdate .= "WHERE recno = ".$rs['recno']." AND dates NOT LIKE '%".date('Y/m/d', strtotime($thisjson['scheduled'][$i]['scheduled_in']))."%'";
                        $db->PDOMiniquery($sqlupdate);
                    }
                }
                /*
                $temparray['customer'] = "Qantas Airways Limited";
                $thisident = $thisjson['scheduled'][$i]['ident'];
                $temparray['flightnumber'] = substr($thisident, 3);
                $temparray['actype'] =  $thisjson['scheduled'][$i]['aircraft_type'];
                $temparray['dates'] = date('m/d/Y', strtotime($thisjson['scheduled'][$i]['scheduled_in']));            //2022-12-27T13:55:00Z
                $temparray['fromstation'] =  $thisjson['scheduled'][$i]['origin'];
                $temparray['scheduledepature'] = date('H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_out']));                
                $temparray['tostation'] =  $thisjson['scheduled'][$i]['destination'];
                $temparray['schedulearrival'] = date('H:i:s', strtotime($thisjson['scheduled'][$i]['scheduled_in']));
                $bigarrayschedule[] = $temparray;*/
            }
            $i++;
        }

        //Now that we have an array with our data, we will now iterate through it and update/insert
        /*
        foreach($bigarrayschedule as $key => $val)
        {
            $thisdata = $val;

            foreach($val as $key2 => $val2)
            {
                echo $key2." => ".$val2."<br>";
            }
            echo "<br>";

        }*/
    }
?>
    

