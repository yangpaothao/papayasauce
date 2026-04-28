<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/classes/PageloaderClass.php");
require("./common/classes/PasswordClass.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/EmailClass.php");
require("./common/prompt.php");
$pr = new PROMPT();
$ne = new Email_Class();
$load_headers = new PageloaderClass();
$db = new PDOCON();
$pc = new Password_Class();

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
        <script type="text/javascript">
            function submitPassowrdreset(){
                if($("#txtpassword").val() == ""){
                     alert("Password can not be empty.");
                     return(false);
                }
                if($("#txtconfirmpw").val() == ""){
                     alert("Confirm password field can not be empty.");
                     return(false);
                }
                thisArray = [{
                    "thispassword": $("#txtpassword").val(), 
                    "thisrecno": $("body").data('recno'), 
                    "thisvericode": $("body").data('vericode'), 
                    "thisfrom": $("body").data("from")
                }];     
                //alert('from: '+$("body").data("from"));
                //alert('thisrecno: '+$("body").data('recno'));
               $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitPassowrdreset&thisarray='+JSON.stringify(thisArray), function(result){
                   if(result == "First Time" || result == "Forgot Password" || result == "Default"){
                       alert("Successfully updated password.");
                       window.location.href = "./login.php";
                    }
                   else{
                       alert(result);
                       return(false);
                   }
               });
           }
        </script>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function SubmitPassowrdreset()
{
    global $db, $pc, $load_headers, $ne, $pr;
    $thisrecno = 0;
    $thispassword = "";
    $thisvericode = "";
    $thisfrom = "";
    //Can!BePharm0KMO
    //file_put_contents("./dodebug/debug.txt", "thisarray = ".$_POST['thisarray']." \n", FILE_APPEND);
    $temparray = json_decode($_POST['thisarray']);
    //file_put_contents("./dodebug/debug.txt", "thistemparray = ".$temparray." \n", FILE_APPEND);
    foreach($temparray as $key => $value)
    {   
        //file_put_contents("./dodebug/debug.txt", "this key = ".$key." \n", FILE_APPEND);
        foreach($value as $key1 => $value2)
        {
            //file_put_contents("./dodebug/debug.txt", "thisarray = $key1 == $value2 \n", FILE_APPEND);
            if($key1 == "thisrecno")
            {
                $thisrecno = $value2;
            }
            if($key1 == "thispassword")
            {
                $thispassword = $value2;
            }
            if($key1 == "thisvericode")
            {
                $thisvericode = $value2;
            }
            if($key1 == "thisfrom")
            {
                $thisfrom = $value2;
            }
        }
    }
    //file_put_contents('./dodebug/debug.txt', 'what is from? '.$thisfrom.'\n', FILE_APPEND);
    $pc ->set_password($thispassword);
    if(!$pc ->validate_password())
    {
        echo "Password has not met the requirements. Please try again.";
    }
    else
    {
        $thistable = "users";
        $thisfields = Array('email', 'firstname', 'lastname');
        $thiswhere = array("recno" => $thisrecno);
        //If from is coming from Password Requested, we do not have email of the user, so we will need to get it.
        //file_put_contents("./dodebug/debug.txt", "where to 1? ".$thisfrom, FILE_APPEND); 
        $result2 = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        foreach($result2 as $rs)
        {
            $thisemail = $rs['email'];
            $thisfirstname = $rs['firstname'];
            $thislastname = $rs['lastname'];
        }
        $tempdir = $thisfirstname.$thislastname."_".str_replace("@", "_", $thisemail);
        //file_put_contents('./dodebug/debug.txt', 'what is datarecno? '.$_POST['recno'].'\n', FILE_APPEND);
        //file_put_contents("./dodebug/debug.txt", "where to 1? ".$_POST['from']."\n", FILE_APPEND);
        $getpasssword = $load_headers -> Hash_Me_Password($thispassword); //we hash user's entered pw.
        if($thisfrom == "First Time")
        {
            $thisdata = array("password" => $getpasssword, "ispasswordchanged" => true, "isverified" => true, "vericode" => NULL);             
        }
        else if($thisfrom == "Forgot Password")
        {
            $thisdata = array("password" => $getpasssword, "isrequested" => false, "vericode" => NULL);  //After we updatd the requested pw change, we want to reset the fields.
        }
        else if($thisfrom== "Default")
        {
            $thisdata = array(
                "password" => $getpasssword, 
                "ispasswordchanged" => true, 
                "isverified" => true, 
                "isauthenticated" => true, 
                "isauthenticatedverified" => true,
                "media_dir" => $tempdir
            );
        }
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $thisrecno); //$result should be the recno of this insert.
        if(isset($result))
        {
            //file_put_contents("./dodebug/debug.txt", "where to 1? here1 \n", FILE_APPEND);
            $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
            //After we updated the system we will need to send an acknowledgement email to the email in the system to let the
            //user know that it's changed.
            $sentto = Array();
            $replyto = Array();
            $ccto = Array();
            $bccto = Array();
            $attachment = Array();
            $subject = "";
            $body = "";

            //file_put_contents("./dodebug/debug.txt", "where? 130 here \n", FILE_APPEND);
            $subject = $ne->get_verification_passwordreset();
            $sendto[] = array($thisemail => $thisfirstname." ".$thislastname);
            $body = $ne->get_passwordreset_body($thisserver);
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            //file_put_contents("./dodebug/debug.txt", "where? 136 here \n", FILE_APPEND);
            if($thisfrom == "Forgot Password")
            {                
                echo "Forgot Password";
            }
            else if($thisfrom == "First Time")
            {
                echo "First Time";
            }
            else if($thisfrom == "Default")
            {
                //We need to create the director for the user
                $tempdir = $tempdir; //Format for NON-Employee or NON-Barber will be firstname_lastname_email1_hotmail.com
                $pr->CreateUserDirectory($tempdir);
                unset($_SESSION['temprecno']);
                echo "Default";
            }
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", "where to 1? here2 \n", FILE_APPEND);
            echo "Failed";
        }
    }
}
function LoadForm($from)
{
    if(isset($_SESSION['temprecno']))
    {?>
    <script type="text/javascript">
         $("body").data("from", "<?php echo $from ?>");
         $("body").data("vericode", "<?php echo $_GET['vericode'] ?>");
         $("body").data("recno", "<?php echo $_SESSION['temprecno'] ?>");
     </script><?php
    }
    else
    {?>
    <script type="text/javascript">
         $("body").data("from", "<?php echo $from ?>");
         $("body").data("vericode", "<?php echo $_GET['vericode'] ?>");
     </script><?php        
    }?>
     <form id="frmpasswordreset" name="frmpasswordreset" method="post">
        <table class="tbl-admin-register" name="tbl_passwordreset" id="tbl_passwordreset">
            <tr class="tr-passreset">
                <td class="tbl-pw-reset-lbl">New Password:</td>
                <td class="pw-reset-input"><input class="user-profile-input required" type="password" id="txtpassword" name="txtpassword" onchange="checkPassword(this);" value=""/></td>
            </tr>
            <tr class="tr-passreset">
                <td class="tbl-pw-reset-lbl"></td>
                <td>
                    <div class="div-required-param">At least 8 in length</div>
                    <div class="div-required-param">At least 1 special character </div>
                    <div class="div-required-param">At least 1 number</div>
                    <div class="div-required-param">At least 1 UPPER case</div>
                    <div class="div-required-param">At least 1 lower case</div>
                </td>
            </tr>
            <tr class="tr-passreset">
                <td class="tbl-pw-reset-lbl">Confirm New Password:</td>
                <td class="pw-reset-input"><input class="user-profile-input required" type="password" id="txtconfirmpw" name="txtconfirmpw" onchange="checkConfirmpassword(this);" value=""/></td></tr>
            <tr class="tr-passreset">
                <td class="tbl-pw-reset-lbl align-center" colspan="2">
                    <button id="btnsubmitpasswordreset" onclick="submitPassowrdreset();" value="Submit">Submit</button>
                </td>
            </tr>
        </table>
     </form>
<?php
}
function Main()
{
    global $load_headers;
    $from = "";
    if(array_key_exists('from', $_POST))
    {
        $from = $_POST['from'];
    }?>
    <div class="main-div">
        <br><br> <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="div-header-main-container">Password Reset</div>
        <br>
        <div class="div-body-container">
            <?php
            $db = new PDOCON();
            if(array_key_exists('vericode', $_GET) && $_GET['vericode'] != 'Default')
            {?>
                <script type="text/javascript">
                    $("body").data("from", "<?php echo $from ?>");
                    $("body").data("vericode", <?php echo $_GET['vericode'] ?>);
                </script><?php
                $thistable = "users";
                $thisfields = array('recno');
                $thiswhere = array("vericode" => $_GET['vericode'], 'isverified' => 'false');
                $rs = $db -> PDOQuery($thistable, $thisfields, $thiswhere); //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
                
                if(!is_null($rs))
                {
                    ////If there is a record, we know we need to verify 
                    foreach($rs as $row)
                    {
                        $recno = $row['recno'];
                    }
                    //file_put_contents("./dodebug/debug.txt", "Password Requested recno? ".$recno, FILE_APPEND); 
                    //But we have to make sure the link is still valid and less than 24 hours.
                    $sql = "SELECT recno from $thistable WHERE recno = $recno AND passwordtimer <= now() + INTERVAL 1 DAY";
                    $result = $db->PDOMiniquery($sql);
                    
                    if($db->PDORowcount($result) > 0)
                    {?>
                        <script type="text/javascript">
                            $("body").data("recno", <?php echo $recno ?>);
                        </script>
                        <?php
                        LoadForm('First Time');
                    }
                    else
                    {
                        //Link is more than 24 hours.
                        ?>
                        <div class="div-verifexpiredheader">This link has expired.</div><?php
                    }
                }
                else
                {
                    //We could be here due to user requesting password and the user clicked the link from the email they received in the email
                    //so we have to check for that first.  We are checking for the vericode and the isrequested fields
                    $thiswhere2 = array("vericode" => $_GET['vericode'], "isrequested" => true);
                    $rs = $db -> PDOQuery($thistable, $thisfields, $thiswhere2);
                    if(!is_null($rs))
                    {
                        foreach($rs as $row)
                        {
                            $recno = $row['recno'];
                        }
                        //But we have to make sure the link is still valid and less than 24 hours.
                        $sql = "SELECT recno from $thistable WHERE recno = $recno AND passwordtimer <= now() + INTERVAL 1 DAY";
                        //file_put_contents("./dodebug/debug.txt", "sql pw reset? $sql \n", FILE_APPEND);
                        $result = $db->PDOMiniquery($sql);

                        if($db->PDORowcount($result) > 0)
                        {?>
                            <script type="text/javascript">
                                $("body").data("recno", <?php echo $recno ?>);
                            </script>
                            <?php
                            //file_put_contents("./dodebug/debug.txt", "where? Password Requested", FILE_APPEND); 
                            LoadForm('Forgot Password');
                        }
                        else
                        {
                            //Link is more than 24 hours.
                            ?>
                            <div class="div-verifexpiredheader">This link has expired.</div><?php
                        }
                    }
                    else
                    {
                        //If we do not have anything back, that means it's already verified.?> 
                        <div class="div-verifexpiredheader">This link is no longer valid.</div><?php
                    }
                }
            }
            else
            {
                if(isset($_GET['vericode']) && $_GET['vericode'] == "Default")
                {
                    LoadForm($from);
                }
                else
                {
                    LoadForm($from);
                }
            }
            ?>
        </div>
    </div><?php
}