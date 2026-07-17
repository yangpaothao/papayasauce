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
            function addAttachment(catrecno){
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
                    //alert(thisattachmentid);
                    let thisArray = [{
                        "this_thisattachmentid": thisattachmentid,
                        "this_thiscatrecno": catrecno
                    }];
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddAttachments&thisarray='+JSON.stringify(thisArray), function(result){
                        //alert(result);
                        $('#div_attachment_container').append(result);
                        $("#btn_remove_attachment1").show();
                    });
                }
                event.preventDefault();
            }
            function removeAttachment(obj, thisattachment, thisrecno){
                //alert($(obj).prop('id'));
                
                if($(".span-event-numbered").length > 1){
                    if(thisrecno != ""){
                        let thisArray = [{
                            "this_thisatt": thisattachment,
                            "this_thisrecno": thisrecno
                        }];  
                        const thisData = JSON.stringify(thisArray);
                        $.ajax({
                            url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=RemoveAttachment&thisarray="+thisData,
                            type: "POST"
                        }).then(function(result) {
                            // Code here will execute *after* the AJAX request is successful
                            window.location.href = "manageproduct.php";
                        }).catch(function(error) {
                            alert(error);
                            });
                    }

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
                else{
                    alert("A product must have at least 1 attachment to be a valid product.")
                    return(false);
                }
                event.preventDefault();
            }
            function mgmProattach(obj, catrecno){
                let thisArray = [{
                        "this_thiscatrecno": catrecno
                    }];  
                const thisData = JSON.stringify(thisArray);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=MgmProattach&thisarray='+thisData, function(result){
                    //alert(result);
                    $("#div_content_holder_flex").append(result);
                });
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
                                window.location.href = "manageproduct.php";
                            }
                        }
                    });
                }
                else{
                    event.preventDefault();
                }
            }
            function updateModpro(obj){
                let thisArray = [{
                    "this_field": $(obj).prop('id'),
                    "this_val": $(obj).val()
                }];
                const thisData = JSON.stringify(thisArray);
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=UpdateModPro&thisarray="+thisData,
                    type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful
                    if(result == "Success"){
                        alert("Update Successfully");
                    }
                    else{
                        alert("Failed to Update Successfully");
                    }
                }).catch(function(error) {
                    alert(error);
                });
            }
            function cancelMgmattachment(){
                $("#div_body_mgm_attach_container").remove();
            }
            function submitMgmattachment(thisrecno){
            if($("#mgmattachment").val() != ""){
                $("#div_loader").removeClass("display-none");
                form_data = new FormData($('#frmsubmitattachment')[0]);
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitMgmattachment',
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        $("#div_loader").addClass("display-none");
                        if(result != "Success"){
                            alert(result);
                            preventDefault();
                            return(false);
                        }
                        else{
                            alert("Attachment added successfully.");
                            window.location.href = window.location.href;
                        }
                    }
                });
            }
            else{
                alert("Please select a file before you attempt to submit.");
            }
            event.preventDefault();
        }
        function viewFile(thisatt){
            $("#div_loader").removeClass("display-none");
            let thisArray = [{
                "this_thisatt": thisatt
            }];
            const thisData = JSON.stringify(thisArray);
            $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=ViewFile&thisarray="+thisData,
                type: "POST"
            }).then(function(result) {
                $("#div_loader").addClass("display-none");
                // Code here will execute *after* the AJAX request is successful
                if(result != "Failed"){
                    if($("#div_view_image").length > 0){
                        $("#div_view_image").remove();
                    }
                    $("#div_content_holder_flex").append(result);
                }
                else{
                    alert("File is corrupted or failed to load.");
                }
            }).catch(function(error) {
                alert(error);
            });
        }
        function selectMainimage(obj, thisrecno, thisatt){
            $(".slt-image").each(function(){
                $(this).removeClass("mgm-slt-image")
            });
            $(obj).addClass("mgm-slt-image");
            let thisArray = [{
                "this_thisrecno": thisrecno,
                "this_thisatt": thisatt
            }];
            const thisData = JSON.stringify(thisArray);
            $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=SelectMainimage&thisarray="+thisData,
                type: "POST"
            }).then(function(result) {
                // Code here will execute *after* the AJAX request is successful
                alert(result);
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
function SelectMainimage()
{
    global $db, $pt;
    $returnpost = $pt->AnalyzePosts();
    $thistable = "products";
    $thiswhere = ['recno' => $returnpost['thisrecno']];
    $thisdata = ['slt_attachment' => $returnpost['thisatt']];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    if($result == "Success")
    {
        echo "Image Updated Successfully";
    }
    else
    {
        echo "Faled to update image";
    }
}
function ViewFile()
{
    global $db, $pt;
    $returnpost = $pt->AnalyzePosts();

    $sql = "SELECT attachment_dir FROM products WHERE recno = ".$_SESSION['thisproduct_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thisdir = $rs['attachment_dir'];
        
    }?>
    <div class="mgmproduct-viewfile" id="div_view_image">
        <img src='<?php echo $thisdir."/".$_SESSION['thisproduct_recno']."/large/".$returnpost['thisatt'] ?>' onerror="this.onerror=null;this.src='./images/others/default.png">
    </div><?php
}
function SubmitMgmattachment()
{
    global $db, $pt;
    $realcat = "";
    $thisrecno = $_SESSION['thisproduct_recno'];
    //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
    //Assuming we are here, we want to now handle the upload.
    //First we want to check if './images/others/$_SESSION['media_dir']/logo/ e xist, if not, we create it before we move file into it.
    //$_SESSION['CATRECNO'], we will need to get the name of this catrecno which we declard along the way here.
    $sql = "SELECT * FROM category WHERE recno = ".$_SESSION['CATRECNO'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $realcat = $rs['name'];
    }
    if(count(array_filter(($_FILES["file"]["name"]))))
    {
        $thisfile = $_FILES["file"];
        $thisfield = "attachment";
        $countfiles = count($thisfile["name"]); 

        $thisdir = "./images/others/products/$realcat";
        if (!file_exists($thisdir)) {
            mkdir($thisdir, 0777, true);
        }
        $thisprodir = "$thisdir/".$thisrecno;
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
            $thistempdir = $_FILES["file"]['tmp_name'][$i];
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
                    //$thisdir = "./images/others/products/$realcat";
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
    $thiswhere = ['recno' => $thisrecno];
    
    $thisdata = ['attachment' => implode(",", $thislargefilename)];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno'], true);
    
    $thisdata = ['attachment_dir' => $thisdir];
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
function MgmProattach()
{?>
    <div class="div-mgm-profile-attach-container" id="div_body_mgm_attach_container">
        <div class="div-body-profile-attach-sub-container">
            <form name="frmsubmitattachment" id="frmsubmitattachment" enctype="multipart/form-data" method="post">
                <div class="align-left" style="width: 100%; height: 100px; background-color: gray; color: white;">
                    <div><input class="event-attachments" type="file" name="file[]" id="mgmattachment" /></div>
                    <div class="float-left">jpeg, gif, png ONLY</div><br/>
                    <button id="btn_submit" name="btn_submit" onclick="submitMgmattachment();">Submit</button>
                    <button id=""btn_cancel" name=""btn_cancel" id=""btn_cancel" onclick="cancelMgmattachment();">Cancel</button>
                </div>
            </form>
        </div>
    </div><?php
}
function UpdateModPro()
{
    global $pt, $db;
    $thistable = "products";
    $thisdata = [];
    $returnpost = $pt->AnalyzePosts();
    //file_put_contents("./dodebug/debug.txt", $returnpost['field']."==>".$returnpost['val'], FILE_APPEND);
    if($returnpost['field'] == "date")
    {
        $returnpost['val'] = date("Y-m-d", strtotime($returnpost['val']));
    }
    $thisdata = [$returnpost['field'] => $returnpost['val']];
    $thiswhere = ['recno' => $_SESSION['thisproduct_recno']];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    if($result == "Success")
    {
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function RemoveAttachment()
{
    global $db, $pt;
    
    $returnpost = $pt->AnalyzePosts();
    //file_put_contents("./dodebug/debug.txt", "admin company here: ".$_FILES['thisfile']["name"]." \n", FILE_APPEND);
    $thistable = "products";
    
    //First we have to get the attachment which we want to remove
    $sql = "SELECT attachment, attachment_dir FROM $thistable WHERE recno = ".$returnpost['thisrecno'];
    //file_put_contents("./dodebug/debug.txt", "sql : ".$sql." \n", FILE_APPEND);
    $rows = $db->PDOMiniquery($sql);
    $newstr = "";
    foreach($rows as $rs)
    {
        $explodeattachment = explode(',', $rs['attachment']);
        if(($key = array_search($returnpost['thisatt'], $explodeattachment)) !== false)
        {
            unset($explodeattachment[$key]);
            //Now since we remove this item from the array, we gotta remove the actual attachment from our system
            unlink($rs['attachment_dir']."/".$returnpost['thisrecno']."/large/".$returnpost['thisatt']);
            unlink($rs['attachment_dir']."/".$returnpost['thisrecno']."/mini/".$returnpost['thisatt']);
            unlink($rs['attachment_dir']."/".$returnpost['thisrecno']."/regular/".$returnpost['thisatt']);
        }
        $explodeattachment = array_values($explodeattachment);
        
    }
    $thisstr = implode(',', $explodeattachment);
    $thisdata = ['attachment' => $thisstr];
    
    $thiswhere = ['recno' => $returnpost['thisrecno']];
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
    $_SESSION['CATRECNO'] = $returnpost['thiscatrecno'];
    //file_put_contents("./dodebug/debug.txt", "new no: ".$returnpost['thisattachmentid']." \n", FILE_APPEND);
    $thisattachmentno++;
    $newattachment = "attachment$thisattachmentno";
    $newbtn_remove_attachment = "btn_remove_attachment$thisattachmentno";
    $newdiv_attachment_ele = "div_attachment_ele$thisattachmentno";?>
    <div class="div-attachment-ele float-left" id="<?php echo $newdiv_attachment_ele ?>">
        <span class="span-event-numbered float-left event-attachments" id="attachments<?php echo $thisattachmentno ?>" style="color: white;"><?php echo $thisattachmentno ?></span><img class="cursor-pointer float-left" style="height: 50px; width: 50%; min-width: 220px;" src="./images/others/dummyattach.png" onclick="mgmProattach(this);"/>
        <button class="remove-attachment float-left" id="<?php echo $newbtn_remove_attachment ?>" name="<?php echo $newbtn_remove_attachment ?>" onclick="removeAttachment(this, '', '');" title="Click to remove attachment">-</button>
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
    global $db, $pc, $pt, $la;
    $sltimageclass = "";?>
    <div class="main-div">
        <?php  
            echo $la->SetLoadscreen();
            echo $la->GetLoadscreen();
            $thisfunc = 'onchange="updateModpro(this);"';
            $sql = "SELECT * FROM products WHERE recno = ".$_SESSION['thisproduct_recno'];
            //file_put_contents("./dodebug/debug.txt", 'sql? '.$sql, FILE_APPEND);
            $result = $db->PDOMiniquery($sql);?>
            <div class="mgmproduct-div-container">
                <div class="main-logo float-left">
                    <?php echo $pc->LoadLogo($db);?>
                </div>
                <div class="float-left" style="width: 7%; display: block;"><?php echo $pc->LoginPanel();?></div>
                <div class="mgm-div-content-holder-flex" id="div_content_holder_flex"><?php
                    if($db ->PDORowcount($result) > 0)
                    {
                        $i = 0;
                        foreach($result as $rs)
                        {
                            $i++;?>
                            <table class="tbl-mgmproduct " id="tbl-mgmproduct" style="min-width: 390px; height: 98%; margin-top: 10px;">
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label" colspan="2"><div class="align-center addproduct-div-title font-size-2em">Modify This Product</div></td>
                                </tr>
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label">Name:<span class="asterisk"> * </span></td>
                                    <td class="tbl-mgmproduct-td-input-container align-left"><input class="tbl-mgmproduct-td-input" type="text" name="name" id="name" <?php echo $thisfunc ?> value="<?php echo $rs['name'] ?>" autofocus></td>
                                </tr>
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label">Category:<span class="asterisk"> * </span></td>
                                    <td class="tbl-mgmproduct-td-input-container align-left"><?php
                                        $pt->SltCategory($db)->GetSelect("category", $rs['foreign_cat_recno'], true, false, 'updateModpro(this)', true, false, true);?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label">Description:<span class="asterisk"> * </span></td>
                                    <td class="tbl-mgmproduct-td-input-container align-left">
                                        <textarea class="tbl-mgmproduct-td-input-txtarea" name="description" id="description" <?php echo $thisfunc ?> style="min-height: 80px; resize: none;"><?php echo $rs['description'] ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label">Price:<span class="asterisk"> * </span></td>
                                    <td class="tbl-mgmproduct-td-input-container align-left"><input class="tbl-mgmproduct-td-input" type="text" name="price" id="price" <?php echo $thisfunc ?> value="<?php echo $rs['price'] ?>"></td>
                                </tr>
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label">Active Date:<span class="asterisk"> * </span></td>
                                    <td class="tbl-mgmproduct-td-input-container align-left"><input class="tbl-mgmproduct-td-input" type="text" name="date" id="date" <?php echo $thisfunc ?> value="<?php echo date('m/d/Y', strtotime($rs['date'])) ?>" onfocus="getJDate(this, false);"  placeholder="dd/mm/yyyy ex: 01/22/2022"></td>
                                </tr>
                                <tr>
                                    <td class="align-right tbl-mgmproduct-td-label">Attachments:<button class="add-attachment" id="btn_add_attachment" onclick="addAttachment('<?php echo $rs['foreign_cat_recno'] ?>');" title='Click to add attachment'>+</button><span class="asterisk"> * </span></td>
                                    <td class="tbl-mgmproduct-td-input-container align-left" style="background-color: gray;">
                                        <div style="min-height: 100px; height: 140px; overflow-y: auto;">
                                            <div id="div_attachment_container"><?php
                                                $i=0;
                                                if(!is_null($rs['attachment']))
                                                {
                                                    $explodeattach = explode(",", $rs['attachment']);
                                                    foreach($explodeattach as $att)
                                                    {
                                                        $i++;?>
                                                        <div class="div-attachment-ele cursor-pointer" id="div_attachment_ele<?php echo $i ?>" style="display: inline-block; width: 98%;">
                                                            <span class="span-event-numbered float-left event-attachments" id="attachments<?php echo $i ?>" style="color: white;"><?php echo $i ?></span>
                                                            <div class="float-left" onclick="viewFile('<?php echo $att ?>');" style="padding-left: 2px; padding-right: 5px; color: white;"><?php echo $att ?></div>
                                                            <button class="remove-attachment float-left" id="btn_remove_attachment<?php echo $i ?>" name="btn_remove_attachment<?php echo $i ?>" onclick="removeAttachment(this, '<?php echo $att ?>', <?php echo $rs['recno'] ?>);" title='Click to remove attachment'>-</button>
                                                        </div><?php                                     
                                                    }
                                                }
                                                else
                                                {
                                                    $i++;?>
                                                    <div class="div-attachment-ele" id="div_attachment_ele">
                                                        <span class="span-event-numbered"><?php echo $i ?></span>
                                                        <input class="event-attachments" type="file" name="files[]" id="attachments<?php echo $i ?>" />
                                                        <button class="remove-attachment display-none" id="btn_remove_attachment1" name="btn_remove_attachment1" onclick="removeAttachment(this, '', '');" title='Click to remove attachment'>-</button>
                                                    </div><?php
                                                }?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="align-center font-size-1em" style="color: white; font-weight: bold;">Select the image you want as your main product display.</div><?php
                                        if(!is_null($rs['attachment']))
                                        {
                                            foreach($explodeattach as $miniatt)
                                            {
                                                if($miniatt == $rs['slt_attachment'])
                                                {
                                                    $sltimageclass = "mgm-slt-image";
                                                    $slttitle = "Selected";
                                                }
                                                else
                                                {
                                                    $sltimageclass = "";
                                                    $slttitle = "";
                                                }?>
                                                <div class="mgmproduct-viewminiimage float-left cursor-pointer" id="div_view_mini_image" style="height: 100px;">
                                                    <img class="<?php echo $sltimageclass ?> slt-image" title="<?php echo $slttitle ?>" onclick="selectMainimage(this, <?php echo $rs['recno'] ?>, '<?php echo $miniatt ?>');" src='<?php echo $rs['attachment_dir']."/".$_SESSION['thisproduct_recno']."/mini/$miniatt" ?>' onerror="this.onerror=null;this.src='./images/others/default.png">
                                                </div><?php
                                            }
                                        }?>
                                    </td>
                                </tr>
                            </table><?php
                        }
                    }?>
                </div>
                <div class="align-center float-left" style="height: 5%; width: 100%;"><?php echo $pc->Load_Footer();?></div>
            </div>
    </div><?php
}