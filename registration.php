<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/PageloaderClass.php");
require("./common/classes/DateClass.php");
require("./common/classes/EmailClass.php");
require("./common/classes/PasswordClass.php");
require("./common/classes/LoginClass.php");
require("./common/classes/EmployeenoClass.php");
require("./common/prompt.php");
$load_headers = new PageloaderClass();
$pr = new PROMPT();
$db = new PDOCON();
$nd = new Date_Class();
$ne = new Email_Class();
$pc = new Password_Class();
$nl = new Login_Class();
$en = new Employeeno_Class();

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
             function submitRegistrationform(){
                if($("#txtfirstname").val() == ""){
                     alert("Employee First name can not be empty.");
                     return(false);
                }
                if($("#txtlastname").val() == ""){
                     alert("Employee last name can not be empty.");
                     return(false);
                }
                if($("#txtemail").val() == ""){
                     alert("Email can not be empty.");
                     return(false);
                }
                if($("#txtlogin").val().length < 3){
                     alert("Employee login can not be empty.");
                     return(false);
                }
                if($("#txthiredate").val() == ""){
                     alert("Must have a hired date.");
                     $("#txthiredate").focus();
                     return(false);
                }
                if($("#txtpassword").val() == ""){
                     alert("Password can not be empty.");
                     return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitRegistration&'+$("#frmregistration").serialize(), function(result){
                    //alert(result);
                    if(result == "Success"){
                        alert("User added successfully.  User must login and verify account before they can access the web contents.");
                        //window.open.href = "localhost";
                        //window.open('','_self').close();
                        window.location.reload();
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
function SubmitRegistration()
{
    global $db, $nd, $en, $ne, $pc, $nl, $load_headers, $pr; 
    $isFailed = false;
    
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
    //We want to just double check the pw one more time in server side to make sure it is good
    //file_put_contents('./dodebug/debug.txt', $_POST['txtbirthday'].' and '.$_POST['txthiredate'], FILE_APPEND);
    
    if(strlen($_POST['txtlogin']) < 3)
    {
        $result = "Login has to be atleast 3 characters long.";
        $isFailed = true;
    }
    
    //We will also need to validate the employee number.
    $ne->set_email($_POST['txtemployeenumber']); //We set the email first
    if($db->PDORowcount(($db->PDOMiniquery($en->check_login()))) > 0)
    {
        $result = "This employee number already exists.  Please use another.";
        $isFailed = true;
    }
    
    //We only check dates if we have a date
    if(isset($_POST['txtbirthdate']))
    {
        $nd->set_date($_POST['txtbirthdate']);  //Set the date first
        $thisdate = $nd->validate_date(); //We evaluate the date
        if($thisdate)
        {
            $birthdate = $nd->compare_dates($_POST['txtbirthdate'], date('Y-m-d'), 'Greater');
            if($birthdate == false)
            {
                $result = "Birthdate can not be greater than current date.";
                $isFailed = true;
            }
        }
        else
        {
            $result = "BAD birthdate detected.  Please make sure the birthdate is in correct format, mm/dd/yyy.  Ex: 01/22/2023.";
            $isFailed = true;
        }
    }
    
    $nd->set_date($_POST['txthiredate']);  //Set the date first
    $thisdate = $nd->validate_date(); //We evaluate the date
    if($thisdate == false)
    {
        $result = "BAD hiredate detected.  Please make sure the hiredate is in correct format, mm/dd/yyy.  Ex: 01/22/2023.";
        $isFailed = true;
    }

    $ne->set_email($_POST['txtemail']); //We set the email first
    if($ne->validate_email() == false)
    {
        $result = "Bad email format detected.  Please make sure the email is in somename@some.domain.  Ex: info@diversityfade.come.";
        $isFailed = true;
    }
    
    if($db->PDORowcount(($db->PDOMiniquery($ne->check_email()))) > 0)
    {
        $result = "This email already exists.  Please use another.";
        $isFailed = true;
    }
    
    //We will check if login already been used.
    $nl->set_login($_POST['txtlogin']);
    if($db->PDORowcount(($db->PDOMiniquery($nl->check_login()))) > 0)
    {
       $result = "This login already exists.  Please use another.";
       $isFailed = true; 
    }

    if($isFailed == false)
    {
        
        $thisfields = Array();
        $thistable = "users";
        //$sendstatus= "";
        $realpassword = "";
        $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.
        $realvericode = $load_headers ->Hash_Me_Vericode();
        
        //We want to send user an email and allow them to verify the email and change their password once they clicked on 
        //the link in the email.
        /*
        $sentto = Array();
        $replyto = Array();
        $ccto = Array();
        $bccto = Array();
        $attachment = Array();
        $subject = "";
        $body = "";
        //Need to get the email for this person
        */
        $thisfields = Array();
        $thiswhere = Array();
        $realfirstname = "";
        $reallastname = "";
        $realemail = "";
        $tempdir = $_POST['txtfirstname'].$_POST['txtlastname'].'_'.$_POST['txtlogin'];
        $thisdata = array("employeenumber" => $_POST['txtemployeenumber'], 
                "firstname" => $_POST['txtfirstname'], 
                "middlename" => $_POST['txtmiddlename'], 
                "lastname" => $_POST['txtlastname'],
                "birthday" => (!isset($_POST['txtbirthdate']) ?  null : date('Y-m-d', strtotime($_POST['txtbirthdate']))), 
                "hiredate" => (!isset($_POST['txthiredate']) ? null : date('Y-m-d', strtotime($_POST['txthiredate']))), 
                "email" => $_POST['txtemail'],
                "login" => $_POST['txtlogin'], 
                "password" => $getpasssword, 
                "media_dir" => $tempdir,
                "address" => (!isset($_POST['txtaddress']) ? null : $_POST['txtaddress']), 
                "city" => (!isset($_POST['txtcity']) ? null : $_POST['txtcity']),
                "state" => (!isset($_POST['txtstate']) ? null : $_POST['txtstate']),
                "zipcode" => (!isset($_POST['txtzipcode']) ? null : $_POST['txtzipcode']),
                "vericode" => $realvericode,
                'isActive' => true,
                'isBarber' => true);  
        $inresult = $db->PDOInsert($thistable, $thisdata);
        //file_put_contents('./dodebug/debug.txt', "1 what is result?: ".$inresult, FILE_APPEND);
        
        if(!is_null($inresult))
        {
            //We want to also insert schedule for this Barber
            $thistable = "schedules";
            $thisdata = array('foreign_ur' => $inresult,
                    'Sunday' => 'OFF',
                    'Monday' => 'OFF',
                    'Tuesday' => '10:00 AM, 6:00 PM',
                    'Wednesday' => '10:00 AM, 6:00 PM',
                    'Thursday' => '10:00 AM, 6:00 PM',
                    'Friday' => '10:00 AM, 6:00 PM',
                    'Saturday' => '10:00 AM, 6:00 PM');
            $db->PDOInsert($thistable, $thisdata);
            
            
            //We want to send verification email to the email above so customer can verify it.
            $sentto = Array();
            $replyto = Array();
            $ccto = Array();
            $bccto = Array();
            $attachment = Array();
            $subject = "";
            $body = "";
            
            //Since we successfuly created the acc we want to add the media dir for this user if the folder does not exist yet.
            $pr->CreateUserDirectory($tempdir);
            /*
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
            }*/
            $sendto[] = array($_POST['txtemail'] => $_POST['txtfirstname']." ".$_POST['txtlastname']);
            //file_put_contents('./dodebug/debug.text', $_POST['txtemail']." <=> ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
            $subject = $ne->get_verification_subject();
            $body = $ne->get_verification_body($thisserver, $realvericode);
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            $result = "Success";
        }
        else
        {
            $result = "Failed";
        }
    }
    echo $result;
}
function Main()
{
    global $db, $load_headers;
    $load_headers::Load_Header_Logo(false);?>
    <div class="main-div-registration"><?php
        
        $sql = "SELECT employeenumber FROM users ORDER BY recno DESC LIMIT 1";
        $result = $db ->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $newempno = $rs['employeenumber'] + 1;
        }
        while(strlen($newempno) < 5)
        {
            $newempno = "0".$newempno;
        }
        //$newempno should be 0000n where n is a number from 1 to ...?>
        <div class="div-header-main-container">Barber Registration</div>
        <div class="div-body-container">
            <form name="frmregistration" id="frmregistration" method="post">
                <table class="tbl-register">
                    <tr>
                        <td class="tbl-register-lbl">Employee No.: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="firstname" id="txtemployeenumber" name="txtemployeenumber" size="24"  value="<?php echo $newempno?>" required readonly /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">First Name: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="firstname" id="txtfirstname" name="txtfirstname" size="24"  value="" required /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Middle Name: </td>
                        <td class="registrationinput"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" size="24"  value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Last Name: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="lastname" id="txtlastname" name="txtlastname" size="24"  value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Birth Day: </td>
                        <td class="registrationinput"><input type="text" class="datepicker" id="txtbirthdate" style="margin-top: -10px;" name="txtbirthdate" size="24" value="" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Hire Date: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="datepicker" id="txthiredate" name="txthiredate" style="margin-top: -10px;" size="24" value="" onfocus="getJDate(this, true);" placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Email: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="email required" id="txtemail" name="txtemail"size="24"  value="" onchange="validateEmail(this);" size="20" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Login: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="login" id="txtlogin" name="txtlogin"size="24"  value=""  /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Password: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="password" id="txtpassword" name="txtpassword" size="24"  value="<?php echo mt_rand(1000, 9999)?>"  /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Address: </td>
                        <td class="registrationinput"><input type="text" class="address" id="txtaddress" name="txtaddress" size="24"  value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">City: </td>
                        <td class="registrationinput"><input type="text" class="city " id="txtcity" name="txtcity" size="24" value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">State: </td>
                        <td class="registrationinput"><input type="text" class="state" id="txtstate" name="txtstate" size="24" value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Zip-code: </td>
                        <td class="registrationinput"><input type="text" class="zipcode" id="txtzipcode" name="txtzipcode" size="24" value="" /></td>
                    </tr>
                    <tr class="tr-register-btn-container">
                        <td class="tbl-register-lbl align-center" colspan="2">
                            <button type="button" value="Submit" id="btnfrmregistration" onclick="submitRegistrationform();">Submit</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div><?php 
    $load_headers::Load_Footer();
}