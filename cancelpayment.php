<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/sendsms.php");
require("./common/classes/PageloaderClass.php");
require("./common/prompt.php");
require("./common/classes/SMSClass.php");
require("./common/classes/EmailClass.php");
$load_headers = new PageloaderClass();
$ne = new Email_Class();
$db = new PDOCON();
$pt = new PROMPT();
$sms = new SMSClass($db);
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
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.?>
        <script type="text/javascript">
             function submitCancelpayment(){
                if($("#txtcancelpayment").val() == ""){
                     alert("Please enter a confirmation number.");
                     return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitCancelpayment&'+$("#frmsearchrecancelpayment").serialize(), function(result){
                    //alert(result);
                    if(result == "No Result"){
                        alert("No result is found, please check the confirmation number and try again.");
                        return(false);
                    }
                    else{
                        //we are going to paint or append a div inside 'div_body_container'
                        $("#div_body_container").append(result);
                    }
                });
            }            
            function submitCancelnow(thisrecno){  
                let thisArray = [{
                        "txtarea_reason": $("#txtarea_reason").val(),
                        "thisrecno": thisrecno,
                    }];  
                $("#div_loader").removeClass("display-none");
                const thisData = JSON.stringify(thisArray);
                fetchAjaxdata(thisData);
            }
            async function fetchAjaxdata(thisData){
                try{
                    const result = await $.ajax({
                    url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=SubmitCancelnow&thisarray='+thisData,
                    type: 'POST',
                    contentType: "application/json"
                    });
                    if(result != "Failed"){
                        $("#div_loader").addClass("display-none");
                        $("#div_cancel_payment_table_container").html(result);
                    }
                    else
                    {
                        alert(result);
                    }
                }
                catch(error){
                    alert("ERROR in paynow");
                    alert(result);
                }
            }
            function closeDive(){
                $("#div_cancel_payment_container").remove();
                $("#txtcancelpayment").select();
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
function SubmitCancelnow()
{
    global $db, $pt, $sms, $ne, $load_headers;
    $squarestatus = "";
    $thisdataupdate = [];
    $thisreturn = "";
    $returnsms = "";
    $thisphone_number = "";
    $square_receiptno = "";
    $isOptin = false;
    $sendto = [];
    $replyto = [];
    $ccto = [];
    $bccto = [];
    $attachment = [];
    $thistimestamp = "";
    $guestname = "";
    $thistable = "";
    $thiswhere = [];
    $refund_fee = 0;
    $thisreason = "";
    $thistotalcancel = 0;
    $thisrecno = "";
    foreach(json_decode($_POST['thisarray']) as $key => $value)
    { 
        foreach($value as $key1 => $value2)
        {
            if($key1 == "txtarea_reason")
            {
                $thisreason = $value2;
            }
            else if($key1 == "thisrecno")
            {
                $thisrecno = $value2;
            }
        }
    }
    $sql = "SELECT DISTINCT sd.*, u.isOpt, ci.api_access_token, ci.cancellation_fees from schedule_dates sd INNER JOIN users u ON sd.ufg_recno = u.recno ";
    $sql .= "INNER JOIN company_info ci ";
    $sql .= "WHERE sd.recno = $thisrecno";
    //file_put_contents('./dodebug/debug.txt', "SubmitRefundnow sql?: ".$sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thisaccesstoken = $rs['api_access_token'];
        $destinationid = $rs['payment_id'];
        $thisphone_number = $rs['phone_number'];
        $isOptin = $rs['isOpt'];
        $thisemail = $rs['email'];
        $guestname = $rs['guest'];
        //payment_id is what we need to do a refund
    }
    $realamount = number_format($thistotalcancel,2);
    $thishash = $load_headers->Hash_Me_Recno(time());
    //file_put_contents('./dodebug/debug.txt', "What is access token?: ".$thisaccesstoken, FILE_APPEND);
    
    //Before we can cancel, we want to check the payment to see what state it is in first
    $returngetpayment = $pt->GetPayment($destinationid, $thisaccesstoken);
    foreach($returngetpayment as $key => $value)
    {
        if(is_array($value))
        {
            foreach($value as $key1 => $value1)
            {
                if($key1 == "status")
                {
                    //We will use the location_id as the confirmation in case guests wants something for tracking
                    $getpaymentstatus = $value1;
                }
            }                 
        }
    }        
    if($getpaymentstatus == "PENDING" || $getpaymentstatus == "APPROVED")
    {
        //We will be able to cancel or let it complete
        $thisreturnsarray = $pt->MakeSquareCancelpayment($destinationid, $realamount, $thishash, $thisaccesstoken, $thisreason);
        foreach($thisreturnsarray as $key => $value)
        {
            if(is_array($value))
            {
                foreach($value as $key1 => $value1)
                {
                    if($key1 == "id")
                    {
                        $thisdataupdate['cancel_id'] = $value1;
                    }
                    if($key1 == "location_id")
                    {
                        //We will use the location_id as the confirmation in case guests wants something for tracking
                        $thisdataupdate['cancel_location_id'] = $value1;
                        $square_receiptno = $value1;
                    }
                    if($key1 == "status")
                    {
                        //We will use the location_id as the confirmation in case guests wants something for tracking
                        $squarestatus = $value1;
                    }
                }                 
            }
        }        

        //file_put_contents('./dodebug/debug.txt', "What is receipt?: ".$square_receiptno, FILE_APPEND);
        //file_put_contents('./dodebug/debug.txt', "What is status?: ".$squarestatus, FILE_APPEND);
        if($squarestatus == "PENDING" || $squarestatus == "APPROVED")
        {
            $thistimestamp = date('Y-m-d H:i:s');
            $thisdataupdate['cancel_date'] = $thistimestamp;

            $thistable = "schedule_dates";
            $thiswhere = ["recno" => $thisrecno];
            $result = $db->PDOUpdate($thistable, $thisdataupdate, $thiswhere);
            //file_put_contents('./dodebug/debug.txt', "What is result?: ".$result, FILE_APPEND);
            if($result == "Success")
            {
                $sendto[] = array($thisemail => $guestname);
                $refund_subject = $ne->get_refund_subject();
                $refund_body = $ne->get_refund_body($realamount, $square_receiptno, $thistimestamp);
                $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $refund_subject, $refund_body, $attachment);

                $thisreturn =  "<div class='align-center'>Successfully Canceled.  Receipt Number is $square_receiptno.</div>";
            }
            else
            {
                $thisreturn = "Failed";
            }
        }
        else
        {
            $thisreturn = "Failed";
        }
        echo $thisreturn;
    }
    else if($getpaymentstatus == "COMPLETED")
    {
        //We will be able to refund
        echo "The payment has been completed and can not be canceled.";
    }
    else if($getpaymentstatus == "FAILED")
    {
        echo "The payment has failed and could not be CANCELED.";
    }
    else
    {
        echo "For some reason, payment could not be canceled.";
    }
}
function SubmitCancelpayment()
{
    global $db, $pt; 
    $thisreturn = "";
    //We will select everything, but we want to get the payment_confirmation but only those that is less than 30 days old.
    //cancel_id - id
    //cancel_status - CANCEL
    //cancel_location_id 
    //cancel_order_id
    //cancel_note
    //isCanceled
    
    $sql = "SELECT sd.*, ci.cancellation_fees from schedule_dates sd INNER JOIN company_info ci WHERE sd.payment_confirmation = '".trim($_POST['txtcancelpayment'])."' ";
    if(!array_key_exists('chkLimitedate', $_POST))
    {
        //If the chk box for limite date is checked, we will only get all dates, otherwise, only the 30 days before.
        $sql .= "AND sd.date > '".date('Y-m-d', strtotime('-30 days'))."'";
    }
    //file_put_contents('./dodebug/debug.txt', "refund sql?: ".$sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    if($db->PDORowcount($result) > 0)
    {
        //We have atleast 1 result.
        foreach($result as $rs)
        {
            if($rs['isRefund'] == true)
            {
                $thisreturn =  "<div class='align-center'>This payment has been refunded already.  Refund# ".$rs['refund_confirmation']."</div>";
            }
            else if($rs['isCanceled'] == true)
            {
                //Using cancel_location_id as confirmation in case guests wants a number for tracking.
                $thisreturn =  "<div class='align-center'>This payment has been canceled already.  Cancel# ".$rs['cancel_location_id']."</div>";
            }
            else
            {?>
                <form name="frmserverecancelpayment" id="frmserverecancelpayment" method="post">
                    <div class="div-cancelpayment-display" id="div_cancel_payment_container">
                        <button class="float-right btn-close-refund" id="btnclosediv" onclick="closeDive();">X</button>
                        <div name="div_refund_table_container" id="div_cancel_payment_table_container">
                            <table class="tbl-cancel-payment-payment">
                                <tr>
                                    <td class="div-cancelpayment-lbl float-right">Confirmation No.:</td>
                                    <td class="div-cancelpayment-input"><input class="input-cancelpayment" type="text" size="30" value="<?php echo $rs['payment_confirmation'] ?>" readonly/></td>
                                </tr>
                                <tr>
                                    <td class="div-cancelpayment-lbl float-right">Date:</td>
                                    <td class="div-cancelpayment-input"><input class="input-cancelpayment" type="text" size="30" value="<?php echo date('d M Y', strtotime($rs['date'])) ?>" readonly/></td>
                                </tr>
                                <tr>
                                    <td class="div-cancelpayment-lbl float-right">Name:</td>
                                    <td class="div-cancelpayment-input"><input class="input-cancelpayment" type="text" size="30" value="<?php echo $rs['guest'] ?>" readonly/></td>
                                </tr>
                                <tr>
                                    <td class="div-cancelpayment-lbl float-right">Reason:</td>
                                    <td class="div-cancelpayment-input"><div class="div-refund-txtarea"><textarea class="no-resize refund-reason-txtarea" name="txtarea_reason" id="txtarea_reason" rows="2" cols="36" placeholder="Write a reason for the refund." autofocus ></textarea></div></td>
                                </tr><?php
                                if(!is_null($rs['tip']))
                                {?>
                                    <tr>
                                        <td class="div-cancelpayment-lbl float-right">Tip:</td>
                                        <td class="div-cancelpayment-input"><input class="input-cancelpayment" type="text" size="30" value="<?php echo number_format($rs['tip'],2) ?>" readonly/></td>
                                    </tr><?php
                                }?>
                                <tr>
                                    <td class="div-cancelpayment-lbl float-right">Total:</td>
                                    <td class="div-cancelpayment-input"><input class="input-cancelpayment input-cancelpayment-bg" name="txttotalcancelpayment" id="txttotalcancelpayment" type="text" size="30" value="<?php echo number_format($rs['bigtotal'],2) ?>" /></td>
                                </tr>
                                <tr class="tr-register-btn-container">
                                    <td class="tbl-cancelpayment-lbl align-center" colspan="2">
                                        <button class="btn-cancelpayment-cancel" type="button" value="Submit" id="btnrefund" onclick="submitCancelnow(<?php echo $rs['recno'] ?>);">Click To CANCEL this payment</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </form><?php
            }
        }
    }
    else
    {
        echo "No Result";
    }
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo(true);?>
        <br>
        <div name="div_loader" id="div_loader" class="refund-loader-container display-none">
            <img class="cancelpayment-loader-img" src="/images/others/loading.gif" />
        </div>
        <div class="div-header-main-container">Payment Cancellation</div>
        <br>
        <div class="div-body-container" id="div_body_container">
            <form name="frmsearchrecancelpayment" id="frmsearchrecancelpayment" method="post">
                <table class="tbl-cancelpayment">
                    <tr>
                        <td class="div-cancelpayment-lbl float-right">Unlimited Dates:</td>
                        <td class="div-cancelpayment-input"><input class="chkbox-refund" type="checkbox" title="Click if you want to search all dates" id="chkLimitedate" name="chkLimitedate" /></td>
                    </tr>
                    <tr>
                        <td class="div-cancelpayment-lbl float-right">Confirmation: <span class="asterisk"> * </span></td>
                        <td class="div-cancelpayment-input"><input class="input-cancelpayment" type="text" id="txtcancelpayment" name="txtcancelpayment" size="30" value="" placeholder="Confirmation Number" required autofocus /></td>
                    </tr>
                    <tr class="tr-cancelpayment-btn-container">
                        <td class="tbl-cancelpayment-guest-lbl align-center" colspan="2">
                            <button type="button" value="Submit" id="btnfrmrefund" onclick="submitCancelpayment();">Submit</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php $load_headers::Load_Footer();?>
    </div><?php
}?>