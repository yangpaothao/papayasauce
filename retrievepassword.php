<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/pageloaderclass.php");
require("./common/classes/emailclass.php");

$ne = new Email_Class();
$db = new PDOCON();
$load_headers = new PageloaderClass();
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
        <style>
        .qh-padding{
            padding: 20px;
        }
        </style>
        <script type="text/javascript">
            function SendPassreset(){
                //After email validation, we get here when user clicked on the Send Email button.
                if($("#txtemail").val() == ""){
                    alert("Please enter an email address.");
                    $("#"+thisid).focus();
                    return(false);
                }
                //alert('thisemail: '+$("#txtemail").val());
                if(!validateEmail($("#txtemail").val())){
                    alert(validateEmail($("#"+thisid).get(0)));
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SendPassreset&'+$("#frmretrievepassword").serialize(), function(result){
                    //alert(result);
                    if(result != "Bad email."){
                        $(".tr-retrievepw").remove();
                        $("#tbl_retrieve_password").append(result);
                    }
                    else{
                        alert("This email does not exist in our system.  Please check your email and try again.");
                        $("#txtemail").focus();
                    }
                    return(false);
                });
            }
            function submitRetrievepath(){
                if($("#txtlogin").val() == "" && $("#txtemail").val() == ""){
                    alert("You must enter a value for Login or Email.  Please try again.");
                    $("#txtlogin").focus();
                    return(false);
                }
                else{
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitRetrievepath&'+$("#frmretrievepath").serialize(), function(result){
                        $("#maindiv").html(result);
                    }); 
                }
                    
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
function SendPassreset()
{
    global $db, $ne;
    //file_put_contents("./dodebug/debug.txt", 'do i get here 1?', FILE_APPEND);
    $ne->set_email($db, $_POST['txtemail']);
    if(!$ne->validate_email())
    {
        $result = "Bad email format detected.  Please check email and try again.";
    }
    if($db->PDORowcount(($db->PDOMiniquery($ne->check_email()))) > 0)
    {
        //We do have a good email so now we wiil go head and send a link to customer so they can reset pw.
        global $db, $load_headers;
        //file_put_contents("./dodebug/debug.txt", 'do i get here 1?', FILE_APPEND);
        $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
        $sendstatus= "";
        $thisfields = array('firstname', 'lastname');
        $thistable = "users";
        $thiswhere = array('email' => strtolower($_POST['txtemail']));
        //PDOQuery($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null, $ons=null)
        $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
       
        //If we get a row back that means we are able to query properly.
        foreach($result as $key => $rs)
        {
            $thisfirstname = $rs['firstname'];
            $thislastname = $rs['lastname'];
        }
        //We need to add the vericode to add to the row and also add password
        $realvericode = $load_headers ->Hash_Me_Vericode();
        $thisdata = array('vericode' => $realvericode, 'isrequested' => true, 'passwordtimer' => date('Y-m-d H:i:s')); //We only need to update vericode cuz user my remember their current password and decided to ignore.
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
        if($result == "Success")
        {
            //file_put_contents("./dodebug/debug.txt", 'do i get here?', FILE_APPEND);  
            //We want to send user an email and allow them to verify the email and change their password once they clicked on 
            //the link in the email.
            $sentto = Array();
            $replyto = Array();
            $ccto = Array();
            $bccto = Array();
            $attachment = Array();
            $subject = "";
            $body = "";
            $sendto[] = array($_POST['txtemail'] => $thisfirstname." ".$thislastname);
            $subject = "Account Reset Request";
            $body = $ne->get_password_reset($thisserver, $realvericode);
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            $result = "<td>An email has been sent.  Please check your email and follow the link to reset your password.</td>";
        }
        
    }
    else
    {
        //Email given doesn't exist.
        $result = "Bad email.";
    }
    echo $result;
}
function SubmitRetrieveform()
{
    global $db, $load_headers;
    
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
    $sendstatus= "";
    $thisfields = array('firstname', 'lastname');
    $thistable = "users";
    $thiswhere = array('login' => strtolower($_POST['txtlogin']), 'email' => strtolower($_POST['txtemail']));
    //PDOQuery($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null, $ons=null)
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result))
    {
        //file_put_contents("./dodebug/debug.txt", var_dump($result), FILE_APPEND);
        foreach($result as $key => $rs)
        {
            $thisfirstname = $rs['firstname'];
            $thislastname = $rs['lastname'];
        }
        //We need to add the vericode to add to the row and also add password
        $realvericode = $load_headers ->Hash_Me_Vericode();
        $thisdata = array('vericode' => $realvericode, 'ispasswordchanged' => true, 'passwordtimer' => date('Y-m-d H:i:s')); 
        //We only need to update vericode cuz user my remember their current password and decided to ignore.
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
        if($result == "Success")
        {
            //We want to send user an email and allow them to verify the email and change their password once they clicked on 
            //the link in the email.
            $sentto = Array();
            $replyto = Array();
            $ccto = Array();
            $bccto = Array();
            $attachment = Array();
            $subject = "";
            $body = "";
            $sendto[] = array($_POST['txtemail'] => $thisfirstname." ".$thislastname);
            $subject = "Account Reset Request";
            $body = "Please follow the link below to reset your account and change your password.<br><br>";
            $body .= "<a href='$thisserver/resetpassword.php?vericode=".$realvericode."'>Click here to verify your email and change your password.</a>";
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            echo "An email as been sent to the email we have on record.  Please check your email and follow the link to reset your password.";
        }
    }
    else
    {
        echo "The login and email given does not match.  Please try agian.";
    }
}

function ValidateThisemail()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "users";
    $thisfields = array('email');
    $thiswhere = array('email' => strtolower($_POST['txtemail']));
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       echo "EXISTS"; 
    }
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="div-body-container">
            <form name="frmretrievepassword" id="frmretrievepassword" method="post">
                <div class="align-center">
                    <table class="tbl-admin-register" id="tbl_retrieve_password">
                        <tr>        
                            <td colspan="2" class="div-header-main-container">Retrieve Password</td>
                        </tr>
                        <tr class="more-methods tr-retrievepw">
                            <td class="tbl-retrieve-lbl">Email: </td>
                            <td clas="retrieve-input" id="td_methods">
                                <input class="align-left float-left" type="text" id="txtemail" name="txtemail" size="40" value="">
                            </td>
                        </tr>
                        <tr class="more-methods tr-retrievepw">
                            <td class="tbl-retrieve-lbl align-center" colspan="2">
                                <button type="button" id="btnemail" name="btnemail" onclick="SendPassreset();">Send Email</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div><?php
}?>