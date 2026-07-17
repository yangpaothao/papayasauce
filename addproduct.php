<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
use PapayasauceClasses\LoadingAnimation;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME');// will get 'localhost'

if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/website_ad583fcd/Common/page.php");
}
else
{
    require_once("./Common/page.php");
}
$db = new PdoClass();
$pc = new PageloaderClass();
$pt = new PromptClass();
$la = new LoadingAnimation();
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
            function validateForm(){
                if($("#txt_name").val() === ""){
                    alert("Product name can't be emptied.");
                    $("#txt_name").focus();
                    return(false);
                }
                if($("#sltcategory").val() === "Select"){
                    alert("Please select a category.");
                    $("#sltcategory").focus();
                    return(false);
                }
                if($("#txt_description").val() === ""){
                    alert("Description can't be emtpied.");
                    $("#txt_description").focus();
                    return(false);
                }
                if($("#txt_price").val() === ""){
                    //If the Discount box is checked, we have to check the other variables to make sure they are appropriate
                    alert("Please entered a price for this product.");
                    $("#txt_price").focus();
                    return(false);
                }   
                if(isNaN($("#txt_price").val())){
                    alert("The price value entered is not a number.");
                    $("#txt_price").focus();
                    return(false);
                }
                if($("#txt_date").val() === ""){
                    alert("Please entered a date.");
                    $("#txt_date").focus()
                    return(false);
                }  
                var hasAttachment = $('input[type="file"]').filter(function() {
                    return this.value !== "";
                }).length > 0;
                if (!hasAttachment) {
                    alert("Please select at least one file.");
                    return(false);
                }
                return(true);
            }
            function submitProduct(){  
                if(validateForm()){
                    $("#div_loader").removeClass("display-none");
                    form_data = new FormData($('#frmproduct')[0]);
                    event.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitProduct',
                        data: form_data,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            //alert(result);
                            $("#div_loader").addClass("display-none");
                            if(result != "Success"){
                                alert(result);
                                preventDefault();
                                return(false);
                            }
                            else{
                                alert("Product added successfully.");
                                window.location.href = "addproduct.php";
                            }
                        }
                    });
                }
                else{
                    event.preventDefault();
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
function SubmitProduct()
{
    global $db, $pt;
    $returnpost = $pt->AnalyzePostsubmit(); //doesn't work for some reason.  
    if(date('Y-m-d', strtotime($returnpost['date'])) >= date('Y-m-d'))
    {
        //We want to check if the name is unique
        $sqlname = "SELECT * FROM products WHERE name = '".$returnpost['name']."'";
        $resultname = $db->PDOMiniquery($sqlname);
        if($db->PDORowcount($resultname) == 0)
        {
            $isDiscounts = false;
            $thistable = "products";
            //file_put_contents("./dodebug/debug.txt", "admin company = ".var_dump($_POST['sltevent_type'])." \n", FILE_APPEND);
            $thisdata = [];
            $thisdata['foreign_users_recno'] = $_SESSION['user_recno']; //Whoever is logged in will be the user and it's recno
            $thisdata['foreign_cat_recno'] = $returnpost['category']; //Whatever the user select as the category
            $thisdata['name'] = $returnpost['name'];
            $thisdata['description'] = $returnpost['description'];
            $thisdata['price'] = number_format($returnpost['price'], 2);
            $thisdata['date'] = date('Y-m-d', strtotime($returnpost['date']));
            $thisrecno = $db ->PDOInsert($thistable, $thisdata);
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
                    $sqlca = "SELECT * FROM category Where recno = ".$returnpost['category'];
                    $resultca = $db->PDOMiniquery($sqlca);
                    foreach($resultca as $rsca)
                    {
                        $thiscategory = $rsca['name'];
                    }
                    $thisdir = "./images/others/products/$thiscategory";
                    if (!file_exists($thisdir)) {
                        mkdir($thisdir, 0777, true);
                    }
                    $thisprodir = "$thisdir/$thisrecno";
                    if (!file_exists($thisprodir)) {
                        mkdir("$thisdir/$thisrecno", 0777, true);
                    }
                    $thisproimgdir = "$thisdir/$thisrecno/large";
                    if (!file_exists($thisproimgdir)) {
                        //if we are here, that must mean this is the first time we are adding this product so we might as well just create the
                        //whole sub categories
                        mkdir("$thisdir/$thisrecno/large", 0777, true);  //will resize to 60 x ratio
                        mkdir("$thisdir/$thisrecno/mini", 0777, true);  //220 x ratio
                        mkdir("$thisdir/$thisrecno/regular", 0777, true); //Will resize to 1400 x ratio
                    }
                    $strattachments = "";  
                    $typeisgood = "";
                    for($i=0;$i<$countfiles;$i++)
                    {
                        $thistempdir = $_FILES["files"]['tmp_name'][$i];
                        //$filename = strtolower($_FILES["files"]['name'][$i]);
                        $filename = $thisfile['name'][$i];
                        $tempfilename = $filename;
                        //file_put_contents("./dodebug/debug.txt", "this filename ".$filename, FILE_APPEND);
                        $pdfMimearray = array('application/pdf', 'application/doc', 'application/docx');
                        
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $thismime = $finfo->file($thisfile['tmp_name'][$i]);
                        
                        
                        //$thismime = mime_content_type($thisfile['tmp_name'][$i]);
                        //$thismime = mime_content_type($_FILES["files"]['tmp_name'][$i]);
                        //file_put_contents("./dodebug/debug.txt", "this mine ".$thismime, FILE_APPEND);
                        //Now that we have the $filename, we can check for the file type and size and all the goodies.
                        $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
                        $detectedType = exif_imagetype($thisfile['tmp_name'][$i]);

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
                                list($thiswidth, $thisheight) = getimagesize($thistempdir);
                                // Calculate new height to maintain aspect ratio

                                //file_put_contents("./dodebug/debug.txt", "width?  $thiswidth \n", FILE_APPEND);
                                //mini -  80(h) x 120(w)
                                //regular - 220 x 360
                                //large - 800 x 1100 

                                //$new_height = intval($new_width / $aspect_ratio);
                                //$new_height = 240;
                                //file_put_contents("./dodebug/debug.txt", "new width? $new_width && new height? $new_height \n", FILE_APPEND);
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
                                $new_width = 1200;                            
                                if($thiswidth >= 120)
                                {
                                    $new_width = 80;  
                                    $new_height = 100;
                                }
                                else
                                {
                                    $new_width = $thiswidth;
                                    $new_height = $thisheight;
                                }
                                $tempminidir = "$thisdir/$thisrecno/mini";
                                $filename = "$tempfilename";
                                //file_put_contents("./dodebug/debug.txt", "thisdir?  $thisdir \n", FILE_APPEND);
                                $pt->ImageHandler($source_image, $tempminidir, $filename, $detectedType, $new_width, $new_height, $thiswidth, $thisheight);
                                if($thiswidth >= 220)
                                {
                                    $new_width = 220;
                                    $new_height = 400;
                                }
                                else
                                {
                                    $new_width = $thiswidth;
                                    $new_height = $thisheight;
                                }
                                $templargedir = "$thisdir/$thisrecno/large";
                                $filename = "$tempfilename";
                                $thislargefilename[] = "$tempfilename";
                                $pt->ImageHandler($source_image, $templargedir, $filename, $detectedType, $new_width, $new_height, $thiswidth, $thisheight);
                                if($thiswidth >= 1200)
                                {
                                    $new_width = 1200;
                                    $aspect_ratio = $new_width / $thisheight;
                                    $new_height = intval($new_width / $aspect_ratio);
                                }
                                else
                                {
                                    $new_width = $thiswidth;
                                    $new_height = $thisheight;
                                }
                                $tempregdir = "$thisdir/$thisrecno/regular";
                                $filename = "$tempfilename";
                                $pt->ImageHandler($source_image, $tempregdir, $filename, $detectedType, $new_width, $new_height, $thiswidth, $thisheight);
                                //file_put_contents("./dodebug/debug.txt", "Success \n", FILE_APPEND);
                            } 
                            else 
                            {
                                $typeisgood = "Bad size.";
                            }
                        }
                        else
                        {
                            $typeisgood = "BAD";
                        }
                    }
                }
                $thistable = "products";
                $thisdata = ['attachment' => implode(",", $thislargefilename), 'attachment_dir' => $thisdir];
                $thiswhere = ['recno' => $thisrecno];
                $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
                if($result == "Success")
                {
                    echo "Success";
                }
                else
                {
                    echo "Failed to update attachment.";
                }
            }
            else
            {
                echo "Failed to insert";
            } 
        }
        else
        {
            echo "Product name already exist.";
        }
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", "Date can't be less than today?  \n", FILE_APPEND);
        echo "Date can't be less than today.";
    } 
}
function ImageHandler($source_image, $thisdir, $filename, $detectedType, $new_width, $new_height, $thiswidth, $thisheight)
{
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
    global $db, $pc, $pt, $la;?>
    <div class="main-div">
        <?php  
            echo $la->SetLoadscreen();
            echo $la->GetLoadscreen();?>
        <div class="addproduct-div-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);?>
            </div>
            <div class="float-left" style="width: 7%;"><?php echo $pc->LoginPanel();?></div>
            <div class="div-content-holder-flex"">
                <form name="frmproduct" id="frmproduct" method="post" enctype="multipart/form-data">
                    <table class="tbl-addproduct float-left" id="tbl-addproduct" style="min-width: 390px;">
                        <tr>
                            <td class="align-right tbl-addproduct-td-label" colspan="2"><div class="align-center addproduct-div-title font-size-2em">Add A New Product</div></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Name:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input" type="text" name="txt_name" id="txt_name" value="" autofocus></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Category:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><?php
                                $pt->SltCategory($db)->GetSelect("slt_category", '', true, false, false, true, false, true);?>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Description:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left">
                                <textarea class="tbl-addproduct-td-input-txtarea" name="txt_description" id="txt_description" style="min-height: 80px; resize: none;"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Price:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input" type="text" name="txt_price" id="txt_price"  value=""></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Active Date:<span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left"><input class="tbl-addproduct-td-input" type="text" name="txt_date" id="txt_date" value="" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022"></td>
                        </tr>
                        <tr>
                            <td class="align-right tbl-addproduct-td-label">Attachments:<button class="add-attachment" id="btn_add_attachment" onclick="addAttachment();" title='Click to add attachment'>+</button><span class="asterisk"> * </span></td>
                            <td class="tbl-addproduct-td-input-container align-left" style="background-color: gray;">
                                <div style="min-height: 180px;">
                                    <div id="div_attachment_container">                                        
                                        <div class="div-attachment-ele" id="div_attachment_ele1">
                                            <span class="span-event-numbered" style="color: white;">1</span>
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
                </form>   
            </div>
            <div class="align-center" style="height: 5%;"><?php echo $pc->Load_Footer();?></div>
        </div>
        

    </div><?php
}