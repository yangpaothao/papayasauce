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
require("./common/classes/TaxClass.php");
require("./common/classes/ServiceClass.php");
require("./common/classes/PromotionClass.php");
require("./common/classes/MathClass.php");
require("./common/classes/EmailClass.php");
require("./common/classes/SMSClass.php");
$ne = new Email_Class();
$load_headers = new PageloaderClass();
$db = new PDOCON();
$pt = new PROMPT();
$tc = new TaxClass();
$pc = new PromotionClass();
$mc = new MathClass();
$sc = new ServiceClass();
$sms = new SMSClass($db);

if(count($_POST) > 0 && isset($_POST['cmd']))
{
    $_REQUEST['cmd']();
    exit();
}
if(count($_POST) > 0 && isset($_POST['token']))
{
    TokenizedPayment();
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
            $(document).ready(function(){
               getSourceid(); 
            });
            function sltPrepay(obj){
                //alert($(obj).prop('id'));
                if($(obj).prop('id') == "pre_pay_full"){                  
                    $(obj).removeClass("pre-pay-div-data-container").addClass('pre-pay-div-data-container-slt');
                    $(".full-pay-tracker").removeClass("pre-pay-promo-disc-noslt").addClass('pre-pay-promo-disc');
                    $("#div_pre_pay_dis").removeClass("pre-pay-promo-disc").addClass('pre-pay-promo-disc-noslt');
                    $("#div_deposit").removeClass("pre-pay-promo-disc-noslt").addClass('pre-pay-promo-disc');
                    
                    $("#txt_cr_amount").val($('body').data('finalfull'));
                    
                    //We want to reset the deposit div to default
                    $("#pre_pay_deposit").removeClass("pre-pay-div-data-container-slt").addClass('pre-pay-div-data-container');
                    $("#div_promo_deposit").removeClass("pre-pay-promo-disc").addClass('pre-pay-promo-disc-noslt');
                    
                    $("#btn_card").prop('disabled', false);
                    $("#btn_card").removeClass('btn-disabled');
                    $("#btn_carddeposit").prop('btn_carddeposit', true);
                    $("#btn_carddeposit").addClass('btn-disabled');
                }
                else{
                    //alert('in deposit');
                    $("#pre_pay_full").removeClass("pre-pay-div-data-container-slt").addClass('pre-pay-div-data-container');
                    $(".full-pay-tracker").removeClass("pre-pay-promo-disc").addClass('pre-pay-promo-disc-noslt');
                    $("#div_pre_pay_dis").removeClass("pre-pay-promo-disc").addClass('pre-pay-promo-disc-noslt');
                    $("#txt_cr_amount").val($('body').data('finaldeposit'));
                    $("#div_deposit").removeClass("pre-pay-promo-disc").addClass('pre-pay-promo-disc-noslt');
            
                    $(obj).removeClass("pre-pay-div-data-container").addClass('pre-pay-div-data-container-slt');
                    $("#div_promo_deposit").removeClass("pre-pay-promo-disc-noslt").addClass('pre-pay-promo-disc');
                    
                    $("#btn_carddeposit").prop('disabled', false);
                    $("#btn_carddeposit").removeClass('btn-disabled');
                    $("#btn_card").prop('disabled', true);
                    $("#btn_card").addClass('btn-disabled');
                }
            }
            function submitDeposit(thisrecno){   
                //thisrecno is the recno for table schedule_dates
                if(validateCC() === false){
                    return(false);
                }
                thisstatus = "Deposit";
                $(".pre-pay-deposit-data-container").each(function(){
                    //We want to find the color of this background, then we can know the id and find out if use selected to pay in full or just the deposit
                    
                    //alert($(this).css('background-color'));
                    if($(this).css('background-color') == "rgb(29, 137, 209)"){
                        //This is the lightblue,
                        //if we are here, we can find out the id
                        if($(this).prop('id') == "pre_pay_deposit"){
                            thisstatus = "Deposit";
                        }
                        else{
                            thisstatus = "Full";
                        }
                    }
                    thisamount = $('body').data('finalfull');
                })
                //alert(thisstatus);
                //WE want to find the checked radio for the card type
                cardtype = $('input[name="rdo_credity_type"]:checked').val();
                //alert(cardtype);
                //alert(thisstatus);
                let thisArray = [{
                        "cardtype": cardtype,
                        "thisamount": thisamount, 
                        "thiscard": $("#txt_cr_last4").val(), 
                        "thisexpmm": $("#txt_expiredate_mm").val(),
                        "thisexpyy": $("#txt_expiredate_yy").val(),
                        "thissecurity": $("#txt_security").val(),
                        "thisstatus": thisstatus
                    }];  
                $("#div_loader").removeClass("display-none");
                const thisData = JSON.stringify(thisArray);
                
                getSquareinfo(thisrecno);
                
                fetchAjaxdatadeposit(thisData);
            }
            async function fetchAjaxdatadeposit(thisData){
                try{
                    const result = await $.ajax({
                    url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=SubmitDeposit&thisarray='+thisData,
                    type: 'POST',
                    contentType: "application/json"
                    });
                    if(result == "Success"){
                        window.location.href = "./paid.php";
                    }
                    else
                    {
                        alert("this return? "+result);
                    }
                }
                catch(error){
                    alert("ERROR in paynow");
                    alert(result);
                }
            }
            //https://www.google.com/search?q=square+api%2C+PHP+Payments+SDK+example&sca_esv=c84071398b66a2e1&sxsrf=ANbL-n6P1yPIAXFi1NxOlVYEJ26w5yN90w%3A1773282628177&source=hp&ei=RCWyaeeyCaaT8L0P8NLf6AM&iflsig=AFdpzrgAAAAAabIzVE98Flap9obPmHMRlH2ZxviamJPD&ved=0ahUKEwjn5IzJqJmTAxWmCbwBHXDpFz0Q4dUDCCE&uact=5&oq=square+api%2C+PHP+Payments+SDK+example&gs_lp=Egdnd3Mtd2l6IiRzcXVhcmUgYXBpLCBQSFAgUGF5bWVudHMgU0RLIGV4YW1wbGUyBRAhGKABMgUQIRigAUiOP1AAWIY-cAh4AJABAZgBhwKgAYkoqgEHMS4xNi4xMrgBA8gBAPgBAfgBApgCJKAC3ybCAgQQIxgnwgILEAAYgAQYkQIYigXCAg4QLhiABBixAxjRAxjHAcICBRAuGIAEwgILEAAYgAQYsQMYgwHCAgUQABiABMICERAuGIAEGLEDGNEDGIMBGMcBwgIIEC4YgAQYsQPCAggQABiABBixA8ICChAAGIAEGBQYhwLCAgYQABgWGB7CAgUQABjvBcICCBAAGIAEGKIEwgILEAAYgAQYhgMYigXCAgUQIRirAsICBxAhGKABGAqYAwCSBwc4LjE2LjEyoAfozQGyBwcwLjE2LjEyuAfOJsIHBzExLjIxLjTIBzCACAA&sclient=gws-wiz
            async function tokenize(payment, depofull) {
                const result = await payment.tokenize();
                if (result.status === 'OK') {
                    // Send the token to your backend PHP script
                    $("#token").val(result.token);
                    $("#depofull").val(depofull);
                    
                    //alert(result.token);
                    $("#payment_form").submit();
                } else {
                    alert("Please enter your card information correctly and try again.");
                    return(false);
                    //alert(result.errors);
                    //alert('Tokenization failed. See console for details.');
                }
            }
            async function getSourceid() {
                //4111 1111 1111 1111
                const payments = Square.payments($("body").data("square_application_id"), $("body").data("square_ev_location_id"));
                const card = await payments.card();
                await card.attach('#div_card_container');
                
                $('#btn_card').on('click', async function(event){
                    await tokenize(card, 'Full');
                });
                $('#btn_carddeposit').on('click', async function(event){
                    await tokenize(card, 'Deposit');
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
function TokenizedPayment()
{
    global $db, $pt, $mc, $tc, $pc, $load_headers, $ne, $sc, $sms;
    if(!isset($_SESSION['companyname']))
     {
         //if session timed out, we send user to index, front page.
         header("Location: /index.php"); //Unless this is the main/front page, if user does not have a logged session, they will be forced to login first.
     }
    $thistotal = 0;
    $svrecno = 0;
    $thisdata = [];
    $thisdataupdate = [];
    $thisdataupdatecomplete = [];
    $thisreturns = "Invalid";
    $promototal = 0;
    $tempcardtype = "";
    $tempamount = "";
    $tempthiscard = "";
    $tempsecurity = "";
    $tempexpmm = "";
    $tempexpyy = "";
    $thenewrecno = 0;
    $thisid = 0;
    $thisIsdeposit = false;
    $thisoptin_confirm = false;
    $realamount = 0;
    $tempcal = 0;
    $thistip = 0;
    $thissquarecustomerid = "";
    $squarestatus = "FAILED";
    $square_receiptno = "";
    $thisopt = false;
    $isprepayok = false;
    $thissquareid = "";
    $thisservicerecno = "";
    $returnsms = "";
    $sendto = [];
    $replyto = [];
    $ccto = [];
    $bccto = [];
    $thisservicerecnoarray = [];
    $attachment = [];
    $thispr_recno = "";
    $tempsandpropost = "";
    $thisaccesstoken = "";
    $thisdiscount = "";
    $thistoken = $_POST['token']; //We get this token after we tokenized.
    $thisstatus = $_POST['depofull'];
    //file_put_contents("./dodebug/debug.txt", "thisstatus = $thisstatus \n", FILE_APPEND);
    if(isset($_SESSION['isLive']) && $_SESSION['isLive'] == true){
        $tempsandpropost = "_pro";
    }
    $thisserver = $load_headers -> GET_THIS_SERVER();
    
    $sqlacc = "SELECT u.square_api_access_token$tempsandpropost FROM users u INNER JOIN schedule_dates sd ON sd.uf_recno = u.recno WHERE sd.recno = ".$_SESSION['thisrecno'];
    //file_put_contents("./dodebug/debug.txt", "sqlacc = $sqlacc \n", FILE_APPEND);
    $resultacc = $db->PDOMiniquery($sqlacc);
    foreach($resultacc as $rsacc)
    {
        $thisaccesstoken = $rsacc["square_api_access_token$tempsandpropost"];
    }
    //file_put_contents("./dodebug/debug.txt", "thisfrom = ".$_SESSION['thisfrom']." \n", FILE_APPEND);
    $sql = "SELECT DISTINCT sd.*, u.squareid, u.isOpt_confirm, u.recno as user_recno, u.firstname, u.lastname, u.email, u.phone_number, u.isOpt, sd.discount, sd.sr_recno, sd.pr_recno from schedule_dates sd INNER JOIN users u ON sd.ufg_recno = u.recno ";
    $sql .= "WHERE sd.recno = ".$_SESSION['thisrecno'];
    //file_put_contents("./dodebug/debug.txt", "sql = $sql \n", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thistip = ($rs['tip'] == NULL ? 0 : $rs['tip']);
        $thisuser_recno = $rs['user_recno'];
        $thisfirstname = $rs['firstname'];
        $thislastname = $rs['lastname'];
        $thisemail = $rs['email'];
        $thisphone_number = $rs['phone_number'];
        $thissquareid = $rs['squareid'];
        $thisservicerecno = $rs['sr_recno'];
        $thisprrecno = $rs['pr_recno'];
        $thisopt = $rs['isOpt'];
        $thisdiscount = $rs['discount'];
        $thisbalance = $rs['balance'];
        if($thisstatus == "Full")
        {
            if($_SESSION['thisfrom'] != "Daily" && is_null($thisbalance))
            {
                //We only get here if we are NOT paying a balance off of the deposit.
                $thisdataupdate['isPrepaid'] = true;
            }
            
            $thiscost = $rs['cost'];
            $thistotal = $rs['bigtotal'];
            $thisid = $rs['thisid'];
            $thisoptin_confirm = $rs['isOpt_confirm'];
            //file_put_contents("./dodebug/debug.txt", "What is session thisfrom? ".$_SESSION['thisfrom']." \n", FILE_APPEND);
            if($_SESSION['thisfrom'] != "Daily")
            {
                //file_put_contents("./dodebug/debug.txt", "thisstatus inside prepay \n", FILE_APPEND);
                
                $sqlprepay = "SELECT * FROM events WHERE special_event = 'Prepay' AND isActive = true";
                $resultprepay = $db->PDOMiniquery($sqlprepay);
                if($db->PDORowcount($resultprepay) > 0)
                {
                    foreach($resultprepay as $rsprepay)
                    {
                        //In cases where the user already selected the prepay even in cases where, for some reason the user already has the prepare discount, we want
                        //to avoid giving more prepare
                        $thisservicerecnoarray = explode(',', $thisprrecno);
                        if(!in_array($rsprepay['recno'], $thisservicerecnoarray))
                        {   
                            $isprepayok = true;
                            $pc -> SetThisdiscount($db, $rsprepay['recno'], $thiscost);
                            $promototal = $pc -> GetThisdiscount();
                            //$thispr_recno .= ",".$rsprepay['recno'];
                            array_push($thisservicerecnoarray, $rsprepay['recno']);
                        }
                    }
                    if($isprepayok == true)
                    {
                        //file_put_contents("./dodebug/debug.txt", "thisservicerecnoarray: ".implode(',', $thisservicerecnoarray)." \n", FILE_APPEND);
                        $thistotal = number_format(($thistotal - $promototal), 2);
                        //file_put_contents("./dodebug/debug.txt", "thistotal: $thistotal \n", FILE_APPEND);
                        $thisdataupdatecomplete['bigtotal'] = $thistotal;
                        $thisdataupdatecomplete['total'] = $thistotal;
                        $thisdataupdatecomplete['pr_recno'] = implode(',', $thisservicerecnoarray);
                        $thisdataupdatecomplete['discount'] = number_format($thisdiscount+$promototal,2);
                    }
                }
                
            }
            $realamount = $thistotal;
            //file_put_contents("./dodebug/debug.txt", "thistotal: $thistotal \n", FILE_APPEND);
            //Have to now calculate the discount and add the recno to the pr_recno, recalculate to reflect the discount so it will reflect on the bigtotal.
            //We already calculated the tax and everything so we just need to forward this amount to the payment portal
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", "in deposit \n", FILE_APPEND);
            //We are handling deposit
            //We have to keep in mind that the user will only pay the depsosit, so they will not receive the pre-pay 5%.
            //and any other events will be given once they have finished the appointment.
            $thisIsdeposit = true;
            $thissvarray = $pt->GetPrepaydeposit($rs['sr_recno']); //sending in a string of recno 1,2,3,...n, wll get only records with deposit
            $thistotal = $thissvarray['deposit']; //User will pay this deposit.
            $realamount = $thistotal;
            $thiscost = $thissvarray['cost'];
            //file_put_contents("./dodebug/debug.txt", "thiscost = $thiscost and thisamount = $thisamount \n", FILE_APPEND);
            //$thisbalance = number_format($thiscost - $thistotal, 2);  //The total of the cost of all the service minus just the deposit
            //$realamount = $thisbalance;
            //NEED TO RECALCULATE THE TAXES for this deposit.  When the guest comes into the shop to pay the balance,
            //we will calculat the tax and add the totals for tax and the cost.
            //$tc->SetAlltaxper($db, $thistotal);
            //$tax_per_array = $tc->GetAlltaxper();

            $thistable = "schedule_dates";
            //handles the deposit, $_SESSION['thisrecno']
            $thisdatainsert['thisid'] = $rs['thisid'];
            $thisid = $rs['thisid'];
            $thisdata['thisamount'] = $realamount;
            $thisdatainsert['thisidgrp'] = 2;
            $thisdatainsert['total'] = $realamount;
            $thisdatainsert['bigtotal'] = $realamount;
            $thisdatainsert['deposit'] = $thistotal;
            $thisdatainsert['isDeposit'] = true;
            $thisdatainsert['isActive'] = true;
            
            $thisdata['thisamount'] = $thistotal;
            
            $thenewrecno = $db->PDOINSERT($thistable, $thisdatainsert);
            $thisdatainsert = [];
            
            $thisbalance = number_format($thiscost - $thistotal, 2);  //The total of the cost of all the service minus just the deposit
            $realamount = $thisbalance;
            
            //NOTE, in cases where thsi is the firs time the user book appointment with us, we would
            //already have a discount of new customer, if that is the case we need to calculated here.
            //First, we check if the discount is null or if there is something in it.  At this point, if there is
            //to be anything in here, it would be the prepay, nothing else because we are handling deposit.
            if(!is_null($thisprrecno))
            {
                $pc -> SetThisdiscount($db, $thisprrecno, $thiscost);
                $promototal = $pc -> GetThisdiscount();
                //file_put_contents("./dodebug/debug.txt", "promototal = $promototal \n", FILE_APPEND);
                $realamount = $realamount - $promototal;
                //file_put_contents("./dodebug/debug.txt", "realamount = $realamount \n", FILE_APPEND);
            }
            $thisdataupdate['thisid'] = $rs['thisid'];
           
            $thisdataupdate['thisidgrp'] = 1;
            $thisdataupdate['total'] = $realamount;
            $thisdataupdate['bigtotal'] = $realamount;
            $thisdataupdate['balance'] = $realamount;
            $thisdataupdate['isActive'] = true;
            //$db->PDOINSERT($thistable, $thisdatainsert);
            $thiswhere = ["recno" => $_SESSION['thisrecno']];
            $db->PDOUpdate($thistable, $thisdataupdate, $thiswhere);
        }
        if($realamount > 0)
        {
            if($_SESSION['thisfrom'] != "Daily" && is_null($thisbalance))
            {
                $thisdataupdatecomplete['isPrepaid'] = true;
            }
            if(is_null($thissquareid))
            {
                $thisnewcust = $pt->CreateSquareCustomer($thisuser_recno, $thisfirstname, $thislastname, $thisemail, $thisphone_number, $thisaccesstoken);
                //$thispayment is an array but 1 item in it.
                //$sqrecord will show the returned array
                //$sqlvalue will show the value
                foreach($thisnewcust as $sqrecord => $sqlvalue)
                {
                    if(is_array($sqlvalue))
                    {
                        foreach($sqlvalue as $sqrecord2 => $sqlvalue2)
                        {
                            //file_put_contents('./dodebug/debug.txt', "the id is $sqrecord2 \n", FILE_APPEND);
                            if($sqrecord2 == "id")
                            {
                                //file_put_contents('./dodebug/debug.txt', "the id is $sqrecord2: $sqlvalue2 \n", FILE_APPEND);
                                $thissquarecustomerid = $sqlvalue2;
                            }
                        }
                    }
                }
                $thistable = "users";
                $thisdata = ["squareid$tempsandpropost" => $thissquarecustomerid];
                $thiswhere = ["recno" => $thisuser_recno];
                $db->PDOUpdate($thistable, $thisdata, $thiswhere);
            }
            else
            {
                $thissquarecustomerid = $thissquareid;
            }
            //We need to get the recno of the deposit or the single full payment.
            //We will send this payment into the portal
            
            $thishash = $load_headers->Hash_Me_Recno(time());
            
            //https://developer.squareup.com/reference/sdks/web/payments/card-payments
            
            $thisreturnsarray = $pt->MakeSquarepayment($thissquarecustomerid, $realamount, $thistip, $thishash, $thisaccesstoken, $thistoken);
            //https://developer.squareup.com/reference/square/payments-api
            //file_put_contents('./dodebug/debug.txt', "In Pay NoW what is thisreturns: $thisreturns \n", FILE_APPEND);
            //for the return, we will need the id, 
            //id (payment_id in table)- 'some long number ****'
            //status - COMPLETE or something else, look for COMPETE
            //receipt_number (payment_confirmation in table- 'R23bs'
            //receipt_url - a url to view the receipt
            foreach($thisreturnsarray as $key => $value)
            {
                if(is_array($value))
                {
                    foreach($value as $key1 => $value1)
                    {
                        if($key1 == "id")
                        {
                            $thisdataupdatecomplete['payment_id'] = $value1;
                        }
                        if($key1 == "status")
                        {
                            $squarestatus = $value1;
                        }
                        if($key1 == "receipt_number")
                        {
                            $square_receiptno = $value1;
                            $thisdataupdatecomplete['payment_confirmation'] = $value1;
                        }
                        if($key1 == "receipt_url")
                        {
                            $thisdataupdatecomplete['receipt_url'] = $value1;
                        }
                        if($key1 == "location_id")
                        {
                            $thisdataupdatecomplete['location_id'] = $value1;
                        }
                        if($key1 == "order_id")
                        {
                            $thisdataupdatecomplete['order_id'] = $value1;
                        }
                    }                 
                }
            }
            if($squarestatus == "COMPLETED")
            {
                $thisdataupdatecomplete["source_id"] = $thistoken;
                $thisdataupdatecomplete["isActive"] = true; 
                $thisdataupdatecomplete['isPaid'] = true;
                $thisdataupdatecomplete['iscompleted'] = true;
                if($thisIsdeposit == true)
                {
                    $thiswhere = ["recno" => $thenewrecno];
                }
                else
                {
                    //We pay in full or there is no deposit so we just need to update the appropriate fields with the return values from square.
                    //payment_id
                    //location_id
                    //square_receit
                    //payment_confirmation
                    $thiswhere = ["recno" => $_SESSION['thisrecno']];
                }
                $thistable = "schedule_dates";
                $thisupdate = $db->PDOUpdate($thistable, $thisdataupdatecomplete, $thiswhere);
                //file_put_contents("./dodebug/debug.txt", "$thisupdate \n", FILE_APPEND);
                if($thisupdate == "Success")
                {
                    //We need to send a receipt to the user's email and phone if the user opt-in for text
                    
                    //Send email
                    //$thisfirstname, $thislastname 
                    //$thisservicerecno
                    $setservicetitle = $sc->SetService($db, $thisservicerecno);
                    $getservicetitle = $sc->GetServicetitle();
                    
                    $payment_receipt_subject = $ne->get_paymentreceipt_subject();
                    $payment_receipt_body = $ne->get_paymentreceipt_body($thisfirstname, $thislastname, $realamount, $square_receiptno, $getservicetitle);
                    $guestname = $rs['firstname']." ".$rs['lastname'];
                    $sendto[] = array($thisemail => $guestname);
                    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $payment_receipt_subject, $payment_receipt_body, $attachment);
                    $thisreturns = "Success";
                    //NOW we will need to send a text to the guest.  Howevever, depend on if they opt-in or out so we must check this status in the user's table
                    if($thisopt ==  true)
                    {   
                        /*
                         * We do not have a valid sms setup yet, once we do, we will enable it and we shall be able send text!!!!
                        //By default, the system sets it to true, but when it's false, that means user does not opt out and would like to receive txt.
                        //Now we send a text to the user's phone in the database                        
                        $returnsms = $sms->SendSMSpayment($db, $thisphone_number, $square_receiptno, $realamount, $thisuser_recno, $thisserver);
                        if($returnsms == "")
                        {
                            $thisreturns = "Failed to send SMS";
                        }
                        */
                    }
                }
                $_SESSION['THISCONFIRMATION'] = $square_receiptno;
                header("location: ./paid.php");
            }
        }
        else
        {
            $thisreturns = "Cost is not valid.";
        }
    }
    echo $thisreturns;
}
function Main()
{
    global $db, $load_headers, $pt, $tc, $pc, $mc;
    $caltimertotal = 0;
    $calcosttotal = 0;
    $thistip = "";
    $thiscost = 0;
    $totaltip = 0;
    $bigtotal = 0;
    $promo = 0;
    $thisdate = "";
    $realpromo = "";
    $thistime = "";
    $thistitle = "";
    $thisservices = "";
    $thisrecno = "";
    $usethispromocss = "";
    $usethispromocontainercss = "";
    $depositarray = [];
    $thisdataarray1 = [];
    $thisdataarray2 = [];
    $usethiswidth = "width: 600px;";
    $promostr = "";
    $thispr_recno = "";
    $usethistotal = 0;
    $newfedtax = 0;
    $newstatetax = 0;
    $newcountytax = 0;
    $newcitytax = 0;
    $totalwithoutpromo = 0;
    $getpromoarray = [];
    $thistotalwithtax = 0;
    $isprepaid = false;
    $thisprepaypromo = "";
    $thisdiscount = 0;
    $thispromo = 0;
    $isdeposit = false;
    $thisdeposit = 0;
    $thisbalance = 0;
    $tempsandpropost = "";
    $_SESSION['isLive'] = false;
    $_SESSION['realsandpro'] = "Sandbox";?>
    <div class="main-div-paynow">
        <div name="div_loader" id="div_loader" class="payment-loader-container display-none">
            <img class="payment-loader-img" src="/images/others/loading.gif" />
        </div>
        <?php
        $load_headers::Load_Header_Logo(false);
        
        //The reason why we want to pull the actual barber's recno is because, an admin or another person could be viewing this so we want
        //to make sure we get the actual barber's information when we want to do payment.
        $sqllive = "SELECT sd.isLive, sd.uf_recno as barber_recno FROM schedule_dates sd INNER JOIN users u ON u.recno = sd.uf_recno ";
        $sqllive .= "WHERE sd.recno = ".$_SESSION['thisrecno']." AND u.isLive = true";
        $resultlive = $db->PDOMiniquery($sqllive);
        if($db->PDORowcount($resultlive) > 0)
        {
            foreach($resultlive as $rslive)
            {
                $tempsandpropost = "_pro";
                $_SESSION['isLive'] = true;
                $_SESSION['realsandpro'] = "Production";
            }
        }        
        $sql = "SELECT sd.*, u.square_ev_location_id$tempsandpropost, u.square_application_id$tempsandpropost FROM schedule_dates sd INNER JOIN users u ON u.recno = sd.uf_recno ";
        $sql .= "WHERE sd.recno = ".$_SESSION['thisrecno']." AND sd.payment_id IS NULL";
        //file_put_contents("./dodebug/debug.txt", "what is thisrecno: $sql \n", FILE_APPEND);
        $result = $db->PDOMiniquery($sql);?>
        <br/><br/><br/><?php
        if($db->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                $thisguest = $rs['guest'];
                $thistip = $rs['tip'];
                $thisdeposit = $rs['deposit'];
                $thisbalance = $rs['balance'];
                $thiscost = $rs['cost']; //before tax and promotions
                $totaltip = $rs['totaltip']; //!!!!!!!!!!!!!!!!! we need to calculate the total after tip
                $thistotal = $rs['total'];
                $thisbigtotal = $rs['bigtotal'];
                $promo = $rs['pr_recno'];
                $thispr_recno = $rs['pr_recno'];
                $promostr = "";
                $promrecnostr = "";
                $isdeposit = $rs['isDeposit'];
                $thisdiscount = $rs['discount'];
                $thisdepost = $rs['deposit'];
                $thisevlocationid = $rs["square_ev_location_id$tempsandpropost"];
                $thisapplicationid = $rs["square_application_id$tempsandpropost"];
                if(!is_null($promo))
                {
                    $realpromo = $rs['discount'];
                    $sqlpromo = "SELECT * FROM events WHERE recno IN($promo) ";
                    if($_SESSION['thisfrom'] == "Deposit" || $_SESSION['thisfrom'] == "Prepay" || ($_SESSION['thisfrom'] == "Dashpay" && ($rs['date']) >= date('Y-m-d')))
                    {
                        //file_put_contents("./dodebug/debug.txt", "what is thisfrom in paynow ".$_SESSION['thisfrom']." && ".$rs['date']." >= ".date('Y-m-d')."\n", FILE_APPEND);
                        //If the customer is paying before they come in, we will allow the Prepay discount if appropriate.
                        //We should not have added Prepay before this point.
                        $sqlpromo .= "OR special_event='Prepay' ";
                    }
                    $sqlpromo .= "AND isActive = true";
                    //file_put_contents("./dodebug/debug.txt", "sqlpromo = $sqlpromo \n", FILE_APPEND);
                    $resultpromo = $db->PDOMiniquery($sqlpromo);
                    if($db->PDORowcount($resultpromo) > 0)
                    {
                        foreach($resultpromo as $rspromo)
                        {
                            if($rspromo['special_event'] == "Prepay")
                            {
                                $pc->SetThisdiscount($db, $rspromo['recno'], $thiscost);
                                $thispromo = $pc->GetThisdiscount();
                                if($promostr == "")
                                {
                                    $promostr = $rspromo['special_event'];
                                    $promrecnostr = $rspromo['recno'];
                                }
                                else
                                {
                                    $promostr .= ", ".$rspromo['special_event'];
                                    $promrecnostr .= ",".$rspromo['recno'];
                                }
                            }
                            else
                            {
                                if($promostr == "")
                                {
                                    $promostr = $rspromo['special_event'];
                                    $promrecnostr = $rspromo['recno'];
                                }
                                else
                                {
                                    $promostr .= ", ".$rspromo['special_event'];
                                    $promrecnostr .= ",".$rspromo['recno'];
                                }
                            }
                        }
                    }
                }
                //file_put_contents("./dodebug/debug.txt", "promostr = $promostr \n", FILE_APPEND);
                //file_put_contents("./dodebug/debug.txt", "promrecnostr = $promrecnostr \n", FILE_APPEND);
                $thisrecno = $rs['recno']; //recno from schedule_dates table
                $thisservices = $rs['sr_recno'];   
                
                $thistotaltime = $rs['total_time'];
                $thisdate = date('m/d/Y', strtotime($rs['date']));
                switch(date('g:i', strtotime($rs['slot'])))
                {
                    case "10:00":
                    case "10:30":
                    case "11:00":
                    case "11:30":
                        $thistime = date('g:i', strtotime($rs['slot']))." AM";
                        break;
                    default:
                        $thistime = date('g:i', strtotime($rs['slot']))." PM";
                        break;
                }
            }?>
            <script type="text/javascript">
                $("body").data("square_ev_location_id", "<?php echo $thisevlocationid ?>");
                $("body").data("square_application_id", "<?php echo $thisapplicationid ?>");
            </script>
            <div class="pre-pay-div-payment-container" id="div_payment_container">
                <div class="prepay-div-header-main-container-title align-center">Welcome to the payment portal</div><?php
                if($_SESSION['thisfrom'] == 'Prepay' && $thiscost < $_SESSION['disc_limit'])
                {?>
                    <div class="prepay-div-header-main-container align-center">Please Note: For any discount to apply, the service must be at least $30 dollars or more.  We are sorry for this inconvenient.</div><?php
                }
                else if($_SESSION['thisfrom'] == 'Prepay' && $thiscost >= $_SESSION['disc_limit'])
                {
                    $sqlprepay = "SELECT discount, isDollar FROM events WHERE special_event = 'Prepay' AND isActive = true AND isCombo = true";
                    $resultprepay = $db->PDOMiniquery($sqlprepay);
                    if($db->PDORowcount($resultprepay) > 0)
                    {
                        foreach($resultprepay as $rsprepay)
                        {?>
                            <div class="prepay-div-header-main-container align-center">Please Note: Pay Now and save <?php echo ($rsprepay['isDollar'] == true ? "$".$rsprepay['discount'] :  $rsprepay['discount'].'%')?></div><?php
                        }
                    }
                }?>
                <div class="prepay-div-payment-table-holder" id="div_payment_holder_data">
                    <div class="align-center" style="color: red; background-color: black; min-width: 650px;">We do not keep any credit card information on file!</div>
                    <div class="div-time-holder" id="div_totalstime" style="width: 100%;">
                        <div style="margin: 0px auto; width: 80%;">
                            <div>
                                <div class="pre-pay-schedule-div-label float-left align-right">Name:</div><div id='div_totaltime' class="pre-pay-daily-div-total-val align-left"><?php echo $thisguest ?></div>
                            </div>
                            <div>
                                <div class="pre-pay-schedule-div-label float-left align-right">Date:</div><div id='div_totaltime' class="pre-pay-daily-div-total-val align-left"><?php echo $thisdate ?></div>
                            </div>
                            <div>
                                <div class="pre-pay-schedule-div-label float-left align-right">Appointment:</div><div id='div_totaltime' class="pre-pay-daily-div-total-val align-left"><?php echo $thistime ?></div>
                            </div>
                            <div>
                                <div class="pre-pay-schedule-div-label float-left align-right">For:</div>
                                <div id='div_totaltime' class="pre-pay-daily-div-total-val align-left">
                                    <?php 
                                    $sqlser = "SELECT * FROM service WHERE recno IN(".$thisservices.")";
                                    $resultser = $db->PDOMiniquery($sqlser);?>
                                    <textarea name="txtarea_service" id="txtarea_service" cols="40" rows="10" readonly><?php
                                    if($db->PDORowcount($resultser) > 0)
                                    {
                                        $i = 1;
                                        foreach($resultser as $rsser)
                                        {
                                            echo $i.'. '.$rsser['title'].' ($'.$rsser['price'].')&#013; &#010;';

                                            $depositarray[$rsser['recno']] = $rsser['deposit'];                        
                                            $i++;
                                        }                                            
                                    }?>
                                    </textarea>
                                </div>
                            </div>                            
                        </div>
                        <div style="min-width: 980px;">
                            <div class="align-center" style="width: 800px; margin: 0px auto;">
                                <div class="cursor-pointer pre-pay-div-data-container-slt pre-pay-deposit-data-container float-left" style="margin: 0px auto;" name="pre_pay_full" id="pre_pay_full" onclick="sltPrepay(this);">                                
                                    <div class="pre-pay-schedule-div-label float-left align-right">Cost:</div><div id='div_cost' class="pre-pay-daily-div-total-val align-left">$<?php echo ($thiscost == null) ? 0 : number_format($thiscost,2) ?></div><?php
                                    if($thisdeposit > 0 && $thisbalance > 0)
                                    {?>
                                        <div class="pre-pay-schedule-div-label float-left align-right">Deposited:</div><div id='div_deposit' class="pre-pay-daily-div-total-val align-left pre-pay-promo-disc">$<?php echo number_format($thisdeposit,2) ?></div><?php
                                    }
                                    if($promrecnostr == "")
                                    {
                                        $usethispromocss = "display-none";
                                        $usethispromocontainercss = "visibility-hidden";
                                    }
                                    //file_put_contents("./dodebug/debug.txt", "promrecnostr = ".$promrecnostr." \n", FILE_APPEND);
                                    if($promrecnostr != "" && $thiscost >= $_SESSION['disc_limit'])
                                    {
                                        //Cost must be $30 or more to get any sort of events!
                                        $pc->SetPromo($db, $promrecnostr, $thiscost); //Since we are recalculating in case we have additional promotions, we use the original cost.
                                        $getpromoarray = $pc->GetPromotion();
                                        foreach($getpromoarray as $rec)
                                        {
                                            //$rec -> recno|name|promo|val
                                            $exploderec = explode("|", $rec);
                                            $promorecno = $exploderec[0];
                                            $promoname = $exploderec[1];
                                            $promoval = ($exploderec[2] == "NA" ? '' : $exploderec[2]); //$promo will be either NA which wil be '' or the val in percent, ex, 5%
                                            $promo = $exploderec[3];
                                            //We will check if this $promoval is in percent or in dollar val
                                            if(substr($promoval, -1) == '%')
                                            {
                                                //We just want to get the percent.
                                                
                                            }
                                            //file_put_contents("./dodebug/debug.txt", "promoval = ".$promoval." \n", FILE_APPEND);
                                            //file_put_contents("./dodebug/debug.txt", "promo = ".$promo." \n", FILE_APPEND);?>
                                            <div id='div_promo_lbl' class='<?php echo $usethispromocss ?>'><div class="pre-pay-schedule-div-label float-left align-right">Disc.<?php echo $promoname ?>:</div><div id='div_promo_full' class="pre-pay-daily-div-total-val align-left pre-pay-promo-disc full-pay-tracker" ><?php echo $promoval  ?><?php echo ($promo == '' ? '' : ' ('.($promo).')') ?></div></div><?php
                                        }
                                    }
                                    if($thisdeposit > 0 && $thisbalance > 0)
                                    {?>
                                       <div>
                                            <div class="pre-pay-schedule-div-label float-left align-right">Total:</div><div  id='div_final_cost' class="pre-pay-daily-div-total-val align-left actual-total-cost">$<?php echo number_format($thisbigtotal,2) ?></div>
                                        </div><?php 
                                    }
                                    else
                                    {
                                        $pc->SetThisdiscount($db, $promrecnostr, $thiscost); //Since we are recalculating in case we have additional promotions, we use the original cost.
                                        $thisbigtotal = $thiscost - $pc->GetThisdiscount();?>                                   
                                        <div>
                                            <div class="pre-pay-schedule-div-label float-left align-right">Total:</div><div  id='div_final_cost' class="pre-pay-daily-div-total-val align-left actual-total-cost">$<?php echo number_format($thisbigtotal,2) ?></div>
                                        </div><?php
                                    }?>
                                    <script type="text/javascript">
                                        $('body').data('finalfull', '<?php echo number_format($thisbigtotal,2) ?>');
                                    </script>
                                </div><?php
                                $thisbalance = 0;
                                //file_put_contents("./dodebug/debug.txt", "isdeposit = $thistotal AND thisfrom = ".$_SESSION['thisfrom']." \n", FILE_APPEND);
                                if($isdeposit == true && $_SESSION['thisfrom'] != 'Daily')
                                {?>
                                    <div class="cursor-pointer pre-pay-div-data-container float-right pre-pay-deposit-data-container" style="width: 49%;" name="pre_pay_deposit" id="pre_pay_deposit" onclick="sltPrepay(this);">
                                        <div class="pre-pay-schedule-div-label float-left align-right">Cost:</div><div id='div_cost' class="pre-pay-daily-div-total-val align-left">$<?php echo ($thiscost == null) ? 0 : number_format($thiscost,2) ?></div><?php
                                        $depo = 1;
                                        $thisbalance = $thiscost; //Total of this service
                                        $totaldeposittracker = 0;
                                        //If we are from "Booked", we know there has to be atleast 1 required deposit.
                                        foreach($depositarray as $thiskey => $thisvalue)
                                        {?>
                                            <div class="pre-pay-schedule-div-label float-left align-right">Deposit (<?php echo $depo ?>):</div><div id='div_actual' class="pre-pay-daily-div-total-val align-left actual-total-cost">$<?php echo  number_format($thisvalue,2) ?></div><?php
                                            $thisbalance = $thisbalance - $thisvalue;
                                            $totaldeposittracker += $thisvalue;
                                            $depo++;
                                        }
                                        if(!is_null($thispr_recno))
                                        {
                                            //NOTE, in cases where thsi is the firs time the user book appointment with us, we would
                                            //already have a discount of new customer, if that is the case we need to calculated here.
                                            //First, we check if the discount is null or if there is something in it.  At this point, if there is
                                            //to be anything in here, it would be the prepay, nothing else because we are handling deposit.
                                            
                                            //file_put_contents("./dodebug/debug.txt", "thisdiscount = $thisdiscount \n", FILE_APPEND);
                                            $pc -> SetThisdiscount($db, $thispr_recno, $thiscost);
                                            $promototal = $pc -> GetThisdiscount();
                                            $thisbalance = $thisbalance - $promototal;
                                            //file_put_contents("./dodebug/debug.txt", "realamount = $realamount \n", FILE_APPEND);
                                            
                                            $pc->SetPromo($db, $promrecnostr, $thiscost); //Since we are recalculating in case we have additional promotions, we use the original cost.
                                            $getpromoarray = $pc->GetPromotion();
                                            foreach($getpromoarray as $rec)
                                            {
                                                //$rec -> recno|name|promo|val
                                                $exploderec = explode("|", $rec);
                                                $promorecno = $exploderec[0];
                                                $promoname = $exploderec[1];
                                                $promoval = ($exploderec[2] == "NA" ? '' : $exploderec[2]); //$promo will be either NA which wil be '' or the val in percent, ex, 5%
                                                $promo = $exploderec[3];
                                                //We will check if this $promoval is in percent or in dollar val
                                                if(substr($promoval, -1) == '%')
                                                {
                                                    //We just want to get the percent.

                                                }
                                                //file_put_contents("./dodebug/debug.txt", "promoval = ".$promoval." \n", FILE_APPEND);
                                                //file_put_contents("./dodebug/debug.txt", "promo = ".$promo." \n", FILE_APPEND);
                                                if($promoname == "New Customer")
                                                {?>
                                                    <div class="pre-pay-schedule-div-label float-left align-right">Disc.<?php echo $promoname ?>:</div><div class="pre-pay-daily-div-total-val align-left" ><?php echo $promoval  ?><?php echo ($promo == '' ? '' : ' ('.("$promo OFF of $$thiscost").')') ?></div><?php
                                                }
                                            }
                                        }?>
                                        <div class="pre-pay-schedule-div-label float-left align-right">Balance:</div><div id='div_cost' class="pre-pay-daily-div-total-val align-left">$<?php echo number_format($thisbalance,2) ?> (<span class="">Due in store</span>)</div><?php
                                        $thisdataarray1 = $pt->GetCalculatedtotal($_SESSION['thisfrom'], $totaldeposittracker);
                                        //$thisdataarray is an array ['fedtax' => ####, 'statetax' => ......
                                        if($promo == "")
                                        {
                                            $usethispromocss = "display-none";
                                            $usethispromocontainercss = "visibility-hidden";
                                        }
                                        $realtotal = $thisvalue;?>
                                        <div class="pre-pay-schedule-div-label float-left align-right">Total:</div><div id='div_cost' class="pre-pay-daily-div-total-val align-left">$<?php echo number_format($thisbalance,2) ?></div>
                                        <script type="text/javascript">
                                            $('body').data('finaldeposit', '<?php echo number_format($realtotal,2) ?>');
                                        </script>
                                    </div><?php
                                }?>
                            </div>
                        </div>
                        <div style="min-width: 980px; margin: 0px auto;">
                            <form name="payment-form" id="payment_form" method="post">
                                <div class="pay-now-div-card-holder" name="div_card_container" id="div_card_container">
                                </div>
                                <div class="align-center" style="width: 100%;">
                                    <button type="button" name="btn_card" id="btn_card">Pay</button><?php
                                    if($isdeposit == true && $_SESSION['thisfrom'] != 'Daily')
                                    {?>
                                        <button class="btn-disabled" type="button" name="btn_carddeposit" id="btn_carddeposit" disabled>Pay Deposit</button><?php
                                    }?>
                                </div>
                                <input type="hidden" id="token" name="token">
                                <input type="hidden" id="depofull" name="depofull">
                            </form>
                        </div>
                    </div>
                </div>
            </div><?php            
        }
        else
        {
            header('Location: ./paid.php');
        }
        $load_headers::Load_Footer();?>
    </div><?php
}