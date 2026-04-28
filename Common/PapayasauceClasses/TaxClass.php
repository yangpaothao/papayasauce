<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class TaxClass {
    private $db = null;
    private $thisstate = ""; //Operating state, ex: OK, AK....
    private $thistax = ""; //Fed, State, County, City
    private $thistotal = ""; //The cost of the service before tax and promotion in float, ex: 20.25
    private $GetTaxpercent = 0;
    private $GetTaxpercentallarray = [];
    private $result = [];
    private $caltotal = 0;
    private $pervalue = 0;
    function SetTax($db, $tempstate, $temptax, $temptotal)
    {
        $this->db = $db; //passing the db connection in from the call
        $this->thisstate = $tempstate; //OK, AK
        $this->thistax = $temptax; //Fed, State, County, City
        $this->thistotal = $temptotal; //in float in format of 20.00
        $sql = "SELECT * FROM taxme WHERE state = '".$this->thisstate."' AND isActive = true";
        $this->result = $this->db->PDOMiniquery($sql);
    }
    function SetTaxval($thisval)
    {
        //$thisval will be Fed, State, County, City
        $this->GetTaxpercent = $thisval; //passing the db connection in from the call
    }
    function SetAlltaxper($db, $tempstate)
    {
        $this->db = $db;
        $this->thisstate = $tempstate; //Operating state, ex: OK, AK....
        $this->GetTaxpercentallarray = [];
    }
    function GetAlltaxper()
    {
        $sql = "SELECT * FROM taxme WHERE state = '".$this->thisstate."' AND isActive = true";
        $this->result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                if($rs['federal_tax'] != NULL)
                {
                    $this->GetTaxpercentallarray['Fed Tax'] = $rs['federal_tax'];
                }
                if($rs['state_tax'] != NULL)
                {
                    $this->GetTaxpercentallarray['State Tax'] = $rs['state_tax'];
                }
                if($rs['county_tax'] != NULL)
                {
                    $this->GetTaxpercentallarray['County Tax'] = $rs['county_tax'];
                }
                if($rs['city_tax'] != NULL)
                {
                    $this->GetTaxpercentallarray['City Tax'] = $rs['city_tax'];
                }
            }
            return($this->GetTaxpercentallarray);
        }
        else
        {
            return('No Data');
        }
    }
    function GetTaxper()
    {
        //Get only the percent
        //$temptotal is the cost of the service, ex: 35.
        switch($this->GetTaxpercent)
        {
            case "Fed":
                $taxfield = "federal_tax";
                break;
            case "State":
                $taxfield = "state_tax";
                break;
            case "County":
                $taxfield = "county_tax";
                break;
            case "City":
                $taxfield = "city_tax";
                break;
            deafult:
                break;
        }
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                $realtax = $rs[$taxfield];
            }
            return($realtax);
        }
        else
        {
            return('No Data');
        }
    }
    function setTaxdollar($caltotal, $pervalue)
    {
        $this->caltotal = $caltotal;
        $this->pervalue = $pervalue;
    }
    function GetTaxdollar()
    {
        $thissum = $this->caltotal * ($this->pervalue/100);
        return($thissum);
    }
    function GetAllTtaxdollar()
    {
        //return all of tax, ex: fed and state....
        $thissum = 0;
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                if(!is_null($rs['federal_tax']))
                {
                    $thissum += ($this->thistotal * ($rs['federal_tax']/100));
                }
                if(!is_null($rs['state_tax']))
                {
                    $thissum += ($this->thistotal * ($rs['state_tax']/100));
                }
                if(!is_null($rs['county_tax']))
                {
                    $thissum += ($this->thistotal * ($rs['county_tax']/100));
                }
                if(!is_null($rs['city_tax']))
                {
                    $thissum += ($this->thistotal * ($rs['city_tax']/100));
                }
            }
            return($thissum);
        }
        else
        {
            return('No Data');
        }
    }
    function SetActivetax($db, $tempstate)
    {
        $this->db = $db;
        $this->state = $tempstate;
    }
    function GetActivetax()
    {
        $temparray = [];
        $thisarray = [];
        $thissltstr = "";
        //At this point, we can use the schedule_dates to get the active tax, at this point,
        //it should be filled if we process taxes.
        $sql = "DESCRIBE schedule_dates";
        $result = $this->db->PDOminiquery($sql);
        if($this->db->PDOrowcount($result) > 0)
        {
            //We should always have something
            foreach($result as $thiscol)
            {
                if(substr($thiscol['Field'], -7) == "tax_per")
                {
                    $temparray[] = $thiscol['Field'];
                    if($thissltstr == "")
                    {
                        $thissltstr = $thiscol['Field'];
                    }
                    else
                    {
                        $thissltstr .= ", ".$thiscol['Field'];
                    }
                }
            }
            //We should have a string in format of tax1,tax2,tax3,...,taxn
            //We can now check to see which one has a value.
            $sqltax = "SELECT $thissltstr FROM schedule_dates WHERE recno = ".$_SESSION['thisrecno'];
           // file_put_contents("./dodebug/debug.txt", "what is sqltax? = $sqltax \n", FILE_APPEND);
            $resulttax = $this->db->PDOminiquery($sqltax);
            foreach($resulttax as $rs)
            {
                for($i=0; $i<count($temparray); $i++)
                {
                    //file_put_contents("./dodebug/debug.txt", "what is temparray? = ".$rs[$temparray[$i]]." \n", FILE_APPEND);
                    if(!is_null($rs[$temparray[$i]]))
                    {
                        $thisarray[$temparray[$i]] = $rs[$temparray[$i]];
                    }
                }
            }
        }
        return($thisarray);
    }
}
