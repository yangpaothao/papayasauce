<?php
namespace PapayasauceClasses;
use Square\SquareClient;
use Square\Environments;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
use Square\Customers\Requests\CreateCustomerRequest;
use Square\Types\Address;
use Square\Types\Country;
use Square\Types\CustomerQuery;
use Square\Types\CustomerFilter;
use Square\Types\CustomerTextFilter;
use Square\Types\CustomerCreationSourceFilter;
use Square\Types\CustomerCreationSource;
use Square\Types\CustomerInclusionExclusion;
use Square\Types\TimeRange;
use Square\Types\FilterValue;
use Square\Types\CustomerSort;
use Square\Types\CustomerSortField;
use Square\Types\SortOrder;
use Square\Customers\Requests\SearchCustomersRequest;
use Square\Customers\Requests\GetCustomersRequest;
use Square\Refunds\Requests\RefundPaymentRequest;
use Square\Payments\Requests\GetPaymentsRequest;
use Square\Payments\Requests\CancelPaymentsRequest;
use Square\OAuth\Requests\ObtainTokenRequest;
use Square\Exceptions\ApiException;
use Square\OAuth\Requests\RevokeTokenRequest;
use Square\Cards\Requests\CreateCardRequest;
use Square\Types\Card;

//https://developer.squareup.com/docs/devtools/sandbox/payments

//https://github.com/square/connect-php-sdk/blob/master/docs/Model/CreateCustomerRequest.md
//Square Developer
//https://developer.squareup.com/reference/square/customers-api/create-customer
class PromptClass 
{
    //PDOQuery($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null, $ons=null, $distinct=null)
    private $db = null;
    private $thisarray = [];
    private $isDebug = true;

