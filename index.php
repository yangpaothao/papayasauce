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
            function mainTabs(obj){
                //If we are clicking the selected tab, nothing needs to be done
                if(!$(obj).hasClass('div-main-tab-slted')){
                    //If we are here, that means what we clicked is not the selected tab so we do something
                    $(".div-main-tabs").each(function(){
                        if($(obj).prop('id') == $(this).prop('id')){
                            $(obj).removeClass("div-main-tab-nonslted").addClass("div-main-tab-slted");
                        }
                        else{
                            $(this).removeClass("div-main-tab-slted");
                            $(this).addClass("div-main-tab-nonslted");
                        }
                    });
                    $(obj).addClass("div-main-tab-slted");
                }     
            }
            function selectedProduct(product_recno){
                let thisArray = [{
                    "this_recno": product_recno
                }];
                const thisData = JSON.stringify(thisArray);
                fetchAjaxsltproduct(thisData);
            }
            async function fetchAjaxsltproduct(thisData){
            try{
                const result = await $.ajax({
                url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=SelectedProduct&thisarray='+thisData,
                type: 'POST',
                contentType: "application/json"
                });
                if(result == "Success"){
                    window.location.href = "product.php";
                }
                else
                {
                    alert("SESSION variable 'SELECTED_PRODUCT_RECNO' did not get set.");
                    return(false);
                }
            }
            catch(error){
                alert("ERROR");
            }
            }
            function goToschedule(obj, recno){
                window.location.href = "schedule.php?recno="+recno;
            }
            function showOrders(recno){
                window.location.href = "serviceorder.php?recno="+recno;
            }
            function showThisannouncement(){
                //status will come in as Modify or default to Readonly
                //window.location.href = "announcement.php?recno="+recno;
                window.open('manageAnnouncement.php?fromload=index', '_blank');
            }
            function showThisimportant(){
                //status will come in as Modify or default to Readonly
                //window.location.href = "announcement.php?recno="+recno;
                window.open('manageImportant.php?fromload=index', '_blank');
            }
            function goTofid(){
                window.open('fid.php?', '_blank');
            }
            function displayEvent(thisrecno){
                window.open('./show_event.php?thisrecno='+thisrecno, '_blank');
            }
            function closeDiv(){
                $("#div_float").remove();
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
function SelectedProduct()
{
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    $_SESSION['SELECTED_PRODUCT_RECNO'] = $returnpost['recno'];
    echo "Success";
}
function About()
{
    global $db;
    $thisabout = "";
    $sql = "SELECT * FROM company_info WHERE isActive = true AND isDeleted = false";
    $result = $db ->PDOMiniquery($sql);
    if($db->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisabout = $rs['about'];
        }
    }?>
    <div id="div_float" class="index-div-float display-none">
        <button id="btn_close_float_div" class="index-close-div float-right" onclick="closeDiv();">X</button>
        <textarea id="div_about" name="div_about" cols="83" rows="36" style="resize: none;" readonly><?php echo $thisabout ?></textarea>
    </div><?php
}
function ResendAuthenticate()
{
    global $db, $load_headers;
    //By this time, if user is trying to resend a passcode, we should already have $_SESSION['temprecno'] avail.
    
    $thisfields = array();
    $thiswhere = array();
    $thisfields = array('recno', 'firstname', 'lastname', 'email');
    $thistable = "user";
    $thiswhere = array("recno" => $_SESSION['temprecno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result))
    {
        foreach($result as $row)
        {
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
            $realtime = $load_headers -> Hash_Me_Password($temptime);

            $thisdata = array('twofactorcode' => $realtime, 'isauthenticatedverified' => false);
            $thiswhere = array('recno' => $row['recno']);
            $db->PDOUpdate($thistable, $thisdata, $thiswhere, $row['recno']);

            $sendto[] = array($realemail => $realfirstname." ".$reallastname);
            //file_put_contents('./dodebug/debug.txt', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
            $subject = "Authentication Required to login.";

            $body = "Please use this code to verify your account to login.<br><br>CODE: $temptime";
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            echo "Authenticate";
        }
    }
    else
    {
        echo "Failed";
    }
}
function SubmitTwofactor()
{
    global $db, $pc;
    $thisfields = array();
    $thiswhere = array();
    $thisfields = array('recno', 'firstname', 'lastname', 'twofactorcode', 'login', 'profile');
    $thistable = "users";
    $realcode = $pc -> Hash_Me_Password($_POST['txtcode']); //we hash user's entered pw.      
    $thiswhere = array("recno" => $_SESSION['temprecno'], "twofactorcode" => $realcode);
    //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
    //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result))
    {
        foreach($result as $row)
        {
            $_SESSION['user'] = $row['login'];
            $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
            $_SESSION['user_recno'] = $row['recno'];
            $_SESSION['companyname'] = "Avion Tracker";
            $_SESSION['usersearchlist'] = array();
            $_SESSION['customersearchlist'] = array();
            $_SESSION['profile'] = $row['profile']; //SV, 2 letter rep
            //Since we successfully logged in, we want to make vericode NULL so that it wil negate any new password change request or verification
            $thisdata = array('vericode' => NULL);
            $thiswhere = array('recno' => $row['recno'], 'isauthenticatedverified' => true);
            $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $row['recno']);
        }
    }
    else
    {  
        echo "Verify vericode is wrong or expired.";
    }
}
function SubmitIndexform()
{
    global $db, $pc;
    $thisfields = array();
    $thiswhere = array();
    $_SESSION['isShowfb'] = false;
    $_SESSION['isShowcancel'] = false;
    $_SESSION['isShowrefund'] = false;
    $thisfields = array('recno', 'firstname', 'lastname', 'email', 'login', 'isverified', 'isactive', 'isauthenticated', 'isauthenticatedverified', 'profile', 'isAdmin');
    $thistable = "users";
    $getpasssword = $pc -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.      
    $thiswhere = array("login" => $_POST['txtlogin'], "password" => $getpasssword);
    //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
    //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(!is_null($result))
    {
        foreach($result as $row)
        {
            if($row['isverified'] == false)
            {
                //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
                echo "Not Verify";
            }
            else if($row['isactive'] == false)
            {
                //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
                echo "Account is inactive, contact your administrator.";
            }
            else if($row['isauthenticated'] == true && $row['isauthenticatedverified'] == false)
            {
                //If isauthenticatedverified is false, that means 2 factor has NOT been enable, 
                //if it is true, that means 2 factor is enabled and if they verified that they do want it enable, isauthenticatedverified would be true
                ////and then in this case, we would check against it too.
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
                echo "Authenticate";
            }
            else
            {
                $_SESSION['user'] = $row['login'];
                $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
                $_SESSION['user_recno'] = $row['recno'];
                $_SESSION['usersearchlist'] = array();
                $_SESSION['customersearchlist'] = array();
                $_SESSION['profile'] = $row['profile'];
                //Since we successfully logged in, we want to make vericode NULL so that it wil negate any new password change request or verification
                $_SESSION['isShowfb'] = $row['isShowfb'];
                $_SESSION['isShowcancel'] = $row['isShowcancel'];
                $_SESSION['isShowrefund'] = $row['isShowrefund'];
                $_SESSION['isShowcounter'] = $row['isShowcounter'];
                $_SESSION['isLive'] = $row['isLive'];
                $_SESSION['isDeveloper'] = $row['isDeveloper'];
                $thisdata = array('vericode' => NULL);
                $thiswhere = array('recno' => $_SESSION['user_recno']);
                $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
                if(!isset($rows))
                {
                    echo "Failed To Update vericode to NULL";
                }
                else
                {
                    echo "Success";
                }
                exit;
            }
        }
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", 'Failed', FILE_APPEND);
        echo "Failed";
    }
}
function Main()
{
    global $db, $pc;?>
    <div class="main-div">
        <div style="width: 90%; margin: auto; background-color: #f0f5f5;">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo();?>
            </div>
            <div class="float-left" style="width: 7%;"><?php echo $pc->LoginPanel();?></div>
            <div class="div-main-tabs-container">
                <div class="float-left div-main-tabs div-main-tab-slted cursor-pointer align-center" id="div_main" onclick="mainTabs(this);">Main</div>
                <!--<div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_products" onclick="mainTabs(this);">Products</div>-->
                <div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_videos" onclick="mainTabs(this);">Videos</div>
                <div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_recipe" onclick="mainTabs(this);">Recipe</div>
                <div class="float-left div-main-tabs cursor-pointer div-main-tab-nonslted align-center" id="div_about" onclick="mainTabs(this);">About</div>
            </div>
            <div class="div-content-holder-flex align-center"><?php
                //1 month from today only
                $sql = "SELECT * FROM products WHERE isActive = true AND isDeleted = false";
                //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
                $result = $db -> PDOMiniquery($sql);
                if($db->PDORowcount($result) > 0)
                {
                    $i = 1;
                    foreach($result as $rs)
                    {?>
                        <div class="align-left cursor-pointer div-content-holder-flex-data-container" onclick="selectedProduct(<?php echo $rs['recno']?>);">  
                            <div class="float-left white-space-no-wrap" style="width: 100%; color: white; font-weight: bold; background-color: gray; min-height: 20px;">$<?php echo number_format($rs['price'], 2) ?>, <?php echo $rs['name']?></div>
                            <div class="float-left" style="width: 100%;"><img class="div-front-event" src="./images/others/products/<?php echo $rs['attachments']?>" /></div>
                        </div><?php
                        $i++;
                    }
                }
                else
                {?>
                    <div class="align-left cursor-pointer div-content-holder-flex-data-container" id="div_event"><img class="div-front-event" src="./images/others/no-event.png"/></div><?php 
                }?>
            </div>
            <div class="align-center" style="height: 5%;"><?php echo $pc->Load_Footer();?></div>
        </div>
        

    </div><?php
}