<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/pdocon.php");
require("./common/classes/PageloaderClass.php");
require_once("./common/sendmail.php");
require("./common/classes/EmailClass.php");
require("./common/classes/PersonClass.php");
require_once("./common/prompt.php");
$ps = new PersonClass();
$pt = new PROMPT();
$load_headers = new PageloaderClass();
$db = new PDOCON();
$ne = new Email_Class();

$isDebug = true;
//echo var_dump($_POST);
//file_put_contents("./dodebug/debug.txt", 'menuresult: '.var_dump($_POST), FILE_APPEND);
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
               <?php
               if($_SESSION['isAdmin'] == true)
               {?>
                    manageUsers($("#div_manageuser")[0]);<?php
                }
                else
                {?>
                    showEvent($("#div_modifyevent")[0], thisstatus = "Active", thiseventtype = "Event");<?php
                }?>
            });
            function dashboardMenuslt(obj){
                $(".div-menu-dashboard").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
            }
            function manageUsers(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageUsers', function(result){
                    $("#main_div_body_dashboard_right_container").html(result);
                    //searchUser();
                });
            }
            function manageBarbers(obj, thisrecno, thisfrom){
                dashboardMenuslt(obj);
                if(thisfrom == "addAPI"){
                    showUsertbs($("#div_barber_permissionpro")[0], thisrecno, thisfrom);    
                }
                else{
                    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageBarbers', function(result){
                        if(result.indexOf("Barber|") == -1){
                            $("#main_div_body_dashboard_right_container").html(result);
                            //searchUser();
                        }
                        else{
                            //alert(result);
                            tempbarber = result.split('|');
                            getUserdata(obj, tempbarber[1]);
                        }
                    });
                }
            }
            function reloadBarbers(obj){   
                if($("#sltsearchuser").length){
                    $("#sltsearchuser").remove();
                }
                searchUser($("#txtsearchuser")[0], 'Barbers');                   
            }
            function searchUser(obj, thisfrom){   
                let thisArray = [];
                let ischeck = false;
                //from will be Users, Services, Schedules
                if($("#txtsearchuser").val().length >= 3){        
                    tempactive = false;
                    tempinactive = false;
                    tempterminated = false;
                    if(thisfrom === "Barbers" || thisfrom === "Schedule"){
                        if($("#chk_active").is(":checked")){
                            tempactive = true;
                            ischeck = true;
                        }
                        if($("#chk_inactive").is(":checked")){
                            tempinactive = true;
                            ischeck = true;
                        }
                        if($("#chk_terminated").is(":checked")){
                            tempterminated = true;
                            ischeck = true;
                        }
                        if(ischeck === false){
                            alert("At least 1 checkbox must be checked.");
                            $("#txtsearchuser").val("");
                            $("#txtsearchuser").focus();
                            return(false);
                        }
                        thisArray = [{
                            "this_txtsearchuser": $("#txtsearchuser").val(),
                            "this_thisfrom": thisfrom,
                            "this_active": tempactive,
                            "this_inactive": tempinactive,
                            "this_terminated": tempterminated
                        }];
                    }
                    else if(thisfrom === "Users"){
                        thisArray = [{
                            "this_txtsearchuser": $("#txtsearchuser").val(),
                            "this_thisfrom": thisfrom
                        }];
                    }
                    if(thisfrom === "Barbers" || thisfrom === "Schedule"){
                        if(tempactive === true && tempinactive === true && tempterminated === true){
                            alert('Please make sure at least one of the check boxes is checked.');
                            return(false);
                        }
                    }
                    const thisData = JSON.stringify(thisArray);
                    fetchAjaxdatasearchuser(thisData);
                    async function fetchAjaxdatasearchuser(thisData){
                        try{
                            const result = await $.ajax({
                            url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=SearchUser&thisarray='+thisData,
                            type: 'POST',
                            contentType: "application/json"
                            });
                                if(result !== ""){
                                    if($("#sltsearchuser").length){
                                        $("#sltsearchuser").remove();
                                    }
                                    $("#tbluserdata").remove();
                                    $("#div_mgm_search").after(result);
                                }
                        }
                        catch(error){
                            alert("ERROR in paynow");
                            alert(result);
                        }
                    }
                }
                else{
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();
                    }
                }
            }
            function getUserdata(obj, recno){
                //recno - this is the recno for the recno column in the users table
                //alert(recno);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetUserdata&recno='+recno, function(result){
                    //alert(result);
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    //$("#div_mgm_search").html(result);
                    $("#main_div_body_dashboard_right_container").html(result); 
                }); 
            }
            function clearSearchuser(){
                $("#sltsearchuser").remove();
                $("#tbluserdata").remove();
                $("#txtsearchuser").val('');
                $("#txtsearchuser").focus();
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
            function updateUser(obj, recno){   
                //alert($(obj).prop('id'));
                if($(obj).prop('id') == "chkisTerminated" || $(obj).prop('id') == "chkisActive" || $(obj).prop('id') == "chkisAdmin"
                        || $(obj).prop('id') == "chkisShowfb" || $(obj).prop('id') == "chkisShowcancel" || $(obj).prop('id') == "chkisShowrefund"
                        || $(obj).prop('id') == "chkisBarber" || $(obj).prop('id') == "chkisLive"){
                    if($(obj).prop('id') == "chkisTerminated"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                            $("#chkisActive").prop('checked', false);
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisActive"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisAdmin"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisShowfb"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisShowcancel"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisShowrefund"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisBarber"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisLive"){
                        if($(obj).is(":checked")){
                            if(confirm("By Clicking 'OK', you will be ONLINE and LIVE.  Everything is about to get REAL!!!")){
                                realval = 'true';
                                $("#td_live").addClass("flashing-background");
                            }
                            else{
                                $(obj).prop('checked', false);
                                return(false);
                            }
                        }
                        else{
                            if(confirm("By Clicking 'OK', you will be OFFLINE and Working with a Sandbox.  You won't be able to take payments!!!")){
                                realval = 'false';
                                $("#td_live").removeClass("flashing-background");
                            }
                            else{
                                $(obj).prop('checked', true);
                                return(false);
                            }
                        }
                    }
                }
                else{
                    realval = $(obj).val();
                }
                //alert('thisfield='+$(obj).prop('id').slice(3)+' && thisvalue='+realval);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateUser&thisrecno='+recno+'&thisfield='+$(obj).prop('id').slice(3)+'&thisvalue='+realval, function(result){
                    //alert(result);
                    if(result == "Success"){
                        alert('Updated');
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
                if($(obj).prop('id') != "chkterminate" && $(obj).prop('id') != "chkactive" && $(obj).prop('id') != "chkdeleted"){
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
            function reloadUser(from){
                searchUser('Users');
                
            }
            function reloadSchedule(){
                if($("#sltsearchuser").length){
                    $("#sltsearchuser").remove();
                }
                searchUser($("#txtsearchuser")[0], 'Schedule'); 
            }
            function showHistory(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ShowHistory&status=Active', function(result){
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function showTrash(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ShowTrash', function(result){
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function manageServices(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageServices', function(result){
                    $("#main_div_body_dashboard_right_container").html(result);
                    searchServices();
                }); 
            }
            function searchServices(){    
                rdoservice = "";
                if($("#rdoserviceactive").is(":checked")){
                    rdoservice = $("#rdoserviceactive").val();
                }
                if($("#rdoserviceinactive").is(":checked")){
                    rdoservice = $("#rdoserviceinactive").val();
                }
                if($("#rdoservicedeleted").is(":checked")){
                    rdoservice = $("#rdoservicedeleted").val();
                }
                //alert(rdoservice);
                thisArray = [{"this_rdoservice": rdoservice}];
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchServices&thisarray='+JSON.stringify(thisArray), function(result){
                    if($("#tbl_dashboard_service").length > 0){
                        $("#tbl_dashboard_service").remove();
                    }
                    $("#div_mgm_search").after(result);
                    $("#tbl_dashboard_service").dataTable({
                        paging: false,
                        scrollCollapse: true,
                        lengthChange: false,
                        searching: false,
                        sDom: "t"
                    });
                }); 
            }
            function reloadService(){
                searchServices();
            }
            function getService(obj, recno){
                thisArray = [{"this_recno": recno}];
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetService&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").html(result);
                       
                }); 
            }
            function updateService(obj, thisrecno, thisfield){                
                realval = "";
                if($(obj).prop('id') == "chkactive" || $(obj).prop('id') == "chkinactive" || $(obj).prop('id') == "chkdeleted"){
                    $(".service-check").each(function(){
                        if($(this).is(":checked"))
                        {
                            //We want to uncheck all the check boxes
                            $(this).prop('checked', false);
                        }                      
                    });  
                    $(obj).prop('checked', true);
                    realval = $(obj).val();
                }
                else{
                    realval = $(obj).val();
                }
                thisArray = [{"this_thisrecno": thisrecno, "this_thisfield": thisfield, "this_thisval": realval}];
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateService&thisarray='+JSON.stringify(thisArray), function(result){
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
 
                let thisArray = [{'this_title': $("#txtarea_service").val(),
                            'this_time': $("#txttime").val(),
                            'this_description': $("#textarea_comment").val(),
                            'this_price': $("#txtprice").val(),
                            'this_isactive': true}];

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
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=AddCompany', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#main_div_body_dashboard_right_container").html(result);
                }); 
            }
            function updateCompanyinfo(obj, thisrecno){
                //need to work on the naming convention
                thisArray = [{
			"this_thisrecno": thisrecno, 
                        "this_thisfield": $(obj).prop('id'),
			"this_thisval": $(obj).val()
                }];
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateCompanyinfo&thisarray='+JSON.stringify(thisArray), function(result){
                    alert(result);
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
            function doEvent(obj){
                dashboardMenuslt(obj);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DoEvent', function(result){
                    //alert(result);
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function addAttachment(){
                //We have to go through the list of attachments to see how many we already have and then add it after it.
                isAttachment = true;
                if($(".event-attachments").length >= 5){
                    isAttachment = false;
                    alert("The limit for upload is 5.");
                }
                if(isAttachment == true){
                    $(".event-attachments").each(function(){
                        thisattachmentcount = $(this).prop('id').slice(-1);
                    });
                    thisattachmentcount++;
                    newattachment = "attachment"+thisattachmentcount;
                    newbtn_remove_attachment = "btn_remove_attachment"+thisattachmentcount;
                    newdiv_attachment_ele = "div_attachment_ele"+thisattachmentcount;
                    
                    //If we made it to here, we know we have the last number of the last attachment so we just increase by 1
                    thisstr = '<div class="div-attachment-ele" id="'+newdiv_attachment_ele+'"><span class="span-event-numbered">'+thisattachmentcount+'</span>&nbsp;<input class="event-attachments" type="file" name="files[]" id="'+newattachment+'" />';
                    thisstr += '&nbsp;<button class="remove-attachment" id="'+newbtn_remove_attachment+'" name="'+newbtn_remove_attachment+'" onclick="removeAttachment(this);" title="Click to remove attachment">-</button></div';
                    $('#div_attachment_container').append(thisstr);
                    //since we added an attachment, we want to enable the first attachment's deletion button
                    $("#btn_remove_attachment1").show();
                }
                event.preventDefault();
            }
            function submitEvent(){
                isFalse = false;
                isAttachment = false;
                if($("#this_event_type").val() == "Select"){
                    alert("Please select an event type.");
                    isFalse = true;
                }
                if($("#this_special_event").val() == ""){
                    alert("Event can't be emptied.  Please type in an event name.");
                    $("#txt_event").focus();
                    isFalse = true;
                }
                if($("#this_event_restriction").val() == "Lmited"){
                    //We only check the dates if there is a date restriction on this event where we will have 2 dates to check and work with.
                    if($("#this_date").val() == ""){
                        alert("Event start date can't be emptied.  Please type in an start event date.");
                        $("#this_date").focus();
                        isFalse = true;
                    }
                    if($("#this_expire_date").val() == ""){
                        alert("Event expire date can't be emptied.  Please type in an expire event date.");
                        $("#this_expire_date").focus();
                        isFalse = true;
                    }
                }
                if($("#chkdiscount").is(":checked")){
                    //If the Discount box is checked, we have to check the other variables to make sure they are appropriate
                    if($("#sltisCombo").val() == "Select"){
                        alert("Please select 'Yes' or 'No' for Stackable.");
                        isFalse = true;
                    }
                    if($("#txtdiscount").val() == ""){
                        alert("Please enter a value for Value.");
                        isFalse = true;
                    }
                    if($("#sltisAuto").val() == "Select"){
                        alert("Please select 'Yes' or 'No' for Auto Apply.");
                        isFalse = true;
                    }
                }                
                if($("#txtdescription").val() == ""){
                    alert("Description can't be emptied.");
                    isFalse = true;
                }              
                if(isFalse == false){
                    form_data = new FormData($('#frmcreateevent')[0]);
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitEvent',
                        data: form_data,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            alert(result);
                            if(result != "Success"){
                                alert(result);
                                preventDefault();
                                return(false);
                            }
                            else{
                                alert("Event added successfully.");
                                doEvent($("#div_customer")[0]);
                            }
                            event.preventDefault();
                        }
                    });
                }
                event.preventDefault();
            }
            function removeAttachment(obj, thisrecno=0, lineno = 0){
                //alert($(obj).prop('id'));
                thisattachmentcount = $(obj).prop('id').slice(-1);
                $("#div_attachment_ele"+thisattachmentcount).remove();

                //Now we want to renumbered the number span to make it look nice.
                //span-event-numbered
                i = 1;
                $(".span-event-numbered").each(function(){
                    //We should only allow up to 9 attachments at the most so we can assume getting the last character of the id is sufficient.
                    $(this).text(i);
                    i++;
                });
                if(thisrecno != 0){
                    //We are here if we are doing modify and wanted to remove an attachment.
                    //Now we have to go to the database and remove the file.
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=RemoveAttachment&thisrecno='+thisrecno+'&thisattachment='+$("body").data("modattach"+lineno), function(result){
                        //alert(result);
                        if(result != "Success"){
                            alert("Update failed.")
                        }
                        else{
                            alert("Updated!");
                        }
                    });
                }
                event.preventDefault();
            }
            function showEvent(obj, thisstatus = "Active", thiseventtype = "Event"){
                dashboardMenuslt(obj);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowEvent&status='+thisstatus+'&thiseventtype='+thiseventtype, function(result){
                    //alert(result);
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function modifyEvent(obj, thisrecno){
                dashboardMenuslt($("#div_modifyevent")[0]);
                //thisrecno is the recno in event table
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ModifyEvent&thisrecno='+thisrecno, function(result){
                    //alert(result);
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function changeEvent(obj, thisrecno){
                //thisrecno is the recno in event table
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ChangeEvent&thisrecno='+thisrecno+'&'+$(obj).prop('id')+'='+$(obj).val(), function(result){
                    //alert(result);
                    if(result != "Success"){
                        alert("Update failed.")
                    }
                    else{
                        alert("Updated!");
                    }
                });
            }
            function modAttachment(thisrecno){
                //thisrecno is for table events
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ModAttachment&thisrecno='+thisrecno, function(result){
                    //alert(result);
                    $("#div_body_dashbord_createevent_container").append(result);
                });
            }
            function submitModattachment(thisrecno){
                if($("#modattachment").val() != ""){
                    form_data = new FormData($('#frmsubmitattachment')[0]);
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitModattachment&thisrecno='+thisrecno,
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
                                alert("Attachment added successfully.");
                                $("#frmsubmitattachment").remove();
                                //Once we removed the add attachment div, we want to reload this event
                                modifyEvent($("#btnmodify")[0], thisrecno);
                            }

                        }
                    });
                }
                event.preventDefault();
            }
            function updateEventstatus(obj, thisrecno, thisstatus){
                //thisrecno is recno for events table
                //thisstatus is Active, Inactive, Deleted
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateEventstatus&thisrecno='+thisrecno+'&thisstatus='+thisstatus, function(result){
                    if(result != "Success"){
                        alert("Failed to delete.");
                    }
                    else{
                        //We will need to reload the events
                        showEvent($("#div_modifyevent")[0], thisstatus);
                    }
                });
            }
            function getEvenoptions(obj, status, thiseventtype = "Event"){
                //By default we come in as Active on status
                //Status will be Active, Inactive, Deleted
                //use this show-event-option-status to do hightlights
                
                $(".show-event-option-status").each(function(){
                    if($(this).prop('id') == $(obj).prop('id')){
                        //We want to highlight this one
                        $(this).css('background-color', '#173346');
                        $(this).css('color', 'white');
                    }
                    else{
                        $(this).css('background-color', 'white');
                        $(this).css('color', 'black');
                    }
                });
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowEvent&status='+status+'&thiseventtype='+thiseventtype, function(result){
                    //alert(result);
                    $("#main_div_body_dashboard_right_container").html(result);

                });
                
            }
            function getEvenoptionstype(obj, thiseventtype){
                //Now we need to get the status Active, Inactive, Deleted
                $(".show-event-option-status").each(function(){
                    //alert($(this).css("background-color")+ " and "+ $(this).text());
                    if($(this).css("background-color") == "rgb(23, 51, 70)"){
                        status = $(this).text();
                    }
                });
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowEvent&status='+status+'&thiseventtype='+thiseventtype, function(result){
                    //alert(result);
                    $("#main_div_body_dashboard_right_container").html(result);

                });
            }
            function getHistorystatus(obj, status){
                historynMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ShowHistory&status='+status, function(result){
                    $("#main_div_body_dashboard_right_container").html(result);
                    
                    //want it to be datatable but somehow, doesn't work.
                    /*
                    $('#tblhistorydata').DataTable({
                        sortable: true
                    });*/
                });
            }
            function historynMenuslt(obj){
                $(".show-event-option-status").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
            }
            function dashPay(obj, thisrecno){
                //thisrecno is the recno for table schedule_dates
                //alert(thisrecno);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DashPay&thisrecno='+thisrecno, function(result){    
                    //alert(result)
                    window.location.href = './paynow.php';
                    //$("#main_div_body_history_dashpay_container_holder").show();
                    //$("#main_div_body_history_dashpay_container_holder").append(result);
                    //$("#div_main_sub").append(result);
                });
            }
            function doSearch(){
                //alert('doing search');
                if($("#txt_payment_id").val() == "" && $("#txt_login").val() == "" && $("#txt_guest").val() == "" && $("#txt_date").val() == "" && $("#txt_fromdate").val() == "" && $("#txt_todate").val() == ""){
                    alert("At least one of the fields must not be emptied.");
                    return(false);
                }
                else{
                    //alert($("body").data('searchguest'));
                    let thisArray = [{
                            "thispayment_id": $("#txt_payment_id").val(), 
                            "thislogin": $("#sltbarber option:selected").val(), 
                            "thisguest": $("body").data('searchguest'),
                            "thisdate": $("#txt_date").val(),
                            "thisfromdate": $("#txt_fromdate").val(),
                            "thistodate": $("#txt_todate").val()
                        }];        
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowHistory&status=Searching&thisarray='+JSON.stringify(thisArray), function(result){    
                        //alert(result);
                        $("#main_div_body_dashboard_right_container").html(result);
                        //selectSlot($("#div_"+thisdata['n']+"_v"+thisdata['slot'])[0], thisdata['uf_recno'], thisdata['date'], thisdata['view'], thisdata['slot'], thisdata['n'], "");                    
                    });
                }
            }
            function searchGuest(obj){   
                //alert($(obj).val());
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchGuest&thisguest='+$(obj).val(), function(result){
                    if($("#sltsearchguest").length){
                        $("#sltsearchguest").remove();
                    }
                    $("#div_history_search_guest").append(result);
                    
                    return(false);
                }); 
            }
            function selectGuest(obj){
                //thisid = $(obj).prop('id');
                //alert($("#sltsearchguest option:selected").text()+' and '+$("#sltsearchguest option:selected").val());
                $("body").data('searchguest', $("#sltsearchguest option:selected").val());
                $("#txt_guest").val($("#sltsearchguest option:selected").text());
                $("#sltsearchguest").remove();
                return(false);
            }
            function about(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=About', function(result){
                    //alert(result);
                    if($("#div_float_about").length){
                        $("#div_float_about").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function saveAbout(thisrecno){
                thisArray = [{
                        "this_recno": thisrecno, 
                        "this_about": $("#txtarea_about").val()
                }];
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
            function introduction(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=Introduction', function(result){
                    //alert(result);
                    if($("#div_float_intro").length){
                        $("#div_float_intro").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#main_div_body_dashboard_right_container").html(result);
                });
            }
            function saveIntro(thisrecno){
                thisArray = [{"this_recno": thisrecno, "this_introduction": $("#txtarea_intro").val()}];
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
            function hideSelectsearch(){
                //$("#sltsearchuser").remove(); 
            }
            function manageSchedule(obj){
                dashboardMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageSchedule', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();
                    }
                    $("#main_div_body_dashboard_right_container").html(result);
                }); 
            }
            function getUserschedule(obj, recno){
                if(recno == ""){
                    recno = $(obj).val();
                }
                //alert(recno);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetUserchedule&thisrecno='+recno, function(result){
                    //alert(result);
                    $("#main_div_body_dashboard_right_container").html(result); 
                }); 
            }
            function modSchedulestatus(obj, thisrecno){
                thisday = $(obj).text();
                status = "";
                tobestatus = "";
                if($(obj).hasClass('img-dashboard-mgm-schedule-header-bgdarkgreen')){
                    status = "ON";
                    tobestatus = "OFF";
                }
                else if($(obj).hasClass('img-dashboard-mgm-schedule-header-bgdarkred')){
                    status = "OFF";
                    tobestatus = "ON";
                }
                //alert(status+' and '+tobestatus);
                //We want to confirm if user wants to update an OFF day.
                if(confirm("Are you sure you want turn "+thisday+" "+tobestatus+"?")){
                    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ModSchedulestatus&thisrecno='+thisrecno+'&thisstatus='+tobestatus+'&thisday='+thisday, function(result){
                        //alert(result);
                        if(result != "Success"){
                            alert(result);
                        }
                        else{
                            getUserschedule($("#sltsearchuser")[0], thisrecno);
                        }
                    }); 
                }
            }
            function hideUsersearchslt(){
                $("#div_search_containter_slt").remove();
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
                        getUserschedule($("#sltsearchuser")[0], thisrecno);
                    }
                }); 
                
            }
            function checkDiscount(obj){
                if($(obj).is(":checked")){
                    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=CheckDiscount', function(result){
                        //alert(result);
                        $("#tr_discount").after(result);
                    }); 
                }
                else{
                    $(".tr-discountdata").remove();
                }
                
            }
            function checkDates(obj){
                if($(obj).val() == "Limited" || $(obj).val() == "Repeats"){                    
                    if(!$("#this_date").length){
                        $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=CheckDates', function(result){
                            //alert(result);
                            $("#tr_slttype").after(result);
                        }); 
                    }
                }
                else{
                    //We just want to remove the dates since we are only doing continuous
                    $(".eventdate").remove();
                }
              
            }
            function showUsertbs(obj, thisrecno, thisfrom){
                if(thisfrom == "addAPI"){
                    showPermissiontbs($("#div_barber_addpermission")[0], thisrecno);
                }
                else{
                    //First we check where the user is coming from 
                    //User - div_barber_user, table name dashboard_mgm_barbers_tbl_user
                    //API Pro - div_barber_apipro
                    //API Sand - div_barber_apisand

                    //first we check if the incoming click's background is blue, if it is, we dont' need to do anything
                    //alert($(obj).css('background-color'));  //rgb(16, 121, 177) is #173346; (light blue, background of the Dashboard menu on left)
                    //We are checking to see which tab is clicked
                    //alert($(obj).css('background-color'));
                    if($(obj).css('background-color') != "rgb(16, 121, 177)"){   
                        $(".dashboard-mgm-barbers-tabs").each(function(){
                            if($(this).prop('id') == $(obj).prop('id')){
                                $(this).removeClass('dashboard-mgm-barbers-tabs-noselect');
                                $(this).addClass('dashboard-mgm-barbers-tabs-selected');
                            }
                            else{
                                $(this).removeClass('dashboard-mgm-barbers-tabs-selected');
                                $(this).addClass('dashboard-mgm-barbers-tabs-noselect');
                            }
                        });
                        //Now that we are here, we want to check if the table the user is trying to view already exist
                        //The 3 tables has tbl-dashbard-mgm-user as the same class
                        //dashboard_mgm_barbers_tbl_user
                        //dashboard_mgm_barbers_tbl_pro
                        //dashboard_mgm_barbers_tbl_sandbox
                        thistab = "";
                        thistable = ""; 
                        if($(this).prop('id') != "div_barber_user"){
                            $("#div_barbers_img_container").hide();
                        }
                        else{
                            $("#div_barbers_img_container").show();
                        }
                        $(".dashboard-mgm-barbers-tabs").each(function(){
                            if($(obj).prop('id') == $(this).prop('id')){
                                if($(this).prop('id') == "div_barber_user"){
                                    thistable = "dashboard_mgm_barbers_tbl_user";
                                }
                                if($(this).prop('id') == "div_barber_apipro"){
                                    thistable = "dashboard_mgm_barbers_tbl_pro";
                                }
                                if($(this).prop('id') == "div_barber_apisand"){
                                    thistable = "dashboard_mgm_barbers_tbl_sandbox";
                                }
                                if($(this).prop('id') == "div_barber_permissionpro"){
                                    thistable = "dashboard_mgm_barbers_tbl_permissionpro";
                                }
                                if($(this).prop('id') == "div_barber_permission"){
                                    thistable = "dashboard_mgm_barbers_tbl_permissionpro";
                                }
                                if($(this).prop('id') == "div_barber_apitwillio"){
                                    thistable = "dashboard_mgm_barbers_tbl_twillio";
                                }
                            }
                        });
                        if($("#"+thistable).length){
                            $(".tbl-dashbard-mgm-user").hide();
                            $("#"+thistable).show();
                        }
                        else{
                            //Since we are going to build a new table, we will hide all the exiting tables, could be 1 or 2.
                            $(".tbl-dashbard-mgm-user").hide();
                            let thisArray = [{
                                    "this_recno": thisrecno,
                                    "this_from": $(obj).prop('id'),
                                    "this_barbertable": thistable
                                }];  
                            const thisData = JSON.stringify(thisArray);
                            fetchAjaxdatadashbarber(thisData);
                        }
                        async function fetchAjaxdatadashbarber(thisData){
                            try{
                                const result = await $.ajax({
                                url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=ShowUsertbs&thisarray='+thisData,
                                type: 'POST',
                                contentType: "application/json"
                                });
                                //alert(result);
                                //AFter we came back with a table, we append to the div.
                                $("#div_dashboard_mgm_barbers_tbl_user").append(result);

                            }
                            catch(error){
                                alert("ERROR");
                            } 
                        }
                    }
                    else{
                        return(false);
                    }
                }
            } 
            function getSquarecode(){
            //thisfrom =  authorization_code or refresh_token
                window.open('./codefetch.php', '_self'); 
            }
            function refreshToken(thissandpro){
                //window.open('./refreshtoken.php', '_self');
                $("#div_loader").removeClass("display-none");
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=RefreshToken",
                    dataType: 'json',
                    type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful
                    if(result != "Failed"){
                        alert("Successfully updated the Access Token, click the eye to see it.");
                        $("#div_loader").addClass("display-none");
                    }
                    else{
                        alert("Failed to rewnew access token: "+result);
                    }
                }).catch(function(error) {
                    alert(error);
                });
            }
            function showHidden(obj, thisid, thisfield, thisfrom){
            //alert(thisfrom);
            //thisid is the id which it will show
            //thisfield is the field in table that we will need to go get the code or the val to be display
            if($(obj).hasClass('codefetch-seeing-eye')){
                $(obj).removeClass('codefetch-seeing-eye');
                $(obj).addClass('codefetch-seeing-eye-not');
                
                let thisArray = [{
                    "this_field": thisfield,
                    "this_from": thisfrom
                }];
                const thisData = JSON.stringify(thisArray);
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=ShowHidden&thisarray="+thisData,
                    type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful - sandbox-sq0idb--T0TYR72gfayXs3qWKPynA
                    //alert(result);
                    //We need to change the eye depending on what it is right now
                    $("#"+thisid).val(result);
                }).catch(function(error) {
                    alert(error);
                });
            }
            else{
                $(obj).removeClass('codefetch-seeing-eye-not');
                $(obj).addClass('codefetch-seeing-eye');
                $("#"+thisid).val("*********************************************************************");
            }
        }
        function revokeToken(temptablename){
            //window.open('./refreshtoken.php', '_self');
            $("#div_loader").removeClass("display-none");
            $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=RevokeToken",
                dataType: 'json',
                type: "POST"
            }).then(function(result) {
                // Code here will execute *after* the AJAX request is successful
                if(result != "Failed"){
                    alert("Successfully revoked Access Token.");
                    $("#div_loader").addClass("display-none");
                    //After revoked, we want to clear the value so the user knows there is no longer a valid access token
                    //so they will need to go through the proces of getting a new code to get a new access code.
                    $("#txtsquare_api_access_token"+temptablename).val("");
                    $("#txtsquare_access_token_expire_date"+temptablename).val("");
                    $("#div_mgm_barbers_pro_api_token").addClass("display-none");
                }
                else{
                    alert("Failed to rewnew access token: "+result);
                }
            }).catch(function(error) {
                alert(error);
            });
        } 
        function showPermissiontbs(obj, thisrecno){
            //thisrecno is the barber or the admin who searched for this barber and it's the barber's recno.

            //first we check if the incoming click's background is blue, if it is, we dont' need to do anything
            //alert($(obj).css('background-color'));  //rgb(16, 121, 177) is #173346; (light blue, background of the Dashboard menu on left)
            //We are checking to see which tab is clicked
            //alert($(obj).css('background-color'));
            if($(obj).css('background-color') != "rgb(16, 121, 177)"){   
                $(".dashboard-mgm-permission-tabs").each(function(){
                    if($(this).prop('id') == $(obj).prop('id')){
                        $(this).removeClass('dashboard-mgm-permission-tabs-noselect');
                        $(this).addClass('dashboard-mgm-permission-tabs-selected');
                    }
                    else{
                        $(this).removeClass('dashboard-mgm-permission-tabs-selected');
                        $(this).addClass('dashboard-mgm-permission-tabs-noselect');
                    }
                });
            }
            let thisArray = [{
                    "this_recno": thisrecno,
                    "this_from": $(obj).prop('id')
                }];  
            const thisData = JSON.stringify(thisArray);
            $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=ShowPermissiontbs&thisarray="+thisData,
                type: "POST"
            }).then(function(result) {
                // Code here will execute *after* the AJAX request is successful
                //alert(result);
                $("#div_dashboard_mgm_barbers_permissions").html(result);
            }).catch(function(error) {
                alert(error);
            });
        }
        function addAPI(thisrecno){
            if($("#htxt_api_names").val() == ""){
                alert("Please enter a new API name and try again.");
                $("#htxt_api_names").focus();
                return(false);
            }
            thisArray = [{
                "this_recno": thisrecno, 
                "this_val": $("#htxt_api_names").val()
            }];
            const thisData = JSON.stringify(thisArray);
            $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=AddAPI&thisarray="+thisData,
                type: "POST"
            }).then(function(result) {
                // Code here will execute *after* the AJAX request is successful
                alert(result);
                $("#htxt_api_names").val("");
                manageBarbers($("#div_managebarbers")[0], thisrecno, 'addAPI');
                //showUsertbs($("#div_barber_permissionpro")[0], thisrecno);
                //showPermissiontbs($("#div_barber_addpermission")[0], thisrecno);
            }).catch(function(error) {
                alert(error);
            });
        }
        function selectAvailable(thisrecno){
            //thisrecno is the recno of the table square_permission
            //alert($("#div_availlist_no_"+thisrecno).css("background-color"));
            if($("#div_availlist_no_"+thisrecno).css("background-color") != "rgb(114, 144, 161)"){
                $("#div_availlist_no_"+thisrecno).addClass("div-avail-list-selected");
                $("#div_availlist_data_"+thisrecno).addClass("div-avail-list-selected");
            }
            else{
                $("#div_availlist_no_"+thisrecno).removeClass("div-avail-list-selected");
                $("#div_availlist_data_"+thisrecno).removeClass("div-avail-list-selected");
            }
            var isSelected = false;
            //We want to highlight the directional arrow if we have a selected item.
            $(".div-avail-list-data").each(function(){
                if($(this).css('background-color') == "rgb(114, 144, 161)"){
                    isSelected = true;
                    return(false);
                }
            });
            if(isSelected == true){
                $("#div_dashboard_barbers_left").addClass("div-avail-list-selected");
            }
            else{
                $("#div_dashboard_barbers_left").removeClass("div-avail-list-selected");
            }
        }
        function selectApproved(thisrecno){
            //thisrecno is the recno of the table square_permission
            if($("#div_approvedlist_no_"+thisrecno).css("background-color") != "rgb(114, 144, 161)"){
                $("#div_approvedlist_no_"+thisrecno).addClass("div-avail-list-selected");
                $("#div_approvedlist_data_"+thisrecno).addClass("div-avail-list-selected");
            }
            else{
                $("#div_approvedlist_no_"+thisrecno).removeClass("div-avail-list-selected");
                $("#div_approvedlist_data_"+thisrecno).removeClass("div-avail-list-selected");
            }
            var isSelected = false;
            //We want to highlight the directional arrow if we have a selected item.
            $(".div-approved-list-data").each(function(){
                if($(this).css('background-color') == "rgb(114, 144, 161)"){
                    isSelected = true;
                    return(false);
                }
            });
            if(isSelected == true){
                $("#div_dashboard_barbers_rightarrow").addClass("div-approved-list-selected");
            }
            else{
                $("#div_dashboard_barbers_rightarrow").removeClass("div-approved-list-selected");
            }
        }
        function moveAPI(obj, thisrecno, thisfrom){
            //Now we have to walk through the list and see which ones the user want to move to the approve list
            var tempArray = [];
            if(thisfrom == "Left"){
                $(".div-avail-list-data").each(function(){
                    if($(this).css('background-color') == "rgb(114, 144, 161)"){
                        //div_availlist_data_apirecno
                        splitdata = $(this).prop('id').split("_");
                        apirecno = splitdata[3];
                        tempArray.push(apirecno);
                    }
                });
            }
            else{
                $(".div-approved-list-data").each(function(){
                    if($(this).css('background-color') == "rgb(114, 144, 161)"){
                        //div_availlist_data_apirecno
                        splitdata = $(this).prop('id').split("_");
                        apirecno = splitdata[3];
                        tempArray.push(apirecno);
                    }
                });
            }
            thisArray = [{
                "this_recno": thisrecno, 
                "this_from": thisfrom,
                "this_apiarray": tempArray
            }];
            const thisData = JSON.stringify(thisArray);
            $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=MoveAPI&thisarray="+thisData,
                type: "POST"
            }).then(function(result) {
                // Code here will execute *after* the AJAX request is successful
                alert(result);
                manageBarbers($("#div_managebarbers")[0], thisrecno, 'addAPI');
                //showUsertbs($("#div_barber_permissionpro")[0], thisrecno);
                //showPermissiontbs($("#div_barber_addpermission")[0], thisrecno);
            }).catch(function(error) {
                alert(error);
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
function MoveAPI()
{
    global $db, $pt;
    $temptablename = "";
    $newdata = [];
    $realarray = [];
    //Before anything we want to check to see if this user already has a record
    if($_SESSION['isLive'] == true)
    {
        $temptablename = "_pro";
    }
    $returnpost = $pt->AnalyzePosts();
    $newdata = $returnpost['apiarray'];
    $thistable = "square_approved_permissions";
    
    $sql = "SELECT * FROM $thistable WHERE foreign_ur_recno = ".$returnpost['recno'];
    $result = $db->PDOMiniquery($sql);
    if($db->PDORowcount($result) == 0)
    {
    
        $thisdata = ['foreign_ur_recno' => $returnpost['recno'], "approved_permissions$temptablename" => json_encode($newdata)];
        $thisinsert = $db->PDOInsert($thistable, $thisdata);
        if($thisinsert != "Failed Insert")
        {
            echo "Moved Successfully";
        }
        else
        {
            echo "Failed to Move.";
        }
    }
    else
    {
        $tempapprovelist = [];
        $temprecno = "";
        //If we are here, we are doing update to the approved list
        foreach($result as $rs)
        {
            $temprecno = $rs['recno'];
            $tempapprovelist = json_decode($rs["approved_permissions$temptablename"]);
        }
        //file_put_contents("./dodebug/debug.txt", "tempapprovelist? ".json_encode($tempapprovelist)."\n", FILE_APPEND);
        //file_put_contents("./dodebug/debug.txt", "newdata? ".json_encode($newdata)."\n", FILE_APPEND);
        if($returnpost['from'] == "Right")
        {
            //Move items from left to right.
            $realarray = array_diff($tempapprovelist, $newdata);
        }
        else
        {
            //Move from right to left
            $realarray = array_merge($tempapprovelist, $newdata);
        }
        //file_put_contents("./dodebug/debug.txt", "realarray? ".json_encode(array_values($realarray))."\n", FILE_APPEND);
        $thisdata = ["approved_permissions$temptablename" => json_encode(array_values($realarray))];
        $thiswhere = ['recno' => $temprecno];
        $thisreturnarray = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
        if($thisreturnarray == "Success")
        {
            echo "Approved List Updated!";
        }
        else
        {
            echo "Failed to update Approved List.";
        }
    }
}
function AddAPI()
{
    global $db, $pt;
    $temptablename = "";
    if($_SESSION['isLive'] == true)
    {
        $temptablename = "_pro";
    }
    $returnpost = $pt->AnalyzePosts();
    $thistable = "square_permissions";
    //We want to check if this new api already exist in the database
    $sql = "SELECT * FROM $thistable WHERE api_names$temptablename ='".$returnpost['val']."' AND isActive = true";
    //file_put_contents("./dodebug/debug.txt", "AddAPI? $sql\n", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    if($db->PDORowcount($result) == 0)
    {
        $thisdata = ["api_names$temptablename" => $returnpost['val']];
        $thisreturnarray = $db->PDOInsert($thistable, $thisdata);
        if($thisreturnarray != "Failed Insert")
        {
            echo "Added Successfully";
        }
        else
        {
            echo "Failed to insert.";
        }
    }
    else
    {
        echo "This API already exist.";
    }
}
function ShowPermissiontbs()
{
    global $pt;
    $temptablename = "";
    $returnpost = $pt->AnalyzePosts();
    GetSquarepermission($returnpost['recno'], $returnpost['from']);
}
function GetSquarepermission($thisrecno, $from)
{
    global $db;
    //$from = div_barber_permission, div_barber_editpermission, div_barber_addpermission, by default, we come in as div_barber_permission, but
    //as the user start exploring other tabs, we will come as in them.
    $availableArray = [];
    $tempavailableArray = [];
    $approvedapi = [];
    $usethisonchange = "";
    $thistable = "";
    $tempvalue = "";
    $temprecno = 0;
    $tempapprovelist = "";
    $temptablename = "";
    if($_SESSION['isLive'] == true)
    {
        $temptablename = "_pro";
    }
    if($from == "div_barber_permission")
    {
        $sqlsap = "SELECT * FROM square_approved_permissions WHERE approved_permissions$temptablename IS NOT NULL AND foreign_ur_recno = $thisrecno";
        $resultsap = $db->PDOMiniquery($sqlsap);
        if($db->PDORowcount($resultsap) > 0)
        {
            foreach($resultsap as $rssap)
            {
                $tempapprovelist = $rssap["approved_permissions$temptablename"]; //$tempapprovelist = ["1",..."n"];
            }
            $decodelist = json_decode($tempapprovelist);  //Now $decodelist is an array (1,...,n);
            $reallist = implode(',', $decodelist); //$reallist is not a string 1,...,n
            
            $sql = "SELECT api_names$temptablename FROM square_permissions WHERE recno IN(".$reallist.") ORDER BY api_names_pro";
            //file_put_contents("./dodebug/debug.txt", "div_barber_permission? $sql\n", FILE_APPEND);
            $result = $db->PDOminiquery($sql);
            if($db->PDORowcount($result) > 0)
            {?>
                <div class="div-permission-container"><?php
                $i=0;
                foreach($result as $rs)
                {
                    $i++;?>
                    <div class="div-avail-list display-inline-block float-left width-100 cursor-pointer font-size-pt9em">
                        <div class="div-permission-list-no align-right border-bottom-1px border-right-1px float-left"><?php echo $i ?>.</div>
                        <div class="div-permission-list-data align-left border-bottom-1px align-left float-left"><?php echo $rs["api_names$temptablename"] ?></div>
                    </div><?php 
                }?>                            
                </div><?php
            }
            else
            {
                if($from == "div_barber_permission" || $from == "div_barber_editpermission")
                {
                    echo "<div class='dashboard-barber-square-permissions font-size-2em'>No Data</div>";
                }
            }
        }
    }
    else
    {
        
        $sql = "SELECT sap.approved_permissions$temptablename, sp.api_names$temptablename, sp.recno as sp_recno FROM square_approved_permissions sap INNER JOIN square_permissions sp ON sap.foreign_ur_recno = $thisrecno ";
        $sql .= "WHERE sp.isActive = true ORDER BY sp.api_names$temptablename";
        //file_put_contents("./dodebug/debug.txt", "GetSquarepermission? $sql\n", FILE_APPEND);
        $result = $db->PDOMiniquery($sql);
        if($db->PDORowcount($result) > 0)
        {
            foreach($result  as $rs)
            {
                $tempavailableArray[] = ['recno' => $rs['sp_recno'], 'api_name' => $rs["api_names$temptablename"]];
                $approvedlist = $rs["approved_permissions$temptablename"];
            }
            //Now that we have the list of sp_recno in an array, we will work on getting the approved sp_recno from the $approvedlist
            //$approvedlist is in json format so we have to decode it first
            $decode_approvedlist = json_decode($approvedlist); //['4',....]
            //file_put_contents("./dodebug/debug.txt", "Approvedarray??  $decode_approvedlist \n", FILE_APPEND);
            foreach($tempavailableArray as $key => $value)
            {
                if(is_array($value))
                {
                    foreach($value as $key1 => $value1)
                    {
                        if($key1 == "recno" && in_array($value1, $decode_approvedlist))
                        {
                            //If we are looking at the recno and it is in this array, we are adding to the approve list
                            //file_put_contents("./dodebug/debug.txt", "Approved?? recno => ".$value['recno']." && value => ".$value['api_name']." \n", FILE_APPEND);
                            $approvedapi[] = ['recno' => $value['recno'], "api_name" => $value['api_name']];
                            break; //We break here because we do not need to continue this sub array, if we did, it would fail the if check and go into the else and 
                            //we would have this record in the available, which we don't want.  If it exist in the approve, we don't want it exist in the available.
                        }
                        else
                        {
                            //file_put_contents("./dodebug/debug.txt", "available?? recno => ".$value['recno']." && value => ".$value['api_name']." \n", FILE_APPEND);
                            $availableArray[] = ['recno' => $value['recno'], "api_name" => $value['api_name']];
                            break;
                        }
                    }
                    
                }
            }
            //At this point after we come out fo the foreach, we should have an associative aray $approvedapi and a single $availableArray that contains the recno
            //of the available list.
        }
        else
        {
            //Because we have nothing for this user yet, we will have to assume that there is no existing list so we will pull all the available APIs and show it.
            $sqlapi = "SELECT * FROM square_permissions WHERE isActive is true";
            $resultapi = $db->PDOMiniquery($sqlapi);
            if($db->PDORowcount($resultapi) > 0)
            {
                foreach($resultapi as $rsapi)
                {
                    $availableArray[] = ["recno" => $rsapi["recno"], "api_name" => $rsapi["api_names$temptablename"]];
                }
                 
            }
        }?>
        <div class="div-barber-permission-data-container align-left">
            <div class="display-inline-block align-left">
                <input class="float-left dashboard-barber-permission" type="text" name="htxt_api_names" id="htxt_api_names" value="" placeholder="Enter a new API name" />
                <button class="float-left btn-dashboard-barber-add" name="hbtn_addapi" id="htbn_addapi" onclick="addAPI(<?php echo $thisrecno ?>);">Add</button>
            </div>
            <div class="div-dashboard-barber-container display-inline-block">
                <div class="div-data-container-list display-inline-block float-left border-right-1px border-bottom-1px border-top-1px">
                    <div class="div-dashboard-barber-addmod-header align-center display-inline-block font-size-1p5em border-bottom-1px">Approved</div>
                    <div class="div-approved-container" id="div_approved_container"><?php
                        if(!empty($approvedapi))
                        {
                            $i=0;
                            foreach($approvedapi as $key => $value)
                            {
                                if(is_array($value))
                                {
                                    $i++;
                                    foreach($value as $key1 => $value1)
                                    {
                                        
                                        if($key1 == "recno")
                                        {
                                            $temprecno = $value1;
                                        }
                                        else
                                        {
                                            $tempvalue = $value1;
                                        }
                                    }
                                    ?>
                                    <div class="div-approved-list display-inline-block float-left width-100 cursor-pointer font-size-pt9em" id="div_approvedlist_container_data_<?php echo $temprecno ?>" onclick="selectApproved(<?php echo $temprecno ?>)">
                                        <div class="div-approved-list-no align-right border-bottom-1px border-right-1px float-left" id="div_approvedlist_no_<?php echo $temprecno ?>"><?php echo $i ?>.</div>
                                        <div class="div-approved-list-data align-left border-bottom-1px align-left float-left" id="div_approvedlist_data_<?php echo $temprecno ?>"><?php echo $tempvalue ?></div>
                                    </div><?php
                                }
                            }
                        }
                    ?>
                    </div>
                </div><?php
                if($_SESSION['isDeveloper'] == true)
                {?>
                    <div class="div-data-container-arrow display-inline-block float-left">
                        <div class="div-dashboard-barbers-rightarrow align-center font-size-1p5em cursor-pointer" id="div_dashboard_barbers_rightarrow" onclick="moveAPI(this, <?php echo $thisrecno ?>, 'Right');">>></div>
                        <div class="div-dashboard-barbers-leftarrow align-center font-size-1p5em cursor-pointer" id="div_dashboard_barbers_left" onclick="moveAPI(this, <?php echo $thisrecno ?>, 'Left');"><<</div>
                    </div>
                    <div class="div-data-container-list display-inline-block float-left border-right-1px border-bottom-1px border-top-1px border-left-1px">
                        <div class="div-dashboard-barber-addmod-header align-center display-inline-block font-size-1p5em border-bottom-1px">Available</div>
                        <div class="div-available-container" id="div_available_container"><?php
                        if(!empty($availableArray))
                        {
                            $i=0;
                            foreach($availableArray as $key => $value)
                            {
                                if(is_array($value))
                                {
                                    $i++;
                                    foreach($value as $key1 => $value1)
                                    {
                                        
                                        if($key1 == "recno")
                                        {
                                            $temprecno = $value1;
                                        }
                                        else
                                        {
                                            $tempvalue = $value1;
                                        }
                                    }
                                    ?>
                                    <div class="div-avail-list display-inline-block float-left width-100 cursor-pointer font-size-pt9em" id="div_availlist_container_data_<?php echo $temprecno ?>" onclick="selectAvailable(<?php echo $temprecno ?>)">
                                        <div class="div-avail-list-no align-right border-bottom-1px border-right-1px float-left" id="div_availlist_no_<?php echo $temprecno ?>"><?php echo $i ?>.</div>
                                        <div class="div-avail-list-data align-left border-bottom-1px align-left float-left" id="div_availlist_data_<?php echo $temprecno ?>"><?php echo $tempvalue ?></div>
                                    </div><?php
                                }
                            }
                        }?>                            
                        </div>
                    </div><?php
                }?>
            </div>
        </div><?php
    }
}
function RevokeToken()
{
    global $db, $pt;
    $tempsandpropost = "";
    $thisappid = "";
    $thistoken = "";
    $thissecret = "";
    $thistable = 'users';
    $isSuccess = false;
    //We will reset or refresh the access token.
    if($_SESSION['isLive'] == true)
    {
        $tempsandpropost = "_pro";
    }
    $sql = "SELECT square_api_access_token$tempsandpropost, square_application_id$tempsandpropost, square_client_secret$tempsandpropost FROM users WHERE recno = ".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thistoken = $rs["square_api_access_token$tempsandpropost"];
        $thisappid = $rs["square_application_id$tempsandpropost"];
        $thissecret = $rs["square_client_secret$tempsandpropost"];
    }
    //file_put_contents("./dodebug/debug.txt", "fresh_token? $thisrereshtoken\n", FILE_APPEND);
    //We must now use this code to get an access token and a refresh token
    $thisreturnsarray = $pt->GetRevoketoken($thistoken, $thisappid, $thissecret);
    //file_put_contents("./dodebug/debug.txt", "revoke? $thisreturnsarray\n", FILE_APPEND);
    if(is_array($thisreturnsarray))
    {
        foreach($thisreturnsarray as $key1 => $value1)
        {
            if($key1 == "Success")
            {
                $isSuccess = true;
                $thisdata["square_api_access_token$tempsandpropost"] = NULL;  //reset the access token since revoked
                $thisdata["square_code$tempsandpropost"] = NULL;                //reset square code since revoked, will get new one anyway
                $thisdata["quare_access_token_expire_date$tempsandpropost"] = NULL; //reset date to null since it no longer is valid
                //file_put_contents("./dodebug/debug.txt", "access_token? $value1\n", FILE_APPEND);
            }
        }
        if($isSuccess == true)
        {
            $thiswhere['recno'] = $_SESSION['user_recno'];
            $thisupdate = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
            //file_put_contents("./dodebug/debug.txt", "sql. $thisupdate\n", FILE_APPEND);
            if($thisupdate == "Success")
            {
                echo "Success";
            }
        }
        else
        {
            echo "Failed";
        }
    }
    else
    {
        echo "Failed";
    }
    
}
function ShowHidden()
{
    global $db, $pt;
    $thissandpro = "";
    $thisaccesstoken = "";
    $returnpost = $pt->AnalyzePosts();

    if($returnpost['from'] == "div_barber_apipro" || $returnpost['from'] == "div_barber_permissionpro")
    {
        $thissandpro = "_pro"; 
    }
    if($returnpost['from'] == "div_barber_apisand" || $returnpost['from'] == "div_barber_permission" ||
        $returnpost['field'] == "twillio_sms_number" ||  $returnpost['field'] == "twillio_api_token" ||  
        $returnpost['field'] == "twillio_api_id")
    {
        $thissandpro = ""; 
    }
    $sql = "SELECT ".$returnpost['field']."$thissandpro FROM users WHERE recno = ".$_SESSION['user_recno'];
    //file_put_contents("./dodebug/debug.txt", "ShowToken: ".$sql."\n", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thisaccesstoken = $rs[$returnpost['field']."$thissandpro"];
    }
    echo $thisaccesstoken;
}
function RefreshToken()
{
    global $db, $pt;
    $tempsandpropost = "";
    $thistable = 'users';
    $newdate = "";
    $thisappid = "";
    $returnarray = [];
    //We will reset or refresh the access token.
    if($_SESSION['isLive'] == true)
    {
        $tempsandpropost = "_pro";
    }
    $sql = "SELECT square_api_access_token$tempsandpropost, square_refresh_token$tempsandpropost, square_application_id$tempsandpropost, square_client_secret$tempsandpropost,";
    $sql .= "square_approved_url ";
    $sql .= "FROM users WHERE recno = ".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thistoken = $rs["square_api_access_token$tempsandpropost"];
        $thisrereshtoken = $rs["square_refresh_token$tempsandpropost"];
        $thisappid = $rs["square_application_id$tempsandpropost"];
        $thissecret = $rs["square_client_secret$tempsandpropost"];
        $thisurl = $rs['square_approved_url'];
    }
    //file_put_contents("./dodebug/debug.txt", "fresh_token? $thisrereshtoken\n", FILE_APPEND);
    //We must now use this code to get an access token and a refresh token
    //https://developer.squareup.com/docs/oauth-api/square-permissions
    $thisreturnsarray = $pt->GetRefreshtoken($thistoken, $thisrereshtoken, $thisappid, $thissecret, $thisurl);
    if(is_array($thisreturnsarray))
    {
        foreach($thisreturnsarray as $key1 => $value1)
        {
            if($key1 == "access_token")
            {
                $thisdata["square_api_access_token$tempsandpropost"] = $value1;
                $returnarray["acess_token"] = $value1;
                //file_put_contents("./dodebug/debug.txt", "access_token? $value1\n", FILE_APPEND);
            }
            if($key1 == "expires_at")
            {
                $explodedate = explode("T", $value1);
                $thisexpiredate = $explodedate[0];
                $newdate = date('Y-m-d', strtotime($thisexpiredate));
                $thisdata["square_access_token_expire_date$tempsandpropost"] = $newdate;
                //file_put_contents("./dodebug/debug.txt", "expires_at? $value1\n", FILE_APPEND);
                $returnarray["acess_date"] = $newdate;
            }
            if($key1 == "refresh_token")
            {
                $thisdata["square_refresh_token$tempsandpropost"] = $value1;
                //file_put_contents("./dodebug/debug.txt", "refresh_token? $value1\n", FILE_APPEND);
                $returnarray["refresh_token"] = $value1;
            }
            if($key1 == "refresh_token_expires_at")
            {
                $explodedate = explode("T", $value1);
                $thisexpiredate = $explodedate[0];
                $newdate = date('Y-m-d', strtotime($thisexpiredate));
                $thisdata["square_access_token_expire_date$tempsandpropost"] = $newdate;
                //file_put_contents("./dodebug/debug.txt", "expires_at? $value1\n", FILE_APPEND);
                $returnarray["refresh_date"] = $newdate;
            }
        }
        $thiswhere['recno'] = $_SESSION['user_recno'];
        $thisupdate = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
        //file_put_contents("./dodebug/debug.txt", "sql. $thisupdate\n", FILE_APPEND);
        if($thisupdate == "Success")
        {
            header('Content-type: application/json');
            echo json_encode($returnarray);
        }
    }
    else
    {
        echo "Failed";
    }
}                                                  
function ShowUsertbs()
{
    global $db, $pt;
    //User - div_barber_user, table name dashboard_mgm_barbers_tbl_user
    //API Pro - div_barber_apipro
    //API Sand - div_barber_apisand
    $returnpost = $pt->AnalyzePosts();
    //If we come in as Admin, we would have selected a user and this user will have the $returnpost['recno'] since we passed it.
    //If we come in here as a non-admin, we would still get the user recno because we passed it in.
    //We do this so when and if we go to codefetch, we will make sure to pull the right information base on the selected or current user.
    $_SESSION['permission_recno'] = $returnpost['recno']; 
    //API Pro - div_barber_apipro
    //API Sand - div_barber_apisand
    $temptablename = "";
    $tempstars = "*********************************************************************";
    $temp_square_code = "";
    $temp_square_refresh_token_expires_date = "";
    $daysleft = 0;
    $temp_square_access_token_expire_date = ""; //square_access_token_expire_date
    $usethisonchange = "";
    //$returnpost['recno'], $returnpost['from']
    $sql = "SELECT ";
    if($returnpost['from'] == "div_barber_apipro" || $returnpost['from'] == "div_barber_permissionpro")
    {
        $temptablename = "_pro"; 
    }
    if($returnpost['from'] == "div_barber_apisand" || $returnpost['from'] == "div_barber_permission")
    {
        $temptablename = ""; 
    }
    $sql .= "square_access_token_expire_date$temptablename, square_refresh_token_expires_date$temptablename, square_code$temptablename FROM users WHERE recno = ".$returnpost['recno'];
    $result = $db->PDOminiquery($sql);
    //file_put_contents('./dodebug/debug.txt', "ShowUsertbs sql? $sql \n", FILE_APPEND); //div_barber_apipro
    foreach($result as $rs)
    {
        $temp_square_code = $rs["square_code$temptablename"];
        $temp_square_access_token_expire_date = !empty($rs["square_access_token_expire_date$temptablename"]) ? date('m/d/Y', strtotime($rs["square_access_token_expire_date$temptablename"])) : '';
        $temp_square_refresh_token_expires_date = empty($rs["square_refresh_token_expires_date$temptablename"]) ? '' : date('m/d/Y', strtotime($rs["square_refresh_token_expires_date$temptablename"]));
    }
    if($_SESSION['isAdmin'] == true || $_SESSION['isBarber'] == true || $_SESSION['isDeveloper'] == true)
    {
        $usethisonchange = 'onchange="updateUser(this,'.$returnpost['recno'].');"';
    }?>
    <table id="<?php echo $returnpost['barbertable'] ?>" class="tbl-dashbard-mgm-user float-left"  style="border: 1px solid black;"><?php
        if($returnpost['from'] == "div_barber_apipro" || $returnpost['from'] == "div_barber_apisand")
        {
            if(is_null($temp_square_code))
            {?>
                <tr>
                    <td class="tbl-dashboard-user-lbl align-right"></td>
                    <td><button class="cursor-pointer dashboard-mgmbarber-renew-api float-left" name="btn_get_code" id="btn_get_code" onclick="getSquarecode('<?php echo ($returnpost['from'] == "div_barber_apipro" ? 'Production' : 'Sandbox') ?>');">Click to verify Square</button></td>
                </tr><?php
            }
            else
            {?>
                <tr>
                    <td class="tbl-dashboard-user-lbl align-right">Code: </td>
                    <td>
                        <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txtsquare_code<?php echo $temptablename ?>" name="txtsquare_code<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo $tempstars ?>" /></div></div>
                        <div class="float-left div-check-eye"><img id="img_code" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txtsquare_code<?php echo $temptablename ?>', 'square_code', '<?php echo $returnpost['from']?>');" /></div></div>
                    </td>
                </tr><?php
            }?>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">App I.D <img title="Must manually update or enter this value." src="./images/others/question.png"/>: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txtsquare_application_id<?php echo $temptablename ?>" name="txtsquare_application_id<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo $tempstars ?>" /></div>
                    <div class="float-left div-check-eye"><img id="img_app_id" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txtsquare_application_id<?php echo $temptablename ?>', 'square_application_id', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Client Secret:</td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txtsquare_client_secret<?php echo $temptablename ?>" name="txtsquare_client_secret<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo $tempstars ?>" /></div>
                    <div class="float-left div-check-eye"><img id="img_client_secret" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txtsquare_client_secret<?php echo $temptablename ?>', 'square_client_secret', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr>
            <tr>
                <div class="float-left div-check-eye-input"><td class="tbl-dashboard-user-lbl align-right">API Token: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txtsquare_api_access_token<?php echo $temptablename ?>" name="txtsquare_api_access_token<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo $tempstars ?>" /></div>
                    <div id="div_mgm_barbers_pro_api_token" name="div_mgm_barbers_pro_api_token" class="float-left div-check-eye"><img id="img_api_token" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txtsquare_api_access_token<?php echo $temptablename ?>', 'square_api_access_token', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Token Expires: </td>             
                <td><input type="text" class="user-dashboard-input datepicker-mgmbarbers-tabs" id="txtsquare_access_token_expire_date<?php echo $temptablename ?>" name="txtsquare_access_token_expire_date<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo date("m/d/Y", strtotime($temp_square_access_token_expire_date)) ?>" readonly /></td>
            </tr>
            <?php
            if(!empty($temp_square_access_token_expire_date))
            {?>
                <tr>
                    <td class="tbl-dashboard-user-lbl align-right"></td>
                    <td class="tbl-dashboard-user-lbl-span align-center">
                    <?php
                        //We want to blink the button if the access code is going to expire within 1 week.
                        $blinkingdefault = "";
                        if(strtotime(date('m/d/Y', strtotime($temp_square_access_token_expire_date))) < strtotime(date('m/d/Y', strtotime('+1 week'))))
                        {
                            $blinkingdefault = "flashing-background";
                        }
                        $daysleft = (int)date('d', strtotime(date('m/d/Y', strtotime($temp_square_access_token_expire_date))) - strtotime(date('m/d/Y')));?>
                        <button class="cursor-pointer dashboard-mgmbarber-renew-api-btn <?php echo $blinkingdefault ?> align-left float-left" name="btn_renew_api" id="btn_renew_api" onclick="getSquarecode();">
                            <span class="font-size-1p5em">Access Token</span> expires On <?php echo date('m/d/Y', strtotime($temp_square_access_token_expire_date)) ?>.  
                            You have <?php echo $daysleft ?> days left.  If expired, you will need to click this button to get a new access token.  If you want to just renew, click the refresh button below.
                        </button>                      
                        <button class="cursor-pointer dashboard-mgmbarber-revoke-api-btn dashboard-mgmbarber-revoke-api-btn align-left float-left" name="btn_revoke_api" id="btn_revoke_api" onclick="revokeToken('<?php echo $temptablename ?>');">
                            Click to revoke Access Token (WILL REMOVE ALL OAUTHACCESS FOR ALL INSTANCES).
                        </button>
                    </td>
                </tr><?php
            }?>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Refresh Token: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txtsquare_refresh_token<?php echo $temptablename ?>" name="txtsquare_refresh_token<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo $tempstars ?>" /></div>
                    <div class="float-left div-check-eye"><img id="img_refresh_token" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txtsquare_refresh_token<?php echo $temptablename ?>', 'square_refresh_token', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">Refresh Expires: </td>
                <td><input type="text" class="user-dashboard-input datepicker-mgmbarbers-tabs" id="txtsquare_refresh_token_expires_date<?php echo $temptablename ?>" name="txtsquare_refresh_token_expires_date<?php echo $temptablename ?>" <?php echo $usethisonchange ?> value="<?php echo $temp_square_refresh_token_expires_date ?>" readonly /></td>
            </tr>
            <?php
            if(!empty($temp_square_refresh_token_expires_date))
            {?>
                <tr>
                    <td class="tbl-dashboard-user-lbl align-right"></td>
                    <td class="tbl-dashboard-user-lbl-span align-center">
                    <?php
                        //We want to blink the button if the access code is going to expire within 1 week.
                        $blinkingdefault = "";
                        if(strtotime(date('m/d/Y', strtotime($temp_square_refresh_token_expires_date))) < strtotime(date('m/d/Y', strtotime('+1 week'))))
                        {
                            $blinkingdefault = "flashing-background";
                        }
                        $daysleft = (int)(date('d', strtotime(date('m/d/Y', strtotime($temp_square_refresh_token_expires_date))) - strtotime(date('m/d/Y'))));
                        ?>
                        <button class="cursor-pointer dashboard-mgmbarber-refresh-api-btn <?php echo $blinkingdefault ?> align-left float-left" name="btn_renew_api" id="btn_renew_api" onclick="refreshToken('<?php echo $temptablename ?>');">
                            <span class="font-size-1p5em">Refresh token</span> expires On <?php echo date('m/d/Y', strtotime($temp_square_refresh_token_expires_date)) ?>.  
                            You have <?php echo $daysleft ?> days left for the access token, click to refresh the access token!!!
                        </button>
                    </td>
                </tr><?php
            }?>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right" title="Environment Location I.D">ENV Location I.D: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txtsquare_ev_location_id<?php echo $temptablename ?>" name="txtsquare_ev_location_id<?php echo $temptablename ?>" <?php echo $usethisonchange ?> size="63" value="<?php echo $tempstars ?>" /></div></div>
                    <div class="float-left div-check-eye"><img id="img_code" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txtsquare_ev_location_id<?php echo $temptablename ?>', 'square_ev_location_id', '<?php echo $returnpost['from']?>');" /></div></div>
                </td>
            </tr><?php
        }
        else if($returnpost['from'] == "div_barber_apitwillio")
        {?>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">SMS Phone No.: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txttwillio_sms_number" name="txttwillio_sms_number" <?php echo $usethisonchange ?> size="58" value="<?php echo $tempstars ?>" /></div>
                    <div class="float-left div-check-eye"><img id="img_twillio_sms_number" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txttwillio_sms_number', 'twillio_sms_number', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">API Token: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txttwillio_api_token" name="txttwillio_api_token" <?php echo $usethisonchange ?> size="58" value="<?php echo $tempstars ?>" /></div>
                    <div class="float-left div-check-eye"><img id="img_twillio_api_token" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txttwillio_api_token', 'twillio_api_token', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr>
            <tr>
                <td class="tbl-dashboard-user-lbl align-right">API I.D: </td>
                <td>
                    <div class="float-left div-check-eye-input"><input type="text" class="user-dashboard-input" id="txttwillio_api_id" name="txttwillio_api_id" <?php echo $usethisonchange ?> size="58" value="<?php echo $tempstars ?>" /></div>
                    <div class="float-left div-check-eye"><img id="img_twillio_api_id" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showHidden(this, 'txttwillio_api_id', 'twillio_api_id', '<?php echo $returnpost['from']?>');" /></div>
                </td>
            </tr><?php
        }
        else if($returnpost['from'] == "div_barber_permission" || $returnpost['from'] == "div_barber_permissionpro")
        {?>
           <tr>
               <td colspan="2">
                   <div>
                        <div class="float-left display-inline-block" style=" width: 100%; border-bottom: 1px solid black;">
                            <div class="cursor-pointer float-left dashboard-mgm-permission-tabs dashboard-mgm-permission-tabs-selected dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top align-center" id="div_barber_permission" onclick="showPermissiontbs(this, <?php echo $returnpost['recno'] ?>);">Permissions</div>
                            <div class="cursor-pointer float-left dashboard-mgm-permission-tabs dashboard-mgm-barbers-tabs-border-left dashboard-mgm-barbers-tabs-border-top align-center" id="div_barber_addpermission" onclick="showPermissiontbs(this, <?php echo $returnpost['recno'] ?>);">Add/Delete/Mod API Names</div>
                        </div>
                        <div class="float-left div-dashboard-mgm_barbers-permissions" name="div_dashboard_mgm_barbers_permissions" id="div_dashboard_mgm_barbers_permissions"><?php
                            GetSquarepermission($returnpost['recno'], 'div_barber_permission');?>
                        </div>
                   </div>
                </td>
            </tr><?php
        }?>
    </table><?php
    
    //03/16/2026
    //file_put_contents('./dodebug/debug.txt', "Recno? ".$returnpost['recno']." AND From? ".$returnpost['from']."\n", FILE_APPEND);
    //$pc->SetUsertabs($db, $returnpost['recno'], $returnpost['from']);
    //$thisreturn = $pc->GetUsertabs();
    //echo $thisreturn;
}
function CheckDates()
{?>
    <tr class="eventdate">
        <td class="tbl-event-lbl">Date From (Start): <span class="asterisk"> * </span></td>
        <td class="eventinput"><input type="text" class="event datepicker" id="this_date_start" name="this_date_start" size="70" value="" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
    </tr>
    <tr class="eventdate">
        <td class="tbl-event-lbl">Date To (Expire): <span class="asterisk"> * </span></td>
        <td class="eventinput"><input type="text" class="event datepicker" id="this_end_date" name="this_end_date" size="70" value="" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
    </tr><?php
}
function CheckDiscount()
{?>
    <tr class="tr-discountdata">
        <td class="tbl-event-lbl">Stackable:</td>
        <td class="eventinput"><select name="this_isCombo" id="this_isCombo"><option value="Select" selected>Select</option><option value="true">Yes</option><option value="false">No</option></select><img class="dashboard-create-event-question" title="Can this discount be combined with some other discounts?" src="./images/others/question.png" /></td>
    </tr>        
    <tr class="tr-discountdata">
        <td class="tbl-event-lbl">Value:</td>
        <td class="eventinput"><input type="text" name="this_discount" id="this_discount" size="5" value="" placeholder="Enter a value" /><select name="this_isDollar" id="this_isDollar"><option value="Select" selected>Select a type</option><option value="true">Dollars</option><option value="false">Percent</option></td>
    </tr>
    <tr class="tr-discountdata">
        <td class="tbl-event-lbl">Auto Apply:</td>
        <td class="eventinput"><select name="this_isAuto" id="this_isAuto"><option value="Select" selected>Select</option><option value="true">Yes</option><option value="false">No</option></select><img class="dashboard-create-event-question" title="Will this discount automatically apply?" src="./images/others/question.png" /></td>
    </tr> 
    
<?php
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
        default:
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
function GetUserchedule()
{
    global $pt;
    
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    echo ($pt->GetSchedule($_POST['thisrecno']));
    
}
function ManageSchedule()
{
    global $pt;
    if($_SESSION['isAdmin'] == true)
    {
        ManageSearchmenus("Schedule");
    }
    else
    {
        echo ($pt->GetSchedule($_POST['thisrecno'] = $_SESSION['user_recno']));
    }
}

function SaveIntroduction()
{
    global $pt,$db;
    $thisrecno = 0;
    $thisval = "";
    $returnpost = $pt->AnalyzePosts(); 
    $thistable = "company_info";
    $thisdata = ["introduction" => $returnpost['introduction']];
    $thiswhere = ["recno" => $returnpost['recno']];
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
function SaveAbout()
{
    global $db, $pt, $isDebug;
    $returnpost = $pt->AnalyzePosts();
    $i = 0;
    if($isDebug)
    {
        $pt->AnalyzeArray('dashboard', 'SaveAbout', $i, $returnpost, $returnpost);
    }    
    //You should know what you want, you can just get it from the data by accessing the array
    $thistable = "company_info";
    $thisdata = ["about" => $returnpost['about']];
    $thiswhere = ["recno" => $returnpost['recno']];
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
function SearchGuest()
{
    global $db;
    $realname = "";
    if(strlen($_POST['thisguest']) > 1)
    {
        $tempstr = "(firstname LIKE '%".$_POST['thisguest']."%' OR middlename LIKE '%".$_POST['thisguest']."%' OR lastname LIKE '%".$_POST['thisguest']."%')";
    }
    else
    {
        $tempstr = "(firstname LIKE '".$_POST['thisguest']."%' OR middlename LIKE '".$_POST['thisguest']."%' OR lastname LIKE '".$_POST['thisguest']."%')";
    }
    $sql = "SELECT recno, firstname, middlename, lastname, email FROM users WHERE $tempstr ";
    $sql .= "AND isBarber = false AND isActive = true";
    //file_put_contents("./dodebug/debug.txt", "search guest sql = ".$sql."\n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);?>   
    <select class="slt-dashboard-search-guest" name="sltsearchguest" id="sltsearchguest" size="10" onchange="selectGuest(this);"><?php
        if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {  
           foreach($result as $rs)
           {
               $realname = $rs['firstname'].($rs['middlename'] != null ? ' '.$rs['middlename'].'. ' : ' ').$rs['lastname'];
               $_SESSION['guestsearchlist'][$rs['recno']] = $realname;?>
                <option class="dashboard-history-search-guest" value="<?php echo $rs['recno'] ?>"><?php echo $realname ?> (<?php echo $rs['email'] ?>)</option><?php 
           }
        }?>
    </select><?php
    
}
function ShowTrash()
{
    //Allows user to restore deleted images
}
function DashPay()
{
    global $pt;
    $_SESSION['thisrecno'] = $_POST['thisrecno'];
    $_SESSION['thisfrom'] = "Dashpay";
    //file_put_contents("./dodebug/debug.txt", "Dashbord History dashPay: ".$_POST['thisrecno']." \n", FILE_APPEND);
    //$pt -> CompleteCuts($_POST['thisrecno'], 'Dashboard');
}
function ShowHistory()
{
    global $db, $pt;

    $thiscsstatusactive = "show-event-option-status-notselected";
    $thiscsstatushistory = "show-event-option-status-notselected";
    $thiscsstatuscancel = "show-event-option-status-notselected";
    $thiscsstatussearch = "show-event-option-status-notselected";
    $sqlstr = "";
    if($_POST['status'] == "Active")
    {
        $thiscsstatusactive = "show-event-option-status-option-selected";

        //If the barber is not an admin, we want to get only the ones for him.
        $sqlstr = "sd.uf_recno = ".$_SESSION['user_recno']." AND sd.isPaid = false AND sd.iscancelled = false AND sd.isdeleted = false ";
        
    }
    else if($_POST['status'] == "History")
    {
        $thiscsstatushistory = "show-event-option-status-option-selected";
        $sqlstr = "sd.iscancelled = false AND sd.isPaid = true";
    }
    else if($_POST['status'] == "Cancelled" && $_SESSION['isAdmin'] == true)
    {
        $thiscsstatuscancel = "show-event-option-status-option-selected";
        $sqlstr = "sd.iscancelled = true";
    }
    else if(($_POST['status'] == "Search" || $_POST['status'] == "Searching") && $_SESSION['isAdmin'] == true)
    {
        $thiscsstatussearch = "show-event-option-status-selected";
        if($_POST['status'] == "Searching")
        {
            foreach(json_decode($_POST['thisarray']) as $key => $value)
            {   
                //file_put_contents("./dodebug/debug.txt", "thisarray = $key == $value \n", FILE_APPEND);
                foreach($value as $key1 => $value2)
                {
                    //file_put_contents("./dodebug/debug.txt", "thisarray = $key1 == $value2 \n", FILE_APPEND);
                    if($key1 == "thispayment_id")
                    {
                        $thispayment_id = $value2; //recno for schedule date
                        //file_put_contents("./dodebug/debug.txt", "thispayment_id = $thispayment_id \n", FILE_APPEND);
                    }
                    else if($key1 == "thislogin")
                    {
                        $thislogin = $value2; //status will be either Add or Remove
                        //file_put_contents("./dodebug/debug.txt", "thislogin = $thislogin \n", FILE_APPEND);
                    }
                    else if($key1 == "thisguest")
                    {
                        $thisguest = $value2; //discount recno
                        //file_put_contents("./dodebug/debug.txt", "thisguest = $thisguest \n", FILE_APPEND);
                    }
                    else if($key1 == "thisdate")
                    {
                        $thisdate = $value2; //discount recno
                        //file_put_contents("./dodebug/debug.txt", "thisdate = $thisdate \n", FILE_APPEND);
                    }
                    else if($key1 == "thisfromdate")
                    {
                        $thisfromdate = $value2; //discount recno
                        //file_put_contents("./dodebug/debug.txt", "thisfromdate = $thisfromdate \n", FILE_APPEND);
                    }
                    else if($key1 == "thistodate")
                    {
                        $thistodate = $value2; //discount recno
                        //file_put_contents("./dodebug/debug.txt", "thistodate = $thistodate \n", FILE_APPEND);
                    }
                }
            }
            if($thispayment_id != "")
            {
                $sqlstr = "sd.payment_id = $thispayment_id";
            }
            else
            {
                //If we are here, that means there is 1 or more fields of search
                if($thislogin != "" && $thislogin != "Select")
                { 
                    //login will come in as the uf_recno, which is the recno in table users
                    $sqlstr = "sd.uf_recno = $thislogin ";
                }
                if($thisguest != "")
                {
                    if($sqlstr == "")
                    {
                        $sqlstr = "sd.ufg_recno = $thisguest ";
                        //file_put_contents("./dodebug/debug.txt", "thistodate 1 = $thistodate \n", FILE_APPEND);
                    }
                    else
                    {
                        $sqlstr .= "AND sd.ufg_recno = $thisguest ";
                        //file_put_contents("./dodebug/debug.txt", "thistodate 2 = $thistodate \n", FILE_APPEND);
                    }
                }
                if($thisdate != "")
                {
                    if($sqlstr == "")
                    {
                        $sqlstr = "sd.date = '".date('Y-m-d', strtotime($thisdate))."' ";
                    }
                    else
                    {
                        $sqlstr .= "AND sd.date = '".date('Y-m-d', strtotime($thisdate))."' ";
                    }
                }
                if($thisfromdate != "" && $thistodate != "")
                {
                    if($sqlstr == "")
                    {
                        $sqlstr = "sd.date BETWEEN '".date('Y-m-d', strtotime($thisfromdate))."' AND '".date('Y-m-d', strtotime($thistodate))."' ";
                    }
                    else
                    {
                        $sqlstr .= "AND sd.date BETWEEN '".date('Y-m-d', strtotime($thisfromdate))."' AND '".date('Y-m-d', strtotime($thistodate))."' ";
                    }
                }
            }
        }
    }
    //file_put_contents("./dodebug/debug.txt", "show event sql: ".$sqlstr." \n", FILE_APPEND);
    //./images/others/$thismedia/avatar/$profileimage"?>
    <div class="show-event-option-status-container float-left">
        <div class="<?php echo $thiscsstatusactive ?> cursor-pointer float-left show-event-option-status" id="divhistoryactivce" onclick="getHistorystatus(this, 'Active');">Active</div>
        <div class="<?php echo $thiscsstatushistory ?> cursor-pointer float-left show-event-option-status" id="divhistoryhistory" onclick="getHistorystatus(this, 'History');">History</div><?php
        if($_SESSION['isAdmin'] == true)
        {?>
            <div class="<?php echo $thiscsstatuscancel ?> cursor-pointer float-left show-event-option-type-status" id="divhistorycancel" onclick="getHistorystatus(this, 'Cancelled');">Cancelled</div>
            <div class="<?php echo $thiscsstatussearch ?> cursor-pointer float-left show-event-option-type-status" id="divhistorysearch" onclick="getHistorystatus(this, 'Search');">Search</div><?php
        }?>
    </div>
    <div class="div-show-history-data-container" id="div_show_event_data_container"><?php            
        if($_POST['status'] != "Search")
        {?>
            <table id="tblhistorydata" name="tblhistorydata" class="dashboard-tbl-history">
                <thead>
                    <tr style="height: 40px;">
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 40px;">No.</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 80px;">Barber</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 80px;">Guest</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 100px;">Receipt</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 80px;">Date</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 80px;" title="Apppointment Time">App Time</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 240px;">Comment</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 340px;">Services</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 90px;">Status</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 80px;">Tip</th>
                        <th class="align-center tbl-history-dashboardpay-th" style="width: 80px;">Cost <span style="font-size: .8em"><br/>(include tax/tip)</span></th>
                    </tr>
                </thead>
                <tbody><?php
                    $tempbarberlogin = "No Available";
                    $tiptotal = 0;
                    $bigtotaltotal = 0;
                    $sql = "SELECT sd.*, u.login FROM schedule_dates sd INNER JOIN users u ON sd.uf_recno = u.recno WHERE $sqlstr AND sd.sr_recno IS NOT NULL";
                    //file_put_contents("./dodebug/debug.txt", "Dashbord History: $sql \n", FILE_APPEND);
                    $result = $db ->PDOMiniquery($sql);
                    if($db ->PDORowcount($result) > 0)
                    {
                        $i = 1;
                        foreach($result as $rs)
                        {
                            $sqlg = "SELECT * FROM users WHERE recno = ".$rs['ufg_recno'];
                            $resultg = $db->PDOMiniquery($sqlg);
                            foreach($resultg as $rsg)
                            {
                                $thisguest = $rsg['firstname'].' '.empty($rsg['middlename']) ? '' : $rsg['middle'].' '.$rsg['lastname'];
                            }?>
                            <tr class="history-table-tr">
                                <td class="align-right tbl-history-td"><?php echo $i ?>.</td>
                                <td class="align-left tbl-history-td" style="padding-left: 10px; font-size: .8em;"><?php echo $rs['login'] ?></td>
                                <td class="align-left tbl-history-td" style="padding-left: 10px; font-size: .8em;"><?php echo $thisguest ?></td>
                                <td class="align-left tbl-history-td" style="padding-left: 10px; font-size: .8em;"><?php echo $rs['square_receipt'] ?></td>
                                <td class="align-center tbl-history-td"><?php echo date('m/d/Y', strtotime($rs['date'])) ?></td>
                                <td class="align-center tbl-history-td"><?php echo $rs['slot'] ?></td>
                                <td class="align-center tbl-history-td" style="width: 260px;"><textarea class="history-table-comment-txtarea" rows="5" readonly><?php echo $rs['comment'] ?></textarea></td>
                                <td class="align-center tbl-history-td" style="width: 420px;">
                                    <textarea class="history-table-comment-txtarea" rows="2" readonly><?php   
                                        $servno = 1;
                                        $sqlserv = "SELECT * FROM service WHERE recno IN (".$rs['sr_recno'].")";
                                        $resultserv = $db->PDOMiniquery($sqlserv);
                                        if($db->PDORowcount($resultserv) > 0)
                                        {
                                            foreach($resultserv as $rsserv)
                                            {?>
                                                <?php echo $servno ?>.&nbsp;&nbsp;<?php echo $rsserv['title']."\n\n";
                                                $servno++;
                                            }
                                        }
                                        else
                                        {?>
                                          No Service.
                                        <?php
                                        }?>
                                    </textarea>
                                </td>
                                <td class="align-center tbl-history-td"><?php echo ($rs['isPaid'] == true) ? 'Paid' : '<button style="margin: auto 0px;" name="btnhistorypay" id="btnhistorypay" onclick="dashPay(this, '.$rs["recno"].');">Click to Pay</button>' ?></td>
                                <td class="align-center tbl-history-td align-right">$<?php echo ($rs['tip'] == NULL) ? 0 : $rs['tip'] ?></td>
                                <td class="align-center tbl-history-td align-right">$<?php echo $rs['bigtotal'] ?></td>
                            </tr><?php
                            
                            $tiptotal += $rs['tip'];
                            $bigtotaltotal += $rs['bigtotal'];
                            $i++;
                        }?>
                        <tr>
                            <td class="align-center tbl-history-last-td align-right" colspan="9">&nbsp;</td>
                            <td class="align-center tbl-history-last-td align-right">$<?php echo ($tiptotal == 0) ? 0 : number_format($tiptotal,2) ?></td>
                            <td class="align-center tbl-history-last-td align-right">$<?php echo ($bigtotaltotal == 0) ? 0: number_format($bigtotaltotal,2) ?></td>
                        </tr><?php
                    }
                    else
                    {?>
                            <tr style="border-bottom: 1px solid;"><td colspan="10">No Data</td></tr> 
                    <?php
                    }?>
                </tbody>
            </table><?php
        }
        else
        {?>
            <div id="div_history_search_guest">
                <table class="dashboard-tbl-history-search">
                    <tr class="tr-dashboard-search-val">
                        <td class="td-dashboard-search-lbl align-right">Receipt</td><td class="td-dashboard-search-val align-left"><input class="td-dashboard-search-val-inp" type="text" name="txt_payment_id" id="txt_payment_id" value="" placeholder="#12345678" /></td>
                    </tr>
                    <tr class="tr-dashboard-search-val">
                        <td class="td-dashboard-search-lbl align-right">Barber</td>
                        <td class="td-dashboard-search-val align-left">
                            <div style="width: 20%; height: 98%;"><?php echo $pt->GetBarber()->GetSelect("sltbarber", "", false, false, false, true, false, true) ?></div>
                        </td>
                    </tr>
                    <tr class="tr-dashboard-search-val">
                        <td class="td-dashboard-search-lbl align-right">Guest</td><td class="td-dashboard-search-val align-left"><input class="td-dashboard-search-val-inp" type="text" name="txt_guest" id="txt_guest" onkeyup="searchGuest(this);" value="" placeholder="Name or Email" /></td>
                    </tr>
                    <tr class="tr-dashboard-search-val">
                        <td class="td-dashboard-search-lbl align-right">Date</td><td class="td-dashboard-search-val align-left"><input class="td-dashboard-search-val-inp datepicker" style="margin-top: -22px;" type="text" id="txt_date" name="txt_date" size="24" value="" onfocus="getJDate(this, true);" onchange="chkSearchdates(this);" placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
                    </tr>
                    <tr class="tr-dashboard-search-val">
                        <td class="td-dashboard-search-lbl align-right">Date Range</td>
                        <td class="td-dashboard-search-val">
                            <div style="width: 40%; height: 98%; line-height: 40px; min-width: 320px;">
                                <div class="float-left">
                                    Date:<input type="text" class="td-dashboard-search-val-inp datepicker" id="txt_fromdate" name="txt_fromdate" size="24" value="" onfocus="getJDate(this, true);" onchange="chkSearchdates(this);" placeholder="dd/mm/yyyy ex: 01/22/2022" />
                                </div>
                                <div class="float-right">
                                    To Date:<input type="text" class="td-dashboard-search-val-inp datepicker" id="txt_todate" name="txt_todate" size="24" value="" onfocus="getJDate(this, true);" onchange="chkSearchdates(this);" placeholder="dd/mm/yyyy ex: 01/22/2022" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><br/><div style="width: 900px;"><button class="align-center" name="btn_his_search" id="btn_his_search" onclick="doSearch();">Search</button></div></td>
                    </tr>
                </table>
                
            </div><?php
        }?>
    </div><?php 

}
function UpdateEventstatus()
{
    global $db; 
    $thistable = "events";
    if($_POST['thisstatus'] == "Active")
    {
        $thisdata = ['isActive' => true, 'isDeleted' => false];
    }
    else if($_POST['thisstatus'] == "Inactive")
    {
        $thisdata = ['isActive' => false, 'isDeleted' => false];
    }
    else
    {
        //Deleted
        $thisdata = ['isDeleted' => true, 'isActive' => false];
    }
    
    $thiswhere = ['recno' => $_POST['thisrecno']];
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
function SubmitModattachment()
{
    global $db, $pt;
    //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
    //Assuming we are here, we want to now handle the upload.
    //First we want to check if './images/others/$_SESSION['media_dir']/logo/ exist, if not, we create it before we move file into it.
    $thistable = "events";
    $thisdir = "./images/others/".$_SESSION['media_dir']."/event";
    if (!file_exists($thisdir)) {
        mkdir("./images/others/".$_SESSION['media_dir']."/event", 0777, true);
    }
    //Once we confirmed that it is there after, now we want to move the file or files there and also update the name of the file to the table.

    $msg = $pt ->UploadFile($thisdir, $_FILES["file"], $thistable, "attachment", $_POST['thisrecno'], 'Yes', 'Event');
    
}
function ModAttachment()
{
    //We want to build a div with a form in it for submitting attachment
    ?>
    <div class="div-body-dashbord-createevent-container">
        <form name="frmsubmitattachment" id="frmsubmitattachment" enctype="multipart/form-data" method="post">
            <div class="align-left" style="width: 500px; height: 100px; background-color: gray;">
                <div>Upload Attachment</div>
                <div><input class="event-attachments" type="file" name="file[]" id="modattachment" /></div><br/>
                <button id="btn_submit" name="btn_submit" onclick="submitModattachment(<?php echo $_POST['thisrecno'] ?>);">Submit</button>
            </div>
        </form>
    </div><?php
}
function RemoveAttachment()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", "admin company here: ".$_FILES['thisfile']["name"]." \n", FILE_APPEND);
    $thistable = "events";
    
    //First we have to get the attachment which we want to remove
    $sql = "SELECT recno, attachment FROM events WHERE recno = ".$_POST['thisrecno'];
    $rows = $db->PDOMiniquery($sql);
    $newstr = "";
    foreach($rows as $rs)
    {
        $explodeattachment = explode(',', $rs['attachment']);
        for($i=0; $i<count($explodeattachment); $i++)
        {
            //file_put_contents("./dodebug/debug.txt", "attachment: ".$explodeattachment." != ".$_POST['thisattachment']." \n", FILE_APPEND);
            if($explodeattachment[$i] != $_POST['thisattachment'])
            {
                if($newstr == "")
                {
                    $newstr = $explodeattachment[$i];
                }
                else
                {
                    $newstr .= ",".$explodeattachment[$i];
                }
            }
        }
    }
    $thisdata = ['attachment' => $newstr];
    
    $thiswhere = ['recno' => $_POST['thisrecno']];
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
function ChangeEvent()
{
    global $db, $pt;
    //file_put_contents("./dodebug/debug.txt", "admin company here: ".$_FILES['thisfile']["name"]." \n", FILE_APPEND);
    $thistable = "events";

    //$thisdata = $pt ->PostIt($_POST); //PostIt is a function that will return an associative array with non-empty values and substring first 3 chars
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
        }
    }
    $thiswhere = ['recno' => $_POST['thisrecno']];
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
function ModifyEvent()
{
    global $db, $pt;
    
    //Need to know the status of this event
    $sql = "SELECT u.media_dir, u.firstname, u.lastname, u.login, e.special_event, e.recno as erecno, e.date_start, e.end_date, e.event_type, e.description, e.attachment, e.isActive, e.isDeleted, e.event_restriction, e.discount, e.isDollar, e.isAuto ";
    $sql .= "FROM users u INNER JOIN events e ON e.creator = u.recno WHERE e.recno = ".$_POST['thisrecno'];
    $rows = $db->PDOMiniquery($sql);
    $j = 1;
    foreach($rows as $rs)
    {
        $thisfunc = "changeEvent(this, ".$rs['erecno'].");";
        ?>
        <div class="div-body-dashbord-createevent-container" id="div_body_dashbord_createevent_container">
            <table id="tbl_event" class="tbl-dashbord-event">
                <tr>
                    <td class="tbl-event-lbl">Event Type: <span class="asterisk"> * </span></td>
                    <td class="eventinput"><?php 
                        $sqlev = "SELECT * FROM current_events WHERE isDeleted = false";
                        $resultev = $db->PDOMiniquery($sqlev);?>
                        <select name="sltevent_type" id="sltevent_type" onchange="changeEvent(this, <?php echo $rs['erecno'] ?>);"><?php
                            foreach($resultev as $rsev)
                            {
                                if($rsev['event_type'] == $rs['event_type'])
                                {?>
                                    <option value="<?php echo $rsev['event_type'] ?>" selected><?php echo $rsev['event_type'] ?></option><?php
                                }
                                else
                                {?>
                                    <option value="<?php echo $rsev['event_type'] ?>"><?php echo $rsev['event_type'] ?></option><?php                                    
                                }
                            }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tbl-event-lbl">
                        Author: <span class="asterisk"> * </span></td>
                    <td class="eventinput"><input type="text" id="txtspecial_event" name="txtspecial_event" value="<?php echo $rs['firstname']." ".$rs['lastname'] ?>" size="70" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-event-lbl">Login: <span class="asterisk"> * </span></td>
                    <td class="eventinput"><input type="text" id="txtspecial_event" name="txtspecial_event" value="<?php echo $rs['login'] ?>" size="70" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-event-lbl">Event: <span class="asterisk"> * </span></td>
                    <td class="eventinput"><input type="text" id="txtspecial_event" name="txtspecial_event" value="<?php echo $rs['special_event'] ?>" size="70" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-event-lbl">Event Restriction: <span class="asterisk"> * </span></td>
                    <td class="eventinput"><input type="text" id="txtevent_restriction" name="txtevent_restriction" value="<?php echo $rs['event_restriction'] ?>" size="70" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-event-lbl">Discount: <span class="asterisk"> * </span></td>
                    <td class="eventinput"><input type="text" id="txtdiscount" name="txtdiscount" value="<?php echo $rs['discount'] ?>" size="70" onchange="changeEvent(this, <?php echo $rs['erecno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-event-lbl">Discount Type:</td>
                    <td class="eventinput"><?php echo ($rs['isDollar'] == true ? 'Dollars' : 'Percentage') ?></td>
                </tr><?php
                if($rs['event_restriction'] == "Limited" || $rs['event_restriction'] == "Repeat")
                {?>
                    <tr>
                        <td class="tbl-event-lbl">Date From (Start): <span class="asterisk"> * </span></td>
                        <td class="eventinput"><input type="text" class="event datepicker" id="txtdate" name="txtdate" size="70" value="<?php echo empty($rs['date_start']) ? '' : date('m/d/Y', strtotime($rs['date_start'])) ?>" onchange="changeEvent(this, <?php echo $rs['erecno'] ?>);" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-event-lbl">Date To (Expire):</td>
                        <td class="eventinput"><input type="text" class="event datepicker" id="txtexpire_date" name="txtexpire_date" size="70" value="<?php echo empty($rs['end_date']) ? '' : date('m/d/Y', strtotime($rs['end_date'])) ?>" onchange="changeEvent(this, <?php echo $rs['erecno'] ?>);" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022" /></td>
                    </tr><?php
                }?>
                    <tr>
                        <td class="tbl-event-lbl">Auto Apply (Start): <span class="asterisk"> * </span></td>
                        <td class="eventinput"><input class='dashboard-tbl-chkbox' type="checkbox" id="chkisAuto" name="chkisAuto" <?php echo (($rs['isAuto'] == true && $rs['special_event'] != "Prepay") ? 'checked' : 'checked disabled') ?> /></td>
                    </tr>
                <tr>
                    <td class="tbl-event-lbl">Description:</td>
                    <td class="eventinput">
                        <textarea id="txtdescription" name="txtdescription"rows="10" cols="70" resize="none" onchange="changeEvent(this, <?php echo $rs['erecno'] ?>);"><?php echo $rs['description'] ?></textarea>
                    </td>
                </tr>
                <tr class="tr-event" id="tr_event1">
                    <td class="tbl-event-lbl">
                        attachments: <button class="add-attachment" id="btn_add_attachment" onclick="modAttachment(<?php echo $rs['erecno']?>);" title='Click to add attachment'>+</button><span class="asterisk"> * </span></td>
                    <td class="eventinput">
                        <div class="div-attachment-container" id="div_attachment_container"><?php
                                if($rs['attachment'] != NULL & $rs['attachment'] != "")
                                {
                                    $explodeattachment = explode(",", $rs['attachment']);
                                    for($i=0; $i<count($explodeattachment); $i++)
                                    {?>
                                        <script type="text/javascript">
                                            $("body").data("modattach<?php echo $j ?>", "<?php echo $explodeattachment[$i] ?>");
                                        </script>
                                        <div class="div-attachment-ele" id="div_attachment_ele<?php echo $j ?>">
                                            <span class="span-event-numbered"><?php echo $j ?>.</span>
                                            <a style="text-decoration:none; font-size: .7em;" href='./images/others/<?php echo $rs['media_dir'] ?>/event/<?php echo $explodeattachment[$i] ?>', target='_blank'><?php echo $explodeattachment[$i] ?></a>
                                            <button class="remove-attachment" id="btn_remove_attachment<?php echo $j ?>" name="btn_remove_attachment<?php echo $j ?>" onclick="removeAttachment(this, <?php echo $rs['erecno']?>, <?php echo $j ?>);" title='Click to remove attachment'>-</button><br/>
                                         </div><?php
                                        $j++;
                                    }
                                }?>
                           
                        </div>
                    </td>
                </tr>
            </table>
            <div><?php
                if($rs['isActive'] == true)
                {?>
                    <button class="cursor-pointer" name="btnactive" id="btnactive" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Active');" title="Event is active." style="width: 50px; margin: 0px auto; background-color: green; color: white;">Active</button>
                    <button class="cursor-pointer" name="btninactive" id="btninactive" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Inactive');" title="Set event inactive." style="width: 70px; margin: 0px auto; background-color: darkred; color: white;">Inactive</button>
                    <button class="cursor-pointer" name="btndeleted" id="btndeleted" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Deleted');" title="Delete this event." style="width: 60px; margin: 0px auto; background-color: darkred; color: white;">Delete</button>
                <?php
                }
                else if($rs['isActive'] == false && $rs['isDeleted'] == false)
                {?>
                    <button class="cursor-pointer" name="btnactive" id="btnactive" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Active');" title="Click to set event active." style="width: 50px; margin: 0px auto; background-color: darkred; color: white;">Active</button>
                    <button class="cursor-pointer" name="btninactive" id="btninactive" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Inactive');" title="Even is currently inactive." style="width: 70px; margin: 0px auto; background-color: green; color: white;">Inactive</button>
                    <button class="cursor-pointer" name="btndeleted" id="btndeleted" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Deleted');" title="Delete this event." style="width: 60px; margin: 0px auto; background-color: darkred; color: white;">Delete</button>
                <?php
                }
                else if($rs['isDeleted'] == true)
                {?>
                    <button class="cursor-pointer" name="btnactive" id="btnactive" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Active');" title="Click to set event active." style="width: 50px; margin: 0px auto; background-color: darkred; color: white;">Active</button>
                    <button class="cursor-pointer" name="btninactive" id="btninactive" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Inactive');" title="Click to set event inactive." style="width: 70px; margin: 0px auto; background-color: darkred; color: white;">Inactive</button>
                    <button class="cursor-pointer" name="btndeleted" id="btndeleted" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Deleted');" title="Even is currently deleted." style="width: 60px; margin: 0px auto; background-color: green; color: white;">Delete</button>
                <?php
                }?>
            </div>
        </div><?php
    }
}
function ShowEvent()
{
    global $db;
    $thisstatus = "";
    $thiscsstatusactive = "show-event-option-status-notselected";
    $thiscsstatusinactive = "show-event-option-status-notselected";
    $thiscsstatusdeleted = "show-event-option-status-notselected";
    
    $thiseventtypeevent = "show-event-option-status-option-notselected";
    $thiseventtypepromo = "show-event-option-status-option-notselected";
    $thiseventtypepostit = "show-event-option-status-option-notselected";
    
    if($_POST['thiseventtype'] == "Event")
    {
        $thiseventtypeevent = "show-event-option-status-option-selected";
    }
    else if($_POST['thiseventtype'] == "Promotional")
    {
        $thiseventtypepromo = "show-event-option-status-option-selected";
    }
    else if($_POST['thiseventtype'] == "Postit")
    {
        $thiseventtypepostit = "show-event-option-status-option-selected";
    }
    
    if($_POST['status'] == 'Active')
    {
        $thisstatus = "e.isActive = true AND e.isDeleted = false";
        $thiscsstatusactive = "show-event-option-status-selected";
    }
    else if($_POST['status'] == "Inactive")
    {
        $thisstatus = "e.isActive = false AND e.isDeleted = false";
        $thiscsstatusinactive = "show-event-option-status-selected";
    }
    else
    {
        $thisstatus = "e.isDeleted = true";
        $thiscsstatusdeleted = "show-event-option-status-selected";
    }
    $sql = "SELECT u.media_dir, u.firstname, u.lastname, u.login, e.special_event, e.recno as erecno, e.date_start, e.end_date, e.event_type, e.description, e.attachment, e.discount, e.event_restriction, e.isDollar ";
    $sql .= "FROM users u INNER JOIN events e ON e.creator = u.recno WHERE $thisstatus AND event_type='".$_POST['thiseventtype']."' ORDER BY e.date_start";
    $rows = $db->PDOMiniquery($sql);
    
    //file_put_contents("./dodebug/debug.txt", "show event sql: ".$sql." \n", FILE_APPEND);
    //./images/others/$thismedia/avatar/$profileimage"?>
    <div class="show-event-option-status-container float-left">
        <div class="<?php echo $thiscsstatusactive ?> cursor-pointer float-left show-event-option-status" id="diveventactive" onclick="getEvenoptions(this, 'Active');">Active</div><?php
        if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == true)
        {?>
            <div class="<?php echo $thiscsstatusinactive ?> cursor-pointer float-left show-event-option-status" id="diveventinactive" onclick="getEvenoptions(this, 'Inactive');">Inactive</div>
            <div class="<?php echo $thiscsstatusdeleted ?> cursor-pointer float-left show-event-option-status" id="diveventdeleted" onclick="getEvenoptions(this, 'Deleted');">Deleted</div><?php
        }?>
    </div>
    <div class="show-event-option-status-container-type float-left">
        <div class="cursor-pointer float-left show-event-option-type-status <?php echo $thiseventtypeevent ?>" id="diveventevent" onclick="getEvenoptionstype(this, 'Event');">Event</div>
        <div class="cursor-pointer float-left show-event-option-type-status <?php echo $thiseventtypepromo ?>" id="diveventpromo" onclick="getEvenoptionstype(this, 'Promotional');">Promotional</div><?php
        if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == true)
        {?>
            <div class="cursor-pointer float-left show-event-option-type-status <?php echo $thiseventtypepostit ?>" id="diveventpostit" onclick="getEvenoptionstype(this, 'Postit');">Post It</div><?php
        }?>
    </div>
    <div id="div_show_event_data_container">
        <table id="tblservicedata" class="tbl-modify-event">
            <thead>
                <tr style="height: 40px;">
                    <th class="align-center tbl-modify-event-th" style="width: 40px;">No.</th>
                    <th class="align-center tbl-modify-event-th" style="width: 120px;">Name.</th>
                    <th class="align-center tbl-modify-event-th" style="width: 120px;">Barber</th>
                    <th class="align-center tbl-modify-event-th" style="width: 80px;">Date</th>
                    <th class="align-center tbl-modify-event-th" style="width: 80px;">XDate</th>
                    <th class="align-center tbl-modify-event-th" style="width: 150px;">Event</th>
                    <th class="align-center tbl-modify-event-th" style="width: 100px;">Event Type</th>
                    <th class="align-center tbl-modify-event-th" style="width: 100px;">Discount</th>
                    <th class="align-center tbl-modify-event-th" style="width: 140px;">Event Restriction</th>
                    <th class="align-center tbl-modify-event-th" style="width: 200px;">Description</th>
                    <th class="align-center tbl-modify-event-th" style="width: 140px;">Attachments</th><?php
                    if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == true)
                    {?>
                        <th style="width: 60px;"></th><?php
                    }?>
                </tr>
            </thead>
            <tbody><?php
                if($db ->PDORowcount($rows) > 0)
                {
                    $i = 1;
                    foreach($rows as $rs)
                    {
                        if($rs['event_type'] == "Event")
                        {
                            $thiseventtypeclass = "event-type-event";
                        }
                        else if($rs['event_type'] == "Promotional")
                        {
                            $thiseventtypeclass = "event-type-promo";
                        }
                        else if($rs['event_type'] == "Postit")
                        {
                            $thiseventtypeclass = "event-type-postit";
                        }?>
                        <tr class="<?php echo $thiseventtypeclass?>" style="border-bottom: 1px solid;">
                            <td class="align-right tbl-modify-event-td"><?php echo $i ?>.</td>
                            <td class="align-left tbl-modify-event-td" style="padding-left: 10px; font-size: .8em;"><?php echo $rs['firstname']." ".$rs['lastname']?></td>
                            <td class="align-left tbl-modify-event-td" style="padding-left: 10px; font-size: .8em;"><?php echo $rs['login'] ?></td>
                            <td class="align-center tbl-modify-event-td"><?php echo empty($rs['date_start']) ? '' : date('m/d/Y', strtotime($rs['date_start'])) ?></td>
                            <td class="align-center tbl-modify-event-td"><?php echo empty($rs['end_date']) ? '' : date('m/d/Y', strtotime($rs['end_date'])) ?></td>
                            <td class="align-center tbl-modify-event-td" style="width: 280px;"><textarea style="width: 98%; resize: none;" rows="5" readonly><?php echo $rs['special_event'] ?></textarea></td>
                            <td class="align-left tbl-modify-event-td" style="padding-left: 10px; font-size: .8em;"><?php echo $rs['event_type'] ?></td>
                            <td class="align-left tbl-modify-event-td" style="padding-left: 10px; font-size: .8em;"><?php echo ($rs['isDollar'] == true ? '$'.$rs['discount'] : (!empty($rs['discount']) ? $rs['discount']."%" : '')) ?></td>
                            <td class="align-left tbl-modify-event-td" style="padding-left: 10px; font-size: .8em;"><?php echo $rs['event_restriction'] ?></td>
                            <td class="align-center tbl-modify-event-td" style="width: 360px;"><textarea style="width: 98%; resize: none;" rows="5" readonly><?php echo $rs['description'] ?></textarea></td>
                            <td class="align-center tbl-modify-event-td" style="width: 200px; font-size: .7em;">
                                <div class="align-left" style="width: 98%; resize: none; white-space: normal;"><?php
                                    $explodeattachment = explode(",", $rs['attachment']);
                                    for($j=0; $j<count($explodeattachment); $j++)
                                    {?>
                                        <a style="text-decoration:none;" href='./images/others/<?php echo $rs['media_dir'] ?>/event/<?php echo $explodeattachment[$j] ?>', target='_blank'><?php echo $explodeattachment[$j] ?></a><br/><?php
                                    }?>
                                </div>
                            </td><?php
                            if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == true)
                            {?>
                                <td>
                                    <button class="cursor-pointer" name="btnmodify" id="btnmodify" onclick="modifyEvent(this, <?php echo $rs['erecno'] ?>);" style="width: 50px; margin: 0px auto;">Modify</button>
                                    <?php
                                    if($_POST['status'] == 'Active' || $_POST['status'] == 'Inactive')
                                    {?>
                                        <button class="cursor-pointer" name="btnmodify" id="btnmodify" onclick="updateEventstatus(this, <?php echo $rs['erecno'] ?>, 'Deleted');" style="width: 50px; margin: 0px auto;">Delete</button>
                                    <?php
                                    }?>
                                </td><?php
                            }?>
                        </tr><?php
                        $i++;
                    }
                }
                else
                {?>
                        <tr style="border-bottom: 1px solid;"><td colspan="8">No Data</td></tr> 
                <?php
                }?>
            </tbody>
        </table>
    </div>
