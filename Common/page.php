<?php
use PapayasauceClasses\PdoClass;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');
$_SESSION['predir'] = "";
$_SESSION['OPERATING_STATE'] = "OK";
if($temp_host != "localhost")
{
    $_SESSION['predir'] = "/home1/gcwwkite/public_html/website_ad583fcd";
}
$db = new PdoClass();
$sqlisL = "SELECT isLive, name FROM company_info";
$resultisL = $db->PDOMiniquery($sqlisL);
foreach($resultisL as $rsiL)
{
    $_SESSION['companyname'] = $rsiL['name'];
    if($rsiL["isLive"] == true)
    {
        $_SESSION['isSandpro'] = "_pro";
        $_SESSION['isLive'] = true;
    }
    else
    {
        $_SESSION['isSandpro'] = "";
        $_SESSION['companyname'] = "";
        $_SESSION['isLive'] = false;
    }
}
//file_put_contents("./dodebug/debug.txt", 'not here '.$_SESSION['isSandpro'], FILE_APPEND);
//Hostgator pre dir
//date_default_timezone_set('America/Chicago'); //THIS MAKES THE WEBSITE USE THIS TIMEZONE AS THE TIME.
//date_default_timezone_set('Australia/Sydney'); //THIS MAKES THE WEBSITE USE THIS TIMEZONE AS THE TIME.
date_default_timezone_set('America/Chicago'); //THIS MAKES THE WEBSITE USE THIS TIMEZONE AS THE TIME.
$temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
$explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
$this_page = end($explode_page);
//file_put_contents("./dodebug/debug.txt", 'this_page '.$this_page, FILE_APPEND);
if(!isset($_SESSION['user']) && $this_page != "verifyme.php" && $this_page != "index.php" && $this_page != "retrievepassword.php" && $this_page != "resetpassword.php"  && 
        $this_page != "registration.php" && $this_page != "passwordreset.php" && $this_page != "login.php" &&
        $this_page != "cancellation.php" && $this_page != "registerguest.php" && $this_page != "paynow.php" && $this_page != "paid.php" &&
        $this_page != "smsconf.php" && $this_page = "optin_verification.php" && $this_page != "cancelpayment.php" && $this_page != "codefetch.php" && $this_page != "apifetch.php" &&
        $this_page != "addccard.php" && $this_page != "product.php" && $this_page != "cart.php")
{
    //file_put_contents("./dodebug/debug.txt", 'not here ', FILE_APPEND);
    header("Location: /index.php"); //Unless this is the main/front page, if user does not have a logged session, they will be forced to login first.
    exit();
}
if(!function_exists('GetTimes'))
{
    function GetTimes()
    {
        $thisutctime = gmdate('H:i:s');
        $thisdate = date('d M y');
        $thislocaltime = localtime(time(), true);
        $thislocalactualtime = ($thislocaltime['tm_hour'] < 10 ? "0".$thislocaltime['tm_hour'] : $thislocaltime['tm_hour']).":".($thislocaltime['tm_min'] < 10 ? "0".$thislocaltime['tm_min'] : $thislocaltime['tm_min']).":".($thislocaltime['tm_sec'] < 10 ? "0".$thislocaltime['tm_sec'] : $thislocaltime['tm_sec']);
        $thisarray = Array();
        $thisarray = array('thisutctime' => $thisutctime,
                           'thisdate' => $thisdate,
                           'thislocaltime' => $thislocalactualtime);
        echo json_encode($thisarray);
    }
}
if(!function_exists('Logout'))
{
    
    function Logout()
    {
        if(isset($_SESSION))
        {
            //file_put_contents("./dodebug/debug.txt", 'Function exists logging out', FILE_APPEND);
            session_unset();
            session_destroy();
            echo 'Success';
        }
        else
        {
           echo 'Failed'; 
        }
    }
}?>
