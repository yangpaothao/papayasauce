<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/pdocon.php");
require("./common/classes/PageloaderClass.php");
require("./common/classes/EmailClass.php");
require("./common/prompt.php");
require("./common/sendmail.php");
require("./common/classes/DateClass.php");
require("./common/classes/PasswordClass.php");
require("./common/classes/LoginClass.php");
require("./common/classes/EmployeenoClass.php");

$load_headers = new PageloaderClass();
$db = new PDOCON();
$nd = new Date_Class();
$ne = new Email_Class();
$pc = new Password_Class();
$nl = new Login_Class();
$en = new Employeeno_Class();
$pt = new PROMPT();
//file_put_contents("./dodebug/debug.txt", 'menuresult: '.$thisauth, FILE_APPEND);
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
            //file_put_contents("./dodebug/debug.txt", 'menuresult: '.$thisauth[0], FILE_APPEND);
            //$thisauth now holds an array of 'Read', 'Write', 'Modify', and or 'Delete',
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                manageUsers($("#div_manageuser")[0]);
            });
            function adminMenuslt(obj){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
            }
            function manageUsers(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageUsers', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                    searchUser(obj, 'Users');
                });
            }
            function getUserdata(obj, recno = ''){
                if(recno == ""){
                    recno = $(obj).val();
                }
                //recno - this is the recno for the recno column in the users table
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetUserdata&thisrecno='+recno, function(result){
                    //alert(result);
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_mgm_search").html(result); 
                }); 
            }
            function clearSearchuser(){
                $("#sltsearchuser").remove();
                $("#tbluserdata").remove();
                $("#txtsearchuser").val('');
                $("#txtsearchuser").focus();
            }
            function searchUser(obj, thisfrom){
                //from will be either 'Schedule' or 'Users'
                thisactive = 'false';
                thisterminate = 'false';
                thisbarber = "false";
                if($("#chkactive").is(":checked")){
                    thisactive = 'true';
                }
                if($("#chkbarber").is(":checked")){
                    thisbarber = 'true';
                }
                if($("#chkterminate").is(":checked")){
                    thisterminate = 'true';
                }
                /*
                if($("#txtsearchuser").val().trim().length == 0){
                    clearSearchuser();
                    $.post('<?php //echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchUserunset&txtsearchuser='+$("#txtsearchuser").val(), function(){
                        return(false);
                    }); 
                }*/    
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchUser&txtsearchuser='+$("#txtsearchuser").val()+'&isTerminated='+thisterminate+'&isActive='+thisactive+'&isBarber='+thisbarber+'&thisfrom='+thisfrom, function(result){
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();
                    }
                    $("#tbluserdata").remove();
                    //$("#div_mgm_search").after(result); //div_searchuser_container
                    $("#div_searchuser_container").after(result)
                }); 
            }
            function updateUser(obj, recno){     
                if($(obj).prop('id') == "chkisBarber" || $(obj).prop('id') == "chkisActive" || $(obj).prop('id') == "chkisAdmin"){
                    if($(obj).is(":checked")){
                        realval = 'true';
                    }
                    else{
                        realval = 'false';
                    }
                    
                }
                else{
                    realval = $(obj).val();
                }
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateUser&thisrecno='+recno+'&thisfield='+$(obj).prop('id').slice(3)+'&thisvalue='+realval, function(result){
                    //alert(result);
                    if(result == "Success"){
                        //alert('Updated');
                    }
                    else if(result == "Failed"){
                        alert('Failed to update.  Contact Administrator.');
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
                    }
                    else{
                        alert(result);
                        //alert($("body").data($(obj).prop('id')));
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
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
            function reloadUser(obj){
                searchUser(obj, 'Users');
            }
            function manageServices(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageServices', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                    searchServices();
                }); 
            }
            function searchServices(){
                thisactive = 'false';
                thisdeleted = 'false';
                
                if($("#rdoactive").is(":checked")){
                    rdoservice = $("#rdoactive").val();
                }
                if($("#rdodeleted").is(":checked")){
                    rdoservice = $("#rdodeleted").val();
                }
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchServices&rdoservice='+rdoservice, function(result){
                    if($("#tbl_admin_service").length > 0){
                        $("#tbl_admin_service").remove();
                    }
                    $("#div_mgm_search").after(result);
                    
                    /* this datatable works but don't need it
                    $("#tbl_admin_service").dataTable({
                    });
                    */
                }); 
            }
            function reloadService(){
                searchServices();
            }
            function getService(obj, recno){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetService&recno='+recno, function(result){
                    //alert(result);
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").html(result);  
                }); 
            }
            function updateService(obj, thisrecno, thisfield){                
                if($(obj).prop('id') == "chkactive" || $(obj).prop('id') == "chkdeleted"){
                    if($(obj).is(":checked")){
                        realval = 'true';
                    }
                    else{
                        realval = 'false';
                    }
                }
                else{
                    realval = $(obj).val();
                }
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateService&thisrecno='+thisrecno+'&thisfield='+thisfield+'&thisval='+realval, function(result){
                    //alert(result);
                    if(result != 'Success'){
                        alert(result);
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
                        $(obj).select();
                    }  
                }); 
            }
            function addService(){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=AddService', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").html(result);
                }); 
            }
            function submitNewservice(obj){
 
                let thisArray = [['title', $("#txtarea_service").val()],
                            ['time', $("#txttime").val()],
                            ['description', $("#textarea_comment").val()],
                            ['price', $("#txtprice").val()],
                            ['isactive', 'true']];

                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SubmitNewservice&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    if(result != "Success"){
                        alert(result);
                    }
                    else{
                        alert("Successfully added.");
                        $("#tblservicedata").find('input:text').val('');
                        $("#txtarea_service").val('');
                        $("#textarea_comment").val('');
                    }
                }); 
            }
            function addCompany(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=AddCompany', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#main_div_body_admin_right_container").html(result);
                }); 
            }
            function updateCompanyinfo(obj, thisrecno){
                //thisrecno is the recno of the table company_info
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateCompanyinfo&thisrecno='+thisrecno+'&'+$(obj).prop('id')+'='+$(obj).val(), function(result){
                    //alert(result);
                    if(result != "Success"){
                        alert(result);
                    }
                    else{
                        alert("Updated!");
                    }
                }); 
            }
            function submitNewcompany(){
                if($("#txtname").val() == ""){
                     alert("Company name can not be empty.");
                     return(false);
                }
                var form_data = new FormData($('#frmcompany')[0]);
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitNewcompany',
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if(result != "Success"){
                            alert(result);
                            preventDefault();
                            return(false);
                        }
                        else{
                            alert(result);
                        }
                    }
                });
            }
            function showCompanyimage(){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ShowCompanyimage', function(result){
                    //alert(result);
                    if(result != "Success"){
                        $("#div_search_containter").html(result);
                    }
                }); 
            }
            function selectImage(obj, thisfield, thisimage){
                //We are updating thisfield, either profile_image or thumb_nail in table attachments, depending on what they clicked in profile.
                //thisimage is the new image that will replace
                //alert("img_"+thisimage);
                //$("#"+thisimage).addClass("admin-company-image-bucket-selected"); //this line doesn't work for some reason so we are focusing a reload of the tab on success call below.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectImage&thisfield='+thisfield+'&thisimage='+thisimage, function(result){
                    //alert(result);
                    //If no error, we should return the old image so we can manipulate the dom, $thisoldimage
                    if(result != "Success"){
                        alert("Failed to select image.  Please contact I.T for help.");
                    }
                    else{
                        addCompany($("#div_managecompany")[0]);
                    }
                });
            }
            function adminMgmcomattach(obj, thisrecno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AdminMgmcomattach&thisrecno='+thisrecno, function(result){
                    //alert(result);
                    $("#div_admin_container").append(result);
                });
            }
            function cancelAdminattachment(){
                $("#div_body_admin_mgmcomp_attach_container").remove();
            }
            function submitAdminattachment(thisrecno){
                if($("#adminattachment").val() != ""){
                    form_data = new FormData($('#frmsubmitattachment')[0]);
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitAdminattachment&thisrecno='+thisrecno,
                        data: form_data,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if(result != "Success"){
                                alert(result);
                                preventDefault();
                                return(false);
                            }
                            else{
                                $("#div_body_admin_mgmcomp_attach_container").remove();
                                alert("Attachment added successfully.");
                                addCompany($("#div_managecompany")[0]);
                            }
                        }
                    });
                }
                else{
                    alert("Please select a file before you attempt to submit.");
                }
                event.preventDefault();
            }
            function introduction(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=Introduction', function(result){
                    //alert(result);
                    if($("#div_float_intro").length){
                        $("#div_float_intro").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function about(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=About', function(result){
                    //alert(result);
                    if($("#div_float_about").length){
                        $("#div_float_about").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function saveIntro(thisrecno){
                thisArray = [{"thisrecno": thisrecno, "thisval": $("#txtarea_intro").val()}];
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SaveIntroduction&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    if(result != 'Success'){
                        alert("Failed to update");
                        return(false);
                    }
                    else{
                        alert('Updated!');
                        return(false);
                    }
                });
            }
            function saveAbout(thisrecno){
                thisArray = [{"thisrecno": thisrecno, "thisval": $("#txtarea_about").val()}];
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SaveAbout&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    if(result != 'Success'){
                        alert("Failed to update");
                        return(false);
                    }
                    else{
                        alert('Updated!');
                        return(false);
                    }
                });
            }
            function removeLogo(thisimage){
                //thisrecno is recno for table users
                if(confirm("Are you sure you want to remove this image?")){
                    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=Removelogo&thisimage='+thisimage, function(result){
                        //alert(result);
                        if(result != 'Success'){
                            if(result == "Main Logo"){
                                alert("Can't delete the main logo.");
                                return(false);
                            }
                            else{
                                alert("Failed to delete LOGO!");
                                return(false);
                            }
                        }
                        else{
                            alert('Updated!');
                            addCompany($("#div_managecompany")[0]);
                        }
                    });
                }
                event.preventDefault();
            }
            function manageSchedule(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageSchedule', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();
                    }
                    $("#main_div_body_admin_right_container").html(result);
                }); 
            }
            function addNewuser(){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=AddNewuser', function(result){
                    //alert(result);
                    $("#main_div_body_admin_right_container").html(result); 
                }); 
            }
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
                        adminMenuslt($("#div_manageuser")[0]);
                        addNewuser();
                    }
                    else{
                        alert(result);
                        return(false);
                    }
                });
            }
            function getUserSchedule(obj, recno){
                if(recno == ""){
                    recno = $(obj).val();
                }
                //alert(recno);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetUserSchedule&thisrecno='+recno, function(result){
                    //alert(result);
                    $("#main_div_body_admin_right_container").html(result); 
                }); 
            }
            function modSchedulestatus(obj, thisrecno){
                thisday = $(obj).text();
                status = "";
                tobestatus = "";
                if($(obj).hasClass('img-admin-mgm-schedule-header-bgdarkgreen')){
                    status = "ON";
                    tobestatus = "OFF";
                }
                else if($(obj).hasClass('img-admin-mgm-schedule-header-bgdarkred')){
                    status = "OFF";
                    tobestatus = "ON";
                }
                //alert(status);
                //We want to confirm if user wants to update an OFF day.
                if(confirm("Are you sure you want turn "+thisday+" "+tobestatus+"?")){
                    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ModSchedulestatus&thisrecno='+thisrecno+'&thisstatus='+tobestatus+'&thisday='+thisday, function(result){
                        //alert(result);
                        if(result != "Success"){
                            alert(result);
                        }
                        else{
                            getUserSchedule($("#sltsearchuser")[0], thisrecno);
                        }
                    }); 
                }
            }
            function updateSlot(obj, thisrecno, thisday, actuallot){ 
                //thisday will be 0-6, 0 = Sunday, 6 = Saturday
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateSlot&thisrecno='+thisrecno+'&thisslot='+$(obj).text()+'&thisday='+thisday+'&actuallot='+actuallot, function(result){
                    //alert(result);
                    if(result != "Success"){
                        if(result == "OFF Day"){
                            alert("Today is an OFF day, please enable the day before you can turn on a slot.");
                            return(false);
                        }
                        else
                        {
                            alert(result);
                        }
                    }
                    else{
                        //alert('in here');
                        getUserSchedule($("#sltsearchuser")[0], thisrecno);
                    }
                }); 
                
            }
            function updateProfile(obj){
                //User entered a password, we will now check this password.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateProfile&thisfield='+$(obj).prop('id').substring(3)+'&thisval='+$(obj).val(), function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Wrong password.  Please try again");
                        return(false);
                    }
                    else if(result == "Bad State"){
                        alert("State does not exist, please try again.  Enter the 2 letter abbreviation or the full name.");
                        return(false);
                    }
                    else{
                        if($(obj).prop('id') == "txtstate"){
                            $(obj).val(result);
                        }
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

function GetUserdata()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $profileimage = "";
    $thumbnail = "";
    $thismedia = "";
    $thistable = "users";
    $thisfields = array('recno', 'firstname', 'middlename', 'lastname', 'birthday', 'hiredate', 'address', 'city', 'state', 'zipcode', 'login', 'email', 'media_dir', 'isActive', 'isAdmin', 'isBarber');
    $thiswhere = array('recno' => $_POST['thisrecno']);    
    $rows = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>
    <div class="div-admin-mgm-user" id="div_admin">
            <table class="tbl-admin-mgm-user float-left">
                <?php
                foreach($rows as $rs)
                {
                    $thismedia = $rs['media_dir'];?>
                    <tr><td class="tbl-admin-mgm-user-lbl">First Name:</td><td><input class="user-profile-input" type="text" id="txtfirstname" name="txtfirstname" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['firstname'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Middle Name:</td><td><input class="user-profile-input" type="text" id="txtmiddlename" name="txtmiddlename" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['middlename'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Last Name:</td><td><input class="user-profile-input" type="text" id="txtlastname" name="txtlastname" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['lastname'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Birthday:</td><td class="datepicker"><input class="user-profile-input" type="text" id="txtbirthday" name="txtbirthday" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= empty($rs['birthday']) ? '' : date('m/d/Y', strtotime($rs['birthday'])) ?>" style="margin-top: -10px;" onfocus="getVal(this);getJDate(this);"`onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" placeholder="dd/mm/yyy ex: 01/22/2022" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Hire Date:</td><td class="datepicker"><input class="user-profile-input" type="text" id="txthiredate" name="txthiredate" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= empty($rs['hiredate']) ? '' : date('m/d/Y', strtotime($rs['hiredate'])) ?>" style="margin-top: -10px;" onfocus="getVal(this);getJDate(this);"`onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" placeholder="dd/mm/yyy ex: 01/22/2022" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Login:</td><td><input class="user-profile-input" type="text" id="txtlogin" name="txtlogin" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['login'] ?>"/></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Address:</td><td><input class="user-profile-input" type="text" id="txtaddress" name="txtaddress" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['address'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">City:</td><td><input class="user-profile-input" type="text" id="txtcity" name="txtcity" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['city'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">State:</td><td><input class="user-profile-input" type="text" id="txtstate" name="txtstate" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['state'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Zip-Code:</td><td><input class="user-profile-input" type="text" id="txtzipcode" name="txtzipcode" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['zipcode'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Email:</td><td><input class="user-profile-input" type="text" id="txtemail" name="txtemail" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" value="<?= $rs['email'] ?>" /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Active:</td><td><input type="checkbox" id="chkisActive" name="chkisActive" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" <?php echo ($rs['isActive'] == true ? 'checked' : '') ?> /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Admin:</td><td><input type="checkbox" id="chkisAdmin" name="chkisAdmin" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" <?php echo ($rs['isAdmin'] == true ? 'checked' : '') ?> /></td></tr>
                    <tr><td class="tbl-admin-mgm-user-lbl">Barber:</td><td><input type="checkbox" id="chkisBarber" name="chkisBarber" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['thisrecno'] ?>);" <?php echo ($rs['isBarber'] == true ? 'checked' : '') ?> /></td></tr><?php 
                }?>
            </table><?php
            $sql = "SELECT * FROM attachments WHERE foreign_ur = ".$_SESSION['user_recno']." AND name='profile_image' OR name='thumb_nail'";
            $result = $db ->PDOMiniquery($sql);
            if($db ->PDORowcount($result) > 0)
            {
                foreach($result as $rs)
                {
                    if($rs['name'] == "profile_image")
                    {
                        $profileimage = $rs['file'];
                    }
                    if($rs['name'] == "thumb_nail")
                    {
                        $thumbnail = $rs['file'];
                    }
                }
            }?>
        <div class="float-left">
            <img class="cursor-pointer profile-img-size" id="img_profile" src="./images/others/<?php echo $thismedia ?>/avatar/<?php echo $profileimage ?>" onerror="this.onerror=null; this.src='./images/others/<?php echo $thismedia ?>/avatar/defaultimage.png'"><br />
            <span style="font-size: .7em;">Profile Image:<br /> <span id="span_profile_image"><?php echo $profileimage ?></span></span>
        </div>
        <div class="float-left">
            <img class="cursor-pointer profile-img-size" id="img_thumbnail" src="./images/others/<?php echo $thismedia ?>/avatar/<?php echo $thumbnail ?>" onerror="this.onerror=null; this.src='./images/others/<?php echo $thismedia ?>/avatar/defaultimage.png'"><br />
            <span style="font-size: .7em;">Thumbnail Image::<br /> <span id="span_profile_thumbnail"><?php echo $thumbnail ?></span></span>
        </div>
    </div><?php
}
function UpdateSlot()
{
    global $db;
    //$_POST['thisday'] will be 0-6, 0 = Sunday, 6 = Saturday
    //$_POST['thisrecno']  will be the recno of the selected person or the user if the user is NOT an admin
    //$_POST['thisslot'] will come in as a string of this format '10:00 AM' or '1:00 PM' or 'OFF'
    //$_POST['actuallot'] will be just the slot time, ex: '10:00 AM' or '1:00 PM', THERE IS NO 'OFF", we want to use this one rather than the $_POST['thisslot'];
    //slot_offs will be the field in the table schedules, the value in this field will be in format of '10:00 AM, 1:00 PM',
    $thistable = "schedules";
    $thiswhere = array("foreign_ur" => $_POST['thisrecno']);
    //file_put_contents('./dodebug/debug.txt', "mgm shcedule thislot: ".$_POST['thisslot']." \n", FILE_APPEND);
    $isanoffday = false;
    $thisfield = array("All");
    $resultoff_days = $db->PDOQuery($thistable, $thisfield, $thiswhere);
    foreach($resultoff_days as $rsoff_days)
    {
        $thisoff = $rsoff_days['off_days'];
    }
    $explodethisoff = explode(",", $thisoff);
    for($i=0; $i<count($explodethisoff); $i++)
    {
        //$explodethisoff[$i] will be 1-10:00pm
        $explodeslot = explode("-", $explodethisoff[$i]);
        $thisactualday = $explodeslot[0]; //$thisactualday will be 1 or 2 or 3 up to 7
        if($_POST['thisday'] == $thisactualday)
        {
            $isanoffday = true;
        }
    }
    if($isanoffday == false)
    {
        if(trim($_POST['thisslot']) != "OFF")
        {
            //file_put_contents('./dodebug/debug.txt', "mgm shcedule thislot: In ON \n", FILE_APPEND);
            //If we are here, that means the user is going to flip from OFF to ON, all we need to do is rove this time frame from the slot_offs field.
            //Because we just want to add to the field, we just add to it.
            $thisdata = array("slot_offs" => trim($_POST['thisday']."-".$_POST['actuallot']));
            $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, NULL, 'Yes');
            if($result == "Success")
            {
                echo "Success";
            }
        }
        else
        {
            //file_put_contents('./dodebug/debug.txt', "mgm shcedule thislot: In OFF \n", FILE_APPEND);
            $thisfields = array("All");
            //We want to turn this field OFF so we just add thsi slot to the field separated by ',', because we want to remove, we have to get the value and 
            //do a search and remove and then update back to the table.
            $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
            if(isset($result))
            {
                foreach($result as $rs)
                {
                    $thisslot_offs = $rs['slot_offs'];
                    //file_put_contents('./dodebug/debug.txt', "mgm shcedule thislot: 1 ".$thisslot_offs." \n", FILE_APPEND);
                }
                //file_put_contents('./dodebug/debug.txt', "mgm shcedule thislot: 2 ".$thisslot_offs." \n", FILE_APPEND);
                $explodethis = explode(",", $thisslot_offs);
                //Now we have an array of $explodethis['10:00 AM', '1:00 PM',...];
                if(($thiskey = array_search(trim($_POST['thisday'])."-".trim($_POST['actuallot']), $explodethis)) !== false)
                {
                    unset($explodethis[$thiskey]);
                }
                $implodethis = implode(",", $explodethis);
                //file_put_contents('./dodebug/debug.txt', "mgm shcedule 1: $implodethis \n", FILE_APPEND);
                $thisdata = array("slot_offs" => $implodethis);
                $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
                if($result == "Success")
                {
                    echo "Success";
                }
            }
            else
            {
                //file_put_contents('./dodebug/debug.txt', "mgm shcedule 1: are we failing? \n", FILE_APPEND);
                echo "Failed";
            }
        }
    }
    else
    {
        echo "OFF Day";
    }
}
function ModSchedulestatus()
{
    global $db;
    $thisday = "";
    switch($_POST['thisday'])
    {
        case "Sunday":
            $thisday = "7";
            break;
        case "Monday":
            $thisday = "1";
            break;
        case "Tuesday":
            $thisday = "2";
            break;
        case "Wednesday":
            $thisday = "3";
            break;
        case "Thursday":
            $thisday = "4";
            break;
        case "Friday":
            $thisday = "5";
            break;
        case "Saturday":
            $thisday = "6";
            break;
        defaut:
            break;
    }
    
    $thistable = "schedules";
    $thisfields = array("All");
    $thiswhere = array("foreign_ur" => $_POST['thisrecno']);
    //file_put_contents('./dodebug/debug.txt', "mgm shcedule 2: ".$_POST['thisstatus']."\n", FILE_APPEND);
    if($_POST['thisstatus'] == "ON")
    {
        $resultdel = $db->PDOquery($thistable, $thisfields, $thiswhere);
        foreach($resultdel as $rsdel)
        {
            $thisoffday = $rsdel['off_days'];
        }
        $explodethis = explode(",", $thisoffday);
        if(($thiskey = array_search($thisday, $explodethis)) !== false)
        {
            unset($explodethis[$thiskey]);
        }
        $implodethis = implode(",", $explodethis);
        //file_put_contents('./dodebug/debug.txt', "mgm shcedule 1: $implodethis \n", FILE_APPEND);
        $thisdata = array("off_days" => $implodethis);
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    }
    else
    {
        $thisdata = array("off_days" => $thisday);
        //file_put_contents('./dodebug/debug.txt', "mgm shcedule 2: $thisday \n", FILE_APPEND);
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, NULL, 'Yes');
    }
    
    if($result == "Success")
    {
        echo "Success";
    }
    else
    {
        echo "Failed to update schedule.";
    }
}
function GetUserschedule()
{
    global $d, $pt;
    
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    echo ($pt->GetSchedule($_POST['thisrecno']));
    
}
function SubmitRegistration()
{
    global $db, $nd, $en, $ne, $pc, $nl, $load_headers, $pt; 
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
    $ne->set_email($db, $_POST['txtemployeenumber']); //We set the email first
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

    $ne->set_email($db, $_POST['txtemail']); //We set the email first
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
                    'Sunday' => '10:00 AM, 6:00 PM',
                    'Monday' => 'OFF',
                    'Tuesday' => 'OFF',
                    'Wednesday' => '10:00 AM, 6:00 PM',
                    'Thursday' => '10:00 AM, 6:00 PM',
                    'Friday' => '10:00 AM, 6:00 PM',
                    'Saturday' => '10:00 AM, 6:00 PM',
                    'off_days' => '1,7');
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
            $pt->CreateUserDirectory($tempdir);
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
function AddNewuser()
{
    global $db, $load_headers;?>
    <div><?php
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
        <div class="div-body-container">
            <form name="frmregistration" id="frmregistration" method="post">
                <table class="tbl-register">
                    <tr class="tr-manageuser-add-new-barber-header align-center">
                        <td colspan="2">Add New Barber</td>
                    </tr>
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
                        <td class="registrationinput"><input type="text" class="email required" id="txtemail" name="txtemail"size="24"  value="" size="20" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Login: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="login" id="txtlogin" name="txtlogin"size="24"  value=""  /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Password: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="password" id="txtpassword" name="txtpassword" size="24"  value="<?php echo mt_rand(1000, 9999)?>" readonly /></td>
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
}
function ShowSchedule()
{
    global $db;?>
    <div class="admin-mgmservice-table-holder">
        <table class="tbl-admin-mgmschedule" id="tbl_admin_mgmschedule" name="tbl_admin_mgmschedule">
            <thead>
                <tr class="tbl-admin-mgmschedule-th-tr align-center">
                    <th class="tbl-admin-mgmschedule-th-td">No.</th>
                    <th class="tbl-admin-mgmschedule-th-td">Service <button class="float-right" title="Add Service" style="width: 30px; height: 30px;" onclick="addService();">+</button></th>
                    <th class="tbl-admin-mgmschedule-th-td">Time</th>
                    <th class="tbl-admin-mgmschedule-th-td">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($db ->PDORowcount($result) > 0)
                {
                    $i = 1;
                    foreach($result as $rs)
                    {?>
                        <tr class="tbl-admin-service-tbody-tr" onclick="getService(this, <?php echo $rs['recno']?>);">
                            <td class="tbl-admin-mgmschedule-tbody-td" class="td-num-rows align-right"><?php echo $i ?>.</td>
                            <td class="tbl-admin-mgmschedule-tbody-td"><?php echo $rs['title'] ?></td>
                            <td class="tbl-admin-mgmschedule-tbody-td align-right"><?php echo $rs['time'] ?></td>
                            <td class="tbl-admin-mgmschedule-tbody-td align-right">$<?php echo number_format($rs['price'], 2) ?></td>
                        </tr><?php
                        $i++;
                    }
                }?>
            </tbody>
        </table>
    </div><?php
}
function ManageSchedule()
{
    global $db;
    if($_SESSION['isAdmin'] == true)
    {
        ManageSearchmenus("Schedule");
    }
    else
    {
        ShowSchedule();
    }
}
function Removelogo()
{
    global $db;
    $thistrash = "./images/others/realtrash/".$_POST['thisimage'];
    $curlogo = "";
    //$_POST['thisrecno'] is the recno from table users.
    $thisdir = "./images/others/".$_SESSION['media_dir']."/logo/".$_POST['thisimage'];
    //file_put_contents("./dodebug/debug.txt", "dir  ".$thisdir." \n", FILE_APPEND);
    
    $sql = "SELECT * FROM company_info WHERE foreign_ur = ".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $curlogo = $rs['mainlogo'];
    }
    
    if (file_exists($thisdir)){
        if(strtolower($curlogo) != strtolower($_POST['thisimage']))
        {
            //file_put_contents("./dodebug/debug.txt", "remove success \n", FILE_APPEND);
            rename($thisdir, $thistrash); //After we made a copy in the trash, we will remove from the folder
            //unlink($thisdir);   
            echo "Success";
        }
        else
        {
            echo "Main Logo";
        }
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", "remove logo = failed \n", FILE_APPEND);
        echo "Failed";
    }
}
function SaveIntroduction()
{
    global $db;
    $thisrecno = 0;
    $thisval = "";
    foreach(json_decode($_POST['thisarray']) as $key => $value)
    {   
        //file_put_contents("./dodebug/debug.txt", "thisarray = $key == $value \n", FILE_APPEND);
        foreach($value as $key1 => $value2)
        {
            //file_put_contents("./dodebug/debug.txt", "thisarray = $key1 == $value2 \n", FILE_APPEND);
            if($key1 == "thisrecno")
            {
                $thisrecno = $value2;
            }
            if($key1 == "thisval")
            {
                $thisval = $value2;
            }
        }
    }
    $thistable = "company_info";
    $thisdata = ["introduction" => $thisval];
    $thiswhere = ["recno" => $thisrecno];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!isset($result))
    {
        echo "Failed";
    }
    else
    {
        echo "Success";
    }
}
function SaveAbout()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", "thisarray = ".$_POST['thisarray']." \n", FILE_APPEND);
    $thisrecno = 0;
    $thisval = "";
    foreach(json_decode($_POST['thisarray']) as $key => $value)
    {   
        //file_put_contents("./dodebug/debug.txt", "thisarray = $key == $value \n", FILE_APPEND);
        foreach($value as $key1 => $value2)
        {
            //file_put_contents("./dodebug/debug.txt", "thisarray = $key1 == $value2 \n", FILE_APPEND);
            if($key1 == "thisrecno")
            {
                $thisrecno = $value2;
            }
            if($key1 == "thisval")
            {
                $thisval = $value2;
            }
        }
    }
    $thistable = "company_info";
    $thisdata = ["about" => $thisval];
    $thiswhere = ["recno" => $thisrecno];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!isset($result))
    {
        echo "Failed";
    }
    else
    {
        echo "Success";
    }
}
function About()
{
    global $db;
    $thisabout = "";
    $thisrecno = 0;
    $sql = "SELECT * FROM company_info WHERE isActive = true AND isDeleted = false";
    $result = $db ->PDOMiniquery($sql);
    if($db->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisabout = $rs['about'];
            $thisrecno = $rs['recno'];
        }
    }?>
    <div id="div_float_about" class="index-div-float-mgm">
        <textarea id="txtarea_about" name="txtarea_about" cols="83" rows="36" style="resize: none;"><?php echo $thisabout ?></textarea>
        <button id="btn_save" onclick="saveAbout(<?php echo $thisrecno ?>);" style="margin: 0px auto;">Save</button>
    </div><?php
}
function Introduction()
{
    global $db;
    $thisintro = "";
    $thisrecno = 0;
    $sql = "SELECT * FROM company_info WHERE isActive = true AND isDeleted = false";
    $result = $db ->PDOMiniquery($sql);
    if($db->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisintro = $rs['introduction'];
            $thisrecno = $rs['recno'];
        }
    }?>
    <div id="div_float_intro" class="index-div-float-mgm">
        <textarea id="txtarea_intro" name="txtarea_intro" cols="83" rows="36" style="resize: none;"><?php echo $thisintro ?></textarea>
        <button id="btn_save" onclick="saveIntro(<?php echo $thisrecno ?>);" style="margin: 0px auto;">Save</button>
    </div><?php
}
function SubmitAdminattachment()
{
    global $db, $pt;
    //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
    //Assuming we are here, we want to now handle the upload.
    //First we want to check if './images/others/$_SESSION['media_dir']/logo/ exist, if not, we create it before we move file into it.
    $thistable = "company_info";
    $thisdir = "./images/others/".$_SESSION['media_dir']."/logo";
    if (!file_exists($thisdir)) {
        mkdir("./images/others/".$_SESSION['media_dir']."/logo", 0777, true);
    }
    
    //First we want to get the type of file
    
    //Once we confirmed that it is there after, now we want to move the file or files there and also update the name of the file to the table.
    //$msg = $pt ->UploadFile($thisdir, $_FILES["file"], $thistable, "mainlogo", $_POST['thisrecno'], NULL, 'company_info');
    $countfiles = count($_FILES["file"]['name']); 
    $strattachments = "";  
    $typeisgood = "";
    $thisfield = "mainlogo";
    for($i=0;$i<$countfiles;$i++)
    {
        $filename = strtolower($_FILES["file"]['name'][$i]);

        $pdfMimearray = array('application/pdf', 'application/doc', 'application/docx');
        $thismime = mime_content_type($_FILES["file"]['tmp_name'][$i]);
        //file_put_contents("./dodebug/debug.txt", "this mine ".$thismime, FILE_APPEND);
        //Now that we have the $filename, we can check for the file type and size and all the goodies.
        $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
        $detectedType = exif_imagetype($_FILES["file"]['tmp_name'][$i]);
        
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
            move_uploaded_file($_FILES["file"]['tmp_name'][$i],"$thisdir/$filename");
        }
        else
        {
            $typeisgood = "BAD";
        }
        
    }
    if($typeisgood != "BAD")
    {
        $thisdata = array($thisfield => $strattachments);
        $thiswheres = array('recno' => $_POST['thisrecno']);
        $result = $db->PDOUPDATE($thistable, $thisdata, $thiswheres, $_POST['thisrecno']);
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
        echo "Bad file type.  File type must be PNG, JPEG, GIF, and OR PDF.";
    }
    
}
function AdminMgmcomattach()
{
    //We want to build a div with a form in it for submitting attachment
    ?>
    <div class="div-body-admin-mgmcomp-attach-container" id="div_body_admin_mgmcomp_attach_container">
        <div class="div-body-admin-mgmcomp-attach-sub-container">
            <form name="frmsubmitattachment" id="frmsubmitattachment" enctype="multipart/form-data" method="post">
                <div class="align-left" style="width: 300px; height: 100px; background-color: gray;">
                    <div>Upload Attachment</div>
                    <div><input class="event-attachments" type="file" name="file[]" id="adminattachment" /></div><br/>
                    <button id="btn_submit" name="btn_submit" onclick="submitAdminattachment(<?php echo $_POST['thisrecno'] ?>);">Submit</button>
                    <button id=""btn_cancel" name=""btn_cancel" id=""btn_cancel" onclick="cancelAdminattachment();">Cancel</button>
                </div>
            </form>
        </div>
        <div class="align-left">jpeg, gif, png ONLY</div>
    </div><?php
}
function SelectImage()
{
    global $db;
    
    $thistable = "company_info";
    $thisdata = [$_POST['thisfield'] => $_POST['thisimage']];
    $thiswhere = ['foreign_ur' => $_SESSION['user_recno']];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!isset($result))
    {
        echo "Failed";
    }
    else
    {
        $_SESSION['main_logo'] = $_POST['thisimage']; //We must update the session because we declared it in login.php however we now changed so we must update.
        echo "Success";
    }
}
function ShowCompanyimage()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thismediadir = "";
    $thisfrontimage = "";
    $thisthumbnail = "";
    $usethiscssborder = "";
    $usethistitle = "";
    $sql = "SELECT u.media_dir, a.name, a.file FROM users u INNER JOIN attachments a ON a.foreign_ur = u.recno WHERE a.foreign_ur = ".$_SESSION['user_recno']." AND a.isDeleted = false";
   
    $rows = $db->PDOMiniquery($sql);
    //./images/others/$thismedia/avatar/$profileimage"
    foreach($rows as $rs)
    {
        $thismediadir = $rs['media_dir'];
        if($rs['name'] == "profile_image")
        {
            $thisfrontimage = $rs['file'];
        }
        if($rs['name'] == "thumb_nail")
        {
            $thisthumbnail = $rs['file'];
        }
    }        
    $thisdir = "./images/others/$thismediadir/avatar/*";
    $thispath = "./images/others/$thismediadir/avatar";?>
    <div class="div-admin-image-container" id="div_admin_image_container"><?php
        foreach(glob($thisdir) as $file)
        {
            if(!is_dir($file)) 
            {
                //basename($file) will be name.filetype, ex: name.png
                //file_put_contents('./dodebug/debug.txt', 'profile state: '.basename($file).' == '.$thisfrontimage.' || '.basename($file).' == '.$thisthumbnail.' \n', FILE_APPEND);
                $usethiscssborder = "";
                $usethistitle = "";
                if(strtolower(basename($file)) == strtolower($thisfrontimage) || strtolower(basename($file)) == strtolower($thisthumbnail))
                {
                    $usethiscssborder = "admin-company-image-bucket-selected";
                    $usethistitle = "Selected";
                }
                ?>
                <div onclick="selectImage(this, 'mainlogo', '<?php echo basename($file) ?>');">
                    <img id="img_<?php echo basename($file) ?>" class="adminbucketimage <?php echo $usethiscssborder ?>" title="<?php echo $usethistitle ?>" src="<?php echo $thispath ?>/<?php echo basename($file) ?>" onerror="this.src='<?php echo $thispath ?>/defaultimage.png'"></a>
                    <br/><span class="admin-span-image-disc"><?php echo basename($file) ?></span>
                </div><?php
            }
        }?>
    </div><?php
}
function SubmitNewcompany()
{
    global $db, $pt;
    //file_put_contents("./dodebug/debug.txt", "admin company here: ".$_FILES['thisfile']["name"]." \n", FILE_APPEND);
    $thistable = "company_info";
    $msg = "";
    $_POST['txtforeign_ur'] = $_SESSION['user_recno'];
    $thisdata = $pt ->PostIt($_POST); //PostIt is a function that will return an associative array with non-empty values and substring first 3 chars
    
    //$thisrecno is the recno of $thistable
    $thisrecno = $db ->PDOInsert($thistable, $thisdata, $_SESSION['user_recno']);
    if(isset($thisrecno))
    {
        //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
        //Assuming we are here, we want to now handle the upload.
        //First we want to check if './images/others/$_SESSION['media_dir']/logo/ exist, if not, we create it before we move file into it.
        $thisdir = "./images/others/".$_SESSION['media_dir']."/logo";
        if (!file_exists($thisdir)) {
            mkdir("./images/others/".$_SESSION['media_dir']."/logo", 0777, true);
        }
        //Once we confirmed that it is there after, now we want to move the file or files there and also update the name of the file to the table.
        
        $msg = $pt ->UploadFile($thisdir, $_FILES["thisfile"], $thistable, "mainlogo", $thisrecno);

    }
    else
    {
        $msg = "Failed to insert";
    }
    echo $msg;
}
function UpdateCompanyinfo()
{
    global $db;
    $thisdata = [];
    foreach($_POST as $key => $value)
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
            //file_put_contents("./dodebug/debug.txt", "admin company 111 = $thisfield == $value \n", FILE_APPEND);
        }
    }
    $thistable = "company_info";
    $thiswhere = Array('recno' => $_POST['thisrecno']);
    $result = $db ->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!is_null($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       $thismsg = "Success";
    }
    
    echo $thismsg;
}
function AddCompany()
{
    global $db;
    $thisrecno = "";
    $thisname = "";
    $thisaddress = "";
    $thiscity = "";
    $thisstate = "";
    $thiszipcode = "";
    $thislogo = "";
    $thispaymentcompany = "";
    $thisapiid = "";
    $thisapikey = "";
    $usthisfunc = "";
    $thisbutton = "style='display: none;'";
    $sql = "SELECT * FROM company_info WHERE foreign_ur = '".$_SESSION['user_recno']."'";
    $result = $db ->PDOMiniquery($sql);
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisrecno = $rs['recno'];
            $userrecno = $rs['foreign_ur'];
            $thisname = $rs['name'];
            $thispaymentcompany = $rs['api_company'];
            $thisphonenumber = $rs['phone_number'];
            $thissmsnumber = $rs['smsnumber'];
            $thisapitoken = $rs['api_token'];
            $thisapiid = $rs['api_id'];
            //$thisapiid = $rs['api_id'];
            //$thisapikey = $rs['api_key'];
            $thisaddress = $rs['address'];
            $thiscity = $rs['city'];
            $thisstate = $rs['state'];
            $thiszipcode = $rs['zipcode'];
            $thislogo = $rs['mainlogo'];
        }
        $usthisfunc = 'onfocus = "getVal(this)" onchange = "updateCompanyinfo(this, '.$thisrecno.');"';
        $thisbutton = "";
    }?>
    <div id="div_admin_container" class="div-admin-mc-container">
        <form name="frmcompany" id="frmcompany" method="post" enctype="multipart/form-data">
            <table id="tblservicedata" class="tbl-admin-company">
                <tr>
                    <td class="tbl-admin-company-lbl">Name Of Company: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtname" name="txtname" <?php echo $usthisfunc ?> value="<?php echo $thisname ?>" required /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">Payment Company: </td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtapi_company" name="txtapi_company" <?php echo $usthisfunc ?> value="<?php echo $thispaymentcompany ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">Phone No.: </td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtphone_number" name="txtphone_number" <?php echo $usthisfunc ?> value="<?php echo $thisphonenumber ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">SMS Phone No.: </td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtsmsnumber" name="txtsmsnumber" <?php echo $usthisfunc ?> value="<?php echo $thissmsnumber ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">API Token: </td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtapi_token" name="txtapi_token" <?php echo $usthisfunc ?> value="<?php echo $thisapitoken ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">API I.D: </td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtapi_id" name="txtapi_id" <?php echo $usthisfunc ?> value="<?php echo $thisapiid ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">Address: </td>
                    <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtaddress" name="txtaddress" <?php echo $usthisfunc ?> value="<?php echo $thisaddress ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">City: </td>
                    <td><input type="text" class="admin-company-input" id="txtcity" name="txtcity" <?php echo $usthisfunc ?> value="<?php echo $thiscity ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">State: </td>
                    <td><input type="text" class="admin-company-input" id="txtstate" name="txtstate" <?php echo $usthisfunc ?> value="<?php echo $thisstate ?>" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">Zipcode: </td>
                    <td><input type="text" class="admin-company-input" id="txtzipcode" name="txtzipcode" <?php echo $usthisfunc ?> value="<?php echo $thiszipcode ?>" /></td>
                </tr><?php
                if($thisname != "")
                {?>
                    <tr>
                        <td class="tbl-admin-company-lbl">Current Logo: </td>
                        <td class="admin-company-input"><img id="imgcurrentlogo" style="height: 100px;" src="./images/others/<?php echo $_SESSION['media_dir']?>/logo/<?php echo $thislogo ?>"/></td>
                    </tr><?php
                }
                if($thisname == "")
                {?>
                    <tr>
                        <td class="tbl-admin-company-lbl">Main Banner: </td>
                        <td class="admin-company-input"><input type="file" class="admin-company-input" id="txtmainbanner" accept="image/png, image/gif, image/jpeg" name="thisfile[]" multiple="multiple"  /><div class="align-left">jpeg, gif, png ONLY</div></td>
                    </tr><?php
                }
                else
                {?>
                    <tr>
                        <td class="tbl-admin-company-lbl">Upload Attachment: </td>
                        <td class="admin-company-input" id="tduploadattachment"><img class="cursor-pointer" style="height: 50px;" src="./images/others/dummyattach.png" onclick="adminMgmcomattach(this, <?php echo $thisrecno ?>);"/><div class="align-left">jpeg, gif, png ONLY</div></td>
                    </tr><?php

                }
                if($thisname != "")
                {?> 
                    <tr>
                        <td class="tbl-admin-company-lbl">Active:</td>
                        <td class="admin-company-input"><input type="checkbox" id="chkisActive" name="chkisActive"  checked disabled="disabled" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-admin-company-lbl">Deleted:</td>
                        <td class="admin-company-input"><input type="checkbox" id="chkisDeleted" name="chkisDeleted" disabled /></td>
                    </tr><?php 
                }
                if($thisname == "")
                {?> 
                    <tr>
                        <td colspan="2" class="align-center"><button name="btnsubmit" id="btnsubmit" onclick="submitNewcompany();">Submit</button></td>
                    </tr><?php
                }
                if($thisname != "")
                {?>
                    <tr>
                       <td colspan="2" class="align-center">
                            <div style="width: 100%; height: 30px; background-color: #1079B1;">    
                               <div style="width: 120px; height: 100%; line-height: 30px; background-color: white; color: black;">Main Logo</div>
                            </div>
                            <div class="div-admin-image-container" id="div_profile_image_container" style="height: 350px; overflow: auto;"><?php
                                $thisdir = "./images/others/".$_SESSION['media_dir']."/logo/*";
                                $thispath = "./images/others/".$_SESSION['media_dir']."/logo";
                                BuildCompanyimagecontainer('mainlogo', $thisdir, $thispath, $thislogo, $userrecno);?>
                            </div>
                       </td>
                    </tr><?php
                }?>
            </table>
        </form>
    </div><?php 
}
function BuildCompanyimagecontainer($from, $thisdir, $thispath, $sltedimage, $userrecno)
{
    //$from is so far 'Main Logo'
    foreach(glob($thisdir) as $file)
    {
        if(!is_dir($file)) 
        {
            //basename($file) will be name.filetype, ex: name.png
            //file_put_contents('./dodebug/debug.txt', 'profile state: '.basename($file).' == '.$thisfrontimage.' || '.basename($file).' == '.$thisthumbnail.' \n', FILE_APPEND);
            $usethiscssborder = "";
            $usethistitle = "";
            //file_put_contents("./dodebug/debug.txt", "admin IMAGE container: ".strtolower(basename($file))." == ".strtolower($sltedimage)." \n", FILE_APPEND);
            if(!is_null($sltedimage))
            {
                if(strtolower(basename($file)) == strtolower($sltedimage))
                {
                    $usethiscssborder = "company-image-bucket-selected";
                    $usethistitle = "Selected";
                }
            }?>
            <div>
                <button class="btn-remove-logo-image cursor-pointer" name="btn_remove_logo_image" id="btn_remove_logo_image" title="Remove this image" onclick="removeLogo('<?php echo basename($file) ?>');">X</button>
                <div onclick="selectImage(this, 'mainlogo', '<?php echo basename($file) ?>');">
                    <img name="<?php echo basename($file) ?>" id="<?php echo basename($file) ?>" class="admin-bucket-image <?php echo $usethiscssborder ?>" title="<?php echo $usethistitle ?>" src="<?php echo $thispath ?>/<?php echo basename($file) ?>"></a>
                    <span class="admin-span-image-disc"><?php echo basename($file) ?></span>
                </div>
            </div><?php
        }
    }
}
function SubmitNewservice()
{
    global $db;
    $thismsg = "";
    //We want to check time and place
    $thisdata = Array();
    $thistable = "service";
    foreach(json_decode($_POST['thisarray']) as $key => $value)
    {   
        //file_put_contents("./dodebug/debug.txt", "admin thisarray = $value[0] == $value[1] \n", FILE_APPEND);
        if($value[0] == 'time' && !is_numeric($value[1]))
        {
            $thismsg = "Time must be a number.";
            break;
        }
        if($value[0] == 'price' && !is_numeric($value[1]))
        {
            $thismsg = "Price must be a number.";
            break;
        }    
        $thisdata[$value[0]] = $value[1];  
    }
    if($thismsg == "")
    {
        $result = $db ->PDOInsert($thistable, $thisdata, $_SESSION['user_recno']);
        //file_put_contents("./dodebug/debug.txt", "admin menu sql = ".$result." \n", FILE_APPEND);
        if(!is_null($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function AddService()
{?>
    <table id="tblservicedata" class="tbl-admin-service">
        <tr>
            <td class="tbl-admin-register-lbl">Service: <span class="asterisk"> * </span></td>
            <td><textarea cols="60" rows="4" class="required admin-services-input" id="txtarea_service" name="txtarea_service"></textarea></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Time: <span class="asterisk"> * </span></td>
            <td><input type="text" class="firstname required admin-services-input" id="txttime" name="txttime" value="" required />mins.</td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Price: <span class="asterisk"> * </span></td>
            <td><input type="text" class="lastname required admin-services-input" id="txtprice" name="txtprice" value="" onfocus="getVal(this);" /></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Comment: </td>
            <td><textarea class="admin-services-input" cols="60" rows="4" id="textarea_comment" name="textarea_comment"></textarea></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Active:</td>
            <td><input type="checkbox" class="lastname required admin-services-input" id="chkactive" name="chkactive" checked dissabled /></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Deleted:</td>
            <td><input type="checkbox" class="lastname required admin-services-input" id="chkdeleted" name="chkdeleted" disabled /></td>
        </tr>
        <tr>
            <td colspan="2" class="align-center"><button name="btnsubmit" id="btnsubmit" onclick="submitNewservice();">Submit</button></td>
        </tr>
    </table><?php 
}
function UpdateService()
{
    global $db;
    $thismsg = "";
    //We want to check time and place
    switch($_POST['thisfield'])
    {
        case 'time':
        case 'price':
            if(!is_numeric($_POST['thisval']))
            {
                $thismsg = "This field must be a number.";
            }
            break;
        default:
            break;
    }
    if($thismsg == "")
    {
        $thistable = "service";
        if($_POST['thisval'] == "true" || $_POST['thisval'] == "false")
        {
            if($_POST['thisval'] == "true")
            {
                $thisdata = Array($_POST['thisfield'] => true);
            }
            else
            {
                $thisdata = Array($_POST['thisfield'] => false);
            }
        }
        else
        {
            $thisdata = Array($_POST['thisfield'] => $_POST['thisval']);
        }
        $thiswhere = Array('recno' => $_POST['thisrecno']);
        $result = $db ->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
        if(!is_null($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function GetService()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "service";
    $thisfields = array('All');
    $thiswhere = array("recno" => $_POST['recno']);
    //$thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <table id="tbluserdata" class="tbl-admin-service">
                <tr>
                    <td class="tbl-admin-register-lbl">Service: <span class="asterisk"> * </span></td>
                    <td><textarea cols="60" rows="4" class="required admin-services-input" onfocus="getVal(this);" id="txtarea_service" name="txtarea_service" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'title');"><?php echo  $rs['title']; ?></textarea></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Time: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="firstname required admin-services-input" id="txttime" name="txttime" value="<?php echo  $rs['time']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'time');" required />mins.</td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Price: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="lastname required admin-services-input" id="txtprice" name="txtprice" value="<?php echo number_format($rs['price'], 2); ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'price');" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Deposit: </span></td>
                    <td><input type="text" class="lastname required admin-services-input" id="txtprice" name="txtprice" value="<?php echo ($rs['deposit'] == NULL) ? "" : number_format($rs['deposit'], 2); ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'deposit');" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Comment: </td>
                    <td><textarea class="admin-services-input" cols="60" rows="4" id="textarea_comment" name="textarea_comment" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'description');"><?php echo  $rs['description']; ?></textarea></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Active:</td>
                    <td><input type="checkbox" class="lastname required admin-services-input" id="chkactive" name="chkactive" value="<?php echo  $rs['isactive']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'isactive');" <?php echo ($rs['isactive'] == true) ? 'checked' : '' ?>/></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Deleted:</td>
                    <td><input type="checkbox" class="lastname required admin-services-input" id="chkdeleted" name="chkdeleted" value="<?php echo  $rs['isdeleted']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'isdeleted');" <?php echo ($rs['isdeleted'] == true) ? 'checked' : '' ?> /></td>
                </tr>
            </table><?php 
       }
    }
}
function ManageServices()
{
    ManageSearchmenus("Services");
}
function SearchServices()
{    
    global $db;
    
    $sql = "SELECT * FROM service WHERE ";
    //file_put_contents("./dodebug/debug.txt", "admin menu sql = ".$_POST['isDeleted']." and ". $_POST['isActive']." \n", FILE_APPEND);
    if($_POST['rdoservice'] == 'active')
    {
        $sql .= "isdeleted = false AND isactive=true";
    }
    if($_POST['rdoservice'] == 'deleted')
    {
        $sql .= "isactive = false and isdeleted=true";
    }    
    //file_put_contents("./dodebug/debug.txt", "admin menu sql = $sql \n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);?>
    <div class="admin-mgmservice-table-holder">
        <table class="tbl-admin-service" id="tbl_admin_service" name="tbl_admin_service">
            <thead>
                <tr class="tbl-admin-service-th-tr align-center">
                    <th class="tbl-admin-service-th-td">No.</th>
                    <th class="tbl-admin-service-th-td">Service <button class="float-right" title="Add Service" style="width: 30px; height: 30px;" onclick="addService();">+</button></th>
                    <th class="tbl-admin-service-th-td">Time</th>
                    <th class="tbl-admin-service-th-td">Price</th>
                    <th class="tbl-admin-service-th-td">Deposit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($db ->PDORowcount($result) > 0)
                {
                    $i = 1;
                    foreach($result as $rs)
                    {?>
                        <tr class="tbl-admin-service-tbody-tr" onclick="getService(this, <?php echo $rs['recno']?>);">
                            <td class="tbl-admin-service-tbody-td" class="td-num-rows align-right"><?php echo $i ?>.</td>
                            <td class="tbl-admin-service-tbody-td"><?php echo $rs['title'] ?></td>
                            <td class="tbl-admin-service-tbody-td align-right"><?php echo $rs['time'] ?></td>
                            <td class="tbl-admin-service-tbody-td align-right">$<?php echo number_format($rs['price'], 2) ?></td>
                            <td class="tbl-admin-service-tbody-td align-right"><?php echo ($rs['deposit'] == NULL) ? "" : number_format($rs['deposit'], 2) ?></td>
                        </tr><?php
                        $i++;
                    }
                }?>
            </tbody>
        </table>
    </div><?php
}
function ManageSearchmenus($from)
{
    //$from - Users
    if($from == "Users")
    {?>
        <div id="div_search_containter" class="div-search-containter">
            <div class="float-left" id="div_mgm_search">                
                <div>
                    <div class="float-left div-chk-active">Active: <input type="checkbox" name="chkactive" id="chkactive" onclick="reloadUser(this);" checked /></div>
                    <div class="float-left div-chk-active">Barber: <input type="checkbox" name="chkbarber" id="chkbarber" onclick="reloadUser(this);" checked /></div>
                    <div class="float-left">Terminated: <input type="checkbox" name="chkterminate" id="chkterminate" onclick="reloadUser(this);" /></div>
                </div>
                <div id="div_searchuser_container">
                    <input class="txt-search-admin" type="text" id="txtsearchuser" name="txtsearchuser" value="" placeholder="Enter a name to start search." onclick="searchUser(this, '<?php echo $from ?>');" onkeyup="searchUser(this, '<?php echo $from ?>');" />
                    <button type="button" onclick="clearSearchuser();">Clear</button>
                    <button type="button" onclick="addNewuser();" title="Click to add new employee">New Empno.</button>
                </div>

            </div>
        </div><?php
    }
    if($from == "Schedule")
    {?>
        <div id="div_search_containter" class="div-search-containter-schedule">
            <div class="float-left" id="div_mgm_search">
                <div>
                    <div class="float-left div-chk-active">Active: <input type="checkbox" name="chkactive" id="chkactive" onclick="reloadUser(this);" checked /></div>
                    <div class="float-left">Terminated: <input type="checkbox" name="chkterminate" id="chkterminate" onclick="reloadUser(this);" /></div>
                </div>
                <div id="div_searchuser_container">
                    <input class="txt-search-admin-schedule" type="text" id="txtsearchuser" name="txtsearchuser" value="" placeholder="Enter a name to start search." onclick="searchUser(this, '<?php echo $from ?>');" onkeyup="searchUser(this, '<?php echo $from ?>');" />
                    <button type="button" onclick="clearSearchuser();">Clear</button>
                </div>
            </div>
        </div><?php
    }
    if($from == "Services")
    {?>
        <div id="div_search_containter" class="div-search-containter">
            <div class="float-left" id="div_mgm_search">
                <div class="admin-mgmservices-radio-container" class="float-right">
                    <div class="float-left div-chk-active"><input type="radio" name="rdoservice" id="rdoactive" value="active" onclick="reloadService(this);" checked />Active</div>
                    <div class="float-left"><input type="radio" name="rdoservice" id="rdodeleted" value="deleted" onclick="reloadService(this);" />Deleted</div>
                </div>
            </div>
        </div><?php        
    }
}
function UpdateUser()
{
    global $db, $pt, $ne, $load_headers;
    $thisreturn = "";
    $thistable = "users";
    $thisfield = $_POST['thisfield'];
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
    if($_POST['thisfield'] == "birthday" || $_POST['thisfield'] == "hiredate")
    {
        $formatthisdate = date('Y-m-d', strtotime($_POST['thisvalue']));
        $thisdata = array($_POST['thisfield'] => $formatthisdate); 
    }
    else if($_POST['thisfield'] == "login" || $_POST['thisfield'] == 'email')
    {
        if($_POST['thisfield'] == "email")
        {
            file_put_contents("./dodebug/debug.txt", "field: ".$_POST['thisfield']." = ".$_POST['thisvalue']." for recno ".$_POST['thisrecno']."\n", FILE_APPEND);
            //WE want to do a final check on email, make sure it is a valid email.
            $ne->set_email($db, $_POST['thisvalue']);
            if($ne->validate_email() == false)
            {
                $thisreturn = "Bad email format.  Please check email and try again.";
            }
        }
        if($_POST['thisfield'] == "login")
        {
        //Can't get this to work.
            $thisfield = [$_POST['thisfield']];
            $sqlcheck = $pt -> CheckIfexist($thistable, $thisfield, $thiswhere);
            if($sqlcheck)
            {
                $thisreturn = "This ".$_POST['thisfield']." already exist.  Please use a different one.";
            }
        }
        $thisdata[$_POST['thisfield']] = $_POST['thisvalue'];
    }
    else if($_POST['thisfield'] == "isActive" || $_POST['thisfield'] == "isTarminated" || $_POST['thisfield'] == "isBarber" || $_POST['thisfield'] == "isAdmin")
    {
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
        }
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", "field: ".$_POST['thisfield']." = ".$_POST['thisvalue']." for recno ".$_POST['thisrecno']."\n", FILE_APPEND);
        $thisdata[$_POST['thisfield']] = $_POST['thisvalue'];
        //file_put_contents("./dodebug/debug.txt", "field: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
    }
    if($thisreturn == "")
    {
        $thiswhere = array("recno" => $_POST['thisrecno']);
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['thisrecno']);
        //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
        if(isset($result))
        {
            $thisreturn = 'Success';
            
            //Since we successfully updated the system when it comes to terminating or setting inactive on an employee,
            //we want to email people who are admin status
            if($_POST['thisfield'] == "isActive" || $_POST['thisfield'] == "isTerminated")
            {
                $sentto = Array();
                $replyto = Array();
                $ccto = Array();
                $bccto = Array();
                $attachment = Array();
                $tempempname = "";
                $sql = "SELECT firstname, middlename, lastname, email FROM users WHERE isAdmin = true";
                $result = $db ->PDOMiniquery($sql);
                foreach($result as $rs)
                {
                    $sendto[] = array($rs['email'] => $rs['firstname'].empty($rs['middlename'] ? ' ' : $rs['middlename'])." ".$rs['lastname']);
                    //$sendto[] = array("14058890899@sms.smtp2go.com");
                }
                $sql = "SELECT firstname, middlename, lastname FROM users WHERE recno = ".$_POST['thisrecno'];
                $result = $db ->PDOMiniquery($sql);
                foreach($result as $rs)
                {
                    $tempempname = $rs['firstname'].empty($rs['middlename'] ? ' ' : $rs['middlename'])." ".$rs['lastname'];
                }
                if($_POST['thisfield'] == "isActive")
                {
                    $subject = $ne ->get_active_subject();
                    $body = $ne ->get_active_body($thisserver, $tempempname, $_POST['thisvalue']);
                }
                else
                {
                    $subject = $ne ->get_terminated_subject($thisserver, $tempempname);
                    $body = $ne ->get_terminated_body($thisserver, $tempempname, $_POST['thisvalue']);
                }
                $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            }
        }
        else
        {
            $thisreturn = 'Failed';
        }
    }
    echo $thisreturn;
}
function SearchUserunset()
{
    $_SESSION['usersearchlist'] = array();
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function SearchServicerunset()
{
    $_SESSION['servicesearchlist'] = array();
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function GetUsers()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    $thisname = "";
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "users";
    $thisfields = array('All');
    //file_put_contents("./dodebug/debug.txt", $_POST['isActive']." and ".$_POST['isTerminated']."\n", FILE_APPEND);
    $sql = "SELECT * FROM users WHERE ";
    if($_POST['isActive'] == 'true' && $_POST['isTerminated'] == 'true')
    {
        $sql .= "isActive = true OR isTerminated = true";
    }
    else
    {
        if($_POST['isActive'] == 'true')
        {
            $sql .= "isActive = true AND ";
        }
        else
        {
            $sql .= "isActive = false AND ";
        }
   
        if($_POST['isTerminated'] == 'true')
        {
            $sql .= "isTerminated = true ";
        }
        else
        {
            $sql .= "isTerminated = false ";
        }
    }
    $result = $db->PDOMiniquery($sql);
    if($db ->PDORowcount($result) > 0) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {?>
       <table id="tbluserdata" class="tbl-admin-register float-left"><?php
        foreach($result as $rs)
        {
            $thisname = $rs['firstname'].(empty($rs['middlename']) ? " " : " ".$rs['middlename'])." ".$rs['lastname'];?>
            <tr>
                <td><span class="align-left float-left get-users cursor-pointer" id="spn_user_<?php echo $rs['recno'] ?>" onclick="getUserdata(this, <?php echo $rs['recno'] ?>);"><?php echo $thisname ?></span></td>
            </tr><?php
        }?>
       </table><?php 
    }
}
function SearchUser()
{
    global $db;
    /*
    if(count($_SESSION['usersearchlist']) > 0)
    {
        //If this session variable has stuffs in it, that means user already started the search and
        //we want to use this array rather than going back to the database every time user typed a char.
        //file_put_contents("./dodebug/debug.txt", 'inside session', FILE_APPEND);
        SearchUserexistinglist($_POST['txtsearchuser']);
        exit();
    }*/
    $tempexplodeuser = explode(' ', $_POST['txtsearchuser']);
    //We get here when user entered just the first name, middle or last name.

    $sql = "SELECT * FROM users WHERE ";

    if($_POST['isActive'] == 'true')
    {
        $sql .= "isActive = true ";
    }
    else
    {
        $sql .= "isActive = false ";
    }
    if($_POST['thisfrom'] == "Schedule")
    {
        $thisfunc = "getUserSchedule(this, '');";
        $sql .= "AND isBarber = true ";
    }
    else if($_POST['thisfrom'] == "Users")
    {
        $thisfunc = "getUserdata(this, '');";
        if($_POST['isBarber'] == 'true')
        {
            $sql .= "AND isBarber = true ";
        }
        else
        {
            $sql .= "AND isBarber = false ";
        }
    }
    if($_POST['isTerminated'] == 'true')
    {
        $sql .= "AND isTerminated = true ";
    }
    else
    {
        $sql .= "AND isTerminated = false ";
    }
    //file_put_contents("./dodebug/debug.txt", "admin slt user sql ".$_POST['thisfrom'].": $sql \n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);
    $realname = "";?>   
    <select class="admin-slt-search-user" name="sltsearchuser" id="sltsearchuser" size="5" onclick="<?php echo $thisfunc ?>"><?php
        if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {  
           foreach($result as $rs)
           {
               $realname = $rs['firstname'];
               if(!is_null($rs['middlename']) && $rs['middlename'] != "")
               {
                   $realname .= " ". substr($rs['middlename'], 0, 1). ".";
               }
               $realname .= " ".$rs['lastname'];
               //$_SESSION['usersearchlist'][$rs['recno']] = $realname;?>
                <option class="admin-search-user" value="<?php echo  $rs['recno']?>"><?php echo  $realname ?></option><?php 
           }
        }?>
    </select><?php
}
function SearchUserexistinglist($thisstr)
{
    //file_put_contents("./dodebug/debug.txt", "sessionlist: ".var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $realname = "";?>   
    <select class="admin-slt-search-user" name="sltsearchuser" id="sltsearchuser"  size="5" onchange="hideSelectsearch();" onclick="getUserdata(this);"><?php
        foreach($_SESSION['usersearchlist'] as $rs => $value)
        {
           if(strpos(strtolower($value), strtolower($thisstr)) !== false)
           {?>
                <option  class="admin-search-user" value="<?php echo $rs ?>"><?php echo $value ?></option><?php 
            }
        }?>
    </select><?php
}
function ManageUsers()
{
    ManageSearchmenus('Users');
    
}
function Main()
{
    global $load_headers;?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo(false);?>
        <div class="main-div-body-admin">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-admin-left">
                            <div class="main-div-body-admin-header">Admin</div>
                            <div style="float: left;">
                                <div class="div-menu-admin" id="div_manageuser" onclick="manageUsers(this);">Manage a user</div>
                                <div class="div-menu-admin" id="div_manageservices" onclick="manageServices(this);">Manage Services</div>
                                <div class="div-menu-admin" id="div_managecompany" onclick="addCompany(this);">Manage Company</div>
                                <div class="div-menu-admin" id="div_manageSchedule" onclick="manageSchedule(this);">Manage Schedule</div>
                                <div class="div-menu-admin" id="div_manageintro" onclick="introduction(this);">Introduction</div>
                                <div class="div-menu-admin" id="div_manageabout" onclick="about(this);">About</div>
                            </div> 
                        </div>
                    </td>
                    <td>
                        <div id="main_div_body_admin_right_container" class="main-div-body-admin-right-container"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}?>