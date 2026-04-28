<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class PromotionClass {
    private $db = null;
    private $recnostr = ""; //1,2,3,....,n
    private $totalcost = 0;
    private $returnarray = [];
    private $thistotal = 0;
    private $thispromocal = [];
    private $result = [];
    private $caltotal = 0;
    private $state = "";
    private $pr_recno = "";
    private $recno = 0;
    private $discount = 0;
    private $promo = 0;
    private $bigtotal = 0;
    
    function SetPromo($db, $temprecnostr, $temptotal)
    {
        //If we want to recalculate with the prepay, we have to pass it, otherwise it will only calculate the appropriate tax.
        $this->db = $db; //passing the db connection in from the call
        $this->recnostr = $temprecnostr;
        $this->totalcost = $temptotal;
    }
    function GetPromotion()
    {
        //Will return an array of info on the appropriate promoions in format of array('recno|name|val|prom', ....)
        //recno - the recno of the table in events
        //name - the name of the promotion or discount
        //val - is the calculated discount in decimal value
        //discount - is either in percent or in dollar, if it is in dollar, we use NA because we don't need to cal, percent
        $sql = "SELECT * FROM events WHERE recno IN($this->recnostr)";
        $this->result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                $tempstr = $rs['recno'].'|'.$rs['special_event'];
                if($rs['isDollar'] == true)
                {
                    $tempstr .= "|$".$rs['discount']."|$".$rs['discount'];//recno|name|NA|discount
                }
                else
                {
                    $thiscal = number_format(($this->totalcost * ($rs['discount']/100)),2);
                    $tempstr .= "|$$thiscal|".$rs['discount']."%";//recno|name|$thiscal|discount
                }
                $this->returnarray[$rs['special_event']] = $tempstr;
            }
            return($this->returnarray);
        }
        else
        {
            return('No Data');
        }
    }
    function GetPromotiontotal()
    {
        //$this->recnostr = $temprecnostr;
        //$this->totalcost = $temptotal;
        $realpromocost = 0;
        $sql = "SELECT * FROM events WHERE isActive = true";
        $this->result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                $exploderecnostr = explode(",", $this->recnostr); //$exploderecnostr is an array that has [1,2,3,...,n];
                
                if(in_array($rs['recno'], $exploderecnostr))
                {
                    if($rs['isDollar'] == true)
                    {
                        $realpromocost += $rs['discount'];
                    }
                    else
                    {
                        $realpromocost += number_format(($this->totalcost * ($rs['discount']/100)),2);
                    }
                }    
            }
            return($this->thistotal = $realpromocost);
        }
        else
        {
            return('No Data');
        }
    }
    function SetUpdatepromo($tempdb, $temppromo, $temptotal)
    {
        $this->db = $tempdb;
        $this->thispromo = $temppromo;
        $this->thistotal = $temptotal;
    }
    function UpdatePromo()
    {
        $realtotal = $this->GetBigpaymenttotal();
        $thistable = "schedule_dates";
        
        $thisdata = [
            'total' => $realtotal, 
            "bigtotal" => $realtotal,
            "discount" => ($this->thispromo == 0 ? NULL : $this->thispromo)
            ]; 
        $thiswhere = ["recno" => $this->recno];
        $this->db->PDOUpdate($thistable, $thisdata, $thiswhere);
        //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal totalb4 =  $totalbefore  \n", FILE_APPEND);
        $thisdata1 = [
            'total_time' => $this->thistime, 
            'cost' => "$".number_format($this->thistotal,2),   
            "discount" => ($this->thispromo == 0 ? 0 : "$".number_format($this->thispromo,2)),  
            "tip" => ($this->thisfedtiptax == 0 ? 0 : "$".number_format($this->thisfedtiptax,2)." (".$this->thisfedtiptax_per.")"), 
            "fedtax_per" => ($this->thisfed != NULL ? "$".$this->thisfed. " (".$this->thisfed_per."%)" : ''),
            "statetax_per" => ($this->thisstate != NULL ? "$".$this->thisstate. " (".$this->thisstate_per."%)" : ''),
            "countytax_per" => ($this->thiscounty != NULL ? "$".$this->thiscounty. " (".$this->thiscounty_per."%)" : ''),
            "citytax_per" => ($this->thiscity != NULL ? "$".$this->thiscity. " (".$this->thiscity_per."%)" : ''),
            "bigtotal" => "$".number_format($realtotal, 2)
            ];
        
        return($thisdata1);
    }
    function GetPromotionwithprepay()
    {
        $sql = "SELECT * FROM events WHERE isActive = true";
        $this->result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                $tempstr = "";
                $exploderecnostr = explode(",", $this->recnostr); //$exploderecnostr is an array that has [1,2,3,...,n];
                
                if(in_array($rs['recno'], $exploderecnostr))
                {
                    $tempstr = $rs['recno'].'|'.$rs['special_event'];
                    if($rs['isDollar'] == true)
                    {
                        $tempstr .= "|NA|$".$rs['discount'];//recno|name|NA|discount
                    }
                    else
                    {
                        $thiscal = number_format(($this->totalcost * ($rs['discount']/100)),2);
                        $tempstr .= "|$$thiscal|".$rs['discount']."%";//recno|name|$thiscal|discount
                    }
                }    
                $this->returnarray[$rs['special_event']] = $tempstr;
                
                //IF prepay is NULL, that means we only want the normal discount, otherwise, we want to add prepay manually here
                if($rs['special_event'] == "Prepay")
                {
                    $tempstr = $rs['recno'].'|'.$rs['special_event'];
                    if($rs['isDollar'] == true)
                    {
                        $tempstr .= "|NA|$".$rs['discount']; 
                    }
                    else
                    {
                        $thiscal = number_format(($this->totalcost * ($rs['discount']/100)),2);
                        $tempstr .= "|$$thiscal|".$rs['discount']."%";
                    }
                }
                $this->returnarray[$rs['special_event']] = $tempstr;
            }
            return($this->returnarray);
        }
        else
        {
            return('No Data');
        }
    }
    function GetPromotiontotalwithprepay()
    {
        $sql = "SELECT * FROM events WHERE isActive = true";
        $realpromocost = 0;
        $this->result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                $tempstr = "";
                $exploderecnostr = explode(",", $this->recnostr); //$exploderecnostr is an array that has [1,2,3,...,n];
                
                if(in_array($rs['recno'], $exploderecnostr))
                {
                    if($rs['isDollar'] == true)
                    {
                        $realpromocost += $rs['discount'];
                    }
                    else
                    {
                        $realpromocost += number_format(($this->totalcost * ($rs['discount']/100)),2);
                    }
                }    
                //IF prepay is NULL, that means we only want the normal promo, otherwise, we want to add prepay manually here
                if($rs['name'] == "Prepay")
                {
                    if($rs['isDollar'] == true)
                    {
                        $realpromocost += $rs['discount'];
                    }
                    else
                    {
                        $realpromocost += number_format(($this->totalcost * ($rs['discount']/100)),2);
                    }
                }
                $this->returnarray[$rs['special_event']] = $tempstr;
            }
            return($this->thistotal = $realpromocost);
            
        }
        else
        {
            return('No Data');
        }
    }
    function SetAnalysromotions($db, $temptotal, $tempdate, $tempstate, $tempauto, $tempcombo, $tempstatus)
    {
        $this->db = $db;
        $this->caltotal = $temptotal;
        $this->date = $tempdate;
        $this->state = $tempstate;
        $this->isAuto = $tempauto; //We want to get ONLY the promo that has isAuto true.
        $this->isCombo = $tempcombo;
        $this->status = $tempstatus; //1, 2, or 3
        /*
        1 - recno str only
        2 - promo string only
        3 - we want both
        */
    }
    function AnalysPromotions()
    {
        $temppromocal = "";
        $temppromorecno = "";
        $tempcur_yr = date("Y");
        $sqlpromo = "SELECT * FROM events WHERE isDeleted = false AND isActive = true ";
        if($this->isAuto == true)
        {
            $sqlpromo .= "AND isAuto = true ";
        }
        if($this->isCombo == true)
        {
            $sqlpromo .= "AND isCombo = true ";
        }
        $sqlpromo .= "ORDER BY discount DESC";
        //file_put_contents("./dodebug/debug.txt", "AnalysPromotions sql =  $sqlpromo  \n", FILE_APPEND);
        $resultpromo = $this->db->PDOMiniquery($sqlpromo);
        if($this->db->PDORowcount($resultpromo) > 0)
        {
            foreach($resultpromo as $rspromo)
            {
                if(is_null($rspromo['date_start']))
                {
                    //Since we ORDER BY promo and DESC, we will get the highest promo dollar value for the customer.  However, after this first one, we will check to see if this one
                    //is able to combine with the others first.  If it can, then the following ones will need to be checked or skipped.

                    //If the dates are NULL, that means the discounts is valid and mabe either a Promotion with coninuous status
                    //The first record, regardless of the isCombo status, we will process it.
                    
                    $this->BuildPromostr($rspromo['isDollar'], $rspromo['discount'], $rspromo['isCombo'], $rspromo['recno'], $temppromocal, $temppromorecno, $this->caltotal, $this->status);
                }
                else
                {
                    if($rspromo['event_restriction'] == "Repeats")
                    {
                        //Will be valid every year when this date $this->date is between this time date_start and end_date, so we need to check the date just for the day and month, yr not needed
                        //$tempstr = date('m-d', strtotime(($this->date)))." >= ".date('m-d', strtotime($rspromo['date_start']))." && ".date('m-d', strtotime(($this->date)))." <= ".date('m-d', strtotime($rspromo['date_start']));
                        //file_put_contents("./dodebug/debug.txt", "Repeaters  =  $tempstr  \n", FILE_APPEND);
                        //file_put_contents("./dodebug/debug.txt", $this->date." >= ".date("m-d-$tempcur_yr", strtotime($rspromo['date_start']))." && ".$this->date." <= ".date("m-d-$tempcur_yr", strtotime($rspromo['date_start'])), FILE_APPEND);
                        //if(strtotime(date('m-d', strtotime(($this->date)))) >= strtotime(date('m-d', strtotime($rspromo['date_start']))) && strtotime(date('m-d', strtotime(($this->date)))) <= strtotime(date('m-d', strtotime($rspromo['date_start']))))
                        if(strtotime(($this->date >= date("m-d-$tempcur_yr", strtotime($rspromo['date_start'])))) && strtotime(($this->date <= strtotime(date("m-d-$tempcur_yr", strtotime($rspromo['date_start']))))))
                        {
                            //file_put_contents('./dodebug/debug.txt', "Not supposd to be here", FILE_APPEND);
                            $this->BuildPromostr($rspromo['isDollar'], $rspromo['discount'], $rspromo['isCombo'], $rspromo['recno'], $temppromocal, $temppromorecno, $this->caltotal, $this->status);
                        }
                    }
                    else if($rspromo['event_restriction'] == "Limited")
                    {
                        //Will be valid for certain dates only, check fo the actual dates
                        if(date('Y-m-d', strtotime(($this->date))) >= date('Y-m-d', strtotime($rspromo['date_start'])) && date('Y-m-d', strtotime(($this->date))) <= date('Y-m-d', strtotime($rspromo['date_start'])))
                        {
                            $this->BuildPromostr($rspromo['isDollar'], $rspromo['discount'], $rspromo['isCombo'], $rspromo['recno'], $temppromocal, $temppromorecno, $this->caltotal, $this->status);
                        }
                    }
                }
 
            }
            $this->thispromocal['thispromostr'] = $temppromocal;
            $this->thispromocal['thispromorecnostr'] = $temppromorecno;
            return($this->thispromocal);
        }
    }
    function BuildPromostr($tempdollar, $tempdiscount, $tempCombo, $temprecno, &$temppromocal, &$temppromorecno, $tempcaltotal, $tempstatus)
    {
        
        /*
        $tempcal
        1 - recno str only
        2 - promo string only
        3 - we want both
        */
        if($temppromocal == "" && $temppromorecno == "")
        {
            if($tempstatus == 1 || $tempstatus == 3)
            {
                $temppromorecno = $temprecno;
            }
            if($tempstatus == 2 || $tempstatus == 3)
            {
                if($tempdollar == true)
                {
                    $temppromocal = "$".$tempdiscount;
                }
                else
                {
                    $temppromocal = $tempdiscount."%";
                }
            }
        }
        else
        {
            //If we are in the else, that means we are handling the 2nd+ record so we need to make sure it is able to combine
            //if($tempCombo == true)
            //{
                if($tempstatus == 1 || $tempstatus == 3)
                {
                    $temppromorecno .= ",".$temprecno;
                }
                if($tempstatus == 2 || $tempstatus == 3)
                {
                    if($tempdollar == true)
                    {
                        $temppromocal .= ",$".$tempdiscount;
                    }
                    else
                    {
                        $temppromocal .= ",".$tempdiscount."%";
                    }
                }
            //}
        }
    }
    function SetPromoupdate($tempdb, $temprecno, $temptotal, $tempdiscount, $tempimplode_temppr_recno)
    {
        //$db, $thisrecno, $temptotal, $thispromo
        $this->db = $tempdb;
        $this->recno = $temprecno;
        $this->total = $temptotal;
        $this->discount = ($tempdiscount == 0 ? NULL : $tempdiscount);
        $this->recnostr = ($tempimplode_temppr_recno == "" ? NULL : $tempimplode_temppr_recno);
    }
    function GetPromoupdate()
    {
        $thistable = "schedule_dates";
        
        $thisdata = [
            'total' => $this->total, 
            "bigtotal" => $this->total,
            "discount" => $this->discount,
            "pr_recno" => $this->recnostr
            ]; 
        $thiswhere = ["recno" => $this->recno];
        $result = $this->db->PDOUpdate($thistable, $thisdata, $thiswhere);
        return($result);
    }
    function SetThisdiscount($tempdb, $temppr_recno, $tempcost)
    {
        $this->db = $tempdb;
        $this->pr_recno = $temppr_recno;
        $this->thistotal = $tempcost;
    }
    function GetThisdiscount()
    {
        //returns the total of promo
        $realpromocost = 0;
        $sql = "SELECT * FROM events WHERE recno IN($this->pr_recno)";
        $this->result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($this->result) > 0)
        {
            foreach($this->result as $rs)
            {
                if($rs['isDollar'] == true)
                {
                    $realpromocost += $rs['discount'];
                }
                else
                {
                    $realpromocost += number_format(($this->thistotal * ($rs['discount']/100)),2);
                }  
            }
            return($this->thistotal = $realpromocost);
        }
        else
        {
            return('No Data');
        }
    
    }
    function SetCalnewpromo($db, $thisrecno)
    {
        $this->db = $db;
        $this->recno = $thisrecno;
    }
    function GetNewpromodata()
    {
        //We come in here from Adding a discount to the service.  We have to recalculate the total.
        $sql = "SELECT * FROM schedule_dates WHERE recno = $this->recno";
        $result = $this->db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $temparray['pr_recno'] = $rs['pr_recno'];  //Just want to get the events recno in format of 1,2,3...
            $temparray['cost'] = $rs['cost'];
            $temparray['total'] = $rs['total'];
            $temparray['bigtotal'] = $rs['bigtotal'];
            $temparray['discount'] = $rs['discount'];
        }
        return($temparray);
    }
    function SetProcaltotal($db, $thisrecno, $thispromo, $thisdiscount, $thistotal, $thisbigtotal, $thisstatus)
    {
        $this->db = $db;
        $this->recno = $thisrecno;
        $this->promo = $thispromo;
        $this->discount = $thisdiscount;
        $this->total = $thistotal;
        $this->bigtotal = $thisbigtotal;
        $this->status = $thisstatus;
    }
    function UpdateProcaltotal()
    {
        $thistable = "schedule_dates";
        if($this->status == "Add")
        {
            $thisdata = [
                'total' => number_format(($this->total - $this->promo), 2), 
                'bigtotal' => number_format(($this->bigtotal - $this->promo), 2), 
                'discount' => number_format($this->discount, 2)];
        }
        else
        {
            //$this->status == 'Remove'
            $thisdata = [
                'total' => number_format(($this->total + $this->promo), 2), 
                'bigtotal' => number_format(($this->bigtotal + $this->promo), 2), 
                'discount' => number_format($this->discount, 2)];
        }
        $thiswhere = ["recno" => $this->recno];
        $result = $this->db->PDOUpdate($thistable, $thisdata, $thiswhere);
        if($result == "Success")
        {
            return($result);
        }
        else
        {
            return('Failed to update');
        }
    }
    function SetPromoreturns($db, $pr, $thisrecno)
    {
        $this->db = $db;
        $this->pr = $pr;
        $this->recno = $thisrecno;
    }
    function GetPromoreturns()
    {
        $sql = "SELECT * FROM schedule_dates WHERE recno = $this->recno";
        $result = $this->db->PDOMiniquery($sql);
        foreach($result as $rs)
        {

            $total = $rs['total'];
            $bigtotal = $rs['bigtotal'];
            $total_time = $rs['total_time'];
            $cost = $rs['cost'];
            $discount = $rs['discount'];
        }
        $returnarray = [
        'total_time' => $this->pr->ConvertMinToHour($total_time), 
        'cost' => "$".number_format($cost,2),   
        "discount" => ($discount == NULL ? 0 : "$".number_format($discount,2)),            
        "bigtotal" => "$".number_format($bigtotal, 2)
        ];
        return($returnarray);
    }
    function SetAddpromo($thisrecno, $thispromo, $thisdiscount, $thisbigtotal)
    {
        $this->recno = $thisrecno;
        $this->promo = $thispromo;
        $this->discount = $thisdiscount;
        $this->bigtotal = $thisbigtotal;
    }
    function GetAddpromo()
    {
        $returnarray = [ 'bigtotal' => number_format(($this->bigtotal - $this->promo), 2), 
                        'discount' => number_format(($this->discount + $this->promo), 2)];
        return($returnarray);
    }
}
