<?php
require("./common/page.php");
require("./common/pdocon.php");
require("./common/classes/pageloaderclass.php");
require_once("./common/sendmail.php");
require_once("./common/prompt.php");
$pt = new PROMPT();
$load_headers = new PageloaderClass();
$db = new PDOCON();
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
                $(".promp-select2").each(function(){
                    $(this).select2({
                        placeholder: "Type to search and or select from list ONLY.",
                        width: 'style',
                    });
                });
                addCustomerdefault();
            });
            function checkRequiredfields()
            {
                if($("#txtfirstname").val() != "" && $("#txtlastname").val() != "" && $("#txtemail").val() != "")
                {
                    $("#btnfrmregistration").prop('disabled', false);
                }
                else
                {
                    $("#btnfrmregistration").prop('disabled', true);
                }
            }
            function doRegistration(obj){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DoRegistration', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function loadLogin(obj){
                if($(obj).prop('id') != "txtlogin"){
                    if($("#txtfirstname").val() != "" && $("#txtlastname").val() != ""){
                        //Since we have both first and last, we will check if this login is available
                        var templogin = $("#txtfirstname").val().trim().toLocaleLowerCase()+'.'+$("#txtlastname").val().trim().toLocaleLowerCase();
                        $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CheckRegistration&field=login&value'+templogin, function(result){
                            if(result == "EXISTS"){
                                alert('This user already exists.  Please manually type in another login.');
                                $("#txtlogin").val('');
                                $("#txtlogin").focus();
                                return(false);
                            }
                            else{
                               $("#txtlogin").val(templogin);
                               //We want to check to see if we have the 3 required fields, if we do, we enable the submit button.
                               checkRequiredfields();
                               return(false);
                            }
                        });
                    }
                }
                else{
                    //We are here probably because user decided to type in their own user name and we want to check if this user name exists or not.
                    var templogin = $(obj).val().trim().toLocaleLowerCase();
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CheckRegistration&field=login&value'+templogin, function(result){
                        if(result == "EXISTS"){
                            alert('This user already exists.  Please type in another login.');
                            $("#txtlogin").val('');
                            $("#txtlogin").focus();
                            return(false);
                        }
                        checkRequiredfields();
                        return(false);
                    });
                }
            }
            function validateCustomeremail(obj){
                
                if($(obj).val() == ""){
                    return(false);
                }
                validateEmail(obj); //It should stop here if it failed to validate.
                //Now we will check if this email already exists.
                var tempemail = $("#txtemail").val().trim().toLocaleLowerCase();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CheckCustomeremail&field=email&value='+tempemail, function(result){
                    if(result == "EXISTS"){
                        alert('This email already exists.  Please use another email.');
                        $("#txtemail").val('');
                        $("#txtemail").focus();
                        return(false);
                    }
                    updateCustomer(obj); 
                });
            }
            function validateThisemail(obj, thisfrom){
                validateEmail(obj); //It should stop here if it failed to validate.
                    //Now we will check if this email already exists.
                    //thisfrom will be 'Change' or 'Load'
                    var tempemail = $("#txtemail").val().trim().toLocaleLowerCase();
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CheckRegistration&field=email&value='+tempemail, function(result){
                        if(result == "EXISTS"){
                            alert('This email already exists.  Please use another email.');
                            $("#txtemail").val('');
                            $("#txtemail").focus();
                            return(false);
                        }
                        checkRequiredfields();
                        if(thisfrom == "Change")
                        {
                            updateUser(obj);
                        }
                    });
                
            }
            function submitRegistrationform(){
                //We will check to see if the name exists in our database.
                checkEmployeenumber('Submit'); //We want to double check on the employee number before we submit.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitRegistration&'+$("#frmregistration").serialize(), function(result){
                        if(result == "Success")
                        {
                            alert("Your account has been created. A link has been sent to the provided email.  Please login and follow the link to verify your account.");
                            clearRegistrationform();
                        }
                        else{
                            alert(result);
                            return(false);
                        }
                });
            }
            function doMenus(obj){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
            }
            function manageUsers(obj){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageUsers', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function clearSearch(){
                $("#sltsearchuser").remove();
                $("#tbluserdata").remove();
                $("#txtsearchuser").val('');
                $("#txtsearchuser").focus();
            }
            function clearCustomersearch(){
                $("#sltsearchcustomer").remove();
                $("#tblcustomerdata").remove();
                $("#txtsearchcustomer").val('');
                $("#txtsearchcustomer").focus();
            }
            function searchUser(){
                if($("#txtsearchuser").val().trim().length == 0){
                    clearSearch();
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchUserunset&txtsearchuser='+$("#txtsearchuser").val(), function(){
                        return(false);
                    }); 
                }
                if($("#txtsearchuser").val().trim().length < 2){
                    return(false);
                }
             
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchUser&txtsearchuser='+$("#txtsearchuser").val(), function(result){
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();
                    }
                    $("#tbluserdata").remove();
                    $("#div_mgm_search").after(result);
                }); 
            }
            function hideSelectsearch(){
                $("#txtsearchuser").val($("#sltsearchuser").find(":selected").text());
                $('body').data('recno', $("#sltsearchuser").find(":selected").val());
                $("#sltsearchuser").remove();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetUserdata&recno='+$('body').data('recno'), function(result){
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").after(result);
                       
                }); 
            }
            function hideSelectsearchcustomer(){
                $("#txtsearchcustomer").val($("#sltsearchcustomer").find(":selected").text());
                $('body').data('recno', $("#sltsearchcustomer").find(":selected").val());
                $("#sltsearchcustomer").remove();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetCustomerdata&recno='+$('body').data('recno'), function(result){
                    if($("#sltsearchcustomer").length){
                        $("#sltsearchcustomer").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").after(result);
                       
                }); 
            }
            function updateUser(obj){    
                var realvalue = $(obj).val();
                if($(obj).prop('id') == 'chkisengineer' || $(obj).prop('id') == 'chkismechanic' || $(obj).prop('id') == 'chkistechnician'){
                    if($(obj).is(":checked")){
                        realvalue='true';
                    }
                    else{
                        realvalue='false';
                    }
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateUser&txtrecno='+$('body').data('recno')+'&field='+$(obj).prop('id')+'&value='+realvalue, function(result){
                    //alert(result);
                    if(result == "Success"){
                        //alert('Updated');
                    }
                    else{
                        alert('Failed to update.  Contact Administrator.');
                    }
                });
            }
            function updateCustomer(obj){
                var realvalue = $(obj).val();
                if($(obj).prop('id') == 'chkiscargo' || $(obj).prop('id') == 'chkispasenger' || $(obj).prop('id') == 'chkisactive')
                {
                    realvalue = "No";
                    if($(obj).is(":checked"))
                    {
                        realvalue = "Yes";
                    }
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateCustomer&txtrecno='+$('body').data('recno')+'&field='+$(obj).prop('id')+'&value='+realvalue, function(result){
                    if(result == "Success"){
                        //alert('Updated');
                    }
                    else{
                        alert('Failed to update.  Contact Administrator.');
                    }
                });
            }
            function addCustomerdefault(){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_add").css("background-color", "white");
                $("#div_add").css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddCustomer', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function addCustomer(obj){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddCustomer', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function submitCustomerform(){
                //We will check to see if the name exists in our database.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitCustomerform&'+$("#frmaddcustomer").serialize(), function(result){
                        if(result == "Success")
                        {
                            alert("Customer has been successfully added.");
                            clearAddcustomerform();
                        }
                        else{
                            alert(result);
                            return(false);
                        }
                });
            }
            function validateCustomer(obj){
                switch($(obj).prop('id'))
                {
                    case "txtcustomer":
                        if($(obj).val().length == 0 && $(obj).val() == "")
                        {
                            return(false);
                        }
                        break;
                    case "txtairlinecode":
                        if($(obj).val().length != 3)
                        {
                            alert("The airline code has to be 3 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                    case "txticaocode":
                        if($(obj).val().length != 3)
                        {
                            alert("The ICAO code has to be 3 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                    case "txtiatacode":
                        if($(obj).val().length != 2)
                        {
                            alert("The ICAO code has to be 2 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ValidateCustomer&field='+$(obj).prop('id')+'&value='+$(obj).val(), function(result){
                        if(result != "Success")
                        {
                            alert(result);
                            $(obj).val('');
                            $(obj).focus();
                            return(false);
                        }
                        if($("#txtcustomer").val != "" && $("#txtairlinecode").val != "" && $("#txticaocode").val != "" && $("#txtiatacode").val != "")
                        {
                            $("#btnfrmaddcustomer").prop('disabled', false);
                        }
                });
            }
            function manageCustomer(obj){
               $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageCustomer', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                }); 
            }
            function searchCustomer(){
                if($("#txtsearchcustomer").val().trim().length == 0){
                    clearCustomersearch();
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchCustomerunset&txtsearchcustomer='+$("#txtsearchcustomer").val(), function(){
                        return(false);
                    }); 
                }
                if($("#txtsearchcustomer").val().trim().length < 2){
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchCustomer&txtsearchcustomer='+$("#txtsearchcustomer").val(), function(result){
                    if($("#sltsearchcustomer").length){
                        $("#sltsearchcustomer").remove();
                    }
                    $("#tblcustomerdata").remove();
                    $("#div_mgm_search_customer").after(result);
                }); 
            }
            function checkEmployeenumber(thisfrom){
                var tempempno = "";
                if(thisfrom == "Submit"){
                    tempempno = $("#txtemployeenumber").val();
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CheckEmployeenumber&thisvalue='+$('#txtemployeenumber').val()+'&tempempno='+tempempno, function(result){
                    if(result == "EXISTS"){
                        if(thisfrom == "Load"){
                           alert("This employee number already exist.  Please try again.");
                        }
                        else if(thisfrom == "Submit"){
                           alert("The entered employee number has been assigned to another employee recently.  Please try again."); 
                        }
                        $('#txtemployeenumber').focus();
                        return(false);
                    }
                    return(true);
                });
            }
            function manageProfiles(obj)
            {
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=modMenu', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function manageMenu(obj)
            {
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageMenu', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function manageMenudefault()
            {
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $('#div_menu').css("background-color", "white");
                $('#div_menu').css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageMenu', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function addNewmenu()
            {
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddNewmenu', function(result){
                    $('#tbl_order_data').prepend(result);
                    i=1;
                    $(".tdnumbered").each(function(){
                        $(this).text(i);
                        i++;
                    });
                    $("#txttitle1").focus();
                });
            }
            function clickToadd(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ClickToadd&title='+$("#txttitle1").val()+'&profile='+$("#txtprofile1").val(), function(result){
                    //alert(result);
                    if(result == "Success"){
                        manageProfilesreload();
                    }
                    else if(result == "Failed"){
                        alert("Failed to update.");
                        return(false);
                    }
                    else{
                        alert("This title 2 character synonyms already exists.  Please try again.");
                        return(false);
                    }
                    
                });
            }
            function manageProfilesreload()
            {
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_mangemenu").css("background-color", "white");
                $("#div_mangemenu").css('color', 'black');
                //window.location.href = "registration.php";
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=modMenu', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                });
            }
            function updateMenu(obj, recno, lineno){
                thisfield = "";
                thisval = "";
                if($(obj).prop('id') == "btnisdeleted"+lineno){
                    $("#tr"+lineno).remove();
                    thisfield = "isdeleted";
                    thisval = 'true';
                    i=0;
                    $(".tdnumbered").each(function(){
                        i++;
                        $(this).text(i);
                    });
                }
                if($(obj).prop('id') == "txttitle"+lineno){
                    $("txttitle"+lineno).remove();
                    thisfield = "title";
                    thisval = $(obj).val();
                }
                if($(obj).prop('id') == "txtprofile"+lineno){
                    thisfield = "profile";
                    thisval = $(obj).val();
                }
                //alert(thisfield+' and '+thisval+' and recno: '+recno);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateMenu&recno='+recno+'&field='+thisfield+'&thisval='+thisval, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert('Failed to update.');
                        return(false);
                    }
                    else if(result == "Exists"){
                        alert("This already exists.  Please pick another name.");
                        return(false);
                    }
                });
            }
            function getMenus(obj, recno){
                //recno is the recno of profiles table
                $("#div_availableitems").hide();  //We want to hide the Authorization column whern user click on any Menu category and it will show again once they clicked
                //on the Pages category to load the respective Authorization.
                $('body').data('currentmenurecno', recno);
                $(".menu-list").each(function(){
                    $(this).css('background-color', '#E8EAEA');
                    $(this).css('color', 'black');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                //alert('here');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetPages&pagerecno='+recno+'&prorecno='+$('body').data('currentmenurecno'), function(result){
                    if(result != 'Failed'){
                        $("#div_menupages").html(result);
                    }
                    else{
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function updateAuthorized(obj, prorecno, pagerecno){
                //recno is recno of table pages
                //$('body').data('currentmenurecno') is the recno for profile
                //alert('check');
                
                from = "uncheck";
                if($(obj).is(":checked")){
                    from = "checked";
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateAuthorized&pagerecno='+pagerecno+'&prorecno='+prorecno+'&from='+from, function(result){
                    if(result == 'Failed'){
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function getAuthorizationprivilege(obj, prorecno, pagerecno){
                //alert(pagerecno);
                 
                 $(".category-list").each(function(){
                    $(this).css('background-color', '#E8EAEA');
                    $(this).css('color', 'black');
                })
                //alert('we are here '+prorecno+' and '+pagerecno);
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetAuthorizationprivilege&pagerecno='+pagerecno+'&prorecno='+prorecno, function(result){
   
                    $("#div_availableitems").show();
                    $("#div_menuprivilege").html(result);
                    
                });
            }
            function updateAuthorizedprevilege(obj, prorecno, pagerecno){
               
                //recno is recno of table pages
                //$('body').data('currentmenurecno') is the recno for profile
                
                from = "uncheck";
                if($(obj).is(":checked")){
                    from = "checked";
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateAuthorizedprevilege&pagerecno='+pagerecno+'&prorecno='+prorecno+'&from='+from+'&thisval='+$(obj).val(), function(result){
                   // alert(result);
                    if(result == 'Failed'){
                        alert("Failed to load items for this category.");
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
function UpdateAuthorizedprevilege()
{
    global $db;
    
    $thistable = "menu_authorized";
    $thisfields = array("action");
    $thiswheres = array("profilerecno" => $_POST['prorecno'], "pagerecno" => $_POST['pagerecno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    
    //First, we check for the check
    file_put_contents("./dodebug/debug.txt", 'from: '.$_POST['from'].' > prorecno: '.$_POST['prorecno'].' > pagerecno: '.$_POST['pagerecno'], FILE_APPEND);
    if($_POST['from'] == "checked")
    {
        //We are going to either do an insert or update to the field
        //Now we want to add this to the field
        $sql = "UPDATE $thistable SET action = CONCAT_WS(',' ,action, '".$_POST['thisval']."') WHERE profilerecno = ".$_POST['prorecno']." AND pagerecno=".$_POST['pagerecno'];
        file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND);
        $db->PDOMiniquery($sql);
    }
    else
    {
        //We are going to remove from the field
        foreach($result as $rs)
        {
            $explodeaction = explode(',', $rs['action']); //$explodeaction is an array that may or may not have all of the prevs, READ,WRITE,MODIFY,DELETE
            if (($thiskey = array_search($_POST['thisval'], $explodeaction)) !== false) 
            {
                unset($explodeaction[$key]);
            }
            $thisdata = array("action" => implode(',', $explodeaction));
            $result1 = $db->PDOUpdate($thistable, $thisdata, $thiswheres, $rs['recno']);
            if(!result1)
            {
                echo "Failed";
            }
        }
        
    }
}
function GetAuthorizationprivilege()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", 'this page pagerecno: '.$_POST['pagerecno'].' prorecno: '.$_POST['prorecno'], FILE_APPEND);
    if($_POST['pagerecno'] == 0)
    {?>
        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
            <div id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;" value="Read" />Read
            </div>
            <div  id="divwrite" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                <input type="checkbox" name="chkwrite" id="chkwrite" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Read" />Write
            </div>
            <div  id="divmodify" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                <input type="checkbox" name="chkmodify" id="chkmodify" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Read"/>Modify
            </div>
            <div id="divdelete" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                <input type="checkbox" name="chkdelete" id="chkdelete" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Read"/>Delete
            </div>
        </div><?php        
    }
    else
    {
            
        $thistable = "menu_authorized";
        //file_put_contents("./dodebug/debug.txt", ' prorecno: '.$_POST['prorecno'].' > pagerecno: '.$_POST['pagerecno'], FILE_APPEND);
        $sql = "SELECT * FROM $thistable WHERE profilerecno = ".$_POST['prorecno']." AND pagerecno = ".$_POST['pagerecno']." AND action IS NOT NULL";
        $result = $db->PDOMiniquery($sql);
        if($db->PDORowcount($result) == 0)
        {
            file_put_contents("./dodebug/debug.txt", ' should behere55 IS NULL', FILE_APPEND);?>
            <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                    <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Read"/>Read
                </div>
                <div  id="divwrite" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                    <input type="checkbox" name="chkwrite" id="chkwrite" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Write"/>Write
                </div>
                <div  id="divmodify" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                    <input type="checkbox" name="chkmodify" id="chkmodify" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Modify"/>Modify
                </div>
                <div  id="divdelete" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                    <input type="checkbox" name="chkdelete" id="chkdelete" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Delete"/>Delete
                </div>
            </div><?php
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", 'should be here now NOT NULL', FILE_APPEND);
            $actionarray = array('Read', 'Write', 'Modify', 'Delete');
            
            
            foreach($result as $rs)
            {
                $explodeaction = explode(",", $rs['action']);
                             
                    if(in_array('Read', $explodeaction))
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;" checked  value="Read"/>Read
                            </div>
                        </div><?php                    
                    }
                    else
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Read"/>Read
                            </div>
                        </div><?php
                    }
                    
                    if(in_array("Write", $explodeaction))
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;" checked  value="Write"/>Write
                            </div>
                        </div><?php
                    }
                    else
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Write"/>Write
                            </div>
                        </div><?php                

                    }

                    if(in_array("Modify", $explodeaction))
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;" checked  value="Modify"/>Modify
                            </div>
                        </div><?php                    
                    }
                    else
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Modify"/>Modify
                            </div>
                        </div><?php 
                    }

                    if(in_array("Delete", $explodeaction))
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;" checked  value="Delete"/>Delete
                            </div>
                        </div><?php
                    }
                    else
                    {?>
                        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                            <div  id="divread" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                                <input type="checkbox" name="chkread" id="chkread" onclick="updateAuthorizedprevilege(this, <?=$_POST['prorecno']?>, <?=$_POST['pagerecno']?>);" style="height: 20px; width: 20px;"  value="Delete"/>Delete
                            </div>
                        </div><?php                    
                    }
               
            }
        }
    }
}
function UpdateAuthorized()
{
    global $db;
    
    if($_POST['from'] == "checked")
    {
        //We are doing insert or update
        $thistable = 'menu_authorized';
        $thisfields = array('All');
        $thiswheres = array('profilerecno' => $_POST['prorecno'], 'pagerecno' => $_POST['pagerecno']);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
        //file_put_contents("./dodebug/debug.txt", 'from: '.$_POST['from'].' > prorecno: '.$_POST['prorecno'].' > pagerecno: '.$_POST['pagerecno'], FILE_APPEND);
        if(!$result)
        {
            //We insert if nothing came back
            $thisdata = array('profilerecno' => $_POST['prorecno'], 'pagerecno' => $_POST['pagerecno']);
            $inresult = $db->PDOInsert($thistable, $thisdata);
            if(!$inresult)
            {
                echo "Failed to insert new record.";
            }
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", 'from: '.$_POST['from'].' > prorecno: '.$_POST['prorecno'].' > pagerecno: '.$_POST['pagerecno'], FILE_APPEND);
            //We update if we got something back
            $thisdata = array('isdeleted' => false);
            $thiswheres = array('profilerecno' => $_POST['prorecno'], 'pagerecno' => $_POST['pagerecno']);
            $uresult = $db->PDOUpdate($thistable, $thisdata, $thiswheres, $_POST['prorecno']);
            if(!$unresult)
            {
                echo "Failed to update this record.";
            }
        }
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", 'from: '.$_POST['from'].' > prorecno: '.$_POST['prorecno'].' > pagerecno: '.$_POST['pagerecno'], FILE_APPEND);
        //If we are doing an uncheck, that means there should already be something so we just going to turn it false.
        $thistable = 'menu_authorized';
        //We update if we got something back
        $thisdata = array('isdeleted' => true);
        $thiswheres = array('profilerecno' => $_POST['prorecno'], 'pagerecno' => $_POST['pagerecno']);
        $uresult = $db->PDOUpdate($thistable, $thisdata, $thiswheres, $_POST['prorecno']);
        if(!$uresult)
        {
            echo "Failed to update this record.";
        }
    }
       
}
function GetPages()
{
    global $db;
    //We want to check to see if this Menu already have some pages...
    $thistable = "menu_authorized";
    $thisfield = array("All");
    $thiswheres = array("profilerecno" => $_POST['prorecno'], "isdeleted" => false);
    $proresult = $db->PDOQuery($thistable, $thisfield, $thiswheres);
    $i = 0;
    //file_put_contents("./dodebug/debug.txt", ' prorecno: '.$_POST['prorecno'].' > pagerecno: '.$_POST['pagerecno'], FILE_APPEND);
    if(!$proresult)
    {
        //$sql = "SELECT p.recno as precno, p.pagename, ma.action from pages p LEFT JOIN menu_authorized ma ON p.recno = ma.pagerecno ";
        //sql .= "WHERE ma.employee_recno = '".$_SESSION['employee_master_recno']."' AND ma.isdeleted = false AND p.isactive = true AND p.isdeleted = false";
        $sql = "SELECT * FROM pages WHERE isactive = true AND isdeleted = false";
        $pageresult = $db->PDOMiniquery($sql);
        //file_put_contents("./dodebug/debug.txt", "should not be here", FILE_APPEND);?>
        <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;"><?php
            foreach($pageresult as $rs)
            {
                $i++?>
                <div class="category-list" id="div_<?=$i?>" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                    <input class="category-list" type="checkbox" name="chkpagename_<?=$rs['recno']?>" id="chkpagename_<?=$rs['recno']?>" onclick="updateAuthorized(this, <?=$_POST['prorecno']?>, <?=$rs['recno']?>);" style="height: 20px; width: 20px; float: left;" />
                    <div class="category-list" style="cursor: pointer;" id="divprivilege_<?=$rs['recno'];?>" onclick="getAuthorizationprivilege(this, <?=$_POST['prorecno']?>, <?=$rs['recno']?>);"><?=$rs['pagename']?></div>
                </div><?php
            }?>
        </div><?php
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", "should  be here 99", FILE_APPEND);
        //We will build the pro recno list first so then we will just do in_array() for checks or unchck
        $thisprorecno = [];
        foreach($proresult as $rs)
        {
            $thisprorecno[] = $rs['pagerecno'];
        }
        $sql = "SELECT * FROM pages WHERE isactive = true AND isdeleted = false";
        $pageresult = $db->PDOMiniquery($sql);
        
        foreach($pageresult as $prs)
        {
            $i++;
            $thischeck = "";
            if(in_array($prs['recno'], $thisprorecno))
            {
                $thischeck = "checked";
            }?>
            <div class="category-list" id="div_<?=$i?>" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px;">
                <input class="category-list" type="checkbox" name="chkpagename_<?=$prs['recno']?>" id="chkpagename_<?=$prs['recno']?>" onclick="updateAuthorized(this, <?=$_POST['prorecno']?>, <?=$prs['recno']?>);" style="height: 20px; width: 20px; float: left;" <?=$thischeck?>/>
                <div class="category-list" style="cursor: pointer;" id="divprivilege_<?=$prs['recno'];?>" onclick="getAuthorizationprivilege(this, <?=$_POST['prorecno']?>, <?=$prs['recno']?>);"><?=$prs['pagename']?></div>
            </div><?php             
        }
    }
}
function ManageMenu()
{
    global $db;
    $thistable = "profiles";
    $thisfields = array("All");
    $thiswheres = array('isdeleted' => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);?>
    <div style="float: left; width: 800px; color: white; margin: left;">
        <div style="width: 200px; height: 760px; float: left; background-color: #E8EAEA; padding-top: 10px; overflow: auto;">
            <div style="height: 40px; width: 100%; float: left; text-align: center; color: black; font-size: 1.2em; font-weight: bold; padding-bottom: 10px;">
                <u>Menu</u>
            </div>
            <div id="div_categorycontainer" ><?php
                $i = 1;
                    foreach($result as $rs)
                    {?>
                        <div class="menu-list" id="div_<?=$i?>" style="height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; text-indent: 10px;" onclick="getMenus(this, <?=$rs['recno']?>);">
                            <?=$rs['title']?>&nbsp;(<?=$rs['profile']?>)
                        </div><?php
                        $i++;
                    }?>
            </div>
        </div>
        <div id="div_curentitems" style="width: 300px; height: 760px; float: left; background-color: #E8EAEA; padding-top: 10px; overflow: auto; margin-left: 10px;">
            <div style="height: 40px; width: 100%; float: left; text-align: center; cursor: pointer; color: black; font-size: 1.2em; font-weight: bold; padding-bottom: 10px;">
                <u>Pages</u>
            </div>
            <div id='div_menupages'></div>
        </div>
        <div id="div_availableitems" style="width: 200px; height: 760px; float: left; background-color: #E8EAEA; padding-top: 10px; overflow: auto; margin-left: 10px; display: none;">
            <div style="height: 40px; width: 100%; float: left; text-align: center; cursor: pointer; color: black; font-size: 1.2em; font-weight: bold; padding-bottom: 10px;">
                <u>Authorization</u>
            </div>
            <div id="div_menuprivilege"></div>
        </div>                   
    </div><?php  
}
function UpdateMenu()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", $_POST['field']." = ".$_POST['thisval']." recno = ".$_POST['recno'], FILE_APPEND);

    $thistable = 'profiles';
    if($_POST['field'] != 'isdeleted')
    {
        //We must check to see if this already exist.
        $thisfield = array($_POST['field']);
        $thiswhere = array($_POST['field'] => $_POST['thisval']);
        $result = $db->PDOQuery($thistable, $thisfield, $thiswhere);
        if(!$result)
        {
            if($_POST['field'] == "isdeleted")
            {
                $thisdata = array($_POST['field'] => true);
            }
            else
            {
                $thisdata = array($_POST['field'] => $_POST['thisval']);
            }
            $thiswhere = array('recno' => $_POST['recno']);
            $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
            if(isset($result))
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
            echo "Exists";
        }
    }
    else
    {
        $thisdata = array($_POST['field'] => true);
        $thiswhere = array('recno' => $_POST['recno']);
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
        if(isset($result))
        {
            echo 'Success';
        }
        else
        {
            echo 'Failed';
        }
    }
}
function ClickToadd()
{
    global $db;
    $thistable = "profiles";
    $sql = "SELECT profile, title FROM profiles WHERE profile = '".$_POST['profile']."' OR title = '".$_POST['title']."'";
    $result = $db->PDOMiniquery($sql);
    if(!$result)
    {
        $thisdata = array('title' => $_POST['title'], 'profile' => $_POST['profile']);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $db->PDOInsert($thistable, $thisdata);
        //file_put_contents("./dodebug/debug.txt", $result, FILE_APPEND);
        if(isset($result))
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
        echo "Exists";
    }
}
function AddNewmenu()
{
    $canmod = "display: none;";
    if($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV")
    {
        $canmod = "";
    }?>
    <tr id="tr" style="font-size: .8em;">
        <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;">1</td>
        <td style="height: 40px; width: 180px; padding-left: 10px; cursor: pointer;" ><input type="text" id="txttitle1" name="txttitle1" style="width: 98%; height: 98%; border: none;" value="" /></td>
        <td style="height: 40px; width: 40px; padding-left: 20px; cursor: pointer;" ><input type="text" id="txtprofile1" name="txtprofile1" style="width: 95%; height: 98%; border: none;" value="" /></td>
        <td style="height: 40px; width: 125px; padding-left: 10px; <?=$canmod?>">
            <button class="serviceorder-button-delete" style="height: 38px; width: 100px;" id="btnisdeleted" value="Delete" onclick="clickToadd();">Click to add</button>
        </td>
    </tr><?php    
}
function modMenu()
{
    global $db;
    $canmod = "display: none;";
    if($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV")
    {
        $canmod = "";
    }
    $sql = "SELECT * FROM profiles WHERE isdeleted = false";
    $result = $db->PDOMiniquery($sql);
    ?>
    <div>
        <table id="tbl_order_data" class="tbl-order-data">
            <thead>
                <tr style="background-color: #173346;" style="font-size: .8em;">
                    <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                    <th style="width: 180px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Title&nbsp&nbsp<span style="height: 20px; width: 20px; cursor: pointer;" title="Click to add more profiles" onclick="addNewmenu();">+</span></th>
                    <th style="width: 100px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Abbreviation</th>
                    <th style="<?= $canmod ?>"></th>
                 </tr>
            </thead>
            <tbody id="tblbody"><?php
                $i=1;
                if($result)
                {
                    foreach($result as $rs)
                    {?>
                        <tr id="tr<?=$i?>" style="font-size: .8em;">
                            <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;" style="height: 90%; width: 95%; border: none;" ><?= $i ?></td>
                            <td style="height: 40px; width: 180px; padding-left: 10px; cursor: pointer;" ><input type="text" name="txttitle<?=$i?>" id="txttitle<?=$i?>" value="<?= $rs['title']?>" style="height: 90%; width: 95%; border: none;" onchange="updateMenu(this, <?=$rs['recno']?>, <?=$i?>);"/></td>
                            <td style="height: 40px; width: 40px; padding-left: 20px; cursor: pointer;" ><input type="text" name="txtprofile<?=$i?>" id="txtprofile<?=$i?>" onchange="updateMenu(this, <?=$rs['recno']?>, <?=$i?>);" style="height: 90%; width: 95%; border: none;" value="<?= $rs['profile'];?>"/></td>
                            <td style="height: 40px; width: 125px; padding-left: 10px; <?=$canmod?>">
                                <button class="serviceorder-button-delete" style="height: 38px; width: 60px;" id="btnisdeleted<?=$i?>" value="Delete" onclick="updateMenu(this, <?= $rs['recno'] ?>, <?=$i?>);">Delete</button>
                            </td>
                        </tr><?php
                        $i++;
                    }
                    if($i==1)
                    {?>
                        <tr id="tr_nodata"><td style="width: 100%; text-align: center;" colspan="5">There is no data</td></tr><?php
                    }
                }?> 
            </tbody>
        </table>
    </div><?php
}
function CheckEmployeenumber()
{
    global $db;
    /*
    $sql = "SHOW TABLE STATUS LIKE 'employee_master'";
    $result = $db->PDOMiniquery($sql);
    */
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "employee_master";
    $thisfields[] = "employeenumber";
    $thiswhere = array("employeenumber" => $_POST['thisvalue']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       echo "EXISTS"; 
    }
    else
    {
        echo "Failed";
    }
}
function CheckCustomeremail()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "customer_master";
    $thisfields[] = $_POST['field'];
    $thiswhere = array($_POST['field'] => $_POST['value']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       echo "EXISTS"; 
    }
}
function ManageCustomer()
{?>
    <div id="div_search_containter" style="background-color: blue; width: 310px; min-width: 310px; text-align: left; background-color: red;">
        <div class="div-mgm-search-container" id="div_mgm_search_customer">
            <input class="txt-search-user" type="text" id="txtsearchcustomer" name="txtsearchcustomer" value="" placeholder="Enter a customer to start search" onclick="searchCustomer();" onkeyup="searchCustomer(this);" />
            <button type="button" onclick="clearCustomersearch();">Clear</button</div>
        </div>
        
    </div><?php
}
function SearchCustomerexistinglist($thisstr)
{
    //file_put_contents("./dodebug/debug.txt", "sessionlist: ".var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $realname = "";?>   
    <select name="sltsearchcustomer" id="sltsearchcustomer" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -310px;"  size="5" onchange="hideSelectsearchcustomer();"><?php
        foreach($_SESSION['customersearchlist'] as $rs => $value)
        {
           if(strpos(strtolower($value), strtolower($thisstr)) !== false)
           {?>
                <option value="<?= $rs ?>"><?= $value ?></option><?php 
            }
        }?>
    </select><?php
}
function SearchCustomer()
{
    global $db;
    if(count($_SESSION['customersearchlist']) > 0)
    {
        //If this session variable has stuffs in it, that means user already started the search and
        //we want to use this array rather than going back to the database every time user typed a char.
        //file_put_contents("./dodebug/debug.txt", 'inside session', FILE_APPEND);
        SearchCustomerexistinglist($_POST['txtsearchcustomer']);
        exit();
    }
    //file_put_contents("./dodebug/debug.txt", var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "customer_master";
    $thisfields = array('recno', 'customer');
    $tempexplodeuser = explode(' ', $_POST['txtsearchcustomer']);
    //We get here when user entered just the first name, middle or last name.
    $thiswhere = array("customer LIKE" => strtolower($_POST['txtsearchcustomer']));
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>   
    <select name="sltsearchcustomer" id="sltsearchcustomer" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -310px;"  size="5" onchange="hideSelectsearchcustomer();"><?php
        if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {  
           foreach($result as $rs)
           {
               
               $_SESSION['customersearchlist'][$rs['recno']] = $rs['customer'];?>
                <option value="<?= $rs['recno']?>"><?= $rs['customer'] ?></option><?php 
           }
        }?>
    </select><?php
}
function SubmitCustomerform()
{
    global $db, $load_headers;
    
    $thisdata = Array();
    $thistable = "customer_master";
    foreach($_POST as $key => $value)
    {
        if($key != "cmd")
        {
            if(substr($key, 3) == "iscargo" || substr($key, 3) == "ispassenger")
            {
                $thisdata[substr($key, 3)] = true;
            }
            else
            {
                $thisdata[substr($key, 3)] =  $value;
            }
        }
    }
    $result = $db->PDOInsert($thistable, $thisdata); 
    if(isset($result))
    {
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function ValidateCustomer()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "customer_master";
    $thisfields[] = substr($_POST['field'], 3);
    $thiswhere = array(substr($_POST['field'], 3) => $_POST['value']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       switch(substr($_POST['field'], 3))
       {
           case "customer":
               echo "This customer already exist in the database.";
               break;
           case "icaocode":
               echo "This ICAO code already exists in the database with another customer.";
               break;
           case "iatacode":
               echo "This IATA code already exists in the database with another customer.";
               break;
           case "airlinecode":
               echo "This airline code already exists in the database with another customer.";
               break;
           default: brea;
       }
    }
    else
    {
        echo "Success";
    }
}
function UpdateCustomer()
{
    global $db;
    $thisfields = Array();
    $thistable = "customer_master";
    $thisfield = substr($_POST['field'], 3);
    //file_put_contents("./dodebug/debug.txt", $thisfield.' = '.$_POST['value'], FILE_APPEND);
    if(($thisfield == "iscargo" || $thisfield == "ispassenger" || $thisfield == "isactive" ) && $_POST['value'] == 'Yes')
    {
        $thisdata = array($thisfield => true);  
    }
    else if(($thisfield == "iscargo" || $thisfield == "ispassenger" || $thisfield == "isactive" ) && $_POST['value'] == 'No')
    {
        $thisdata = array($thisfield => false);  
    }
    else
    {
        $thisdata = array($thisfield => $_POST['value']);  
    }
    $thiswhere = array("recno" => $_POST['txtrecno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['txtrecno']);
    //file_put_contents("./dodebug/debug.txt", $result, FILE_APPEND);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }
}
function UpdateUser()
{
    global $db;
    $thisfields = Array();
    $thistable = "employee_master";
    $thisfield = substr($_POST['field'], 3);
    if($thisfield == "birthday" || $thisfield == "hiredate")
    {
        $formatthisdate = date('Y-m-d', strtotime($_POST['value']));
        $thisdata = array($thisfield => $formatthisdate); 
    }
    else if(($thisfield == "isengineer"  || $thisfield == "ismechanic"  || $thisfield == "istechnician"))
    {
        if($_POST['value'] == 'true')
        {
            $thisdata[$thisfield] =  true;
        }
        else
        {
            $thisdata[$thisfield] =  false;
        }
    }
    else
    {
        $thisdata[$thisfield] = $_POST['value'];  
    }
    $thiswhere = array("recno" => $_POST['txtrecno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['txtrecno']);
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }
}
function SearchUserunset()
{
    $_SESSION['usersearchlist'] = array();
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function SearchCustomerunset()
{
    $_SESSION['customersearchlist'] = array();
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function GetCustomerdata()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "customer_master";
    $thisfields = array('All');
    $thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <table id="tblcustomerdata" class="tbl-admin-register">
                <tr>
                    <td class="tbl-admin-register-lbl">Customer Name: </td>
                    <td class="registrationinput"><input type="text" class="firstname required" id="txtcustomer" name="txtcustomer" value="<?= $rs['customer'] ?>" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Airline Code: </td>
                    <td class="registrationinput"><input type="text" class="login required" id="txtairlinecode" name="txtairlinecode" value="<?= $rs['airlinecode'] ?>" readonly  /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">ICAO Code: </td>
                    <td class="registrationinput"><input type="text" class="middlename required" id="txticaocode" name="txticaocode" value="<?= $rs['icaocode'] ?>" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">IATA Code: </td>
                    <td class="registrationinput"><input type="text" class="lastname required" id="txtiatacode" name="txtiatacode" value="<?= $rs['iatacode'] ?>" readonly  /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Address: </td>
                    <td class="registrationinput"><input type="text" class="birthday" id="txtaddress" name="txtaddress" value="<?= $rs['address'] ?>" onchange="updateCustomer(this);" autofocus /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">City: </td>
                    <td class="registrationinput"><input type="text" class="email" id="txtcity" name="txtcity" value="<?= $rs['city'] ?>" onchange="updateCustomer(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">State: </td>
                    <td class="registrationinput"><input type="text" class="address" id="txtstate" name="txtstate" value="<?= $rs['state'] ?>" onchange="updateCustomer(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Country: </td>
                    <td class="registrationinput"><input type="text" class="city " id="txtcountry" name="txtcountry" value="<?= $rs['country'] ?>" onchange="updateCustomer(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Zip-Code: </td>
                    <td class="registrationinput"><input type="text" class="state" id="txtzipcode" name="txtzipcode" value="<?= $rs['zipcode'] ?>" onchange="updateCustomer(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Email: </td>
                    <td class="registrationinput"><input type="text" class="zipcode customeremail" id="txtemail" name="txtemail" value="<?= $rs['email'] ?>" onchange="validateCustomeremail(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Service(s): </td>
                    <td class="registrationinput"><?php
                        if($rs['iscargo'] == true)
                        {?>
                            <input type="checkbox" class="zipcode" id="chkiscargo" name="chkiscargo" value="chkiscargo"onchange="updateCustomer(this);"  checked />&nbsp;&nbsp;Cargo<br/><?php
                        }
                        else
                        {?>
                            <input type="checkbox" class="zipcode" id="chkiscargo" name="chkiscargo" value="chkiscargo"onchange="updateCustomer(this);"  />&nbsp;&nbsp;Cargo<br/><?php                                
                        }
                        if($rs['ispassenger'] == true)
                        {?>
                            <input type="checkbox" class="zipcode" id="chkispassenger" name="chkispassenger" value="chkispassenger"onchange="updateCustomer(this);"  checked />&nbsp;&nbsp;Passenger<br/><?php
                        }
                        else
                        {?>
                            <input type="checkbox" class="zipcode" id="chkispassenger" name="chkispassenger" value="chkispassenger"onchange="updateCustomer(this);"  />&nbsp;&nbsp;Passenger<br/><?php                                
                        }
                        if($rs['isactive'] == true)
                        {?>
                            <input type="checkbox" class="zipcode" id="chkisactive" name="chkisactive" value="chkisactive"onchange="updateCustomer(this);"  checked />&nbsp;&nbsp;Active<br/><?php
                        }
                        else
                        {?>
                            <input type="checkbox" class="zipcode" id="chkisactive" name="chkisactive" value="chkisactive"onchange="updateCustomer(this);"  />&nbsp;&nbsp;Active<br/><?php                                
                        }?>    
                    </td>
                </tr>
            </table><?php
       }
    }
}
function GetUserdata()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "employee_master";
    $thisfields = array('All');
    $thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <table id="tbluserdata" class="tbl-admin-register">
                <tr>
                    <td class="tbl-admin-register-lbl">Employee Number: </td>
                    <td class="registrationinput"><input type="text" class="firstname required" id="txtemployeenumber" name="txtemployeenumber" value="<?= $rs['employeenumber']; ?>" onchange="updateUser(this);" autofocus required /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">First Name: </td>
                    <td class="registrationinput"><input type="text" class="firstname required" id="txtfirstname" name="txtfirstname" value="<?= $rs['firstname']; ?>" onchange="updateUser(this);" required /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Middle Name: </td>
                    <td class="registrationinput"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" value="<?= $rs['middlename']; ?>" onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Last Name: </td>
                    <td class="registrationinput"><input type="text" class="lastname required" id="txtlastname" name="txtlastname" value="<?= $rs['lastname']; ?>" onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Profile: </td>
                    <td class="registrationinput">
                        <select id="sltprofile" name="sltprofile" onchange="updateUser(this);"><?php
                            $thistablep = "profiles";
                            $thisfieldsp = array('All');
                            $resultp = $db->PDOQuery($thistablep, $thisfieldsp);
                            foreach($resultp as $rsp)
                            {
                                if($_SESSION['profile'] == $rsp['profile'])
                                {?>
                                    <option value="<?=$rsp['profile']?>" selected><?=$rsp['title']?> (<?=$rsp['profile']?>)</option><?php
                                }
                                else
                                {?>
                                    <option value="<?=$rsp['profile']?>"><?=$rsp['title']?>(<?=$rsp['profile']?>)</option><?php
                                }
                            }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Login: </td>
                    <td class="registrationinput"><input type="text" class="login" id="txtlogin" name="txtlogin" value="<?= $rs['login']; ?>"  onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Birth Day: </td>
                    <td class="registrationinput">&nbsp;<input type="text" class="birthday datepicker" id="txtbirthday" name="txtbirthday" size="20" value="<?= date('m/d/Y', strtotime($rs['birthday'])); ?>" onfocus="getJDate(this);" onchange="updateUser(this);" placeholder="dd/mm/yyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Hire Date: </td>
                    <td class="registrationinput">&nbsp;<input type="text" class="birthday" id="txthiredate" name="txthiredate" size="20" value="<?= date('m/d/Y', strtotime($rs['hiredate'])); ?>" onfocus="getJDate(this);"`onchange="updateUser(this);" placeholder="dd/mm/yyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Email: </td>
                    <td class="registrationinput"><input type="text" class="email required" id="txtemail" name="txtemail" value="<?= $rs['email']; ?>" onchange="validateEmail(this, 'Change');" size="20" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Address: </td>
                    <td class="registrationinput"><input type="text" class="address" id="txtaddress" name="txtaddress" value="<?= $rs['address']; ?>" onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">City: </td>
                    <td class="registrationinput"><input type="text" class="city " id="txtcity" name="txtcity" value="<?= $rs['city']; ?>" onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">State: </td>
                    <td class="registrationinput"><input type="text" class="state" id="txtstate" name="txtstate" value="<?= $rs['state']; ?>" onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Zip-code: </td>
                    <td class="registrationinput"><input type="text" class="zipcode" id="txtzipcode" name="txtzipcode" value="<?= $rs['zipcode']; ?>" onchange="updateUser(this);" /></td>
                </tr>
                <tr>
                <td class="tbl-admin-register-lbl">Is Engineer: </td>
                    <td class="registrationinput"><?php
                    if($rs['isengineer'])
                    {?>
                        <input type="checkbox" class="zipcode" id="chkisengineer" name="chkisengineer" value="Yes" onchange="updateUser(this);" checked /></td><?php
                    }
                    else
                    {?>
                        <input type="checkbox" class="zipcode" id="chkisengineer" name="chkisengineer" value="No" onchange="updateUser(this);" /></td><?php                        
                    }?>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Is Mechanic: </td>
                    <td class="registrationinput"><?php
                    if($rs['ismechanic'])
                    {?>
                        <input type="checkbox" class="zipcode" id="chkismechanic" name="chkismechanic" value="Yes" onchange="updateUser(this);" checked /></td><?php
                    }
                    else
                    {?>
                        <input type="checkbox" class="zipcode" id="chkismechanic" name="chkismechanic" value="No" onchange="updateUser(this);" /></td><?php                        
                    }?>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Is Technician: </td>
                    <td class="registrationinput"><?php
                    if($rs['istechnician'])
                    {?>
                        <input type="checkbox" class="zipcode" id="chkistechnician" name="chkistechnician" value="Yes" onchange="updateUser(this);" checked /></td><?php
                    }
                    else
                    {?>
                        <input type="checkbox" class="zipcode" id="chkistechnician" name="chkistechnician" value="No" onchange="updateUser(this);" /></td><?php                        
                    }?>                    
                </tr>
            </table><?php 
       }
    }
}
function SearchUserexistinglist($thisstr)
{
    //file_put_contents("./dodebug/debug.txt", "sessionlist: ".var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $realname = "";?>   
    <select name="sltsearchuser" id="sltsearchuser" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -310px;"  size="5" onchange="hideSelectsearch();"><?php
        foreach($_SESSION['usersearchlist'] as $rs => $value)
        {
           if(strpos(strtolower($value), strtolower($thisstr)) !== false)
           {?>
                <option value="<?= $rs ?>"><?= $value ?></option><?php 
            }
        }?>
    </select><?php
}
function SearchUser()
{
    global $db;
    if(count($_SESSION['usersearchlist']) > 0)
    {
        //If this session variable has stuffs in it, that means user already started the search and
        //we want to use this array rather than going back to the database every time user typed a char.
        //file_put_contents("./dodebug/debug.txt", 'inside session', FILE_APPEND);
        SearchUserexistinglist($_POST['txtsearchuser']);
        exit();
    }
    //file_put_contents("./dodebug/debug.txt", var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "employee_master";
    $thisfields = array('recno', 'firstname', 'middlename', 'lastname');
    $tempexplodeuser = explode(' ', $_POST['txtsearchuser']);
    //We get here when user entered just the first name, middle or last name.
    $thiswhere = array("firstname LIKE" => strtolower($_POST['txtsearchuser']), "middlename LIKE" => strtolower($_POST['txtsearchuser']), "lastname LIKE" => strtolower($_POST['txtsearchuser']));
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    $realname = "";?>   
    <select name="sltsearchuser" id="sltsearchuser" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -310px;"  size="5" onchange="hideSelectsearch();"><?php
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
               $_SESSION['usersearchlist'][$rs['recno']] = $realname;?>
                <option value="<?= $rs['recno']?>"><?= $realname ?></option><?php 
           }
        }?>
    </select><?php
}
function ManageUsers()
{?>
    <div id="div_search_containter" style="background-color: blue; width: 310px; min-width: 310px; text-align: left; background-color: red;">
        <div class="div-mgm-search-container" id="div_mgm_search">
            <input class="txt-search-user" type="text" id="txtsearchuser" name="txtsearchuser" value="" placeholder="Enter a name to start search." onclick="searchUser();" onkeyup="searchUser(this);" />
            <button type="button" onclick="clearSearch();">Clear</button</div>
        </div>
        
    </div>
<?php
}
function SubmitRegistration()
{
    global $db, $load_headers;
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com

    $thisfields = Array();
    $thistable = "employee_master";
    $sendstatus= "";
    foreach($_POST as $key => $value)
    {
        if($key != "cmd")
        {
            //file_put_contents("./dodebug/debug.txt", "key: ".$key, FILE_APPEND);
            if(substr($key, 3) == "birthday" && !is_null($value))
            {
                $thisfields[substr($key, 3)] =  date('Y-m-d', strtotime($value));
            }
            else if((substr($key, 3) == "isengineer"  || substr($key, 3) == "ismechanic"  || substr($key, 3) == "istechnician"))
            {
                if(isset($_POST[$key]))
                {
                    $thisfields[substr($key, 3)] =  true;
                }
                else
                {
                    $thisfields[substr($key, 3)] =  false;
                }
            }
            else
            {
                $thisfields[substr($key, 3)] =  $value;
            }
        }
    }
    //file_put_contents("./dodebug/debug.txt", var_dump($thisfields), FILE_APPEND);
    
    //We need to add the vericode to add to the row and also add password
    $realpassword = $load_headers -> Hash_Me_Password(); //.We want to get a dummy pw.
    $realvericode = $load_headers ->Hash_Me_Vericode();

    $thisfields['password'] =  $realpassword;
    $thisfields['vericode'] = $realvericode;
    //At this point $thisfields array should now have an associative array of key and value.
    //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
    $result = $db->PDOInsert($thistable, $thisfields);
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

        $sendto[] = array($_POST['txtemail'] => $_POST['txtfirstname']." ".$_POST['txtlastname']);
        //file_put_contents('./dodebug/debug.text', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
        $subject = "Account Creation From ".$_SESSION['companyname'];
        $body = "Please follow the link below to verify your account and change your password.<br><br>";
        $body .= "<a href='$thisserver/verifyme.php?vericode=".$realvericode."'>Click here to verify your email and change your password.</a>";
        $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
        //echo $sendstatus;
    }
    //file_put_contents('./dodebug/debug.text', $result, FILE_APPEND); 
    echo $result;
}
function CheckRegistration()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "employee_master";
    $thisfields[] = $_POST['field'];
    $thiswhere = array($_POST['field'] => $_POST['value']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       echo "EXISTS"; 
    }
}
function DoRegistration()
{
    global $db;?>
    <form name="frmregistration" id="frmregistration">
        <table class="tbl-admin-register">
            <tr>
                <td class="tbl-admin-register-lbl">Employee Number: </td>
                <td class="registrationinput"><input type="text" class="firstname required" id="txtemployeenumber" name="txtemployeenumber" value="" onchange="checkEmployeenumber('Load');" required autofocus /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">First Name: </td>
                <td class="registrationinput"><input type="text" class="firstname required" id="txtfirstname" name="txtfirstname" value="" onchange="loadLogin(this);" required /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Middle Name: </td>
                <td class="registrationinput"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Last Name: </td>
                <td class="registrationinput"><input type="text" class="lastname required" id="txtlastname" name="txtlastname" value="" onchange="loadLogin(this);" /></td>
            </tr>
            <tr>
                    <td class="tbl-admin-register-lbl">Profile: </td>
                    <td class="registrationinput">
                        <select id="sltprofile" name="sltprofile"><?php
                            $thistablep = "profiles";
                            $thisfieldsp = array('All');
                            $resultp = $db->PDOQuery($thistablep, $thisfieldsp);
                            foreach($resultp as $rsp)
                            {
                                if($rsp['profile'] == 'RU')
                                {?>
                                    <option value="<?=$rsp['profile']?>" selected><?=$rsp['title']?>(<?=$rsp['profile']?>)</option><?php
                                }
                                else
                                {?>
                                    <option value="<?=$rsp['profile']?>"><?=$rsp['title']?>(<?=$rsp['profile']?>)</option><?php
                                }
                            }?>
                        </select>
                    </td>
                </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Login: </td>
                <td class="registrationinput"><input type="text" class="login" id="txtlogin" name="txtlogin" value="" onchange="loadLogin(this);"  /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Birth Day: </td>
                <td class="registrationinput">&nbsp;<input type="text" class="birthday" id="txtbirthday" name="txtbirthday" value="" onfocus="getJDate(this);" onchange="updateUser(this);" placeholder="dd/mm/yyyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Email: </td>
                <td class="registrationinput"><input type="text" class="email required useremail" id="txtemail" name="txtemail" value="" onchange="validateThisemail(this, 'Load');" size="16" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Address: </td>
                <td class="registrationinput"><input type="text" class="address" id="txtaddress" name="txtaddress" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">City: </td>
                <td class="registrationinput"><input type="text" class="city " id="txtcity" name="txtcity" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">State: </td>
                <td class="registrationinput"><input type="text" class="state" id="txtstate" name="txtstate" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Zip-code: </td>
                <td class="registrationinput"><input type="text" class="zipcode" id="txtzipcode" name="txtzipcode" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Is Engineer: </td>
                <td class="registrationinput"><input type="checkbox" class="zipcode" id="chkisengineer" name="chkisengineer" value="Yes" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Is Mechanic: </td>
                <td class="registrationinput"><input type="checkbox" class="zipcode" id="chkismechanic" name="chkismechanic" value="Yes" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Is Technician: </td>
                <td class="registrationinput"><input type="checkbox" class="zipcode" id="chkistechnician" name="chkistechnician" value="Yes" /></td>
            </tr>
            <tr class="div-admin-register-lbl" style="width: 100%; text-align: center;">
                <td class="tbl-admin-register-lbl" style="text-align: center;" colspan="2">
                    <button type="submit" form="frmregistration" value="Submit" id="btnfrmregistration" onclick="submitRegistrationform();" disabled="disabled">Submit</button>
                    <button value="Cancel" onclick="clearRegistrationform();">Clear</button>
                </td>
            </tr>
        </table>
    </form><?php
}
function AddCustomer()
{
    global $pt;?>
    <form name="frmaddcustomer" id="frmaddcustomer">
        <table class="tbl-admin-register">
            <tr>
                <td class="tbl-admin-register-lbl">Customer Name: </td>
                <td class="registrationinput"><input type="text" class="firstname required" id="txtcustomer" name="txtcustomer" value="" onchange="validateCustomer(this);" autofocus /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Airline Code: </td>
                <td class="registrationinput"><input type="text" class="login required" id="txtairlinecode" name="txtairlinecode" value="" onchange="validateCustomer(this);"  /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">ICAO Code: </td>
                <td class="registrationinput"><input type="text" class="middlename required" id="txticaocode" name="txticaocode" value="" onchange="validateCustomer(this);" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">IATA Code: </td>
                <td class="registrationinput"><input type="text" class="lastname required" id="txtiatacode" name="txtiatacode" value="" onchange="validateCustomer(this);"  /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Address: </td>
                <td class="registrationinput"><input type="text" class="birthday" id="txtaddress" name="txtaddress" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">City: </td>
                <td class="registrationinput"><input type="text" class="email" id="txtcity" name="txtcity" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">State: </td>
                <td class="registrationinput"><input type="text" class="address" id="txtstate" name="txtstate" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Country: </td>
                <td class="registrationinput"><input type="text" class="city " id="txtcountry" name="txtcountry" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Zip-Code: </td>
                <td class="registrationinput"><input type="text" class="state" id="txtzipcode" name="txtzipcode" value="" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Email: </td>
                <td class="registrationinput"><input type="text" class="zipcode customeremail" id="txtemail" name="txtemail" value="" onchange="validateCustomeremail(this);" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-register-lbl">Service(s): </td>
                <td class="registrationinput">
                    <input type="checkbox" class="zipcode" id="chkiscargo" name="chkiscargo" value="iscargo" />&nbsp;&nbsp;Cargo<br/>
                    <input type="checkbox" class="zipcode" id="chkispassenger" name="chkispassenger" value="ispassenger" />&nbsp;&nbsp;Passenger<br/>
                </td>
            </tr>
            <tr class="div-admin-register-lbl" style="width: 100%; text-align: center;">
                <td class="tbl-admin-register-lbl" style="text-align: center;" colspan="2">
                    <button type="submit" form="frmregistration" value="Submit" id="btnfrmaddcustomer" onclick="submitCustomerform();" disabled="disabled">Submit</button>
                </td>
            </tr>
        </table>
    </form><?php
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
                        <div class="main-div-body-admin-left" style="margin-top: -30px;">
                            <div class="main-div-body-admin-header">Admin</div>
                            <div style="float: left;">
                                <div class="div-menu-admin" id="div_add" onclick="addCustomer(this);">Add customer</div>
                                <div class="div-menu-admin" id="div_manage" onclick="manageCustomer(this);">Manage customer</div>
                                <div class="div-menu-admin" id="div_menu" onclick="manageMenu(this);">Manage Menu</div>
                                <div class="div-menu-admin" id="div_mangemenu" onclick="manageProfiles(this);">Manage Profiles</div>
                                <div class="div-menu-admin" id="div_adduser" onclick="doRegistration(this);">Register a new user</div>
                                <div class="div-menu-admin" id="div_manageuser" onclick="manageUsers(this);">Manage a user</div>
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