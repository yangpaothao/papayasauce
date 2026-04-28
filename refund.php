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
             function submitRefund(){
                if($("#txtrefund").val() == ""){
                     alert("Please enter a confirmation number.");
                     return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitRefund&'+$("#frmsearchrefund").serialize(), function(result){
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
            function submitRefundnow(thisrecno){  
                let thisArray = [{
                        "txtarea_reason": $("#txtarea_reason").val(),
                        "txttotalrefund": $("#txttotalrefund").val(),
                        "thisrecno": thisrecno,
                    }];  
                $("#div_loader").removeClass("display-none");
                const thisData = JSON.stringify(thisArray);
                fetchAjaxdata(thisData);
            }
            async function fetchAjaxdata(thisData){
                try{
                    const result = await $.ajax({
                    url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=SubmitRefundnow&thisarray='+thisData,
                    type: 'POST',
                    contentType: "application/json"
                    });
                    if(result != "Failed"){
                        $("#div_loader").addClass("display-none");
                        $("#div_refund_table_container").html(result);
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
                $("#div_refund_container").remove();
                $("#txtrefund").select();
            }
            function chkRefundpayment(obj){
                if($(obj).is(":checked")){     
                    $("body").data('thisfee', $("#txtrefundfees").val());
                    $("body").data('thistotal', $("#txttotalrefund").val());
                    $("#txtrefundfees").val("");
                    $("#txttotalrefund").val($("#txttotalrefunddisp").val());
                }
                else{
                    $("#txtrefundfees").val($("body").data('thisfee'));
                    $("#txttotalrefund").val($("body").data('thistotal'));
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
function SubmitRefundnow()
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
    $thistotalrefund = 0;
    $thisrecno = "";
    foreach(json_decode($_POST['thisarray']) as $key => $value)
    { 
        foreach($value as $key1 => $value2)
        {
            if($key1 == "txtarea_reason")
            {
                $thisreason = $value2;
            }
            else if($key1 == "txttotalrefund")
            {
                $thistotalrefund = $value2;
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
        //payment_id is what we need to do a refund, we need payment_id from createpayment function
    }
    $realamount = number_format($thistotalrefund,2);
    if(!is_null($rs['cancellation_fees']))
    {
        $refund_fee = $pt->CancelFees($rs['cancellation_fees'], $thistotalrefund);
        $realamount = number_format($realamount - $refund_fee,2);
    }
    $thishash = $load_headers->Hash_Me_Recno(time());
    //file_put_contents('./dodebug/debug.txt', "What is access token?: ".$thisaccesstoken, FILE_APPEND);
    $thisreturnsarray = $pt->MakeSquarerefund($destinationid, $realamount, $thishash, $thisaccesstoken, $thisreason);

    foreach($thisreturnsarray as $key => $value)
    {
        foreach($thisreturnsarray as $key => $value)
        {
            if(is_array($value))
            {
                foreach($value as $key1 => $value1)
                {
                    if($key1 == "id")
                    {
                        $thisdataupdate['refund_id'] = $value1;
                    }
                    if($key1 == "location_id")
                    {
                        //We will use the location_id as the confirmation in case guests wants something for tracking
                        $thisdataupdate['refund_confirmation'] = $value1;
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
    }
    //file_put_contents('./dodebug/debug.txt', "What is receipt?: ".$square_receiptno, FILE_APPEND);
    //file_put_contents('./dodebug/debug.txt', "What is status?: ".$squarestatus, FILE_APPEND);
    if($squarestatus == "PENDING")
    {
        $thistimestamp = date('Y-m-d H:i:s');
        //file_put_contents('./dodebug/debug.txt', "What is isOptin?: ".$isOptin, FILE_APPEND);
        if($isOptin == true)
        {
            //If guest optin in to get message, then we will execute this line of code and send text otherwise, we will just send email.
            $returnsms = $sms->SendSMSrefund($db, $thisphone_number, $square_receiptno, $realamount, $thistimestamp);
        
            if($returnsms == "")
            {
                $thisreturns = "Failed";
            }
        }
        $thisdataupdate['refund_amt'] = number_format($realamount, 2);
        $thisdataupdate['refund_reason'] = $thisreason;
        $thisdataupdate['refund_fees'] = number_format($refund_fee,2);
        //file_put_contents('./dodebug/debug.txt', "What is reason?: ".$thisreason, FILE_APPEND);
        $thisdataupdate['refund_date'] = $thistimestamp;
        $thisdataupdate['isRefund'] = true;
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

            $thisreturn =  "<div class='align-center'>Refunded Successfully.  Receipt Number is $square_receiptno.</div>";
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
function SubmitRefund()
{
    global $db, $pt; 
    //We will select everything, but we want to get the payment_confirmation but only those that is less than 30 days old.
    $sql = "SELECT sd.*, ci.cancellation_fees from schedule_dates sd INNER JOIN company_info ci WHERE sd.payment_confirmation = '".trim($_POST['txtrefund'])."' ";
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
        {?>
            <form name="frmserverefund" id="frmserverefund" method="post">
                <div class="div-refund-display" id="div_refund_container">
                    <button class="float-right btn-close-refund" id="btnclosediv" onclick="closeDive();">X</button>
                    <div name="div_refund_table_container" id="div_refund_table_container">
                        <table class="tbl-refund-payment">
                            <tr>
                                <td class="div-refund-lbl float-right">Confirmation No.:</td>
                                <td class="div-refund-input"><input class="input-refund" type="text" size="30" value="<?php echo $rs['payment_confirmation'] ?>" readonly/></td>
                            </tr>
                            <tr>
                                <td class="div-refund-lbl float-right">Date:</td>
                                <td class="div-refund-input"><input class="input-refund" type="text" size="30" value="<?php echo date('d M Y', strtotime($rs['date'])) ?>" readonly/></td>
                            </tr>
                            <tr>
                                <td class="div-refund-lbl float-right">Name:</td>
                                <td class="div-refund-input"><input class="input-refund" type="text" size="30" value="<?php echo $rs['guest'] ?>" readonly/></td>
                            </tr>
                            <tr>
                                <td class="div-refund-lbl float-right">Reason:</td>
                                <td class="div-refund-input"><div class="div-refund-txtarea"><textarea class="no-resize refund-reason-txtarea" name="txtarea_reason" id="txtarea_reason" rows="2" cols="36" placeholder="Write a reason for the refund." autofocus ></textarea></div></td>
                            </tr><?php
                            if(!is_null($rs['tip']))
                            {?>
                                <tr>
                                    <td class="div-refund-lbl float-right">Tip:</td>
                                    <td class="div-refund-input"><input class="input-refund" type="text" size="30" value="<?php echo number_format($rs['tip'],2) ?>" readonly/></td>
                                </tr><?php
                            }
                            $realamount = $rs['bigtotal'];
                            if(!is_null($rs['cancellation_fees']))
                            {?>
                                <tr>
                                    <td class="div-refund-lbl float-right">Total:</td>
                                    <td class="div-refund-input"><input class="input-refund" name="txttotalrefunddisp" id="txttotalrefunddisp" type="text" size="30" value="<?php echo number_format($realamount,2) ?>" /></td>
                                </tr><?php
                            }
                            if(!is_null($rs['cancellation_fees']))
                            {
                                $thisfee = $pt->CancelFees($rs['cancellation_fees'], $rs['bigtotal']);
                                $realamount = $realamount - $thisfee;
                                ?>
                                <tr>
                                    <td class="div-refund-lbl float-right">Cancel Fee:</td>
                                    <td class="div-refund-input"><input class="input-refund" type="text" size="30" id="txtrefundfees" value="<?php echo number_format($thisfee,2) ?>" readonly/></td>
                                </tr>
                                <tr>
                                    <td class="div-refund-lbl float-right"></td>
                                    <td class="div-refund-input div-refund-input-font">
                                        <input class="chk-refund" type="checkbox" id="chk_refund" name="chk_refund" onchange="chkRefundpayment(this);" />Check to remove refund fee.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="div-refund-lbl float-right">Total:</td>
                                    <td class="div-refund-input"><input class="input-refund input-refund-bg" name="txttotalrefund" id="txttotalrefund" type="text" size="30" value="<?php echo number_format($realamount,2) ?>" /></td>
                                </tr><?php
                            }
                            else
                            {?>
                                <tr>
                                    <td class="div-refund-lbl float-right">Total:</td>
                                    <td class="div-refund-input"><input class="input-refund input-refund-bg" name="txttotalrefund" id="txttotalrefund" type="text" size="30" value="<?php echo number_format($realamount,2) ?>" /></td>
                                </tr><?php                            
                            }?>
                            <tr class="tr-register-btn-container">
                                <td class="tbl-register-guest-lbl align-center" colspan="2">
                                    <button type="button" value="Submit" id="btnrefund" onclick="submitRefundnow(<?php echo $rs['recno'] ?>);">REFUND</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </form><?php
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
            <img class="refund-loader-img" src="/images/others/loading.gif" />
        </div>
        <div class="div-header-main-container">Refund Payment</div>
        <br>
        <div class="div-body-container" id="div_body_container">
            <form name="frmsearchrefund" id="frmsearchrefund" method="post">
                <table class="tbl-refund">
                    <tr>
                        <td class="div-refund-lbl float-right">Unlimited Dates:</td>
                        <td class="div-refund-input"><input class="chkbox-refund" type="checkbox" title="Click if you want to search all dates" id="chkLimitedate" name="chkLimitedate" /></td>
                    </tr>
                    <tr>
                        <td class="div-refund-lbl float-right">Confirmation: <span class="asterisk"> * </span></td>
                        <td class="div-refund-input"><input class="input-refund" type="text" id="txtrefund" name="txtrefund" size="30" value="" placeholder="Confirmation Number" required autofocus /></td>
                    </tr>
                    <tr class="tr-register-btn-container">
                        <td class="tbl-refund-guest-lbl align-center" colspan="2">
                            <button type="button" value="Submit" id="btnfrmrefund" onclick="submitRefund();">Submit</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php $load_headers::Load_Footer();?>
    </div><?php
}?>