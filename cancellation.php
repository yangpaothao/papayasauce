<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/classes/pageloaderclass.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/emailclass.php");

$load_headers = new PageloaderClass();
$db = new PDOCON();
$ne = new Email_Class();

if(count($_POST) > 0 && isset($_POST['cmd']))
{
    $_REQUEST['cmd']();
    exit();
}
if(count($_GET) > 0)
{
    $keys = array_keys($_GET);
    foreach($keys as $value)
    {
        $_POST[$value] = $_GET[$value];
    }
    if(isset($_GET['cmd']))
    {
        $_REQUEST['cmd']();
        exit();
    }
}?>
<!DOCTYPE html>
<html>
    <head>
        <?php
            $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); // will get 'localhost'
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function Main()
{
    global $db, $load_headers, $ne;?>
    <div class="main-div">
        <br><br> <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="main-div-body">
            <?php
                $subject = "Hair Cut Appointment Cancellation at ".$_SESSION['companyname'];
                $thisslot = "";
                $thistable = "schedule_dates";
                $thisfields = array('recno', 'guest', 'date', 'email', 'slot');
                $thiswhere = array("recno" => $_GET['recno'], "iscancelled" => false, "isdeleted" => false);
                $result = $db -> PDOQuery($thistable, $thisfields, $thiswhere); //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
                if(!is_null($result))
                {
                    $sentto = Array();
                    $replyto = Array();
                    $ccto = Array();
                    $bccto = Array();
                    $attachment = Array();

                    foreach($result as $rs)
                    {
                        $guestdate = $rs['date'];
                        $guestname = $rs['guest'];
                        $guestemail = $rs['email'];
                        $thisslot = $rs['slot'];
                    }
                    $sendto[] = array($guestemail => $guestname);
                    
                    
                    $thisdata = array("iscancelled" => true);
                    $thiswhere = array("recno" => $_GET['recno']);
                    $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
                    if($rows == 'Success')
                    {?>
                        <div class="div-verifexpiredheader">Your appointment has been cancelled.  You do not need to take any further action.</div>;
                        <?php
                        //Once we cancelled successfully, we must then send a confirmation email to the user.
                        $body = $ne -> confirm_cancellation($guestdate, $thisslot, 'User');
                        $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
                    }
                }
                else
                {
                    //If we do not have anything back, that means it's already verified.?> 
                    <div class="div-verifexpiredheader">This appointment is no longer valid.</div><?php
                }
            ?>
        </div>
    </div><?php
}