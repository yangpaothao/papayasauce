<?php
require __DIR__ . '/common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');// will get 'localhost'

if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/common/page.php");
}
else
{
    require_once("./common/page.php");
}
$db = new PdoClass();
$pc = new PageloaderClass();
$pt = new PromptClass();
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
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $pc->Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            function addAttachment(){
                //We have to go through the list of attachments to see how many we already have and then add it after it.
                isAttachment = true;
                if($(".event-attachments").length >= 20){
                    isAttachment = false;
                    alert("The limit for upload is 20.");
                }
                if(isAttachment == true){
                    $(".event-attachments").each(function(){
                        thisattachmentid = $(this).prop('id');
                    });
                    let thisArray = [{
                        "this_thisattachmentid": thisattachmentid
                    }];
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddAttachments&thisarray='+JSON.stringify(thisArray), function(result){
                        //alert(result);
                        $('#div_attachment_container').append(result);
                        $("#btn_remove_attachment1").show();
                    });
                }
                event.preventDefault();
            }
            function removeAttachment(obj){
                //alert($(obj).prop('id'));
                if($(".span-event-numbered").length > 1){
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
                     if($(".remove-attachment").length == 1){
                        $(".remove-attachment").each(function(){
                            $(this).hide();
                        });
                    }
                }
                event.preventDefault();
            }
            function submitProduct(){
                isFalse = false;
                isAttachment = false;
                if(validateText($("#txt")))
                
                if($("#txt_name").val() == "Select"){
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
        </script>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
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
function AddAttachments()
{
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    $thisattachmentno = substr($returnpost['thisattachmentid'], -1);
    //file_put_contents("./dodebug/debug.txt", "new no: ".$returnpost['thisattachmentid']." \n", FILE_APPEND);
    $thisattachmentno++;
    $newattachment = "attachment$thisattachmentno";
    $newbtn_remove_attachment = "btn_remove_attachment$thisattachmentno";
    $newdiv_attachment_ele = "div_attachment_ele$thisattachmentno";?>
    <div class="div-attachment-ele" id="<?php echo $newdiv_attachment_ele ?>"><span class="span-event-numbered"><?php echo $thisattachmentno ?></span>&nbsp;<input class="event-attachments" type="file" name="files[]" id="<?php echo $newattachment ?>" />
        <button class="remove-attachment" id="<?php echo $newbtn_remove_attachment ?>" name="<?php echo $newbtn_remove_attachment ?>" onclick="removeAttachment(this);" title="Click to remove attachment">-</button>
    </div><?php    
}
function SelectedProduct()
{
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    $_SESSION['SELECTED_PRODUCT_RECNO'] = $returnpost['recno'];
    echo "Success";
}
function Main()
{
    global $db, $pc, $pt?>
    <div class="main-div">
        <div class="addproduct-div-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);?>
            </div>
            <div class="float-left" style="width: 7%;"><?php echo $pc->LoginPanel();?></div>
            <form name="frmcompany" id="frmcompany" method="post" enctype="multipart/form-data">
                <div class="div-content-holder-flex">
                    <table class="tbl-addproduct" id="tbl-addproduct" style="margin-top: 20px;">
                        <tr>
                            <td class="align-right tbl-addproduct-td-label" colspan="2"><div class="align-center addproduct-div-title font-size-2em">Add A New Product</div></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Name:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input" type="text' name="txt_name" id="txt_name" onblur="validateText(this);" value="" autofocus></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Category:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><?php
                                    $pt->SltCategory($db)->GetSelect("sltcategory", '', true, false, false, true, false, true);?>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Description:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input-txtarea" type="textarea" name="txt_description" id="txt_description" onblur="validateText(this);"  rows="5"></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Price:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input" type="text' name="txt_name" id="txt_name" onblur="validateNum(this);"  value=""></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Active Date:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input" type="text' name="txt_date" id="txt_date" value="" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022"></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Attachments:<button class="add-attachment" id="btn_add_attachment" onclick="addAttachment();" title='Click to add attachment'>+</button><span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left" style="background-color: gray;">
                                <div style="min-height: 200px;">
                                    <div id="div_attachment_container">                                        
                                        <div class="div-attachment-ele" id="div_attachment_ele1">
                                            <span class="span-event-numbered">1</span>
                                            <input class="event-attachments" type="file" name="files[]" id="attachments1" />
                                            <button class="remove-attachment display-none" id="btn_remove_attachment1" name="btn_remove_attachment1" onclick="removeAttachment(this);" title='Click to remove attachment'>-</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="align-center"><button name="btnsubmit" id="btnsubmit" onclick="submitProduct();">Submit</button></td>
                        </tr>
                    </table>
                </div>
            </form>   
            <div class="align-center" style="height: 5%;"><?php echo $pc->Load_Footer();?></div>
        </div>
        

    </div><?php
}