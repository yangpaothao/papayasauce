<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/pdocon.php");
require("./common/classes/PageloaderClass.php");
require("./common/prompt.php");

$load_headers = new PageloaderClass();
$pt = new PROMPT();
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
            var pickedDates = [];
            //need to check for when trying to undo the OFF for all and for individual.
            $(document).ready(function(){
                getUserprofile($("#div_menu_profile")[0]); //Initially we will show this user's profile.
            });
            function profileMenuslt(obj){
                $(".div-menu-profile").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
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
            function setupAuthentication(obj){
                profileboardMenuslt(obj)
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SetupAuthentication', function(result){
                    $("#main_div_body_profile_right_container").html(result);
                });
            }
            function setupQuestionniare(obj){
                profileMenuslt(obj);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SetupQuestionniare', function(result){
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
            function enableAuthentication(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=EnableAuthentication&btntext='+$("#btnauthentication").html(), function(result){
                    if(result == "Disabled")
                    {
                       $("#btnauthentication").html("Disabled")//#1D89D1
                       $("#btnauthentication").css("background-color", "#1D89D1");
                       $("#btnauthentication").prop('title', 'Click to enable.');
                    }
                    else
                    {
                       $("#btnauthentication").html("Enabled")//#1D89D1
                       $("#btnauthentication").css("background-color", "#288331;");
                       $("#btnauthentication").prop('title', 'Click to disable.');
                    }
                });
            }
            function validateAnswers(obj){
                if($(obj).val().length < 3){
                    alert("Answer must be atleast 3 character long.")
                    $(obj).focus();
                    $(obj).select();
                }
            }
            function validatQuestions(obj){
                
                var thisarray = [$("#sltquestion1").val(), $("#sltquestion2").val(), $("#sltquestion3").val()];
                countitem = 0;
                for(i=0; i<thisarray.length; i++)
                {
                    if(thisarray[i] == "Select")
                    {
                        countitem++;
                    }   
                }
                if(countitem < 2)
                {
                    if(thisarray.length !== new Set(thisarray).size)
                    {
                        alert("You can not select the same question twice.  Please check your questions and try again.");
                        $(obj).prop("selectedIndex", 0);
                        return(false);
                    }
                }
            }
            function clearAnswerform(){
                $('#tblquestionniare').find('input[type=text]').val('');
                $('#sltquestion1').prop("selectedIndex", 0);
                $('#sltquestion2').prop("selectedIndex", 0);
                $('#sltquestion3').prop("selectedIndex", 0);
            }
            function editQuestionniaresanswers(obj){
                //First we want to verify the user's password before we allow for the editing or renewing of these questions.
                var thispassword = prompt("Please Confirm your password", "Type your current password");
                if(thispassword == null)
                {
                    return(false);
                }
                else
                {
                    //User entered a password, we will now check this password.
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=validatePassword&txtpassword='+thispassword, function(result){
                        if(result == "Failed"){
                            alert("Wrong password.  Please try again");
                            return(false);
                        }
                    });
                }
            }
            function enableQuestionniareedit(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=NewQuestionniares', function(result){
                    //User now has the option to redo their questions and answers.
                    $("#main_div_body_profile_right_container").html(result);
                });
            }
            function submitQuestionniaresanswers(obj){
                $(obj).hide();
                if($("#txtanswer1").val() == ""){
                    alert("Answer number 1 must not be empty.  Please type in a password and try again.");
                    $("#txtanswer1").focus();
                    $("#txtanswer1").select();
                    return(false);
                }
                if($("#txtanswer2").val() == ""){
                    alert("Answer number 2 must not be empty.  Please type in a password and try again.");
                    $("#txtanswer2").focus();
                    $("#txtanswer2").select();
                    return(false);
                }
                if($("#txtanswer3").val() == ""){
                    alert("Answer number 3 must not be empty.  Please type in a password and try again.");
                    $("#txtanswer3").focus();
                    $("#txtanswer3").select();
                    return(false);
                } //If we made it to here, we are ready to submit the new questions and answer.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitQuestionniaresanswers&sltquestion1='+$("#sltquestion1").val()+'&sltquestion2='+$("#sltquestion2").val()+'&sltquestion3='+$("#sltquestion3").val()+'&txtanswer1='+
                                                       $("#txtanswer1").val()+'&txtanswer2='+$("#txtanswer2").val()+'&txtanswer3='+$("#txtanswer3").val(), function(result){
                    //User now has the option to redo their questions and answers.
                    $("#sltquestion1").prop('disabled', true);
                    $("#txtanswer1").val("**********");
                    $("#txtanswer1").prop('type', "password");
                    $("#txtanswer1").prop('disabled', true);
                    $("#sltquestion2").prop('disabled', true);
                    $("#txtanswer2").val("**********");
                    $("#txtanswer2").prop('type', "password");
                    $("#txtanswer2").prop('disabled', true);
                    $("#sltquestion3").prop('disabled', true);
                    $("#txtanswer3").val("**********");
                    $("#txtanswer3").prop('type', "password");
                    $("#txtanswer3").prop('disabled', true);
                    $("#btnsubmitquestions").hide();
                    $("#btnclearquestions").hide();
                    $("#btneditquestionanswer").show();
                    $("#btnsubmitquestionanswer").show();
                    alert("Successfully setup questionniares.");
                });
            }
            function emptyInputtext(obj){
                $(obj).select();
            }
            function dashboard(obj){
                alert('Dashboard Coming soon!');
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
            function showProfilefileimage(obj){
                //alert($(obj).prop('id'));
                if($(obj).prop('id') == "img_profile"){
                    thisfield = "profile_image";
                    $("#img_profile").addClass("profile-image-bucket-selected");
                    if($("#img_thumbnail").hasClass("profile-image-bucket-selected") == true){
                        $("#img_thumbnail").removeClass("profile-image-bucket-selected");
                    }
                }
                else{
                    //img_thumbnail
                    thisfield = "thumb_nail";
                    $("#img_thumbnail").addClass("profile-image-bucket-selected");
                    if($("#img_profile").hasClass("profile-image-bucket-selected") == true){
                        $("#img_profile").removeClass("profile-image-bucket-selected");
                    }
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowProfilefileimage&thisfield='+thisfield, function(result){
                    //alert(result);
                    //main_div_body_profile_right_container is the container, we will append to it and see it it will take
                    if($("#div_profile_image_container").length > 0){
                        $("#div_profile_image_container").remove();
                    }
                    $("#main_div_body_profile_right_container").append(result);
                });
            }
            function selectImage(obj, thisfield, thisimage){
                //We are updating thisfield, either profile_image or thumb_nail in table attachments, depending on what they clicked in profile.
                //thisimage is the new image that will replace
                
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectImage&thisfield='+thisfield+'&thisimage='+thisimage, function(result){
                    //alert(result);
                    //If no error, we should return the old image so we can manipulate the dom, $thisoldimage
                    getUserprofile($("#div_menu_profile")[0]);
                    if(thisfield == "profile_image"){
                        showProfilefileimage($("#img_profile")[0]);
                    }
                    else{
                        showProfilefileimage($("#thumb_nail")[0]);
                    }
                });
            }
            function profileAttach(obj, thisrecno){
                //thisrecno is the recno in the users table
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ProfileAttach&thisrecno='+thisrecno, function(result){
                    //alert(result);
                    $("#main_div_body_profile_right_container").append(result);
                });
            }   
            function submitProfileattachment(thisrecno){
                if($("#profileattachment").val() != ""){
                    form_data = new FormData($('#frmsubmitattachment')[0]);
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitProfileattachment&thisrecno='+thisrecno,
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
                                $("#div_body_profile_attach_container").remove();
                                alert("Attachment added successfully.");
                                getUserprofile($("#div_menu_profile")[0]);
                            }
                        }
                    });
                }
                else{
                    alert("Please select a file before you attempt to submit.");
                }
                event.preventDefault();
            }
            function cancelProfileattachment(){
                $("#div_body_profile_attach_container").remove();
            }
            function deleteProfileimage(thisimage){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteProfileimage&thisimage='+thisimage, function(result){
                    //alert(result);
                    if(result == "Success"){
                        //If no error, we should return the old image so we can manipulate the dom, $thisoldimage
                        getUserprofile($("#div_menu_profile")[0]);
                        if(thisfield == "profile_image"){
                            showProfilefileimage($("#img_profile")[0]);
                        }
                        else{
                            showProfilefileimage($("#thumb_nail")[0]);
                        }
                    }
                    else{
                        alert("Failed to remove file, this file does not exist or can't be found.");
                        return(false);
                    }
                });
            }
            function paymentSettings(obj){
                profileMenuslt(obj);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=PaymentSettings', function(result){
                    //alert(result);
                    $("#main_div_body_profile_right_container").html(result);
                });
            }
            function imgSlt(obj, barberrecno){
                $(".img-slt-gray").each(function(){
                    $(this).addClass("div-profile-barber-image-container");
                });
                $(obj).removeClass("div-profile-barber-image-container");
                
                let thisArray = [{
                        "this_recno": barberrecno,
                    }];  
                const thisData = JSON.stringify(thisArray);
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=MmgSlt&thisarray="+thisData,
                    type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful
                    $("#img_contain").append(result);
                }).catch(function(error) {
                    alert(error);
                });
            }
            function addCards(obj, barberrecno){
                let thisArray = [{
                        "this_recno": barberrecno,
                    }];  
                const thisData = JSON.stringify(thisArray);
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=AddCards&thisarray="+thisData,
                    type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful
                    //alert(result);

                    window.open('./addccard.php', '_blank');

                    //resultarray = JSON.parse(result);
                    //getPaymentform(resultarray['locationid'], resultarray['appid']);
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
function AddCards()
{
    global $db, $pt;
    $isOkay = false;
    $returnpost = $pt->AnalyzePosts();
    $_SESSION['barberrecno_addcart'] = $returnpost['recno'];
    /*
    $sql = "SELECT address, address2, city, state, zipcode FROM users WHERE recno = ".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        if(is_null($rs['address']))
        {
            $isOkay = true;
        }
        if(is_null($rs['address2']))
        {
            $isOkay = true;
        }
        if(is_null($rs['city']))
        {
            $isOkay = true;
        }
        if(is_null($rs['state']))
        {
            $isOkay = true;
        }
        if(is_null($rs['zipcode']))
        {
            $isOkay = true;
        }
    }
    if($isOkay == true)
    {
        echo "Please go to 'User Profile' and fill out the address before you can save card to file.  We will need your full address.";
    }
    else
    {
        echo "Success";
    }
     
     */
}
function MmgSlt()
{
    global $db, $pt;
    $tempsandpropost = "";
    $_SESSION['isLive'] = false;
    $_SESSION['realsandpro'] = "Sandbox";
    $returnpost = $pt->AnalyzePosts();
    
    if($pt->BarberOnline($returnpost['recno']) == true){
        $tempsandpropost = "_pro";
        $_SESSION['isLive'] = true;
        $_SESSION['realsandpro'] = "Production";
    }
    //file_put_contents("./dodebug/debug.txt", 'What is isLive? '.$_SESSION['isLive'], FILE_APPEND);
    $sql = "SELECT last4, exp_year, exp_month isActive FROM payment_methods WHERE foreign_users_recno = ".$_SESSION['user_recno']." AND foreign_users_barber_recno = ".$returnpost['recno']." AND isActive = true AND isDeleted = false";
    //file_put_contents("./dodebug/debug.txt", 'MmgSlt? '.$sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);?>
    <div id="div_slt_container">
        <div class="display-inline-block width-100-percent"><button class="btn-add-new-card float-left" id="btn_add_cards"  id="btn_add_cards" onclick="addCards(this, <?php echo $returnpost['recno'] ?>);">Add a card</button></div>
        <div id="div_card_data_container"><?php
            if($db->PDORowcount($result) > 0)
            {?>
                <table class="tbl-profile float-left">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Last 4</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <?php
                    $i = 0;
                    foreach($rows as $rs)
                    {
                        $i++;?>
                        <tr>
                            <td class="user-profile-lbl tbl-profile-lbl"><?php echo $i ?></td>
                            <td><?php echo $rs['last4'] ?></td>
                            <td class="<?php echo ($rs['isActive'] == true ? "profile-payment-green-check" : "profile-payment-red-check") ?>></td>
                        </tr><?php
                    }?>
                </table><?php
            }
            else
            {?>
                <div>There is no saved card.</div><?php
            }?>
        </div>
    </div><?php
}
function PaymentSettings()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $sql = "SELECT recno, media_dir, login FROM users WHERE isactive = true and isverified = true AND isBarber = true ORDER BY lastname";
    $result = $db->PDOMiniquery($sql);?>
    <div id="div_profile" style="width: 100%; height: 100%;">
        <div id="img_contain" style="width: 100%;"><?php
            if($db->PDORowcount($result) > 0)
            {
                foreach($result as $rs)
                {
                    //file_put_contents("./dodebug/debug.txt", 'Front imag? '.$thisfrontimage, FILE_APPEND);
                    ?>
                    <div class="float-left position-relative display-inline-block" style="height: 10%;">
                        <div id="div_img_container_<?php echo $rs['recno'] ?>" class="div-profile-barber-image-container cursor-pointer img-slt-gray" onclick="imgSlt(this, <?php echo $rs['recno'] ?>);"></div>
                            <img class="frontimage" src="../images/others/<?php echo $rs['media_dir']?>/avatar/frontimage.png" onerror="this.src='../images/others/<?php echo $rs['media_dir']?>/avatar/frontimage.png'"></a>
                            <span class="span-front-login"><?php echo $rs['login'] ?></span>
                    </div><?php
                }
            }?>
            <div class="display-inline-block width-100-percent align-left font-size-1p5em">
            Select the barber above to see/add payments.
            </div>
        </div>
    </div><?php
}
function DeleteProfileimage()
{
    global $db;
    
    $thisfile = strtolower("./images/others/".$_SESSION['media_dir']."/avatar/".$_POST['thisimage']);
    //file_put_contents("./dodebug/debug.txt", "profile delete image = $thisfile \n", FILE_APPEND);
    if (file_exists($thisfile)) {
        unlink($thisfile);
        echo "Success";
    }
    else
    {
        echo "No Match";
    }
    
}
function SubmitProfileattachment()
{
    global $db, $pt;
    //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
    //Assuming we are here, we want to now handle the upload.
    //First we want to check if './images/others/$_SESSION['media_dir']/logo/ e xist, if not, we create it before we move file into it.
    
    $thisdir = strtolower("./images/others/".$_SESSION['media_dir']."/avatar");
    if (!file_exists($thisdir)) {
        mkdir("./images/others/".$_SESSION['media_dir']."/avatar", 0777, true);
    }
    
    //First we want to get the type of file
    
    //Once we confirmed that it is there after, now we want to move the file or files there and also update the name of the file to the table.
    //$msg = $pt ->UploadFile($thisdir, $_FILES["file"], $thistable, "mainlogo", $_POST['thisrecno'], NULL, 'company_info');
    $countfiles = count($_FILES["file"]['name']); 
    $typeisgood = "";

    for($i=0;$i<$countfiles;$i++)
    {
        $thistempdir = $_FILES["file"]['tmp_name'][$i];
        $filename = strtolower($_FILES["file"]['name'][$i]);
        //file_put_contents('./dodebug/debug.txt', "profile filename - :".$_FILES["file"]['tmp_name'][$i]." \n", FILE_APPEND);
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
            $image_info = getimagesize($thistempdir);
            if ($image_info !== false) {
                $thiswidth = $image_info[0];
                $thisheight = $image_info[1];
                //$thistype = $image_info[2]; // Numeric constant for image type (e.g., IMAGETYPE_JPEG)
                //$thisattr = $image_info[3]; // String containing width="X" height="Y" for HTML img tag

               // echo "Image Width: " . $width . " pixels<br>";
               // echo "Image Height: " . $height . " pixels<br>";
                //echo "Image Type: " . image_type_to_extension($type) . "<br>"; // Convert numeric type to extension
                //echo "HTML Attributes: " . $attr . "<br>";
                
                //height: 190px;
                //width: 160px;
                $new_width = 160;
                if($thiswidth > 160)
                {
                    list($thiswidth, $thisheight) = getimagesize($thistempdir);

                    // Calculate new height to maintain aspect ratio
                    $aspect_ratio = $thiswidth / $thisheight;
                    $new_height = intval($new_width / $aspect_ratio);
              
                    switch($detectedType)
                    {
                        case IMAGETYPE_PNG:
                            $source_image = imagecreatefrompng($thistempdir);
                            break;
                        case IMAGETYPE_JPEG:
                            $source_image = imagecreatefromjpeg($thistempdir);

                            break;
                        case IMAGETYPE_GIF:
                            $source_image = imagecreatefromgif($thistempdir);
                            break;
                        default:
                            $source_image = imagecreatefrompng($thistempdir);
                            break;
                    }
                    $destination_image = imagecreatetruecolor($new_width, $new_height);

                    imagecopyresampled($destination_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $thiswidth, $thisheight);

                    // Save the resized image (with quality 90)
                    switch($detectedType)
                    {
                        case IMAGETYPE_PNG:
                            imagepng($destination_image, "$thisdir/$filename", 9);
                            break;
                        case IMAGETYPE_JPEG:
                            imagejpeg($destination_image, "$thisdir/$filename", 90);

                            break;
                        case IMAGETYPE_GIF:
                            imagegif($destination_image, "$thisdir/$filename", 90);
                            break;
                        default:
                            imagepng($destination_image, "$thisdir/$filename", 90);
                            break;
                    }

                }
                else
                {
                    move_uploaded_file($_FILES["file"]['tmp_name'][$i],"$thisdir/$filename");
                }
                echo "Success";
            } 
            else 
            {
                $typeisgood = "Bad size.";
            }
            
        }
        else
        {
            echo "Bad file type.  File type must be PNG, JPEG, GIF, and OR PDF.";
        }
    }
    
}
function ProfileAttach()
{
    //We want to build a div with a form in it for submitting attachment
    ?>
    <div class="div-body-profile-attach-container" id="div_body_profile_attach_container">
        <div class="div-body-profile-attach-sub-container">
            <form name="frmsubmitattachment" id="frmsubmitattachment" enctype="multipart/form-data" method="post">
                <div class="align-left" style="width: 300px; height: 100px; background-color: gray;">
                    <div>Upload Attachment</div>
                    <div><input class="event-attachments" type="file" name="file[]" id="profileattachment" /></div>
                    <div class="float-left">jpeg, gif, png ONLY</div><br/>
                    <button id="btn_submit" name="btn_submit" onclick="submitProfileattachment(<?php echo $_POST['thisrecno'] ?>);">Submit</button>
                    <button id=""btn_cancel" name=""btn_cancel" id=""btn_cancel" onclick="cancelProfileattachment();">Cancel</button>
                </div>
            </form>
        </div>
        
    </div><?php
}
function SelectImage()
{
    global $db;
    $thisoldimage = "";
    $sql = "SELECT * FROM attachments WHERE foreign_ur = ".$_SESSION['user_recno']." AND name='".$_POST['thisfield']."'";
    //file_put_contents('./dodebug/debug.txt', "profile selectimage sql - :$sql \n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        if($_POST['thisfield'] == "profile_image")
        {
            $thisoldimage = $rs['file'];
        }
        if($_POST['thisfield'] == "thumb_nail")
        {
            $thisoldimage = $rs['file'];
        }    
    }
    //file_put_contents('./dodebug/debug.txt', "profile selectimage - :".$_POST['thisimage']." and this field: ".$_POST['thisfield']." \n", FILE_APPEND);
    $thistable = "attachments";
    $thisdata = ['file' => $_POST['thisimage']];
    $thiswhere = ['foreign_ur' => $_SESSION['user_recno'], 'name' => $_POST['thisfield']];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!isset($result))
    {
        echo "Failed";
    }
    else
    {
        echo "$thisoldimage";
    }
}
function ShowProfilefileimage()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thismediadir = "";
    $thisfrontimage = "";
    $thisthumbnail = "";
    $usethiscssborder = "";
    $usethistitle = "";
    $usebtncancel = "";
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
    <div class="div-profile-image-container" id="div_profile_image_container"><?php
        foreach(glob($thisdir) as $file)
        {
            if(!is_dir($file)) 
            {
                //basename($file) will be name.filetype, ex: name.png
                //file_put_contents('./dodebug/debug.txt', 'profile state: '.basename($file).' == '.$thisfrontimage.' || '.basename($file).' == '.$thisthumbnail.' \n', FILE_APPEND);
                $usethiscssborder = "";
                $usethistitle = "";
                $usebtncancel = '<button class="btn-del-profile-img float-right cursor-pointer" name="btn_del_profile_img" id="btn_del_profile_img" title="Click to delete" onclick="deleteProfileimage(\''.basename($file).'\');">X</button>';
                if(strtolower(basename($file)) == strtolower($thisfrontimage) || strtolower(basename($file)) == strtolower($thisthumbnail))
                {
                    $usethiscssborder = "profile-image-bucket-selected";
                    $usethistitle = "Selected";
                    $usebtncancel = '';
                }?>
                <div>
                    <?php echo $usebtncancel ?>
                    <img class="profilebucketimage <?php echo $usethiscssborder ?>" title="<?php echo $usethistitle ?>" onclick="selectImage(this, '<?php echo $_POST['thisfield'] ?>', '<?php echo basename($file, basename($file)) ?>');" src="<?php echo $thispath ?>/<?php echo basename($file) ?>" onerror="this.src='<?php echo $thispath ?>/defaultimage.png'"></a>
                    <br/><span class="profile-span-image-disc"><?php echo basename($file) ?></span>
                </div><?php
            }
        }?>
    </div><?php
}
function UpdateProfile()
{
    global $db, $pt; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $thisstate = "";
    $thisrealval = $_POST['thisval'];
    if($_POST['thisfield'] == "state")
    {
        $thisrealval = $pt ->GetStates($_POST['thisval']);
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
function SubmitQuestionniaresanswers()
{
    global $db, $load_headers; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    
    $thistable = "employee_master";
    $thisdata = array('question1' => $_POST['sltquestion1'], 'question2' => $_POST['sltquestion2'], 'question3' => $_POST['sltquestion3'], 
                      'answer1' => $load_headers->Hash_Me_Questionniare_Answers($_POST['txtanswer1']), 
                      'answer2' => $load_headers->Hash_Me_Questionniare_Answers($_POST['txtanswer2']), 
                      'answer3' => $load_headers->Hash_Me_Questionniare_Answers($_POST['txtanswer3']));
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
function EnableAuthentication()
{
    global $db; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $temptext = $_POST['btntext']; //Enabled or Disabled
    $thistable = "employee_master";
    $istemptext = false;
    if($temptext == "Disabled")
    {
        $istemptext = true;
    }
    $thisdata = array('isauthenticated' => $istemptext);
    $thiswhere = array('recno' => $_SESSION['user_recno']);
    $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if($temptext == "Disabled")
    {
        echo "Enabled";
    }
    else
    {
        echo "Disabled";
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
                    <tr>
                        <td class="user-profile-lbl tbl-profile-lbl">Upload Attachment: </td>
                        <td class="user-profile-input" id="tduploadattachment"><img class="cursor-pointer" style="height: 50px;" src="./images/others/dummyattach.png" onclick="profileAttach(this, <?php echo $rs['recno'] ?>);"/><div class="align-left">jpeg, gif, png ONLY</div></td>
                </tr><?php 
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
            <img class="cursor-pointer profile-img-size" title="Click to replace profile image." id="img_profile" onclick="showProfilefileimage(this);" src="./images/others/<?php echo $thismedia ?>/avatar/<?php echo $profileimage ?>" onerror="this.onerror=null; this.src='./images/others/<?php echo $thismedia ?>/avatar/defaultimage.png'"><br />
            <span style="font-size: .7em;">Profile Image:<br /> <span id="span_profile_image"><?php echo $profileimage ?></span></span>
        </div>
        <div class="float-left">
            <img class="cursor-pointer profile-img-size" title="Click to replace thumbnail image." id="img_thumbnail"  onclick="showProfilefileimage(this);" src="./images/others/<?php echo $thismedia ?>/avatar/<?php echo $thumbnail ?>" onerror="this.onerror=null; this.src='./images/others/<?php echo $thismedia ?>/avatar/defaultimage.png'"><br />
            <span style="font-size: .7em;">Thumbnail Image::<br /> <span id="span_profile_thumbnail"><?php echo $thumbnail ?></span></span>
        </div>
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
function SetupAuthentication()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $bgcolor = "";
    $thisstatus = "";
    $thistitle = "";
    $thistable = "employee_master";
    $thisfields = array('email', 'isauthenticated');
    $thiswhere = array('recno' => $_SESSION['user_recno']);
    
    $rows = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>
    <table class="tbl-profile"><?php
        foreach($rows as $rs)
        {
            if($rs['isauthenticated'] == true)
            {
                $bgcolor = "#288331;";
                $thisstatus = "Enabled";
                $thistitle = "Click to disable.";
            }
            else
            {
                $bgcolor = "";
                $thisstatus = "Disabled";
                $thistitle = "Click to enable.";
            }?>  
            <tr><td class="user-profile-lbl tbl-profile-lbl">Email:</td><td><input class="user-profile-input" type="text" id="txtemail" name="txtemail" value="<?= $rs['email'] ?>" readonly="readonly"/></td></tr>
            <tr><td class="user-profile-lbl tbl-profile-lbl">Two Steps Authentication:</td><td><button type="button" name="btnauthentication" id="btnauthentication" title="<?= $thistitle ?>" style=" float: left; background-color: <?= $bgcolor ?>;" onclick="enableAuthentication();"><?= $thisstatus ?></button></td></tr><?php 
        }?>
    </table><?php
}
function SetupQuestionniare()
{
    global $db;

    $sql = "SELECT qn.recno, qn.question FROM employee_master em INNER JOIN questionniares qn ON em.question1 = qn.recno OR em.question2 = qn.recno OR em.question3 = qn.recno   
            WHERE em.recno = ".$_SESSION['user_recno']." ORDER BY qn.question";
    $rows = $db->PDOMiniquery($sql);
    $temparray = Array();
                                      
    if($rows->rowCount() > 0)
    {
        foreach($rows as $rs)
        {
            if(!is_null($rs['question']))
            {
                $temparray[] = $rs['recno'];
            }
        }
        NewQuestionniares($temparray);
    }
    else
    {
        NewQuestionniares();
    }
}
function NewQuestionniares($datarows=[])
{
    global $db; 
    $isdisabled = "";
    if(count($datarows) > 0)
    {
        $isdisabled = 'disabled="disabled"';
    }
    //$datarows is a single array
    $thistable = "questionniares";
    $thisfields = array('recno', 'question');
    $thisorderby = array("question");
    //function PDOQuery($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null, $ons=null)
    $rows = $db->PDOQuery($thistable, $thisfields, null, $thisorderby);?>
    <table id="tblquestionniare" class="tbl-profile"><?php
        $j=0;
        for($i=1; $i<4; $i++)
        {?>
            <tr>
                <td class="user-profile-lbl tbl-profile-lbl">Question <?= $i ?>:</td>
                <td>
                    <select class="user-profile-input required" id="sltquestion<?= $i ?>" name="sltquestion<?= $i ?>" onchange="validatQuestions(this);" <?=$isdisabled?>>
                        <option value="Select">-Select a question from list-</option><?php
                            foreach($rows as $rs)
                            {?>
                                <option value="<?= $rs['recno'] ?>" <?php
                                if(count($datarows) > 0)
                                {
                                    if($datarows[$j] == $rs['recno'])
                                    {?>
                                        selected<?php
                                    }
                                }?>
                                ><?= $rs['question']?></option><?php
                            }
                            $j++;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="user-profile-lbl tbl-profile-lbl">Answer <?= $i ?>:</td>
                <td><?php
                    if(count($datarows) == 0)
                    {?>
                        <input type="text" class="user-profile-input required" id="txtanswer<?= $i ?>" name="txtanswer<?= $i ?>" value="" onchange="validateAnswers(this);"><?php
                    }
                    else
                    {?>
                        <input type="text" class="user-profile-input required" id="txtanswer<?= $i ?>" name="txtanswer<?= $i ?>" value="**********" onchange="validateAnswers(this);" onclick="emptyInputtext(this);" disabled="disabled"><?php
                    }?>
                </td>
            </tr><?php 
        }
        if(count($datarows) == 0)
        {?>
            <tr><td colspan="2" style="width: 100%; text-align: center;">
                <button id="btnsubmitquestions" type="button" onclick="submitQuestionniaresanswers();">Submit</button>
                <button id="btnclearquestions" type="button" onclick="clearAnswerform();">Clear</button>
                <button style="display: none;" type="button"  name="btneditquestionanswer"  id="btneditquestionanswer" onclick="editQuestionniaresanswers(this);">Click To Edit</button>
                <button style="display: none;" type="button" name="btnsubmitquestionanswer" id="btnsubmitquestionanswer" onclick="submitQuestionniaresanswers(this);">Submit</button>
            </tr><?php
        }
        else
        {?>
           <tr><td class="tbl-profile-lbl" colspan="2" style="width: 100%; text-align: center;">
                <button type="button"  name="btneditquestionanswer"  id="btneditquestionanswer" onclick="editQuestionniaresanswers(this);">Click To Edit</button>
                <button style="display: none;" type="button" name="btnsubmitquestionanswer" id="btnsubmitquestionanswer" onclick="submitQuestionniaresanswers(this);">Submit</button>
            </tr><?php 
        }?>
    </table><?php
}
function Main()
{
    global $load_headers;?><?php
        //We are sending false into the load_header_logo(false) because we do not want the logo to show, just the other stuffs.
        $load_headers::Load_Header_Logo(false);?>
    <div class="main-div">
        <div class="main-div-body-profile">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-profile-left" id="main_div_body_profile_left">
                            <div class="main-div-body-profile-header">Profile</div>
                            <div style="float: left;">                                   
                                <div class="div-menu-profile" id="div_menu_profile" onclick="getUserprofile(this);">User Profile</div>
                                <div class="div-menu-profile" id="div_menu_password" onclick="changePassword(this);">Change Password</div>
                                <div class="div-menu-profile" id="div_modifyevent" onclick="paymentSettings(this);">Payment/Settings</div>
                                <!--<div class="div-menu-profile" id="div_menu_profile" onclick="setupAuthentication(this);">Setup Two Steps Authentication</div>-->
                            </div>
                        </div>
                    </td>
                    <td>
                        <div id="main_div_body_profile_right_container" class="main-div-body-profile-right-container"></div>   
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}?>