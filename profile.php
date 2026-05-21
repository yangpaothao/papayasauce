<?php
require __DIR__ . '/Common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');// will get 'localhost'

if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/website_ad583fcd/Common/page.php");
}
else
{
    require_once("./Common/page.php");
}
$load_headers = new PageloaderClass();
$pc = new PromptClass();
$db = new PdoClass();
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
            $load_headers -> Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            var pickedDates = [];
            //need to check for when trying to undo the OFF for all and for individual.
            $(document).ready(function(){
                getUserprofile($("#div_menu_profile")[0]); //Initially we will show this user's profile.
            });
            function profileMenuslt(obj){
                $(".div-menu-profile").each(function(){
                    if($(obj).prop('id') == $(this).prop('id')){
                        $(obj).removeClass("div-tab-nonslted").addClass("div-tab-slted");
                    }
                    else{
                        $(this).removeClass("div-tab-slted");
                        $(this).addClass("div-tab-nonslted");
                    }
                })
                $(obj).addClass("div-tab-slted");
            }
            function getUserprofile(obj){
                profileMenuslt(obj);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetUserprofile', function(result){
                    $("#main_div_body_profile_right_container").html(result);
                });
            }
            function changePassword(obj){
                profileMenuslt(obj);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ChangePassword', function(result){
                    $("#main_div_body_profile_right_container").html(result);
                });
            }
            function clearOldpassword()
            {
                $("#txtpassword").val('');
            }
            function clearForm()
            {
                $("#textnewpassword").val('');
                $("#txtconfirmnewpassword").val('');
            }
            function validatePassword(obj){
               
               if(checkPassword(obj) == false){
                   $(obj).focus();
                   $(obj).select();
                   return(false);
               }
               if($(obj).prop('id') == "txtpassword"){
                   //We are going to check if the password user enter is correct.
                   $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ValidatePassword&txtpassword='+$("#txtpassword").val(), function(result){
                        if(result == "Failed")
                        {
                            alert("The password you enter does not match the one in the system.  Please try again.");  //"The password you entered does not match the current password.  Please trya gain.";
                            $("#txtpassword").val('**********');
                            $("#txtpassword").focus();
                            $("#txtpassword").select();
                            return(false);
                        }
                    });
               }
               else
               {
                   if($("#txtnewpassword").val() != "" && $("#txtconfirmnewpassword").val() != ""){
                        if($("#txtnewpassword").val() != $("#txtconfirmnewpassword").val()){
                            alert("Password does not match, please try again.");
                            $(obj).focus();
                            $(obj).select();
                            return(false);
                        }
                    }
               }
            }
            function submitNewpassword(){
               $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitNewpassword&txtpassword='+$("#txtnewpassword").val(), function(result){
                   if(result != "Success")
                   {
                       alert("ERROR in profile js line 110.  Failed to update password.  Contact I.T.");
                       return(false);
                   }
                   else{
                       alert("Successfully updated password.")
                   }
               });
            }
            function emptyInputtext(obj){
                $(obj).select();
            }
            function updateProfile(obj){
                if($(obj).prop('id') == "txtfirstname" && $(obj).val() == ""){
                    alert("First Name can't be empty.");
                    //alert($("body").data($(obj).prop('id')+"_data"));
                    $(obj).val($("body").data($(obj).prop('id')+"_data"));
                    $(obj).focus();
                    return(false)
                }
                if($(obj).prop('id') == "txtlastname" && $(obj).val() == ""){
                    alert("Last Name can't be empty.");
                    $(obj).focus();
                    return(false)
                }
                //User entered a password, we will now check this password.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateProfile&thisfield='+$(obj).prop('id').substring(3)+'&thisval='+$(obj).val(), function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Wrong password.  Please try again");
                        return(false);
                    }
                    else if(result == "Bad State"){
                        alert("State does not exist, please try again.  Enter the 2 letter abbreviation or the full name.");
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
                        return(false);
                    }
                    else{
                        if($(obj).prop('id') == "txtstate"){
                            $(obj).val(result);
                        }
                    }
                });
            }
            function getVal(obj){
                //alert($(obj).val());
                if($(obj).prop('id') != "chkterminate" && $(obj).prop('id') != "chkactive" && $(obj).prop('id') != "chkdeleted" && $(obj).prop('id') != "chkisbarber"){
                    $("body").data($(obj).prop('id'), $(obj).val());
                }
                else{
                    if($(obj).is(":checked")){
                        $("body").data($(obj).prop('id'), "checked");
                    }
                    else{
                        $("body").data($(obj).prop('id'), "");
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
function UpdateProfile()
{
    global $db, $pc; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $thisstate = "";
    $thisrealval = $_POST['thisval'];
    if($_POST['thisfield'] == "state")
    {
        $thisrealval = $pc ->GetStates($_POST['thisval']);
        //file_put_contents('./dodebug/debug.txt', "profile state: $thisstate \n", FILE_APPEND);
    }
    if($thisrealval != "Bad State")
    {
        $thistable = "users";
        if($_POST['thisfield'] == "birthday" || $_POST['thisfield'] == "hiredate")
        {
            $formatthisdate = date('Y-m-d', strtotime($_POST['thisval']));
            $thisdata = array($_POST['thisfield'] => $formatthisdate); 
        }
        else
        {
            $thisdata = array($_POST['thisfield'] => $thisrealval);
        }
        $thiswhere = array('recno' => $_SESSION['user_recno']);
        $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
        if(!isset($rows))
        {
            echo "Failed";
        }
        else
        {
            echo "$thisrealval";
        }
    }
    else
    {
        echo "Bad State";
    }
}
function ValidatePassword()
{
    global $db, $load_headers;
    $thisfields = Array("password");
    $thistable = "users";
    $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.     
    $thiswhere = array("recno" => $_SESSION['user_recno'], 'password' => $getpasssword);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(!isset($result))
    {
        echo "Failed";
    }
    else
    {
        echo "Success";
    }
}
function SubmitNewpassword()
{
    //4, 6, 8, 20, 29, 26
    global $db, $load_headers; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $thistable = "users";
    $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.
    $thisdata = array('password' => $getpasssword);
    $thiswhere = array('recno' => $_SESSION['user_recno']);
    $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!isset($rows))
    {
        echo "Failed";
    }
    else
    {
        echo "Success";
    }
}
function GetUserprofile()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $profileimage = "";
    $thumbnail = "";
    $thismedia = "";
    $thistable = "users";
    $thisfields = array('recno', 'firstname', 'middlename', 'lastname', 'birthday', 'address', 'address2', 'city', 'state', 'zipcode', 'login', 'email', 'phone_number', 'media_dir');
    $thiswhere = array('recno' => $_SESSION['user_recno']);    
    $rows = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>
    <div id="div_profile" style="width: 100%; height: 400px;">
            <table class="tbl-profile float-left">
                <?php
                foreach($rows as $rs)
                {
                    $thismedia = $rs['media_dir'];?>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">First Name:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtfirstname" name="txtfirstname" onchange="updateProfile(this);" onfocus="saveMydata(this);" value="<?= $rs['firstname'] ?>" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Middle Name:</td><td><input class="user-profile-input" type="text" id="txtmiddlename" name="txtmiddlename" onchange="updateProfile(this);" value="<?= $rs['middlename'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Last Name:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtlastname" name="txtlastname" onchange="updateProfile(this);" value="<?= $rs['lastname'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Address:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtaddress" name="txtaddress" onchange="updateProfile(this);" value="<?= $rs['address'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Address2:</td><td><input class="user-profile-input" type="text" id="txtaddress2" name="txtaddress2" onchange="updateProfile(this);" value="<?= $rs['address2'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">City:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtcity" name="txtcity" onchange="updateProfile(this);" value="<?= $rs['city'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">State:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtstate" name="txtstate" onchange="updateProfile(this);" value="<?= $rs['state'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Zip-Code:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtzipcode" name="txtzipcode" onchange="updateProfile(this);" value="<?= $rs['zipcode'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Phone:<span class="asterisk"> * </span></td><td><input class="user-profile-input" type="text" id="txtphone_number" name="txtphone_number" onchange="updateProfile(this);" value="<?= $rs['phone_number'] ?>" onfocus="saveMydata(this);" /></td></tr>
                    <tr><td class="user-profile-lbl tbl-profile-lbl">Email:</td><td><input class="user-profile-input" type="text" id="txtemail" name="txtemail" value="<?= $rs['email'] ?>" readonly onfocus="saveMydata(this);" /></td></tr>
                </tr><?php 
                }?>
            </table>
    </div><?php
}
function ChangePassword()
{?>
    <table id="tblpassword" name="tblpassword" class="tbl-profile">
        <tr><td class="user-profile-lbl tbl-profile-lbl">Old Password:</td><td><input class="user-profile-input" type="password" id="txtpassword" name="txtpassword" onchange="validatePassword(this);" onclick="clearOldpassword();" value="********************" /></td></tr>
        <tr><td class="user-profile-lbl tbl-profile-lbl">New Password:</td><td><input class="user-profile-input" type="password" id="txtnewpassword" name="txtnewpassword" onchange="validatePassword(this);" value=""/></td></tr>
        <tr><td class="user-profile-lbl tbl-profile-lbl">Confirm New Password:</td><td><input class="user-profile-input" type="password" id="txtconfirmnewpassword" name="txtconfirmnewpassword" onchange="validatePassword(this);" value=""/></td></tr>
        <tr><td class="tbl-profile-lbl" colspan="2" style="width: 100%; text-align: center;">
            <button type="button" onclick="submitNewpassword();">Submit</button>
            <button type="button" onclick="clearForm();">Clear</button>
        </tr>    
    </table><?php
}
function Main()
{
    global $db, $pc, $load_headers; $this_page?><?php
    //We are sending false into the load_header_logo(false) because we do not want the logo to show, just the other stuffs.
    $load_headers->Load_Header($this_page);?>
    <div class="main-div">
        <div style="width: 90%; margin: auto; background-color: #f0f5f5;">
            <div class="main-logo float-left">
                <?php echo $load_headers->LoadLogo($db);?>
            </div>
            <div class="float-left" style="width: 7%; display: block;"><?php echo $load_headers->LoginPanel();?></div>
            <div class="div-main-tabs-container">
                <div class="div-menu-profile float-left div-tab-slted cursor-pointer align-center" id="div_menu_profile" onclick="getUserprofile(this);">User Profile</div>
                <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_products" onclick="mainTabs(this);">Products</div>-->
                <div class="div-menu-profile float-left div-tab-nonslted cursor-pointer align-center" id="div_menu_password" onclick="changePassword(this);">Change Password</div>
                <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_recipe" onclick="mainTabs(this);">Recipe</div>-->
                <!--<div class="div-menu-profile float-left div-tab-nonslted cursor-pointer align-center" id="div_modifyevent" onclick="paymentSettings(this);">Payment/Settings</div>-->
            </div>
            <div class="div-content-holder-flex align-center">
                <div id="main_div_body_profile_right_container" class="main-div-body-profile-right-container"></div> 
            </div>
            <div class="align-center" style="height: 5%;"><?php echo $load_headers->Load_Footer();?></div>
        </div>
        

    </div><?php
}?>