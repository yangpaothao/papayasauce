<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class MathClass {
    private $thisrecno = 0;
    private $thistotal = ""; //The cost of the service before tax and promotion in float, ex: 20.25
    private $thispromo = "";
    private $thiscaltotal = "";
    private $db = "";
    private $temptotal = 0;
    function SetPaymenttotal($tempthispromocal, $tempthistotal)
    {
        $this->thispromo = $tempthispromocal;
        $this->thistotal = $tempthistotal;
    }
    function GetPaymenttotal()
    {
        $totalfortaxing = $this->thistotal - $this->thispromo;
        return($totalfortaxing);   
    }
    function GetBigpaymenttotal()
    {
        $totalfortaxing = $this->thistotal - $this->thispromo + $this->thistotaltip;
        return($totalfortaxing);   
    }
    function SetPaymenttotalupdate($tempdb, $temprecno, $temptotaltip, $temptotaltime, $tempthispromocal, $tempthistotal)
    {
        $this->db = $tempdb;
        $this->recno = $temprecno;
        $this->thistotaltip = $temptotaltip;        
        $this->thistime = $temptotaltime;
        $this->thispromo = $tempthispromocal;
        $this->thistotal = $tempthistotal;
    }
    function UpdatePaymenttotal()
    {
        $realtotal = $this->GetBigpaymenttotal();
        $thistable = "schedule_dates";
        
        $thisdata = [
            'cost' => $this->thistotal, 
            'total_time' => $this->thistime, 
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
            "bigtotal" => "$".number_format($realtotal, 2)
            ];
        
        return($thisdata1);
    }
    function CalculatePromos($thistotal, $thispromostr)
    {
        //$thistotal is default to zero,
        //if $thistotal is 0, that means we will return the promotional dillar value only.
        //If $thistoal has a value, we will return the promo, NOT the total + promo.
        //$thisarray is contain the promo.  Ex: array(1,2,3,4) where the 1,2,3,4 is recno from table discounts
        $realtotal = 0;
        $explodepromo = explode(",", $thispromostr);
        for($i=0; $i<count($explodepromo); $i++)
        {
            $thispromotype = substr($explodepromo[$i], -1);
            if($thispromotype != "%")
            {
                //If it is not %, that means we are discounting a dollar amount
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo $1 =  ".(number_format(floatval(ltrim($thispromotype, '$')),2))."  \n", FILE_APPEND);
                $realtotal +=  (number_format(floatval(ltrim($thispromotype, '$')),2)); //$5, we will remove the $ and converted to number and subtract from the total
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo $2 =  $thistotal  \n", FILE_APPEND);
            }
            else
            {
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo % expected".$thistotal." - ".rtrim($explodepromo[$i], '%')."  \n", FILE_APPEND);
                $realtotal += ($thistotal * (number_format(floatval(rtrim($explodepromo[$i], '%')),2)/100));  //We will remove the % and then converted to number and added to the tax
            }
        }
        return($realtotal);
    }
    function SetCalpromo($temppromostr, $temptotal)
    {
        $this->promostr = $temppromostr;
        $this->taxtotal = $temptotal;
    }
    function GetCalpromo()
    {
        //returns only the promotion amount.
        $realtotal = 0;
        $explodepromo = explode(",", $this->promostr);
        
        //file_put_contents("./dodebug/debug.txt", "tax str =  ".$this->taxstr."\n", FILE_APPEND);
        //file_put_contents("./dodebug/debug.txt", "tax total =  ".$this->taxtotal."\n", FILE_APPEND);
                
        for($i=0; $i<count($explodepromo); $i++)
        {
            $thispromotype = substr($explodepromo[$i], -1);
            if($thispromotype != "%")
            {
                //If it is not %, that means we are discounting a dollar amount
                //file_put_contents("./dodebug/debug.txt", "expecting 5 =  ".floatval(ltrim($explodepromo[$i], '&'))."  \n", FILE_APPEND);
                $realtotal +=  floatval(ltrim($explodepromo[$i], '$')); //$5, we will remove the $ and converted to number and subtract from the total
                //file_put_contents("./dodebug/debug.txt", "realtotal In Dollar =  $realtotal  \n", FILE_APPEND);
            }
            else
            {
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo % expected".$thistotal." - ".rtrim($explodepromo[$i], '%')."  \n", FILE_APPEND);
                $realtotal += ($this->taxtotal * (floatval(rtrim($explodepromo[$i]))/100));  //We will remove the % and then converted to number and added to the tax
                //file_put_contents("./dodebug/debug.txt", "realtotal In Percentage =  $realtotal  \n", FILE_APPEND);
            }
        }
        return(number_format($realtotal, 2));
    }    
}