<?php   
}
function SubmitEvent()
{
   global $db, $pt;
   //echo var_dump($_POST);
    //file_put_contents("./dodebug/debug.txt", "admin company here: ".$_FILES['thisfile']["name"]." \n", FILE_APPEND);
    $isDiscounts = false;
    $thistable = "events";
    //file_put_contents("./dodebug/debug.txt", "admin company = ".var_dump($_POST['sltevent_type'])." \n", FILE_APPEND);
    //file_put_contents("./dodebug/debug.txt", "dashboard special_event = ".$_POST['txtspecial_event']." \n", FILE_APPEND);
    //$thisdata = $pt ->PostIt($_POST); //PostIt is a function that will return an associative array with non-empty values and substring first 3 chars
    $thisdata = [];
    
    $returnpost = $pt->AnalyzePostsubmit(); //doesn't work for some reason.
    
    /*
    event_type == Promotional 
    special_event == New Customer 
    event_restriction == Continuous 
    chkdiscount == on 
    isCombo == true/false 
    discount == 5 
    isDollar == true/false  
    isAuto == true/false 
    description == test */
    $thisdata['event_type'] = $returnpost['event_type'];
    $thisdata['special_event'] = $returnpost['special_event'];
    $thisdata['event_restriction'] = $returnpost['event_restriction'];
    if($returnpost['event_restriction'] == "Repeats" || $returnpost['event_restriction'] == "Limited")
    {
        $thisdata['date_start'] = $returnpost['date_start'];
        $thisdata['end_date'] = $returnpost['end_date'];
    }
    if($returnpost['chkdiscount'] == true)
    {
        
        $thisdata['isCombo'] = ($returnpost['isCombo'] == 'true' ? true : false);
        $thisdata['discount'] = $returnpost['discount'];
        $thisdata['isDollar'] = ($returnpost['isDollar'] == 'true' ? true : false);
        $thisdata['isAuto'] = ($returnpost['isAuto'] == 'true' ? true: false);
    }
    $thisdata['description'] = $returnpost['description'];
    $thisdata['creator'] = $_SESSION['user_recno'];
    $thisrecno = $db ->PDOInsert($thistable, $thisdata, $_SESSION['user_recno']);
    
    if(isset($thisrecno))
    {
        if(count(array_filter(($_FILES["files"]["name"]))))
        {
            $thisfile = $_FILES["files"];
            $thisfield = "attachment";
            $countfiles = count($thisfile["name"]); 
            //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
            //Assuming we are here, we want to now handle the upload.
            //First we want to check if './images/others/$_SESSION['media_dir']/logo/ exist, if not, we create it before we move file into it.
            $thisdir = "./images/others/".$_SESSION['media_dir']."/event";
            if (!file_exists($thisdir)) {
                mkdir("./images/others/".$_SESSION['media_dir']."/event", 0777, true);
            }
            //Once we confirmed that it is there after, now we want to move the file or files there and also update the name of the file to the table.
            $filepath = $thisdir;
            //$msg = $pt ->UploadFile($thisdir, $_FILES["files"], $thistable, "attachment", $thisrecno, NULL, 'Event');

            $strattachments = "";  
            $typeisgood = "";
            for($i=0;$i<$countfiles;$i++)
            {
                $filename = $thisfile['name'][$i];
                //file_put_contents("./dodebug/debug.txt", "this filename ".$filename, FILE_APPEND);
                $pdfMimearray = array('application/pdf', 'application/doc', 'application/docx');
                $thismime = mime_content_type($thisfile['tmp_name'][$i]);
                //file_put_contents("./dodebug/debug.txt", "this mine ".$thismime, FILE_APPEND);
                //Now that we have the $filename, we can check for the file type and size and all the goodies.
                $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
                $detectedType = exif_imagetype($thisfile['tmp_name'][$i]);

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
                    move_uploaded_file($thisfile['tmp_name'][$i],"$filepath/$filename");
                }
                else
                {
                    $typeisgood = "BAD";
                }

            }
            if($typeisgood != "BAD")
            {
                $thisdata = array($thisfield => $strattachments);
                $thiswheres = array('recno' => $thisrecno);
                $result = $db->PDOUPDATE($thistable, $thisdata, $thiswheres, $thisrecno);
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
        else
        {
            echo "Success";
        }
    }
    else
    {
        echo "Failed to insert";
    } 
}
function DoEvent()
{
    global $db, $pt;?>
    <div class="div-body-dashbord-createevent-container">
            <form name="frmcreateevent" id="frmcreateevent" enctype="multipart/form-data" method="post">
                <table id="tbl_event" class="tbl-dashbord-event">
                    <tr>
                        <td class="tbl-event-lbl">Event Type: <span class="asterisk"> * </span></td>
                        <td class="eventinput"><?php
                            $thistable = "current_events";
                            $thisfields = array("recno","event_type");
                            $thiswheres = array("isDeleted" => false);
                            $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);?>
                            <select name="this_event_type" id="this_event_type">
                                <option>Select an event</option><?php
                            foreach($result as $rs)
                            {?>
                                <option><?php echo $rs['event_type'] ?></option><?php
                            }?>
                            </select><?php
                            ?>                        
                        </td>
                    </tr>
                    <tr>
                        <td class="tbl-event-lbl">Event Name: <span class="asterisk"> * </span></td>
                        <td class="eventinput"><input type="text" id="this_special_event" name="this_special_event" value="" size="70" /></td>
                    </tr>
                    <tr id="tr_slttype">
                        <td class="tbl-event-lbl">Event Restriction: <span class="asterisk"> * </span></td>
                        <td class="eventinput">
                            <select name="this_event_restriction" id="this_event_restriction" onchange="checkDates(this);">
                                <option value="Select" selected>Select</option>
                                <option value="Continuous">Continuous</option>
                                <option value="Limited">Limited</option>
                                <option value="Repeats">Repeats</option>
                            </select>
                        </td>
                    </tr>                    
                    <tr id="tr_discount">
                        <td class="tbl-event-lbl">Discount: <span class="asterisk"> * </span></td>
                        <td class="eventinput"><input type="checkbox" name="this_chkdiscount" id="this_chkdiscount" onchange="checkDiscount(this);" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-event-lbl">Description:</td>
                        <td class="eventinput">
                            <textarea id="this_description" resize="none" name="this_description"rows="10" cols="70"></textarea>
                        </td>
                    </tr>
                    <td class="tbl-event-lbl">Note:</td>
                        <td class="eventinput" style="font-size: .8em; color: darkred;">
                            Only the first attachment will show up on the front page slideshow.
                        </td>
                    <tr class="tr-event" id="tr_event1">
                        <td class="tbl-event-lbl">
                            attachments: <button class="add-attachment" id="btn_add_attachment" onclick="addAttachment();" title='Click to add attachment'>+</button></td>
                        <td class="eventinput">
                            <div class="div-attachment-container" id="div_attachment_container">
                                <div class="div-attachment-ele" id="div_attachment_ele1">
                                    <span class="span-event-numbered">1</span>
                                    <input class="event-attachments" type="file" name="files[]" id="attachments" />
                                    <button class="remove-attachment display-none" id="btn_remove_attachment1" name="btn_remove_attachment1" onclick="removeAttachment(this);" title='Click to remove attachment'>-</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <button id="btn_submit" name="btn_submit" onclick="submitEvent();">Submit</button>
            </form>
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
    global $db, $pt;
    $thismsg = "";
    $returnpost = $pt->AnalyzePosts();
    //We want to check time and place
    switch($returnpost['thisfield'])
    {
        case 'disc_limit':
        case 'cancellation_fees':
            if(!is_numeric($returnpost['thisval']))
            {
                $thismsg = "This field must be a number.";
            }
            break;
        default:
            break;
    }
    if($thismsg == "")
    {
        $thistable = "company_info";
        if($returnpost['thisfield'] == "isActive" || $returnpost['thisfield'] == "isDeleted")
        {
            if($returnpost['thisval'] == true)
            {
                $thisdata = [$returnpost['thisfield'] => true];
            }
            else
            {
                $thisdata = [$returnpost['thisfield'] => false];
            }
        }
        else
        {
            $thisdata = [$returnpost['thisfield'] => $returnpost['thisval']];
        }
        $thiswhere = ['recno' => $returnpost['thisrecno']];
        $result = $db ->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
        if($result == "Success") //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function AddCompany()
{
    global $db;
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
    $thisgooglemap = "";
    $thisfblink = "";
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
            $thisgooglemap = $rs['googlemap_location'];
            $thispaymentcompany = $rs['api_company'];
            $thisphonenumber = $rs['phone_number'];
            $thissmsnumber = $rs['smsnumber'];
            $thisappid = $rs['application_id'];
            $thisapiaccesstoken = $rs['api_access_token'];
            $thisapitoken = $rs['api_token'];
            $thisapiid = $rs['api_id'];
            //$thisapiid = $rs['api_id'];
            //$thisapikey = $rs['api_key'];
            $thisdisclimit = $rs['disc_limit'];
            $thisaddress = $rs['address'];
            $thiscity = $rs['city'];
            $thisstate = $rs['state'];
            $thiszipcode = $rs['zipcode'];
            $thislogo = $rs['mainlogo'];
            $thisfblink = $rs['facebook_link'];
            $thislimit = $rs['disc_limit'];
        }
        $usthisfunc = 'onfocus="getVal(this)" onchange="updateCompanyinfo(this, '.$thisrecno.');"';
        $thisbutton = "";
    }?>
    <form name="frmcompany" id="frmcompany" method="post" enctype="multipart/form-data">
        <table id="tblservicedata" class="tbl-dashboard-company">
            <tr>
                <td class="tbl-dashboard-company-lbl">Name Of Company: <span class="asterisk"> * </span></td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="name" name="name" <?php echo $usthisfunc ?> value="<?php echo $thisname ?>" required /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Discount Limit: <span class="asterisk"> * </span></td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="disc_limit" name="disc_limit" <?php echo $usthisfunc ?> value="<?php echo $thislimit ?>" required /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Google Map Location:</td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="googlemap_location" name="googlemap_location" <?php echo $usthisfunc ?> value="<?php echo $thisgooglemap ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Facebook Link:</td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="facebook_link" name="facebook_link" <?php echo $usthisfunc ?> value="<?php echo $thisfblink ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Payment Company: </td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="api_company" name="api_company" <?php echo $usthisfunc ?> value="<?php echo $thispaymentcompany ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Phone No.: </td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="phone_number" name="phone_number" <?php echo $usthisfunc ?> value="<?php echo $thisphonenumber ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Address: </td>
                <td><input type="text" class="dashboard-company-input" style="width: 98%;" id="address" name="address" <?php echo $usthisfunc ?> value="<?php echo $thisaddress ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">City: </td>
                <td><input type="text" class="dashboard-company-input" id="city" name="city" <?php echo $usthisfunc ?> value="<?php echo $thiscity ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">State: </td>
                <td><input type="text" class="dashboard-company-input" id="state" name="state" <?php echo $usthisfunc ?> value="<?php echo $thisstate ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-dashboard-company-lbl">Zipcode: </td>
                <td><input type="text" class="dashboard-company-input" id="zipcode" name="zipcode" <?php echo $usthisfunc ?> value="<?php echo $thiszipcode ?>" /></td>
            </tr><?php
            if($thisname != "")
            {?> 
                <tr>
                    <td class="tbl-dashboard-company-lbl">Active:</td>
                    <td><input type="checkbox" class="dashboard-company-input float-left" style="height: 20px; width: 20px;" id="isActive" name="isActive" <?php echo $usthisfunc ?> checked dissabled /></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-company-lbl">Deleted:</td>
                    <td><input type="checkbox" class="dashboard-company-input float-left" style="height: 20px; width: 20px;" id="isShowcounter " name="isShowcounter" <?php echo $usthisfunc ?> disabled /></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-company-lbl">Counter:</td>
                    <td><input type="checkbox" class="dashboard-company-input float-left" style="height: 20px; width: 20px;" id="isDeleted" name="isDeleted" title="Click to show counter on front page." <?php echo $usthisfunc ?> disabled /></td>
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
                        <div class="div-dashboard-image-container" id="div_dashboard_image_container"><?php
                            $thisdir = "./images/others/".$_SESSION['media_dir']."/logo/*";
                            $thispath = "./images/others/".$_SESSION['media_dir']."/logo";
                            BuildCompanyimagecontainer('mainlogo', $thisdir, $thispath, $thislogo);?>
                        </div>
                   </td>
                </tr><?php
            }?>
        </table>
    </form><?php 
}
function BuildCompanyimagecontainer($from, $thisdir, $thispath, $sltedimage)
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
                    $usethiscssborder = "dashboard-image-bucket-selected";
                    $usethistitle = "Selected";
                }
            }?>
            <div onclick="selectImage(this, 'mainlogo', '<?php echo basename($file) ?>');">
                <img name="<?php echo basename($file) ?>" id="<?php echo basename($file) ?>" class="dashboard-bucket-image <?php echo $usethiscssborder ?>" title="<?php echo $usethistitle ?>" src="<?php echo $thispath ?>/<?php echo basename($file) ?>"></a>
                <br/><span class="admin-span-image-disc"><?php echo basename($file) ?></span>
            </div><?php
        }
    }
}
function SubmitNewservice()
{
    global $db, $pt;
    $thismsg = "";
    //We want to check time and place
    $returnpost = $pt->AnalyzePosts();
    $thistable = "service";
    if($thismsg == "")
    {
        $result = $db ->PDOInsert($thistable, $returnpost, $_SESSION['user_recno']);
        //file_put_contents("./dodebug/debug.txt", "admin menu sql = ".$result." \n", FILE_APPEND);
        if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function AddService()
{?>
    <table id="tblservicedata" class="tbl-dashboard-service">
        <tr>
            <td class="tbl-dashboard-sc-lbl">Service: <span class="asterisk"> * </span></td>
            <td><textarea cols="60" rows="4" class="required admin-services-input" id="txtarea_service" name="txtarea_service"></textarea></td>
        </tr>
        <tr>
            <td class="tbl-dashboard-sc-lbl">Time: <span class="asterisk"> * </span></td>
            <td><input type="text" class="firstname required admin-services-input" id="txttime" name="txttime" value="" required />mins.</td>
        </tr>
        <tr>
            <td class="tbl-dashboard-sc-lbl">Price: <span class="asterisk"> * </span></td>
            <td><input type="text" class="lastname required admin-services-input" id="txtprice" name="txtprice" value="" onfocus="getVal(this);" /></td>
        </tr>
        <tr>
            <td class="tbl-dashboard-sc-lbl">Comment: </td>
            <td><textarea class="admin-services-input" cols="60" rows="4" id="textarea_comment" name="textarea_comment"></textarea></td>
        </tr>
        <tr>
            <td class="tbl-dashboard-sc-lbl">Active:</td>
            <td><input type="checkbox" class="lastname required admin-services-input" id="chkactive" name="chkactive" checked dissabled /></td>
        </tr>
        <tr>
            <td class="tbl-dashboard-sc-lbl">Deleted:</td>
            <td><input type="checkbox" class="lastname required admin-services-input" id="chkdeleted" name="chkdeleted" disabled /></td>
        </tr>
        <tr>
            <td colspan="2" class="align-center"><button name="btnsubmit" id="btnsubmit" onclick="submitNewservice();">Submit</button></td>
        </tr>
    </table><?php 
}
function UpdateService()
{
    global $db, $pt;
    $thismsg = "";
    $returnpost = $pt->AnalyzePosts();
    //We want to check time and place
    switch($returnpost['thisfield'])
    {
        case 'time':
        case 'price':
            if(!is_numeric($returnpost['thisval']))
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
        //file_put_contents("./dodebug/debug.txt", "dashboard field = ".$returnpost['thisfield']." && val = ".$returnpost['thisval']."\n", FILE_APPEND);
        if($returnpost['thisfield'] == "isactive")
        {
            if($returnpost['thisval'] == 'true')
            {
                $thisdata = [$returnpost['thisfield'] => true, 'isdeleted' => false];
            }
            else
            {
                $thisdata = [$returnpost['thisfield'] => false, 'isdeleted' => false];
            }
        }
        else if($returnpost['thisfield'] == "isdeleted")
        {
            if($returnpost['thisval'] == 'true')
            {
                $thisdata = [$returnpost['thisfield'] => false, 'isactive' => false];
            }
            else
            {
                $thisdata = [$returnpost['thisfield'] => true, 'isactive' => false];
            }
        }
        else
        {
            $thisdata = [$returnpost['thisfield'] => $returnpost['thisval']];
        }
        $thiswhere = ['recno' => $returnpost['thisrecno']];
        $result = $db ->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
        //file_put_contents("./dodebug/debug.txt", "dashboard result is? = $result\n", FILE_APPEND);
        if($result == "Success") //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function GetService()
{
    global $pt, $db;
    $returnpost = $pt->AnalyzePosts();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "service";
    $thisfields = array('All');
    $thiswhere = array("recno" => $returnpost['recno']);
    //$thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <table id="tbluserdata" class="tbl-dashboard-service">
                <tr>
                    <td class="tbl-dashboard-sc-lbl">Service: <span class="asterisk"> * </span></td>
                    <td><textarea cols="60" rows="4" class="required" onfocus="getVal(this);" id="txtarea_service" name="txtarea_service" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'title');"><?php echo  $rs['title']; ?></textarea></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-sc-lbl">Time: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="required" id="txttime" name="txttime" value="<?php echo  $rs['time']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'time');" required />mins.</td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-sc-lbl">Price: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="required" id="txtprice" name="txtprice" value="<?php echo number_format($rs['price'], 2); ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'price');" /></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-sc-lbl">Comment: </td>
                    <td><textarea cols="60" rows="4" id="textarea_comment" name="textarea_comment" onfocus="getVal(this);" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'description');"><?php echo  $rs['description']; ?></textarea></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-sc-lbl">Active:</td>
                    <td><input type="checkbox" class="service-check required" id="chkactive" name="chkactive" value="true" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'isactive');" <?php echo ($rs['isactive'] == true) ? 'checked' : '' ?>/></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-sc-lbl">In-active:</td>
                    <td><input type="checkbox" class="service-check required" id="chkinactive" name="chkinactive" value="false" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'isactive');" <?php echo ($rs['isactive'] == false && $rs['isdeleted'] == false) ? 'checked' : '' ?>/></td>
                </tr>
                <tr>
                    <td class="tbl-dashboard-sc-lbl">Deleted:</td>
                    <td><input type="checkbox" class="service-check required" id="chkdeleted" name="chkdeleted" value="<?php echo  ($rs['isdeleted'] == true? 'true' : 'false') ?>" onchange="updateService(this, <?php echo $returnpost['recno'] ?>, 'isdeleted');" <?php echo ($rs['isdeleted'] == true) ? 'checked' : '' ?> /></td>
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
    global $pt, $db;
    $temp_disc_limit = "";
    $returnpost = $pt->AnalyzePosts(); 
    $sql = "SELECT * FROM service WHERE ";
    //file_put_contents("./dodebug/debug.txt", "admin menu sql = ".$_POST['isDeleted']." and ". $_POST['isActive']." \n", FILE_APPEND);
    if($returnpost['rdoservice'] == 'active')
    {
        $sql .= "isactive=true";
    }
    if($returnpost['rdoservice'] == 'inactive')
    {
        $sql .= "isactive=false AND isdeleted = false";
    }
    if($returnpost['rdoservice'] == 'deleted')
    {
        $sql .= "isdeleted=true";
    }    
    //file_put_contents("./dodebug/debug.txt", "admin menu sql = $sql \n", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);?>
    <table style="width: 800px; background-color: lightgray" id="tbl_dashboard_service" name="tbl_dashboard_service">
        <thead>
            <tr>
                <th>No.</th>
                <th style="line-height: 30px; width: 600px;">Service<button class="btn-dashboard-add-service float-right" title="Add Service" onclick="addService();">+</button></th>
                <th>Time</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($db ->PDORowcount($result) > 0)
            {
                $i = 1;
                foreach($result as $rs)
                {?>
                    <tr class="cursor-pointer" onclick="getService(this, <?php echo $rs['recno']?>);">
                        <td class="td-num-rows align-right"><?php echo $i ?>.</td>
                        <td><?php echo $rs['title'] ?></td>
                        <td><?php echo $rs['time'] ?></td>
                        <td>$<?php echo number_format($rs['price'], 2) ?></td>
                    </tr><?php
                    $i++;
                }
            }?>
        </tbody>
    </table><?php
}
function ManageSearchmenus($from)
{
    //$from - Users
    $usethisfunc = "reloadBarbers(this)";?>
    <div id="div_search_containter" class="div-search-containter">
        <div id="div_search_containter_slt" class="div-search-containter-slt">
            <div class="float-left" id="div_mgm_search"> <?php
            
            if($from == "Barbers" || $from == "Schedule")
            {
                //file_put_contents("./dodebug/debug.txt", "is from? ".$from."\n", FILE_APPEND);
                if($from == "Schedule")
                {
                    $usethisfunc = "reloadSchedule();";
                }
                if($from == "Services")
                {
                   $usethisfunc = "reloadService();";
                }?>
                <div>
                    <div class="float-left chk-barbers dashboard-div-chkbox-container"><input class="dashboard-chk-boxes" type="checkbox" name="chk_active" id="chk_active" value="true" onclick="<?php echo $usethisfunc ?>" checked /> Active </div>
                    <div class="float-left chk-barbers dashboard-div-chkbox-container"><input class="dashboard-chk-boxes" type="checkbox" name="chk_inactive" id="chk_inactive" value="false" onclick="<?php echo $usethisfunc ?>" />In-Active</div>
                    <div class="float-left chk-barbers dashboard-div-chkbox-container"><input class="dashboard-chk-boxes" type="checkbox" name="chk_terminated" id="chk_terminated" value="true" onclick="<?php echo $usethisfunc ?>" />Terminated</div>
                </div><?php
            }
            if($from == "Services")
            {?>

                <div class="float-right">
                    <div class="float-left div-chk-active"><input type="radio" name="rdoservice" id="rdoserviceactive" value="active" onclick="reloadService();" checked />Active</div>
                    <div class="float-left div-chk-active"><input type="radio" name="rdoservice" id="rdoserviceinactive" value="inactive" onclick="reloadService();" />Inactive</div>
                    <div class="float-left"><input type="radio" name="rdoservice" id="rdoservicedeleted" value="deleted" onclick="reloadService('reloadService();', 'Deleted');" />Deleted</div>
                </div><?php        
            }
            if($from != "Services")
            {
                //$from == "Users"
                ?>
                <div id="div_searchuser_container">
                    <input class="txt-search-dashboard" type="text" id="txtsearchuser" name="txtsearchuser" value="" placeholder="Enter a name to start search." onfocus="searchUser(this, '<?php echo $from ?>');" onkeyup="searchUser(this, '<?php echo $from ?>');" />
                    <button type="button" onclick="clearSearchuser();">Clear</button>                
                </div><?php
            }?>
            </div>
        </div>
    </div><?php
}
function UpdateUser()
{
    global $db, $pt, $ne, $load_headers;
    $thisreturn = "";
    $tempcheck = false;
    $thisfields = Array();
    $thistable = "users";
    $thisfield = $_POST['thisfield'];
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
    $isprosand = "";
    if($_SESSION['isLive'] == true)
    {
        $isprosand = "_pro";
    }
    if($_POST['thisfield'] == "birthday" || $_POST['thisfield'] == "hiredate" || 
            $_POST['thisfield'] == "square_access_token_expire_date$isprosand" || $_POST['thisfield'] == "square_refresh_token_expires_date$isprosand")
    {
        $formatthisdate = date('Y-m-d', strtotime($_POST['thisvalue']));
        $thisdata = array($_POST['thisfield'] => $formatthisdate); 
    }
    else if($_POST['thisfield'] == "login" || $_POST['thisfield'] == 'email')
    {
        if($_POST['thisfield'] == "email")
        {
            //WE want to do a final check on email, make sure it is a valid email.
            $ne->set_email($db, $_POST['thisvalue']);
            if($ne ->validate_email() == false)
            {
                $thisreturn = "Bad email format.  Please check email and try again.";
            }
            if($ne -> check_email() == true)
            {
                $thisreturn = "Email already exist.  Please check email and try again.";
            }
        }
        if($_POST['thisfield'] == "login")
        {
        //Can't get this to work.
            $thisfield = [$_POST['thisfield']];
            $thiswhere = array($_POST['thisfield'] => $_POST['thisvalue']);
            $sqlcheck = $pt -> CheckIfexist($thistable, $thisfield, $thiswhere);
            if($sqlcheck)
            {
                $thisreturn = "This ".$_POST['thisfield']." already exist.  Please use a different one.";
            }
        }
        $thisdata[$_POST['thisfield']] = $_POST['thisvalue'];
    }
    else if($_POST['thisfield'] == "isTerminated")
    {
        
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $thisdata['isActive'] = false;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $thisdata['isActive'] = true;
        }
    }
    else if($_POST['thisfield'] == "isActive")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
        }
    }
    else if($_POST['thisfield'] == "isAdmin")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $_SESSION['isAdmin'] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $_SESSION['isAdmin'] = false;
        }
    }
    else if($_POST['thisfield'] == "isBarber")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $_SESSION['isBarber'] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $_SESSION['isBarber'] = false;
        }
    }
    else if($_POST['thisfield'] == "isShowfb")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $_SESSION['isShowfb'] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $_SESSION['isShowfb'] = false;
        }
    }
    else if($_POST['thisfield'] == "isShowcancel")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $_SESSION['isShowcancel'] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $_SESSION['isShowcancel'] = false;
        }
    }
    else if($_POST['thisfield'] == "isShowrefund")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $_SESSION['isShowrefund'] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $_SESSION['isShowrefund'] = false;
        }
    }
    else if($_POST['thisfield'] == "isLive")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $_SESSION['isLive'] = true;
            $_SESSION['realsandpro'] = "Production";
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $_SESSION['isLive'] = false;
            $_SESSION['realsandpro'] = "Sandbox";
        }
    }
    else
    {
        $thisdata[$_POST['thisfield']] = $_POST['thisvalue'];  
    }
    if($thisreturn == "")
    {
        $thiswhere = array("recno" => $_POST['thisrecno']);
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['thisrecno']);
        //file_put_contents("./dodebug/debug.txt", $_POST['thisrecno'], FILE_APPEND);
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
                    $body = $ne ->get_active_body($db, $thisserver, $tempempname, $_POST['thisvalue']);
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
    $_SESSION['usersearchlist'] = [];
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function SearchServicerunset()
{
    $_SESSION['servicesearchlist'] = [];
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
function GetUserdata()
{
    global $db, $ps;
    unset($_SESSION['usersearchlist']);
    $ps->SetPersoninfo($db, $_POST['recno']);
    $ps->GetPersoninfo();
}
function SearchUser()
{
    global $db, $ps, $pt;
    $realbarber = true; //If we are from schedule, by default, we will be searcing for barbers so we default this declaration to true
    $returnpost = $pt->AnalyzePosts();
    
    if($returnpost['thisfrom'] == "Barbers" || $returnpost['thisfrom'] == "Schedule")
    {
        $ps->SetBarbers($returnpost['txtsearchuser'], $returnpost['active'], $returnpost['inactive'], $returnpost['terminated']); //Looking for this person or this phrase
        $returnarray = $ps->GetBarbers($db);
    }
    else if($returnpost['thisfrom'] == "Users")
    {
        $ps->SetUsers($returnpost['txtsearchuser']); //Looking for this person or this phrase
        $returnarray = $ps->GetUsers($db);
    }
    else
    {
        //Schedules
        $ps->SetPersonno($returnpost['txtsearchuser'], $returnpost['isActive'], $realbarber, $returnpost['isTerminated']); //Looking for this person or this phrase
        $returnarray = $ps->GetPerson($db);
    }
    if(!empty($returnarray))
    {
        //file_put_contents("./dodebug/debug.txt", "Not empty \n", FILE_APPEND);
        $ps->SetPersonselect($returnpost['thisfrom'], $returnarray, '');
        $ps->GetPersonselect();
    }
    
}
function SearchUserexistinglist($tempfrom, $thisstr)
{
    global $ps;
    $ps->SetPersonselect($tempfrom, $_SESSION['usersearchlist'], $thisstr);
    $ps->GetPersonselect();
}
function ManageUsers()
{
    ManageSearchmenus('Users');    
}
function ManageBarbers()
{ 
    //file_put_contents("./dodebug/debug.txt", "is admin? "$_SERVER['SERVER_NAME']."\n", FILE_APPEND);
    if($_SESSION['isAdmin'] == true)
    {
        ManageSearchmenus('Barbers');    
    }
    else
    {
        echo "Barber|".$_SESSION['user_recno'];
    }
}
function Main()
{
    global $load_headers;
    $load_headers::Load_Header_Logo(false);?>
    <div id="div_main" class="main-div-dashboard">
        <script type="text/javascript">
            $("body").data('searchguest', '');
        </script>
        <div id="div_main_sub" class="main-div-body-dashboard">
            <div name="div_loader" id="div_loader" class="api-loader-container display-none">
                <img class="payment-loader-img" src="/images/others/loading.gif" />
            </div>
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-admin-left">
                            <div class="main-div-body-admin-header">DashBoard</div>
                            <div style="float: left;"><?php
                                if($_SESSION['isAdmin'] == true || $_SESSION['isDeveloper'] == true)
                                {?>
                                    <div class="div-menu-dashboard" id="div_manageabout" onclick="about(this);">About</div>
                                    <div class="div-menu-dashboard" id="div_manageintro" onclick="introduction(this);">Introduction</div>   
                                    <!--<div class="div-menu-dashboard div-menu-dashboard" id="div_revenues" onclick="addCompany(this);">Analyze Revenues</div>-->
                                    <div class="div-menu-dashboard" id="div_managecompany" onclick="addCompany(this);">Manage Company</div><?php
                                }
                                if($_SESSION['isBarber'] == true || $_SESSION['isAdmin'] == true || $_SESSION['isDeveloper'] == true)
                                {?>   
                                    <div class="div-menu-dashboard" id="div_managebarbers" onclick="manageBarbers(this, <?php echo $_SESSION['user_recno'] ?>, 'Menu');">Manage Barbers</div>
                                    <div class="div-menu-dashboard" id="div_manageuser" onclick="manageUsers(this);">Manage Guests</div>
                                    <div class="div-menu-dashboard" id="div_manageSchedule" onclick="manageSchedule(this);">Manage Schedule</div> 
                                    <div class="div-menu-dashboard" id="div_manageservices" onclick="manageServices(this);">Manage Services</div>                                 
                                    <div class="div-menu-dashboard div-menu-dashboard" id="div_doevent" onclick="doEvent(this);">Create Event</div><?php
                                }?>
                                <div class="div-menu-dashboard div-menu-dashboard" id="div_modifyevent" onclick="showEvent(this);">Show Event</div>
                                <div class="div-menu-dashboard div-menu-dashboard" id="div_history" onclick="showHistory(this);">Service Search</div><!--search active, history, cancelled service and a search.-->
                                <!--<div class="div-menu-dashboard div-menu-dashboard" id="div_trash" onclick="showTrash(this);">Trash</div>-->
                            </div> 
                        </div>
                    </td>
                    <td>
                        <div class="main-div-body-dashboard-right-container" id="main_div_body_dashboard_right_container"></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="main-div-body-dashboard-container-holder" id="main_div_body_history_dashpay_container_holder"></div>
    </div><?php
    $load_headers::Load_Footer();?><?php
}?>