    private function GetDBcon()
    {
        return new PDOCON();  //Return a connection
    }
    function SltDiscount($thispromo)
    {
        $this->thisarray = [];
        $thistable = "events";
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $sql = "SELECT recno, special_event, discount, isDollar FROM $thistable WHERE isactive = true AND isdeleted = false ";
        if($thispromo != "ALL")
        {
            $sql .= "AND recno IN($thispromo)";
        }
        $result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($result) > 0)
        {
            $tempname = "";
            $tempdollar = "";
            if(!is_null($result))
            {
                foreach($result as $rs)
                {
                    //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
                    $tempdollar = "$".number_format($rs['discount'], 2);
                    if($rs['isDollar'] == false)
                    {
                        $tempdollar = number_format($rs['discount'], 2)."%";
                    }
                    $this->thisarray[$rs['recno']]= $rs['special_event']." ".$tempdollar;
                    //$this->thisarray is now a multi array
                }
            }
        }
        return($this);
    }
    function SltService()
    {
        $this->thisarray = [];
        $thistable = "service";
        $thisfields = array("recno", "title", "time", "price");
        $thiswheres = array("isactive" => true, "isdeleted" => false);
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswheres);
        $tempname = "";
        if(!is_null($result))
        {
            foreach($result as $rs)
            {
                //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
                $this->thisarray[$rs['recno']]= $rs['title']." ".$rs['time']."mins/$".number_format($rs['price'], 2);
                //$this->thisarray is now a multi array
            }
        }
        return($this);
    }
    function SltCategory($db)
    {
        $this->thisarray = [];
        $thistable = "category";
        $thisfields = array("recno", "name");
        $thiswheres = array("isActive" => true);
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
        $tempname = "";
        if(!is_null($result))
        {
            foreach($result as $rs)
            {
                //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
                $this->thisarray[$rs['recno']]= $rs['name'];
                //$this->thisarray is now a multi array
            }
        }
        return($this);
    }
    function CalculateTwotimes($starttime, $endtime)
    {
        //$startime and $endtime can come in as AM and PM
        //This function takes two times in time format hh:mm or in minutes and calculated the total time and return it.
        //Example, 12:00 - 11:00 = 1:00 hour time will return.
        //file_put_contents("./dodebug/debug.txt", 'Calculate Two Times => starttime: '.$starttime.' AND endtime: '.$endtime.'\n', FILE_APPEND);
  
        $tempstart = $starttime;
        $tempend = $endtime;
        if(strpos($starttime, ":") !== false)
        {
            //If this string contain ':', we have incoming as 12:12, if not, we will have time in minutes.
            $tempstart = $this->ConvertHourToMinute($starttime);
        }
        if(strpos($endtime, ":") !== false)
        {
            //If this string contain ':', we have incoming as 12:12, if not, we will have time in minutes.
            $tempend = $this->ConvertHourToMinute($endtime);
        }
        //At this point, $tempstart and $tempend will have time in minutes, for ex: 120;
        //file_put_contents("./dodebug/debug.txt", 'Calculate Two Times => starttime: '.$tempstart.' AND endtime: '.$tempend.'\n', FILE_APPEND);
        $totalminutes = $tempend-$tempstart;
        //file_put_contents("./dodebug/debug.txt", 'Calculate Two Times => totalminutes: '.$totalminutes."\n", FILE_APPEND);
        $realtime = $this->ConvertMinToHour($totalminutes);
        //file_put_contents("./dodebug/debug.txt", 'Calculate Two Times => realtime: '.$realtime, FILE_APPEND);
        
        /*
        $newstart = new DateTime($starttime);
        $newend = new DateTime($endtime);
        $newtime = $newstart -> diff($newend);
        $newtime = $newtime -> format('%h:%i');
        //file_put_contents("./dodebug/debug.txt", 'new time: '.date("h:i", strtotime($newtime)).'\n', FILE_APPEND);
        
         */
        return($realtime);
    }
    /*
    function ConvertMinToHr($thishour)
    {
        //file_put_contents("./dodebug/debug.txt", 'ConvertMinToHr => realtime: '.date('h:m', strtotime($thishour)), FILE_APPEND);
        return(date('h:m', strtotime($thishour)));
    }*/
    function ConvertHourToMinute($thishour)
    {
        if(strpos($thishour, "PM") !== false)
        {
            $thishour = date("H:i", strtotime($thishour));
        }
        if(strpos($thishour, ":") !== false)
        {
            $xplodenumb = explode(":", $thishour);

            return((60 * (int)$xplodenumb[0]) + (int)$xplodenumb[1]);
        }
        else
        {
            return($thishour * 60);
        }
        
    }
    function ConvertMinToHour($thisminutes)
    {
        //return(intdiv($thisminutes, 60).":".($thisminutes % 60));
        if($thisminutes < 60)
        {
            return("00:$thisminutes");
        }
        else
        {
            return(date('h:i', strtotime(intdiv($thisminutes, 60).":".($thisminutes % 60))));
        }
    }
    function sltEvents($eventtype = NULL)
    {
        $this->thisarray = [];
        //if $thisvaslue has no parameter coming in then we assume default to recno as the KEY, ex key = value
        //and if there is a parameter, it has to be a field that user wants to use as value.    //value = value,
        //file_put_contents("./dodebug/debug.txt", 'sltevents sql: Here? \n', FILE_APPEND);
        $thistable = "current_events";

        $thistable = "event_type";
        $thisfields = array("recno","event_type");
        $thiswheres = array("isDeleted" => false);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswheres);
        
        $tempkey = $eventtype;
        $sql = "SELECT recno, event_type FROM $thistable WHERE isDeleted = false";
        file_put_contents("./dodebug/debug.txt", 'sltevents sql: '.$sql, FILE_APPEND);
        $result = $this->db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $tempname = $rs[$eventtype];
            if(is_null($eventtype))
            {
                $tempkey = 'recno';
            }
            //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
            $this->thisarray[$rs[$tempkey]]= $tempname;
            //$this->thisarray is now a multi array
        }
        return($this);
    }
    function SltCustomer($thisvalue=null)
    {
        //if $thisvaslue has no parameter coming in then we assume default to recno as the KEY, ex key = value
        //and if there is a parameter, it has to be a field that user wants to use as value.    //value = value,
        
        $this->thisarray = [];
        $thistable = "customer_master";
        $thisfields = array("recno", "customer");
        $thiswheres = array("iscargo" => true, "isactive" => true);
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswheres, array('customer'), null, null, null, 'DISTINCT');
        $tempname = "";
        $tempkey = $thisvalue;
        if(!is_null($result))
        {
            foreach($result as $rs)
            {
                $tempname = $rs['customer'];
                if(is_null($thisvalue))
                {
                    $tempkey = 'recno';
                }
                //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
                $this->thisarray[$rs[$tempkey]]= $tempname;
                //$this->thisarray is now a multi array
            }
        }
        return($this);
    }
    function JSONEncode($data)
    {
        return(json_encode($data, true));
    }
    function JSONDecode($data)
    {
        return(json_decode($data));
    }
    function GetSelect($thisid, $thisdefault, $isrequired, $ismultiple, $thisonchange="", $isshown = true, $isdisable = false, $isdummy = false, $defaultslt = "Select")
    {
        //string - $thisid - will be the id of this element
        //      if it has more than 1 item in this array, that means we want to combined the values into one string, firstg ex: string1 + string2 + string3 = string1string2string3.
        //      if there is only 1 item in this array, that means we will juse ust the value of this field as is for the text.
        //string - $thisdefault - is if there is a default select that user wants, it can be a string fo something or "", empty.
        //boolen - $isrequired - if this feild is required.
        //string - $thisonchange - the onchange function in format of nameoffunction(parameter1, parameter2,......,paramtern)
        //$isdummy - will include the default select
        
        $tempquired = "";
        $temponchange = "";
        $tempmultiple = "";
        $tempdisabled = "";
        $ishowing = "";
        $tempdefault = array();
        if($ismultiple == true)
        {
            $tempmultiple = "multiple='multiple'";
        }
        if($thisonchange != "")
        {
            $temponchange = "onchange='$thisonchange'";
        }
        if($isrequired == true)
        {
            $tempquired = 'required';
        }
        if($thisdefault != "")
        {
            $tempdefault = explode(',', $thisdefault);
        }
        if($isshown === false)
        {
            $tempdisabled = 'disabled';
        }
        if($isdisable === true)
        {
            $tempdisabled = 'disabled';
        }?>
        <select class="promp-select2 <?=$tempquired?>" style="width: 100%; height: 100%; white-space: nowrap; <?php echo $ishowing ?>" id="<?=$thisid?>" name="<?=$thisid?>"  <?=$temponchange?> <?=$tempmultiple?> <?=$tempdisabled?>><?php
            if($isdummy === true)
            {?>
                <option value="Select"><?php echo $defaultslt ?></option><?php
            }
            foreach($this->thisarray as $key => $value)
            {
                $tempselect = "";
                if(in_array($key, $tempdefault))
                {
                    $tempselect = "selected";
                }?>
                <option value="<?=$key?>" <?=$tempselect?>><?=$value?></option><?php
            }?>
        </select><?php
    }
    function GetString($thisdefault)
    {
        //string - $thisid - will be the id of this element
        //      if it has more than 1 item in this array, that means we want to combined the values into one string, firstg ex: string1 + string2 + string3 = string1string2string3.
        //      if there is only 1 item in this array, that means we will juse ust the value of this field as is for the text.
        //string - $thisdefault - is if there is a default select that user wants, it can be a string fo something or "", empty.
        //boolen - $isrequired - if this feild is required.
        //string - $thisonchange - the onchange function in format of nameoffunction(parameter1, parameter2,......,paramtern)
        $tempdefault = [];
        if($thisdefault != "")
        {
            $tempdefault = explode(',', $thisdefault);
        }
        $tempname = "";
        foreach($this->thisarray as $key => $value)
        {
            $tempselect = "";
            if(in_array($key, $tempdefault))
            {
                if($tempname == "")
                {
                    $tempname = "$value";
                }
                else
                {
                    $tempname .= ", $value";
                }
            }
        }
        echo $tempname;
    }
    function GetStates($thisstate)
    {
        $statearray = [
                        'Alaska' => 'AK', 
                        'Arkansas' => 'AR',
                        'American Samoa' => 'AS',
                        'California' => 'CA',
                        'Colorado' => 'CO',
                        'Connecticut' => 'CT',
                        'District of Columbia' => 'DC',
                        'Georgia' => 'GA',
                        'Florida' => 'FL',
                        'Guam' => 'GU',
                        'Hawaii' => 'HI',
                        'Iowa' => 'IA',
                        'Idaho' => 'ID',
                        'Illinois' => 'IL',
                        'Indiana' => 'IN',
                        'Kansas' => 'KS',
                        'Kentucky' => 'KY',
                        'Louisiana' => 'LA',
                        'Massachusetts' => 'MA',
                        'Maryland' => 'MD',
                        'Maine' => 'ME',
                        'Michigan' => 'MI',
                        'Minnesota' => 'MN',
                        'Missouri' => 'MO',
                        'Mississippi' => 'MS',
                        'Montana' => 'MT',
                        'North Carolina' => 'NC',
                        'North Dakota' => 'ND',
                        'New Hampshire' => 'NH',
                        'New Jersey' => 'NJ',
                        'New Mexico' => 'NM',
                        'Nevada' => 'NV',
                        'New York' => 'NY',
                        'Ohio' => 'OH',
                        'Oklahoma' => 'OK',
                        'Oregon' => 'OR',
                        'Pennsylvania' => 'PA',
                        'Puerto Rico' => 'PR',
                        'Rhode Island' => 'RI',
                        'South Carolina' => 'SC',
                        'South Dakota' => 'SD',
                        'Tennessee' => 'TN',
                        'Texas' => 'TX',
                        'Northern Mariana Islands' => 'MP',
                        'Utah' => 'UT',
                        'Virginia' => 'VA',
                        'Virgin Islands' => 'VI',
                        'Vermont' => 'VT',
                        'Washington' => 'WA',
                        'Wisconsin' => 'WI',
                        'West Virginia' => 'WV',
                        'Wyoming' => 'WY'];
        
    
        if(strlen($thisstate) > 2)
        {
            foreach($statearray as $tempstate => $tempabb){
                if(strtolower($tempstate) == strtolower($thisstate))
                {
                    //file_put_contents('./dodebug/debug.txt', " 1promp temp state: $tempabb \n", FILE_APPEND);
                    return($tempabb);
                }
            }
        }
        else
        {
            file_put_contents('./dodebug/debug.txt', "3promp temp state: not here \n", FILE_APPEND);
            if (in_array($thisstate, $statearray)) {
                return($thisstate);
            }
            else
            {
                return("Bad State.");
            }
        }
    }
    function CheckIfexist($thistable, $thisfields, $thiswhere)
    {
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswhere);

        if(!is_null($result))
        {
            //file_put_contents("./dodebug/debug.txt", "admin check: here \n", FILE_APPEND);
            return(true); //exists
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", "admin check: !here \n", FILE_APPEND);
            return(false); //does not exist
        }
    }
    function UploadFile($filepath, $thisfile, $thistable, $thisfield, $thisrecno, $thiscat = NULL, $from = NULL)
    {
        //$thisfile will come in as $_FILES["thisfile"], to be sure it will be countable below, in the declaration, make it a file
        //$filepath will be the path to the dir
        //$thistable will be the table we want to update the file name to
        //$thisfield is the field we will update with the file
        //$thisrecno is the recno of $thistable
        //$thiscat = if we are going to concat a string to the existing field
        
        $countfiles = count($thisfile['name']); 
        $strattachments = "";  
        $typeisgood = "";
        for($i=0;$i<$countfiles;$i++)
        {
            $filename = $thisfile['name'][$i];
            
            $pdfMimearray = array('application/pdf', 'application/doc', 'application/docx');
            $thismime = mime_content_type($thisfile['tmp_name'][$i]);
            //file_put_contents("./dodebug/debug.txt", "this mine ".$thismime, FILE_APPEND);
            //Now that we have the $filename, we can check for the file type and size and all the goodies.
            $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
            $detectedType = exif_imagetype($thisfile['tmp_name'][$i]);
            if($from != 'Event' && $from != "company_info")
            {
                //We are from other pages so we can accept images, pdf, and doc, docx.
                if(in_array($detectedType, $allowedTypes) || in_array($thismime, $pdfMimearray))
                {
                    //We only get here if the file is PNG, JPEG, GIF, OR PDF
                   if($strattachments == "")
                    {
                        $strattachments = $filename;
                    }
                    else
                    {
                        $strattachments .= ",$filename";
                    }
                    //$thisresizeimage = imagecreatefromjpeg($_FILES['image']['tmp_name']);
                    move_uploaded_file($thisfile['tmp_name'][$i],"$filepath/$filename");
                }
                else
                {
                    $typeisgood = "BAD";
                }
            }
            else
            {
                //For from = Event and company_info, we can only accept PNG, JPEG, GIF, because we will display this images on the front page
                //and it requires images only.
                if(in_array($detectedType, $allowedTypes))
                {
                    //We only get here if the file is PNG, JPEG, GIF, OR PDF
                    //IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF
                    switch($detectedType)
                    {
                        case IMAGETYPE_PNG:
                            
                            break;
                        case IMAGETYPE_JPEG:
                            break;
                        case IMAGETYPE_GIF:
                            break;
                        default:
                            break;
                    }
                   if($strattachments == "")
                    {
                        $strattachments = $filename;
                    }
                    else
                    {
                        $strattachments .= ",$filename";
                    }
                    move_uploaded_file($thisfile['tmp_name'][$i],"$filepath/$filename");
                }
                else
                {
                    $typeisgood = "BAD";
                }
            }
        }
        if($typeisgood != "BAD")
        {
            if($from != "company_info")
            {
                $thisdata = array($thisfield => $strattachments);
                $thiswheres = array('recno' => $thisrecno);
                $result = $this->db->PDOUPDATE($thistable, $thisdata, $thiswheres, $thisrecno, $thiscat);
                //file_put_contents("../dodebug/debug.txt", "not here 1", FILE_APPEND);
                if($result)
                {
                    echo 'Success';
                }
                else
                {
                    echo 'Failed';
                }
            }
            else
            {
                //We only get here if we are from company_info, meaning we want to upload potential logos but we do not want to update the
                //table yet, we just want to upload the image into the system.  If we are here, that means we moved file successful.
                echo 'Success';
            }
        }
        else
        {
            echo "Bad file type.  File type must be PNG, JPEG, GIF, and OR PDF.";
        }
    }
    function PostIt($thispost)
    {
        file_put_contents("../dodebug/debug.txt", "dashboard special_event = ".$thispost['txtspecial_event']." \n", FILE_APPEND);
        //file_put_contents("./dodebug/debug.txt", "admin company = $thispost \n", FILE_APPEND);
        //PostIt is a function that will return an associative array with non-empty values and substring first 3 chars
        $thisdata = [];
        foreach($thispost as $key => $value)
        {
            //$key or the id name must start with somtehing like txt----, this function will get rid of the txt and the ---- will be the field in the table
            //file_put_contents("./dodebug/debug.txt", "admin company = $key == $value \n", FILE_APPEND);
            if($value != "" && $key != "cmd" && $key != 'thisrecno')
            {
                $thisfield = substr($key, 3);
                if(str_contains($thisfield, 'date'))
                {
                    $thisdata[$thisfield] = date('Y-m-d', strtotime($value));
                }
                else
                {
                    $thisdata[$thisfield] = $value;
                }
            }
        }
        return($thisdata);
    }
    function CheckAppointment($thisslot, $thisrecnos, $endtime)
    {
        //ophours is 480 for 8 hours of operation time in minutes, 480minutes
        //5:30 - 6:00 = 30
        $totaltime = 0;
        //file_put_contents("./dodebug/debug.txt", "promp checkapp slot = $thisslot \n", FILE_APPEND);
        $goodtime = $this->CalculateTwotimes($thisslot, $endtime);
        //file_put_contents("./dodebug/debug.txt", "promp checkapp goodtime = $goodtime \n", FILE_APPEND);
        $goodtimeinmin = $this->ConvertHourToMinute($goodtime);
        //Whatever we are going to calculate can't go pass $goodtime.
        //file_put_contents("./dodebug/debug.txt", "promp checkapp goodtimeinmin = $goodtimeinmin \n", FILE_APPEND);
        $sqlsvc = "SELECT * FROM service WHERE recno in ($thisrecnos)";
        $resultsvc = $this->db->PDOMiniquery($sqlsvc);           
        foreach($resultsvc as $rssvc)
        {
            $totaltime += $rssvc['time'];
        }
        //file_put_contents("./dodebug/debug.txt", "2 timeers compare = $totaltime > $goodtimeinmin \n", FILE_APPEND);
        if($totaltime > $goodtimeinmin)
        {
            return(false);
        }
    }
    function CheckTimeoff($bbrecno, $thisrecnos, $thisdate, $thisslotpm, $tempnoffdayarray, $tempnoffslotarray)
    {
        //$tempnoffdayarray -> array(1,2,3,...)
        //$tempnoffslotarray -> array('10:00', '1:00',...)
        
    }
    function ValidatePhonenumber($thisphonenumber)
    {
        if(strlen($thisphonenumber) != 10 && !is_numberic($thisphonenumber))
        {
            return(false);
        }
        else
        {
            return(true);
        }
    }
    function check_phonenumber($thisnumber)
    {
        //Now we want to check if this email already exist. We will get a record back if it exists.
        return("SELECT recno FROM users WHERE phone_number = '$thisnumber'");
    }
    function get_company_info()
    {
        
    }
    function CalculatePaymenttotal()
    {
        
    }
    function GetPromoval($promo)
    {
        $sql_promo = "SELECT * FROM events WHERE recno IN($promo)";
        $result_promo = $this->db->PDOMiniquery($sql_promo);
        $realpromo = "";
        foreach($result_promo as $rs_promo)
        {
            if($realpromo == "")
            {
                if($rs_promo['isDollar'] == true)
                {
                    $realpromo = "$".$rs_promo['promo'];
                }
                else
                {
                    $realpromo = $rs_promo['promo']."%";
                }
            }
            else
            {
                if($rs_promo['isDollar'] == true)
                {
                    $realpromo .= ", $".$rs_promo['promo'];
                }
                else
                {
                    $realpromo .= ", ".$rs_promo['promo']."%";
                }
            }
        }
        return($realpromo); //Will return a string of ex: $5,5%
    }
    /*
    function GetPaymenttotal($thisrecno, $fedtax, $fedtiptax, $statetax, $countytax, $citytax, $thistotal, $thispromocal, $caltotaltime)
    {
        //$thisrecno - recno for schedule_dates
        //$thispromocal - string in format of $5,5%, need to split and then use the number appropriately, may mostly be 1 number
        //$thistotal - this is the calculated total already, we just need to apply the taxes and events and then update to the total field in schedule_dates table
        //$realtax = $fedtax + $statetax + $countytax + $citytax;
        //$explodepromo = explode(",", $thispromocal);
        //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal total =  $thispromocal  \n", FILE_APPEND);
        $realtotal = 0;
        $tempuser = NULL;
        $actualfedtax = 0;
        $actualstatetax = 0;
        $actualcountytax = 0;
        $actualcitytax = 0;
        $totalbefore = $thistotal;
        $realtotal = $thistotal;
        $calpromo = 0;
        if($thispromocal != "")
        {
            $calpromo = number_format($this->CalculatePromos($realtotal, $thispromocal),2);//We want to apply the events before tax.
            $realtotal -= $calpromo; //Because we are giving events, we have to minus it from the base total.
        }
        $totalfortaxing = $realtotal;
        if($fedtax != NULL && $fedtax != 0)
        {
            $actualfedtax = number_format(($totalfortaxing*($fedtax/100)), 2);
            $realtotal += $actualfedtax;
        }
        if($statetax != NULL && $statetax != 0)
        {
            $actualstatetax = number_format(($totalfortaxing*($statetax/100)), 2);
            $realtotal += $actualstatetax;
        }
        if($countytax != NULL && $countytax != 0)
        {
            $actualcountytax = number_format(($totalfortaxing*($countytax/100)), 2);
            $realtotal += $actualcountytax;
        }
        if($citytax != NULL && $citytax != 0)
        {
            $actualcitytax = number_format(($totalfortaxing*($citytax/100)), 2);
            $realtotal += $actualcitytax;
        }
        //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal calpromo =  ".$calpromo."  \n", FILE_APPEND);
        /*
        for($i=0; $i<count($explodepromo); $i++)
        {
            $thispromotype = substr($explodepromo[$i], -1);
            if($thispromotype != "%")
            {
                //If it is not %, that means we are discounting a dollar amount
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo $1 =  ".(number_format(floatval(ltrim($thispromotype, '$')),2))."  \n", FILE_APPEND);
                $realtotal = $realtotal - (number_format(floatval(ltrim($thispromotype, '$')),2)); //$5, we will remove the $ and converted to number and subtract from the total
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo $2 =  $thistotal  \n", FILE_APPEND);
            }
            else
            {
                //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal promo % expected".$thistotal." - ".rtrim($explodepromo[$i], '%')."  \n", FILE_APPEND);
                $realtotal =  $realtotal - ($realtotal * (number_format(floatval(rtrim($explodepromo[$i], '%')),2)/100));  //We will remove the % and then converted to number and added to the tax
            }
        }
        //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal thistotal b4tax =  $thistotal  \n", FILE_APPEND);
        //WE APPLY THE events before we apply the tax on the next line!!!!
        $thistable = "schedule_dates";
        $thisdata = [
            'cost' => $totalbefore, 
            'total_time' => $caltotaltime, 
            'total' => $realtotal, 
            "bigtotal" => $realtotal,
            "fedtax_per" => $fedtax,
            "fedtax" => $actualfedtax,
            "statetax_per" => $statetax,
            "statetax" => $actualstatetax,
            "countytax_per" => $countytax,
            "countytax" => $actualcountytax,
            "citytax_per" => $citytax,
            "citytax" => $actualcitytax,
            "discount" => ($calpromo == 0 ? NULL : $calpromo)
            ]; 
        $thiswhere = ["recno" => $thisrecno];
        $this->db->PDOUpdate($thistable, $thisdata, $thiswhere);
        //file_put_contents("./dodebug/debug.txt", "GetPaymenttotal totalb4 =  $totalbefore  \n", FILE_APPEND);
        $thisdata1 = [
            'total_time' => $caltotaltime, 
            'cost' => "$".number_format($totalbefore,2),   
            "discount" => ($calpromo == 0 ? 0 : "$".number_format($calpromo,2)),            
            "fedtax_per" => $fedtax."% ($".$actualfedtax.")",
            "statetax_per" => $statetax."% ($".($actualstatetax == 0 ? 0 : $actualstatetax).")",
            "countytax_per" => $countytax."% ($".($actualcountytax == 0 ? 0 : $actualcountytax).")",
            "citytax_per" => $citytax."% ($".($actualcitytax == 0 ? 0 : $actualcitytax).")",
            "bigtotal" => "$".number_format($realtotal, 2)
            ];
        
        return($thisdata1);
    }*/
    function GetCalculatedtotal($from, $thistotal)
    {
        //$from = 'Booked'
        //$thistotal = is the total in tollar, ex: 100
        $realtaxarray = [];
        $actualfedtax = 0;
        $actualstatetax = 0;
        $actualcountytax = 0;
        $actualcitytax = 0;
        
        //We get the taxes from GetTax(state);
        $thistaxearray = $this->GetTax($_SESSION['OPERATING_STATE']);
        $realtotal = $thistotal;
        $totalfortaxing = $thistotal;
        if($thistaxearray['fedtax'] != NULL && $thistaxearray['fedtax'] != 0)
        {
            $actualfedtax = number_format(($totalfortaxing*($thistaxearray['fedtax']/100)), 2);
            $realtaxarray['fedtax'] = $actualfedtax." (".$thistaxearray['fedtax']."%)";
            $realtotal += $actualfedtax;
        }
        if($thistaxearray['statetax'] != NULL && $thistaxearray['statetax'] != 0)
        {
            $actualstatetax = number_format(($totalfortaxing*($thistaxearray['statetax']/100)), 2);
            $realtaxarray['statetax'] = $actualstatetax." (".$thistaxearray['statetax']."%)";
            $realtotal += $actualstatetax;
        }
        if($thistaxearray['countytax'] != NULL && $thistaxearray['countytax'] != 0)
        {
            $actualcountytax = number_format(($totalfortaxing*($thistaxearray['countytax']/100)), 2);
            $realtaxarray['countytax'] = $actualcountytax." (".$thistaxearray['countytax']."%)";
            $realtotal += $actualcountytax;
        }
        if($thistaxearray['citytax'] != NULL && $thistaxearray['citytax'] != 0)
        {
            $actualcitytax = number_format(($totalfortaxing*($thistaxearray['citytax']/100)), 2);
            $realtaxarray['citytax'] = $actualcitytax." (".$thistaxearray['citytax']."%)";
            $realtotal += $actualcitytax;
        }
        $realtaxarray['realtotal'] = $realtotal;
        //WE will return an array ['fedtax' => ####, 'statetax' => ......
        return($realtaxarray);
    }
    function GetTax($thisstateabbr)
    {
        $thisTaxarray = [];
        $sqltax = "SELECT * FROM taxme WHERE isActive = true AND state = '$thisstateabbr'";
        $resulttax = $this->db->PDOMiniquery($sqltax);
        if($this->db->PDORowcount($resulttax) > 0)
        {
            foreach($resulttax as $rstax)
            {
                $thisTaxarray['fedtax'] = $rstax['federal_tax'];
                $thisTaxarray['statetax'] = $rstax['state_tax'];
                $thisTaxarray['countytax'] = $rstax['county_tax'];
                $thisTaxarray['citytax'] = $rstax['city_tax']; 
            }
        }
        return($thisTaxarray);
    }
    function CalculateTax($thisstateabbr, $thisamount)
    {
        $thisTaxarray = [];
        $sqltax = "SELECT * FROM taxme WHERE isActive = true AND state = '$thisstateabbr'";
        $resulttax = $this->db->PDOMiniquery($sqltax);
        if($this->db->PDORowcount($resulttax) > 0)
        {
            foreach($resulttax as $rstax)
            {
                $thisTaxarray['fedtax'] = ($rstax['federal_tax'] == NULL ? 0 : (($rstax['federal_tax'] / 100) * $thisamount));
                $thisTaxarray['statetax'] = ($rstax['state_tax'] == NULL ? 0 : (($rstax['state_tax'] / 100) * $thisamount));
                $thisTaxarray['countytax'] = ($rstax['county_tax'] == NULL ? 0 : (($rstax['county_tax'] / 100) * $thisamount));
                $thisTaxarray['citytax'] = ($rstax['city_tax'] == NULL ? 0 : (($rstax['city_tax'] / 100) * $thisamount));
            }
        }
        return($thisTaxarray);
    }
    function CreateSquareCustomer($thisrecno, $thisfirstname, $thislastname, $thisemail, $thisphonenumber, $thisaccesstoken)
    {
        //https://developer.squareup.com/reference/square/customers-api
        //After creating a test account, go to OAuth menu and click on Authoriuze test account and select the test account you created to be authorized.
        //$rs['user_recno'], $rs['firstname'], $rs['lastname'], $rs['email'], $rs['phone_number'], $rs['api_access_token']
        //$thisrecno - recno from table user
        //givenName = firstname from table users
        //familyName = lastname from table users
        //emailAddress = email from table users
        //referenceId = recno from table users
        //$thisaccesstoken = the token api
        $client = $this->GetSquareevn($thisaccesstoken);
        $thisdata = $client->customers->create(
            new CreateCustomerRequest([
                'givenName' => $thisfirstname,
                'familyName' => $thislastname,
                'emailAddress' => $thisemail,
                'referenceId' => $thisrecno,
                'emailAddress' => $thisemail,
                'phoneNumber' => $thisphonenumber,
            ]),
        ); 
        $thisarray = json_decode($thisdata, true);
        return($thisarray);
    }
    function AnalysisThisArray($thisarray)
    {
        //This function runs though 3 multiple arrays
        $thistemparray = [];
        if(is_array($thisarray))
        {
            current($thisarray);
            foreach($thisarray as $key => $value)
            {
                if(is_array($value))
                {
                    //ARRAY
                    foreach($value as $key2 => $value2)
                    {
                        if(is_array($value2))
                        {
                            foreach($value2 as $key3 => $value3)
                            {
                                if(is_array($value3))
                                {
                                    foreach($value3 as $key4 => $value4)
                                    {
                                        if(is_array($value4))
                                        {
                                            foreach($value4 as $key5 => $value5)
                                            {
                                                //CreatePaymentRequest - status, receipt_url, receipt_url, id
                                                
                                                    //APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
                                                $thistemparray[$key5] = $value5;
                                                
                                                file_put_contents('./dodebug/debug.txt', "key5 -> $key5 = $value5 \n", FILE_APPEND);
                                            }
                                        }
                                        else
                                        {
                                            //CreatePaymentRequest - status, receipt_url, receipt_url, id
                                            //CreateCustomerRequest - id, 
                                            
                                                //APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
                                                $thistemparray[$key4] = $value4;
                                           
                                            file_put_contents('./dodebug/debug.txt', "key4 -> $key4 = $value4 \n", FILE_APPEND);
                                        }
                                    }
                                }
                                else
                                {
                                    //CreatePaymentRequest - status, receipt_url, receipt_url, id
                                    
                                        //APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
                                        $thistemparray[$key2] = $value2;
                                    
                                    file_put_contents('./dodebug/debug.txt', "key3 -> $key3 = $value3 \n", FILE_APPEND);
                                }
                            }
                        }
                        else
                        {
                            //CreatePaymentRequest - status, receipt_url, receipt_url, id
                            
                                //APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
                                $thistemparray[$key2] = $value2;
                            
                            file_put_contents('./dodebug/debug.txt', "key2 -> $key2 = $value2 \n", FILE_APPEND);
                        }
                    }
                }
                else
                {
                    //CreatePaymentRequest - status, receipt_url, receipt_url, id
                    
                        //APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
                        $thistemparray[$key] = $value;
                    
                    file_put_contents('./dodebug/debug.txt', "key -> $key = $value \n", FILE_APPEND);
                }
            }
        }
        else
        {
            //CreatePaymentRequest - status, receipt_url, receipt_url, id
            
                //APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
                $thistemparray[$key2] = $value2;
            
            file_put_contents('./dodebug/debug.txt', "NOT AN ARRAY \n", FILE_APPEND);
        }
        return($thistemparray);
    }
    function MakeSquarerefund($destinationid, $realamount, $thishash, $thisaccesstoken, $thisreason)
    {
        //https://developer.squareup.com/reference/square/refunds-api/refund-payment
        $client = $this->GetSquareevn($thisaccesstoken);
        $thisstring = $client->refunds->refundPayment(
            new RefundPaymentRequest([
                'idempotencyKey' => "$thishash",
                'paymentId' => "$destinationid",
                'amountMoney' => new Money([
                    'amount' => $realamount,
                    'currency' => Currency::Usd->value,
                ]),
                'reason' => '$thisreason',
            ]),
        );
        //$thisarray = $this->AnalysisThisArray(json_decode($thisstring, true));
        $thisarray = json_decode($thisstring, true);
        return($thisarray);
    }
    function GetPayment($payment_id, $thisaccesstoken)
    {
        $client = $this->GetSquareevn($thisaccesstoken);
        $thisstring = $client->payments->get(
            new GetPaymentsRequest([
                'paymentId' => "$payment_id",
            ]),
        );
        $thisarray = json_decode($thisstring, true);
        return($thisarray);
    }
    function MakeSquareCancelpayment($destinationid, $thisaccesstoken)
    {
        $client = $this->GetSquareevn($thisaccesstoken);
        $thisstring = $client->payments->cancel(
            new CancelPaymentsRequest([
                'paymentId' => "$destinationid",
            ]),
        );
        //$thisarray = $this->AnalysisThisArray(json_decode($thisstring, true));
        $thisarray = json_decode($thisstring, true);
        return($thisarray);
    }
    function MakeSquarepayment($thiscustomerid, $thistotal, $thistip, $thishash, $thisaccesstoken, $thistoken)
    {
        //https://developer.squareup.com/reference/square/payments-api/create-payment
        //https://developer.squareup.com/docs/devtools/sandbox/payments for credit card# testing 4111 1111 1111 1111, 
        //$thisrecno - recno from schedule_dates
        //$thiscustomerid - the ID from square customer database, we get it by running the create customer api square function
        //$thistotal - total from the web
        //$thisaccesstoken - the square access token
        $client = $this->GetSquareevn($thisaccesstoken);
        $thisstring = $client->payments->create(
            new CreatePaymentRequest([
                'amountMoney' => new Money([
                    'amount' => $thistotal,
                    'tip_money' => $thistip,
                    'currency' => Currency::Usd->value
                ]),
                'idempotencyKey' => $thishash,
                'sourceId' => $thistoken,
                "autocomplete" => true,
                "customer_id" => $thiscustomerid
            ]),
        );
        //file_put_contents('./dodebug/debug.txt', "this string: ".$thisstring, FILE_APPEND);
        //$thisarray = $this->AnalysisThisArray(json_decode($thisstring, true));
        
        $thisarray = json_decode($thisstring, true);
        return($thisarray);
        
    }
    function RenewSquareapi($thisaccesstoken, $thisappid, $thissecretekey)
    {
        //https://developer.squareup.com/docs/oauth-api/migrate-to-refresh-tokens
        //https://developer.squareup.com/reference/square/o-auth-api/obtain-token
        //https://developer.squareup.com/explorer/square_2026-01-22/o-auth-api/obtain-token
        //https://developer.squareup.com/docs/oauth-api/create-urls-for-square-authorization - explained how the url works
        //https://developer.squareup.com/docs/oauth-api/receive-and-manage-tokens - how to create a url receivr
        //Access token and Mirgration token is the same
        $client = $this->GetSquareevn($thisaccesstoken);
        //I don't need the redirectUri, 1 because I already set it up, 2 because I am not asking for a auth token, just a refresh
        $thisstring = $client->oAuth->obtainToken(
                new ObtainTokenRequest([
                    'clientId' => $thisappid,
                    'clientSecret' => $thissecretekey,
                    'grantType' => 'refresh_token',
                    'migrationToken' => $thisaccesstoken
                ]),
        );
        /*
         * Expect return
         {
            "access_token": "EAAl3ikZIe18J-2-cHlV2bL4-EaZHGoJUhtEBT7QA6-7AgwIHw8Xe1IoUvGsNxA",
            "token_type": "bearer",
            "expires_at": "2025-04-03T18:31:06Z",
            "merchant_id": "MLQW2MYBY81PZ",
            "refresh_token": "EQAAl0OcByu3IYJYScGGg-8E5YNf0r0b6jCTCMy5nOcRZ4ok0wbWAL8vY3tZWNcc",
            "short_lived": false
          }

         */
        $thisarray = json_decode($thisstring, true);
        return($thisarray);
    }
    function RetrieveSquareCustomer($squarecustomerid, $thisaccesstoken)
    {
        $client = new SquareClient(
            token: $thisaccesstoken,
            options: [
                'baseUrl' => Environments::$_SESSION['realsandpro']->value,
            ],
        );
        $thisstring = $client->customers->get(
            new GetCustomersRequest([
                'customerId' => $squarecustomerid,
            ]),
        );
        $thisarray = $this->AnalysisThisArray(json_decode($thisstring, true));
        return($thisarray);

    }
    function CreateUserDirectory($tempdir)
    {
        //This function create user dir for files keeping.
        if (!file_exists("./images/others/$tempdir")) {
            mkdir("./images/others/$tempdir", 0777, true);
            mkdir("./images/others/$tempdir/avatar", 0777, true);
            $default_file = "./images/others/default_user/avatar/defaultimage.png";
            $destination_place = "./images/others/$tempdir/avatar/defaultimage.png";
            if( !copy($default_file, $destination_place) ) {  
                //file_put_contents('./dodebug/debug.txt', "registration - copy files: Can not copy.", FILE_APPEND);
            }  
            else {  
                //file_put_contents('./dodebug/debug.txt', "registration - copy files: Can copy.", FILE_APPEND);
            } 

        }
    }
    function CompleteCuts($thisrecno, $from)
    {?>
        <div class="div-payment-holder" id="div_payment_container">
            <div class="div-payment-table-holder" id="div_payment_holder_data">
                <form name="frmpayment" id="frmpayment" method="post">
                    <div class="align-left" style="color: red; background-color: black;">We do not keep any credit card information on file, only the last 4 digits for refund purpose!</div>
                    <div class="align-center">
                        <table class="tbl-payment-table">
                            <tr>
                                <td class="align-right">Type:</td>
                                <td>
                                    <input class="payment_rdo" type="radio" name="rdo_credity_type" id="rdo_credity_visa" size="25" value="Visa" checked />Visa
                                    <input class="payment_rdo" type="radio" name="rdo_credity_type" id="rdo_credity_master" size="25" value="Master" />Master
                                    <input class="payment_rdo" type="radio" name="rdo_credity_type" id="rdo_credity_discover" size="25" value="Discover" />Discover
                                    <input class="payment_rdo" type="radio" name="rdo_credity_type" id="rdo_credity_ae" size="25" value="American Express" />American Express
                                </td>
                            </tr>
                            <tr>
                                <td class="align-right">Card#:</td>
                                <td>
                                    <input class="div-input-payment float-left tab-index" type="text" name="txt_cr_last4" id="txt_cr_last4" size="20" tabindex="0" onkeyup="goTonext(this);" autofocus />
                                </td>
                            </tr>
                            <tr>
                                <td class="align-right">Exp. Date:</td>
                                <td>
                                    <input class="div-input-payment float-left tab-index" type="text" name="txt_expiredate_mm" id="txt_expiredate_mm" size="2" value="" placeholder="mm" tabindex="1" onkeyup="goTonext(this);" />
                                    <input class="div-input-payment float-left tab-index" type="text" name="txt_expiredate_yy" id="txt_expiredate_yy" size="2" value="" placeholder="yy" tabindex="2" onkeyup="goTonext(this);" />
                                </td>
                            </tr>
                            <tr>
                                <td class="align-right">Security#:</td><td><input class="div-input-payment float-left tab-index" type="text" name="txt_security" id="txt_security" size="3" tabindex="3" placeholder="123" onkeyup="goTonext(this);" /></td>
                            </tr>
                        </table>
                    </div>
                    <div class="align-center">
                        <button type="button" name="btn_payment" id="btn_payment" onclick="makePayment(this, <?php echo $thisrecno ?>, '<?php echo $from ?>');"  tabindex="4" disabled>Pay Now</button>
                        <button type="button" name="btn_payment_cancel" id="btn_payment_cancel" onclick="cancelPayment();" tabindex="5">Cancel</button>
                    </div>
                </form>
            </div>   
        </div><?php
    }
    function GetSchedule($thisrecno)
    {
        $thisfields = Array();
        $thiswheres = Array();
        $isOn = ' src="./images/others/on.PNG" onclick="modSchedulestatus(this, \'ON\');">';
        $isOff = '<img class="img-admin-mgm-schedule-header cursor-pointer" name="img_status" id="img_status" src="./images/others/off.PNG" onclick="modSchedulestatus(this, \'OFF\');">';
        //$thiswhere = array("recno" => $_POST['recno']);
        $sql = "SELECT * FROM schedules WHERE foreign_ur = $thisrecno";
        $result = $this->db->PDOMiniquery($sql);
        //file_put_contents("./dodebug/debug.txt", "admin schedule recno ".$_POST['thisrecno']." \n", FILE_APPEND);
        $tempslot = array("10:00 AM", "10:30 AM", "11:00 AM", "11:30 AM", "12:00 PM", "12:30 PM", "1:00 PM", "1:30 PM", "2:00 PM", "2:30 PM", "3:00 PM", "3:30 PM", 
                    "4:00 PM", "4:30 PM", "5:00 PM", "5:30 PM", "6:00 PM");
        
        //2-12:00 PM,2-12:30 PM,3-12:00 PM,3-12:30 PM,4-12:00 PM,4-12:30 PM,5-12:00 PM,5-12:30 PM,6-12:00 PM,6-12:30 PM
        $cssdarkgreen = "img-dashboard-mgm-schedule-header-bgdarkgreen";
        $cssdarkred = "img-dashboard-mgm-schedule-header-bgdarkred";
        $thisslot_offs = array();
        if($this->db->PDORowcount($result) > 0) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
            //$_SESSION['isAdmin']
           foreach($result as $rs)
           {
               $thisOff = explode(',', $rs['off_days']);
               if(!is_null($rs['slot_offs']))
               {
                $thisslot_offs = explode(',', $rs['slot_offs']);
               }?>
                <table id="tblmgmschedule" class="tbl-dashboard-mgm-schedule">
                    <tr>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(1, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Monday</td>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(2, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Tuesday</td>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(3, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Wednesday</td>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(4, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Thursday</td>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(5, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Friday</td>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(6, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Saturday</td>
                        <td class="tbl-dashboard-mgm-schedule-header cursor-pointer <?php echo (!in_array(7, $thisOff) ? $cssdarkgreen : $cssdarkred) ?>" title="Click to turn On/Off" onclick="modSchedulestatus(this, <?php echo $_POST['thisrecno'] ?>);">Sunday</td>
                    </tr>
                    <tr><?php 
                        for($j=1; $j<=7; $j++)
                        {?>
                            <td><?php
                                for($i=0; $i<count($tempslot); $i++)
                                {?>
                                    <div class="get-user-schedule-slot-div-holder align-left cursor-pointer" name="div_slot_<?php echo $j ?>_<?php echo $i ?>" id="div_slot_<?php echo $j ?>_<?php echo $i ?>" onclick="updateSlot(this, <?php echo $thisrecno ?>, <?php echo $j ?>, '<?php echo $tempslot[$i] ?>');"><?php 
                                        if(in_array($j."-".$tempslot[$i], $thisslot_offs) || in_array($j, $thisOff))
                                        {
                                            echo "OFF";
                                        }
                                        else
                                        {
                                            echo $tempslot[$i];
                                        }?>
                                    </div>
                                   <?php
                                }?>
                            </td><?php
                        }?>
                     </tr>
                </table><?php 
           }
        }
    }
    function GetBarber()
    {
        //if $thisvaslue has no parameter coming in then we assume default to recno as the KEY, ex key = value
        //and if there is a parameter, it has to be a field that user wants to use as value.    //value = value,
        
        $this->thisarray = [];
        $thistable = "users";
        $thisfields = array("recno", "login");
        $thiswheres = array("isActive" => true, "isBarber" => true);
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswheres, array('login'), null, null, null, 'DISTINCT');
        $tempname = "";
        $tempkey = "";
        if(!is_null($result))
        {
            foreach($result as $rs)
            {
                $this->thisarray[$rs['recno']] = $rs['login'];
            }
        }
        return($this);
    }
    function PrePaydata($thisdataarray)
    {
        foreach($thisdataarray as $dataarraykey => $dataarrayvalue)
        {
            $temptax = "";
            //file_put_contents('./dodebug/debug.txt', "thisdataarray : $dataarraykey => $dataarrayvalue \n", FILE_APPEND);
            switch($dataarraykey)
            {
                case "fedtax":
                    $temptax = "Fed Tax";
                    break;
                case "statetax":
                        $temptax = "State Tax";
                    break;
                case "countytax":
                        $temptax = "County Tax";
                    break;
                case "citytax":
                        $temptax = "City Tax";
                    break;
                default:
                    break;
            }
            if($temptax != "")
            {?>
                <div class="pre-pay-schedule-div-label float-left align-right"><?php echo $temptax ?>:</div><div id='div_cost' class="pre-pay-daily-div-total-val align-left">$<?php echo $dataarrayvalue ?></div><?php
            }
        }
    }
    function GetPrepaydiscount($bigtotal)
    {

        //$bigtotal = is the toal cost for this service
        //If it is full, we just need to apply the pre-pay bonus if it is enable.  As long as it is enable, if we are here, we will apply automatically.
        $sqlsv = "SELECT * FROM events WHERE isActive = true AND name = 'Prepay'";
        $resultsv = $this->db->PDOMiniquery($sqlsv);
        if($this->db->PDORowcount($resultsv) > 0)
        {
            foreach($resultsv as $rssv)
            {
                $svrecno = $rssv['recno'];  //recno of table events
                if($rssv['isDollar'] == true)
                {
                    $thisdiscount = $bigtotal - $rssv['promo'];
                }
                else
                {
                    $thisdiscount = $bigtotal - ($rssv['promo']/100 * $bigtotal);
                }
            }
        }
        return($thisdiscount);
    }
    function GetNewemployeediscount($pr_recno)
    {
        //$pr_recno is a string of recnos in format of 1,2,3,...n and is from the field in schedule_dates call pr_recno
        $this->thisarray = [];
        $sql = "SELECT * FROM events WHERE recno IN($pr_recno) AND name = 'New Customer'";
        $result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($result) > 0)
        {
            foreach($resultsv as $rssv)
            {
                $svrecno = $rssv['recno'];  //recno of table events
                if($rssv['isDollar'] == true)
                {
                    $thisdiscount = $rssv['promo'];
                    $this->thisarray['Dollar'] = $rssv['promo'];
                }
                else
                {
                    $thisdiscount = $rssv['promo']/100;
                    $this->thisarray['Percent'] = $rssv['promo'];
                }
            }
        }
        return($this->thisarray);
    }
    function GetPrepaydeposit($sr_recno)
    {
        $this->thisarray = [];
        $tempprice = 0;
        $tempdeposit = 0;
        //$sr_recno is a string of recnos in format of 1,2,3,...n and is from the field in schedule_dates call sr_recno
        $sql = "SELECT * FROM service WHERE recno IN($sr_recno) AND deposit IS NOT NULL AND isDeposit = true AND isactive = true AND isdeleted = false";
        $result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($result) > 0)
        {
            foreach($result as $rssv)
            {
                $tempprice += number_format($rssv['price'], 2);
                $tempdeposit += number_format($rssv['deposit'], 2);
            }
        }
        $this->thisarray['cost'] = $tempprice;
        $this->thisarray['deposit'] = $tempdeposit;
        return($this->thisarray);
    }
    function CheckDeposit($thisrecno)
    {
        $returnthis = false;
        //$sr_recno is a string of recnos in format of 1,2,3,...n and is from the field in schedule_dates call sr_recno
        $sql = "SELECT * FROM service WHERE recno IN($thisrecno) AND deposit IS NOT NULL AND isDeposit = true AND isactive = true AND isdeleted = false";
        //file_put_contents('./dodebug/debug.txt', "CheckDeposit SQL: $sql \n", FILE_APPEND);
        $result = $this->db->PDOMiniquery($sql);
        if($this->db->PDORowcount($result) > 0)
        {
            $returnthis = true;
        }
        return($returnthis);
    }
    function CancelFees($thisfees, $thisrefund)
    {
        $temprefund = ($thisfees/100) * $thisrefund;
        return($temprefund);
    }
    function AnalyzePosts()
    {
        //$_POST is a superglobal so we can just access it without passing in with the function call.**
        //All of the keys will come in with 'this_' or any pre 5 characters before the actual name for this function to work.
        //We will remove the first 5 character and then be left with the actual name where we can manipulate.
        //For example, 'this_recno' will become 'recno' and we will get the recno, another example is 'this_about. will become 'about'.
        //Basically we are just trying to get the fields in the table to do updates or to do queries.
        $realdata = [];
        $i = 0;
        foreach($_POST as $key => $value)
        {   
            //If it is an obj|array, it will skip and go to the next item, which, will 
            ////be a string because it comes in as json string and will go into the if statement and get analyze and return at the end.
            if($key == "thisarray")
            {
                $i++;
                $this->AnalyzeArray('prompt', 'AnalyzePosts', $i, json_decode($value, true), $realdata);
            }            
        }
        return($realdata);
    }
    function AnalyzeArray($temppage, $tempfuncname, $i, $temparray, &$realdata)
    {
        $tempfield = "";
        foreach($temparray as $key => $value)
        { 
            if(is_array($value))
            {
                $i++;
                if($key != "this_apiarray")
                {
                    $this->AnalyzeArray($temppage, $tempfuncname, $i, $value, $realdata);
                }
                else
                {
                    $tempfield = substr(trim($key), 5);
                    $realdata["$tempfield"] = $value;
                    if($this->isDebug == true)
                    {
                        file_put_contents('./dodebug/debug.txt', "Page: $temppage \n Function: $tempfuncname \n, AnalyzeArray $i: tempfield = $tempfield && value = ".json_encode($value)." \n", FILE_APPEND);
                    }
                }
            }
            else
            {
                $tempfield = substr(trim($key), 5);
                $realdata["$tempfield"] = $value;
                if($this->isDebug == true)
                {
                    file_put_contents('./dodebug/debug.txt', "Page: $temppage \n Function: $tempfuncname \n, AnalyzeArray $i: tempfield = $tempfield && value = $value \n", FILE_APPEND);
                }
            }
        }
        return($realdata);
        //return($this->DebugAssociativearray($tempdata));
    } 
    function DebugAssociativearray($temparray)
    {
        foreach($temparray as $key => $value)
        { 
            if($this->isDebug == true)
            {
                file_put_contents('./dodebug/debug.txt', "DebugAssociativearray: tempfield = $key && value = $value \n", FILE_APPEND);
            }
        }
    }
    function AnalyzePostsubmit()
    {
        //This function is used to analyze submitted form ONLY, if not submit use AnalyzeArray() above.
        $realdata = [];
        $i = 0;
        foreach($_POST as $key => $value)
        {   
            if($key != "cmd")
            {
                $thisfield = substr($key, 4);                   
                
                if($thisfield != "files") //We don't want to do anything with attachment, we will handle it below.
                {
                    if($this->isDebug == true)
                    {
                        file_put_contents('./dodebug/debug.txt', "$thisfield => $value \n", FILE_APPEND);
                    }

                    if(str_contains($thisfield, 'date'))
                    {
                        $realdata[$thisfield] = date('Y-m-d', strtotime($value));
                    }
                    else
                    {
                        $realdata[$thisfield] = $value;
                    }
                }
            }
        }
        return($realdata);
    }
    function GetDeveloperstatus()
    {
        if($_SESSION['isLive'] == true)
        {
            return("https://connect.squareup.com/oauth2/authorize");
        }
        else
        {
            return("https://connect.squareupsandbox.com/oauth2/authorize");
        }
    }
    function GetDatabase()
    {
        //This function returns the active database.
        $sql = "SELECT DATABASE()";
        $result = $this->db->PDOGetdatabase($sql);
        $currentSchema = $result->fetchColumn();
        //file_put_contents('./dodebug/debug.txt', "What is database? $currentSchema\n", FILE_APPEND);
        return($currentSchema);
    }
    function GetColumnnames($thistable)
    {
        
        $sql = "DESCRIBE $thistable";
        //Field, Type, Null, Key, Default, Extra
        //Case Sensitive
        $result = $this->db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            file_put_contents('./dodebug/debug.txt', "col names? ".$rs['Field']."\n", FILE_APPEND);
        }
    }
    function GetAccesstoken($thisaccesstoken, $thisrefreshtoken, $thisappid, $thissecret, $thiscode, $codeverifier)
    {
        $client = $this->GetSquareevn($thisaccesstoken);
        $reutrnaray = $client->oAuth->obtainToken(
            new ObtainTokenRequest([
                'clientId' => $thisappid,
                'grantType' => 'authorization_code',
                'code' => $thiscode,
                'client_secret' => $thissecret,
                'codeVerifier' => $codeverifier,
            ]),
        );
        $thisarray = json_decode($reutrnaray, true);
        return($thisarray);
    }
    function GetRefreshtoken($thisaccesstoken, $thisrefreshtoken, $thisappid, $thissecret, $thisurl)
    {
        //not used right now.
        $client = $this->GetSquareevn($thisaccesstoken);
        $reutrnaray = $client->oAuth->obtainToken(
            new ObtainTokenRequest([
                'clientId' => $thisappid,
                'grantType' => 'refresh_token',                
                'refreshToken' => $thisrefreshtoken,
                'redirectUri' => $thisurl,
            ]),
        );
        $thisarray = json_decode($reutrnaray, true);
        return($thisarray);
    }
    function GetSquareevn($thisaccesstoken)
    {
        if(isset($_SESSION['realsandpro']) && $_SESSION['realsandpro'] == "Production")
        {
            //file_put_contents('./dodebug/debug.txt', "GetSquareevn Production \n", FILE_APPEND);
            return(new SquareClient(
                token: $thisaccesstoken,
                options: [
                    'baseUrl' => Environments::Production->value,
                ],
            ));
        }
        else
        {
            //file_put_contents('./dodebug/debug.txt', "GetSquareevn Sandbox \n", FILE_APPEND);
            return(new SquareClient(
                token: $thisaccesstoken,
                options: [
                    'baseUrl' => Environments::Sandbox->value,
                ],
            ));
        }
    }
    function GetRevoketoken($thisaccesstoken, $thisappid, $thissecret)
    {
        $client = $this->GetSquareevn($thisaccesstoken);
        $returnarray = $client->oAuth->revokeToken(
            new RevokeTokenRequest([
                'accessToken' => "$thisaccesstoken",
                'clientId' => "$thisappid",
                "Authorization" => "Client $thissecret"
            ]),
        );
        return(json_decode($returnarray, true));
    }
    function SavedcCard($userrecno, $firstname, $lastname, $email, $phonenumber, $address, $city, $state, $zipcode, $idemkey, $thiscustomerid, $thistoken, $thisaccesstoken)
    {
        //https://developer.squareup.com/reference/square/cards-api/create-card EAAAl7DC4QppD5dxMyKiXZuJ57NArb3dK6KcbZXnF2TW_4FDvccXGna3oU-TLPOX
        $client = $this->GetSquareevn($thisaccesstoken);
        if(isset($_SESSION['realsandpro']) && $_SESSION['realsandpro'] == "Production")
        {
            //file_put_contents('./dodebug/debug.txt', "SavedcCard Production \n", FILE_APPEND);
            $returnarray = $client->cards->create(
                new CreateCardRequest([
                    'idempotencyKey' => $idemkey,
                    'sourceId' => $thistoken,
                    'card' => new Card([
                        'customerId' => $thiscustomerid,
                        'referenceId' => $userrecno,
                    ]),
                ]),
            );
        }
        else
        {
            //file_put_contents('./dodebug/debug.txt', "SavedcCard Sandbox \n", FILE_APPEND);
            $returnarray = $client->cards->create(
                new CreateCardRequest([
                    "Authorization" => "Bearer $thistoken",
                    'idempotencyKey' => $idemkey,
                    'sourceId' => 'cnon:card-nonce-ok',
                    'card' => new Card([
                        'customerId' => $thiscustomerid,
                    ]),
                ]),
            );
        }
        /*
        $returnarray = $client->cards->create(
            new CreateCardRequest([
                'idempotencyKey' => $idemkey,
                'sourceId' => $thistoken,
                'card' => new Card([
                    'billingAddress' => new Address([
                        'addressLine1' => $address,
                        'addressLine2' => '',
                        'locality' => $city,
                        'administrativeDistrictLevel1' => $state,
                        'postalCode' => $zipcode,
                        'country' => Country::Us->value,
                    ]),
                    'cardholderName' => "$firstname $lastname",
                    'customerId' => $thiscustomerid,
                    'referenceId' => $userrecno,
                ]),
            ]),
        );*/
        return($returnarray);
    }
    function AnalyzeArraysearch($temppage, $tempfuncname, $i, $searcharray, $temparray, &$realdata)
    {
        //Send in an array of what we want return as associative array.
        //Ex, if we want to find an element of X, we send it in in the array, when it is found, it will return an array['x' => value];
        $tempfield = "";
        foreach($temparray as $key => $value)
        { 
            if(is_array($value))
            {
                $i++;
                if($key != "this_apiarray")
                {
                    $this->AnalyzeArray($temppage, $tempfuncname, $i, $value, $realdata);
                }
                else
                {
                    $tempfield = substr(trim($key), 5);
                    $realdata["$tempfield"] = $value;
                    if($this->isDebug == true)
                    {
                        file_put_contents('./dodebug/debug.txt', "Page: $temppage \n Function: $tempfuncname \n, AnalyzeArray $i: tempfield = $tempfield && value = ".json_encode($value)." \n", FILE_APPEND);
                    }
                }
            }
            else
            {
                $tempfield = substr(trim($key), 5);
                $realdata["$tempfield"] = $value;
                if($this->isDebug == true)
                {
                    file_put_contents('./dodebug/debug.txt', "Page: $temppage \n Function: $tempfuncname \n, AnalyzeArray $i: tempfield = $tempfield && value = $value \n", FILE_APPEND);
                }
            }
        }
        return($realdata);
        //return($this->DebugAssociativearray($tempdata));
    }
    function BarberOnline($db, $barberrecno)
    {
        //Return true or false if the barber is Live or Sandbox.
        $isLive = false;
        $sql = "SELECT isLive FROM users WHERE recno = $barberrecno AND isLive = true";
        $result = $db->PDOMiniquery($sql);
        if($db->PDORowcount($result) > 0)
        {
            $isLive = true;
        }
        return($isLive);      
    }
}?>