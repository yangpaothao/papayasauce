<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class ServiceClass {
    private $db = null;
    private $recnostr = "";
    private $resultsvc = "";
    private $sqlsvc = "";
    function SetService($db, $tempservicerecno)
    {
        //If we want to recalculate with the prepay, we have to pass it, otherwise it will only calculate the appropriate tax.
        $this->db = $db; //passing the db connection in from the call
        $this->recnostr = $tempservicerecno;
    }
    function GetServicetitle()
    {
        $body = "";
        $lineno = 1;
        $body .= "Service:<br/><br/>" ;
        $this->sqlsvc = "SELECT * FROM service WHERE recno IN (".$this->recnostr.")";
        $this->resultsvc = $this->db ->PDOMiniquery($this->sqlsvc);
        foreach($this->resultsvc as $rssvc)
        {
            //file_put_contents('./dodebug/debug.txt', "SC sql: GetServicetitle \n", FILE_APPEND);
            $body .= $lineno.". ".$rssvc['title']."<br/>";
            $lineno++;
        }
        return($body);
    }
    function GetServicetime()
    {
        $totaltime = 0;
        $this->sqlsvc = "SELECT * FROM service WHERE recno IN (".$this->recnostr.")";
        $this->resultsvc = $this->db ->PDOMiniquery($this->sqlsvc);
        foreach($this->resultsvc as $rssvc)
        {
            //file_put_contents('./dodebug/debug.txt', "SC sql: GetServicetime \n", FILE_APPEND);
            $totaltime += $rssvc['time'];
        }
        return($totaltime);
    }
    function GetServiceprice()
    {
        $caltotal = 0;

        $this->sqlsvc = "SELECT * FROM service WHERE recno IN (".$this->recnostr.")";
        $this->resultsvc = $this->db ->PDOMiniquery($this->sqlsvc);
        foreach($this->resultsvc as $rssvc)
        {
            //file_put_contents('./dodebug/debug.txt', "SC sql: GetServiceprice \n", FILE_APPEND);
            $caltotal += $rssvc['price'];
        }
        return($caltotal);
    }
    function GetServicrecnos()
    {
        $servicerecno = "";
        $this->sqlsvc = "SELECT * FROM service WHERE recno IN (".$this->recnostr.")";
        $this->resultsvc = $this->db ->PDOMiniquery($this->sqlsvc);
        foreach($this->resultsvc as $rssvc)
        {
            //file_put_contents('./dodebug/debug.txt', "SC sql: GetServicrecnos \n", FILE_APPEND);
            if($servicerecno == "")
            {
                $servicerecno = $rssvc['recno'];
            }
            else
            {
                $servicerecno += ",".$rssvc['recno'];
            }
        }
        return($servicerecno);
    }
    function GetServicedeposit()
    {
        //file_put_contents('./dodebug/debug.txt', "SC sql: ".$this->sqlsvc." \n", FILE_APPEND);
        //Check to see if any of the recnos has atleast 1 deposit required
        $totaldeposit = 0;
        $this->sqlsvc = "SELECT * FROM service WHERE recno IN (".$this->recnostr.")";
        $this->resultsvc = $this->db ->PDOMiniquery($this->sqlsvc);
        foreach($this->resultsvc as $rssvc)
        {
            if($rssvc['isDeposit'] == true)
            {
                $totaldeposit += $rssvc['deposit'];
            }
        }
        return($totaldeposit);
    }
}
