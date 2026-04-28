<?php
require_once("./common/pdocon.php");
$db = new PDOCON();

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Date_Class {
    public $cdate;
    public $isdate = false;
    function set_date($date)
    {
        $this->cdate = date('Y-m-d', strtotime($date));
    }
    function get_date()
    {
        return($this->cdate);
    }
    function validate_date()
    {
        $pattern = '/^([0-9]{1,2})\\/([0-9]{1,2})\\/([0-9]{4})$/';
        if (!preg_match($pattern, $this->cdate)) 
        {
            $isdate = true;
        }
        return($isdate);
    }
    public function compare_dates($cdate1, $cdate2, $cplace)
    {
        //Compare two dates $cdate1 <= $cdate2
        //dates should come in in format of yyyy-mm-dd string
        //$cplace will have value of  'Greater', and 'Equal'
        if($cplace == "Greater")
        {
            if(strtotime($cdate1) < strtotime($cdate2))
            {
                $isdate = true;
            }
        }
        else
        {
            if(strtotime($cdate1) <= strtotime($cdate2))
            {
                $isdate = true;
            }
        }     
        return($isdate);
    }
    public function GetHolidays($returnthis)
    {
        global $db;
        //$returnthis 
        //- Dates, will return just the date in a single array such as array('01-01-2024', ....)
        //- All
        //Get a list of approved off days including holidays.
        //Excluding weekends
        $datetracker = 0;
        $datearray = [];
        $sql = "SELECT * FROM holidays WHERE datetype = 'HOL' AND isDeleted = false ORDER BY dates";
        $result = $db ->PDOMiniquery($sql);
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                if($returnthis == 'Dates')
                {
                    $datetracker++;
                    $datearray[] = $rs['dates'];
                }
                else
                {
                    $datetracker++;
                    $datearray[] = array($rs['dates'] => $rs['type']."|".$rs['description']);
                    //array would be something like = ('dates' => 'type|descriptions', ....);
                }
            }
        }
        if($datetracker > 0)
        {
            return($datearray);
        }
        else
        {
            return(null);
        }
    }
    public function get_holidays_ele($thisdate, $thisfield)
    {
        //$thisdate will be the date 01/22/1982
        //$datetype will be the fields in the table, dates, datetype, description, isDeleted
        global $db;
        $thisholiday = "";
        $tempyear = date('Y', strtotime($thisdate));
        //First we want to see if today is a holiday, since we get here, we know it is either a holiday or an OFF day.
        //First we want to check if it is a holiday (HOL) and if it is not, then we query to get the OFF day.
        switch(date('M', strtotime($thisdate)))
        {
            case 'Jan':
                if(date('Y-m-d', strtotime($thisdate)) == "$tempyear-01-01")
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "New Year's Day";
                    }
                }
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("third monday of January $tempyear")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Martin Luther King, Jr. Day";
                    }
                }
                break;
            case 'Feb':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("third monday of February $tempyear")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Presidents Day";
                    }
                }
                break;
            case 'May':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("last monday of may $tempyear")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Memorial Day";
                    }
                }
                break;
            case 'Jun':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("$tempyear-06-19")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Juneteenth";
                    }
                }
                break;
            case 'Jul':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("$tempyear-07-04")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Independence Day";
                    }
                }
                break;
            case 'Sep':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("first monday of september $tempyear")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Labor Day";
                    }
                }
                break;
            case 'Oct':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("Second Monday of October $tempyear")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Columbus Day";
                    }
                }
                break;
            case 'Nov':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("$tempyear-11-11")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Veterans Day";
                    }
                }
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("November $tempyear fourth thursday")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Thanksgiving";
                    }
                }
                break;
            case 'Dec':
                if(date('Y-m-d', strtotime($thisdate)) == date('Y-m-d', strtotime("$tempyear-12-25")))
                {
                    if($thisfield == "datetype")
                    {
                        $thisholiday = "HOL";
                    }
                    else
                    {
                        $thisholiday = "Christmas";
                    }
                }
                break;
        }
        if($thisholiday == "")
        {
            $sql = "SELECT $thisfield FROM holidays WHERE dates = '".date('Y-m-d', strtotime($thisdate))."' AND isDeleted = false";
            $result = $db-> PDOMiniquery($sql);
            if($db -> PDORowcount($result) > 0)
            {
                foreach($result as $rs)
                {
                   $thisholiday = $rs["$thisfield"];
                }
            }
        }
        return($thisholiday);
    }
    public function get_holiday_dates($tempyear, $tempbigmonth)
    {
        //$from will be dates or descriptions
        //Federal holidays
        //The reason for this function is to get the holidays for a year that is in the future
        //that has not yet been entered into the holidays table.  We do this because some holidays are
        //not on the same day each year.  Also because some holidays may not be observed.
        
        //Now before we do anything, we want to query table holidays to see if it is already in the table, if not, we will want to insert to the table.
        global $db;
        $holidayarray = [];
       
        switch($tempbigmonth)
        {
            case 'Jan':
                $holidayarray[] = date('Y-m-d', strtotime("first day of january $tempyear"));
                $holidayarray[] = date('Y-m-d', strtotime("third monday of January $tempyear"));
                break;
            case 'Feb':
                $holidayarray[] = date('Y-m-d', strtotime("third monday of February $tempyear"));
                break;
            case 'May':
                $holidayarray[] = date('Y-m-d', strtotime("last monday of may $tempyear"));
                break;
            case 'Jun':
                $holidayarray[] = date('Y-m-d', strtotime("$tempyear-06-19"));
                break;
            case 'Jul':
                $holidayarray[] = date('Y-m-d', strtotime("$tempyear-07-04"));
                break;
            case 'Sep':
                $holidayarray[] = date('Y-m-d', strtotime("first monday of september $tempyear"));
                break;
            case 'Oct':
                $holidayarray[] = date('Y-m-d', strtotime("Second Monday of October $tempyear"));
                break;
            case 'Nov':
                $holidayarray[] = date('Y-m-d', strtotime("$tempyear-11-11"));
                $holidayarray[] = date('Y-m-d', strtotime("November $tempyear fourth thursday"));
                break;
            case 'Dec':
                $holidayarray[] = date('Y-m-d', strtotime("$tempyear-12-25"));
                break;
        }
        return($holidayarray);
    }
}
