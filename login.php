<?php
ob_start();
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\EmailClass;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./Common/sendmail.php");
$pc = new PageloaderClass();
$db = new PdoClass();
$ne = new EmailClass();
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');// will get 'localhost'
if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/website_ad583fcd/Common/page.php");
}
else
{
    require_once("./Common/page.php");
}
if(count($_POST) > 0 && isset($_POST['hid_cmd']))
{
    $_REQUEST['hid_cmd']();
    exit();
}?>
<!DOCTYPE html>
<html>
    <head>
        <?php
            $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); // will get 'localhost'
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $pc->Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            $('body').data('thishost', '<?= $temp_host ?>');
            function submitIndexform(){
                if($("#txtlogin").val() == ""){
                    alert('Please enter user name.');
                    return(false);
                }
                if($("#txtpassword").val() == ""){
                    alert('Please enter a password.');
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', $("#frmlogin").serialize(), function(result){
                    if(result !== "Success")
                    {                        
                        //alert(result);
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
function SubmitLoginForm()
{
    global $db, $pc, $temp_host, $ne;
    //file_put_contents("./dodebug/debug.txt", 'verify1', FILE_APPEND);
    $thisfields = array();
    $thiswhere = array();
    $isNotbarber = false;
    //We want to handle first time customer login with default password with 4 digi, to do this, we have to check the length of it
    //and check against the table
    if(strlen($_POST['txtpassword']) != 4)
    {    
        //file_put_contents("./dodebug/debug.txt", "Here now", FILE_APPEND);  //2137
        $thisfields = array('recno', 'firstname', 'lastname', 'email', 'login', 'media_dir', 'ispasswordchanged', 'isverified', 'isactive', 
            'isauthenticated', 'isauthenticatedverified', 'profile', 'isAdmin', 'isBarber', 'isDeveloper');
        $thistable = "users";
        $getpasssword = $pc -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.      
        
        //Now, we have to handle users or customers that uses their email as login, we have to check if it is an email or not and handle it accordingly
        $ne ->set_email($_POST['txtlogin']);
        if($ne->validate_email($_POST['txtlogin']))
        {
            //It is email
            $thiswhere = array("email" => $_POST['txtlogin'], "password" => $getpasssword);
        }
        else
        {        
            $isNotbarber = true;
            $thiswhere = array("login" => $_POST['txtlogin'], "password" => $getpasssword);
        }
        $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        if(isset($result))
        {
            foreach($result as $row)
            {
                if($row['ispasswordchanged'] == false && $row['isverified'] == false)
                {
                    //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);?>
                    <script type="text/javascript">
                        alert("This account hasn't been confirmed yet.  You may not login at this time.");
                        window.location.href = window.location.href
                    </script><?php
                }
                else if($row['isactive'] == false)
                {
                    //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);?>
                    <script type="text/javascript">
                        alert("Account is inactive, contact your administrator.");
                        window.location.href = window.location.href
                    </script><?php
                }
                else if($row['isauthenticated'] == true && $row['isauthenticatedverified'] == false)
                {
                    //If isauthenticatedverified is false, that means it's not yet verified, if it is true, that means user already verified so it will not come
                    //through hgere.
                    //If we get here, we know the password and login is good so we will take care of the verification code for 2 factor authentication.
                    $sentto = Array();
                    $replyto = Array();
                    $ccto = Array();
                    $bccto = Array();
                    $attachment = Array();
                    //PDOInsert($thistable=null, $thisdata=null)
                    $realfirstname = $row['firstname'];
                    $reallastname = $row['lastname'];
                    $realemail = $row['email'];  
                    $temptime =  substr(time(), -5);
                    $realtime = $pc -> Hash_Me_Password($temptime);

                    $thisdata = array('twofactorcode' => $realtime, 'isauthenticatedverified' => false);
                    $thiswhere = array('recno' => $row['recno']);
                    $db->PDOUpdate($thistable, $thisdata, $thiswhere, $row['recno']);

                    $sendto[] = array($realemail => $realfirstname." ".$reallastname);
                    //file_put_contents('./dodebug/debug.txt', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
                    $subject = "Authentication Required to login.";

                    $body = "Please use this code to verify your account to login.<br><br>CODE: $temptime";
                    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
                    $_SESSION['temprecno'] = $row['recno'];
                    //echo "Authenticate";
                    //WE ARE NOT GOING TO DEAL WITH THIS UNTIL WE WANT TO EANBLE 2 FACTORS AUTHENTICATION
                }
                else
                {
                    $_SESSION['companyname'] = "Ka's Papaya Sauce"; //Hard coded but will be replace by the individual's company below.
                    $_SESSION['companyname_recno'] = 0;
                    $sql = "SELECT * FROM company_info";
                    $result = $db ->PDOMiniquery($sql);
                    if($db ->PDORowcount($result) > 0)
                    {
                        foreach($result as $rs)
                        {
                            $_SESSION['companyname'] = $rs["name"];
                            $_SESSION['companyname_recno'] = $rs["recno"];
                            $_SESSION['companyphonenumber'] = $rs['phone_number']; //In format of 1234567890, 10 number no space or dashes.
                            $_SESSION['main_logo'] = $rs['mainlogo'];
                        }
                    }
                    if($isNotbarber == true)
                    {
                        $_SESSION['user'] = $row['login'];
                    }
                    else
                    {
                        $_SESSION['user'] = $row['email'];
                    }
                    $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
                    $_SESSION['user_recno'] = $row['recno'];
                    $_SESSION['media_dir'] = $row['media_dir'];
                    $_SESSION['usersearchlist'] = array();
                    $_SESSION['customersearchlist'] = array();
                    $_SESSION['profile'] = $row['profile'];
                    $_SESSION['thiswebsite'] = "$temp_host";
                    $_SESSION['isAdmin'] = $row['isAdmin'];
                    $_SESSION['isBarber'] = $row['isBarber'];
                    $_SESSION['realsandpro'] = "Sandbox";
                    $_SESSION['isDeveloper'] = $row['isDeveloper'];
                    //file_put_contents('./dodebug/debug.txt', "What is admin? ".$_SESSION['isAdmin'], FILE_APPEND);
                    //Since we successfully logged in, we want to make vericode NULL so that it wil negate any new password change request or verification

                    $thisdata = array('vericode' => NULL);
                    $thiswhere = array('recno' => $_SESSION['user_recno']);
                    $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
                    if(!isset($rows))
                    {
                        echo "Failed To Update vericode to NULL";
                    }
                    else
                    {
                        header('Location: ./index.php');
                    }
                }
            }
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", 'Failed', FILE_APPEND);?>
            <script type="text/javascript">
                alert("You have entered a wrong login or password.  Pleaase try again.");
                window.location.href = window.location.href
            </script><?php
        }
    }
    else
    {
        //We handle first time logger with default password.
        $sql = "SELECT * FROM users WHERE email = '".$_POST['txtlogin']."' AND password = '".$_POST['txtpassword']."'";
        $result = $db->PDOMiniquery($sql);
        if($db->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                $thisrecno = $rs['recno'];
            }?>
            <script type="text/javascript">
                alert("Please update your password.");
            </script><?php     
            $_SESSION['temprecno'] = $thisrecno;
            header("Location: ./passwordreset.php?from=Default&vericode=Default");
            exit();
        }
        else
        {?>
            <script type="text/javascript">
                alert("No such user.  Please try again.");
                window.location.href = window.location.href
            </script><?php
        }
    } 
}
function Main()
{
    global $db, $pc;
    if(!isset($_SESSION['fullname']))
    {?>
        <div class="main-div">
            <div style="width: 90%; margin: auto; background-color: #f0f5f5;">
                <div class="main-logo float-left">
                    <?php echo $pc->LoadLogo($db);?>
                </div>
                <div class="float-left" style="width: 7%;"><?php echo $pc->LoginPanel();?></div>
                <div class="div-main-tabs-container">
                    <div class="float-left div-main-tabs div-main-tab-slted cursor-pointer align-center" id="div_main" onclick="mainTabs(this);">Main</div>
                    <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_products" onclick="mainTabs(this);">Products</div>-->
                    <div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_videos" onclick="mainTabs(this);">Videos</div>
                    <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_recipe" onclick="mainTabs(this);">Recipe</div>-->
                    <div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_about" onclick="mainTabs(this);">About</div>
                </div>
                <div class="div-content-holder-flex align-center">
                    <div class="align-center" style="width: 80%;">
                        <form name="frmlogin" id="frmlogin" method="post">
                            <input type="hidden" name="hid_cmd" id="hid_cmd" value="SubmitLoginForm" />
                            <div id="logincontainer">
                                <div class="div-loginname">
                                    <div class="div-namelbl">Login: </div>
                                    <div class="div-user"><input type="text" class="input-login-user required" id="txtlogin" name="txtlogin" value="" placeholder="Type in your login" required /></div>
                                </div>
                                <div class="div-loginname">
                                    <div class="div-passwordlbl">Password: </div>
                                    <div class="div-password"><input type="password" class="input-login-password required" id="txtpassword" name="txtpassword" value="" placeholder="Type in password" required /></div>
                                </div>
                                <div class="div-buttons">
                                    <button class="cursor-pointer align-center" onclick="submitIndexform();" value="Submit">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="align-center" style="height: 5%;"><?php echo $pc->Load_Footer();?></div>
            </div>
        </div><?php
    }
    else
    {
        header('Location: ./index.php');
    }
}