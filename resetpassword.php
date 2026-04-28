<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/sendmail.php");
$load_headers = new Page_Loader();
$db = new PDOCON();
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
                function submitFormresetpw(){
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitFormresetpw&txtpassword='+$("#txtpassword").val()+'&txtrecno='+$("#divrecno").data('recno'), function(result){
                        if(result == "Success")
                        {
                            alert("You have successfully reset your password and can now start using your password.")
                            window.open('','_self').close();
                        } 
                    });
                }
                function clearForm(){
                    $('#frmlogin')[0].reset()
                }
                function validatePassword(obj){
                    var regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/;
                    if(!regex.test($(obj).val())){
                        alert("Make sure your password meets the minimum requirements.");
                        $(obj).select();

                        return(false);
                    }
                    if($("#txtpassword").val() != "" && $("#txtconfirmpassword").val() != ""){
                        if($("#txtpassword").val() != $("#txtconfirmpassword").val()){
                            alert("Password does not match, please try again.");
                            $(obj).select();
                            return(false);
                        }
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
function SubmitFormresetpw()
{
    //file_put_contents('./dodebug/debug.txt', "var dump: ".var_dump($_POST), FILE_APPEND);
    global $db, $load_headers;
    $thisfields = Array();
    $thistable = "employee_master";
    $sendstatus= "";
    $realpassword = "";
    $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.
    $thisdata = array("password" => $getpasssword, "vericode" => NULL);       
    $thiswhere = array("recno" => $_POST['txtrecno']);
    //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['txtrecno']);
    //file_put_contents('./dodebug/debug.txt', $result, FILE_APPEND);
    if($result == "Success")
    {
        //file_put_contents('./dodebug/debug.txt', "inhe4re", FILE_APPEND);
        //We want to send user an email to let them know they have successfully reset their password so they can start to use it. 
        //the link in the email.
        $sentto = Array();
        $replyto = Array();
        $ccto = Array();
        $bccto = Array();
        $attachment = Array();
        $subject = "";
        $body = "";
        //Need to get the email for this person
        $thisfields = Array(); //reset array
        $thiswhere = Array();
        $realfirstname = "";
        $reallastname = "";
        $realemail = "";

        $thisfields = ["firstname", "lastname", "email"];
        $thiswhere = array("recno" => $_POST['txtrecno']);
        //file_put_contents('./dodebug/debug.txt', "recno: ".$_POST['divrecno'], FILE_APPEND);
        $rs = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        foreach($rs as $row)
        {
            $realfirstname = $row['firstname'];
            $reallastname = $row['lastname'];
            $realemail = $row['email'];  
        }
        $sendto[] = array($realemail => $realfirstname." ".$reallastname);
        //file_put_contents('./dodebug/debug.txt', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
        $subject = "Password Reset";
        $body = "Your password has been reset.  You may now start to use your new password.";
        $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
        //echo $sendstatus;
    }
    //file_put_contents('./dodebug/debug.text', $result, FILE_APPEND);
    echo "Success";
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>
        <br>
        <div class="main-div-body">
            <?php
                $db = new PDOCON();
                $thistable = "employee_master";
                $thisfields = array('vericode', 'recno');
                $thiswhere = array("vericode" => $_GET['vericode']);
                $rs = $db -> PDOQuery($thistable, $thisfields, $thiswhere); //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
                foreach($rs as $row)
                {
                    $recno = $row['recno'];
                }
                if(isset($rs))
                {
                    //If there is a record, we know we need to verify?>
                    <form id="frmverifypassword" name="frmverifypassword" method="post">
                        <div id="divrecno" class="div-veriheader">
                            Reset Password
                        </div>
                        <script type="text/javascript">
                            $("#divrecno").data("recno", <?= $recno ?>);
                        </script>
                        <div>
                            <div class="div-verify-pwmin">
                                Password must contain at least:<br><br>
                                1.  At least 8 characters long.<br>
                                2.  At least a small letter.<br>
                                3.  At least a capital letter.<br>
                                4.  At least 1 number.<br>
                                5.  At least 1 special character (@#$%^&*).<br><br>
                            </div>
                            <div class="div-confirmpassword">
                                <div class="div-passwordlbl">Password: </div>
                                <div class="div-password"><input type="password" class="input-login-password required" id="txtpassword" name="txtpassword" value="" placeholder="Type in a new password" onchange="validatePassword(this);" required /></div>
                            </div>
                            <div class="div-confirmpassword">
                                <div class="div-passwordlbl">Confirm Password: </div>
                                <div class="div-password"><input type="password" class="input-login-password required" id="txtconfirmpassword" value="" placeholder="Type in a new password" onchange="validatePassword(this);" required /></div>
                            </div>
                            <div class="div-confirmbuttons">
                                <button onclick="submitFormresetpw();" value="Submit">Submit</button>
                                <button value="Cancel" onclick="clearForm();">Clear</button>
                            </div>
                        </div>
                    </form><?php
                }
                else
                {
                    //If we do not have anything back, that means it's already verified or expired.?> 
                    <div class="div-verifexpiredheader">This link has expired.</div><?php
                }
            ?>
        </div>
    </div><?php
}