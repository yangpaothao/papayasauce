<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
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
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $pc->Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
               getSourceid();
            });
            function submitDeposit(thisrecno){   
                //thisrecno is the recno for table schedule_dates
                if(validateCC() === false){
                    return(false);
                }
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
                //alert($("body").data("square_application_id"));
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
function AddCart()
{
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    if(!isset($_SESSION['TEMP_CART']))
    {
        $_SESSION['TEMP_CART'] = $returnpost['thisval'];
    }
    else
    {
        $_SESSION['TEMP_CART'] = $_SESSION['TEMP_CART'] + $returnpost['thisval'];
    }
    echo $_SESSION['TEMP_CART'];
}
function MiniImgslt(){
    global $pt;
    $returnpost = $pt->AnalyzePosts();
    echo "./images/others/products/".$returnpost['thiscatename']."/".$returnpost['thisrecno']."/large/l_".substr($returnpost['thisattachment'],2);
}
function Main()
{
    global $db, $pc;
    if(!isset($_SESSION['SELECTED_PRODUCT_RECNO']))
    {?>
        <script type="text/javascript">
            window.location.href = "index.php";
        </script><?php
        exit;
    }
    ?>
    <div class="main-div">
        <div class="pro-div-data-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);?>
            </div>
            <div class="float-left div-loginpanel"><?php echo $pc->LoginPanel();?></div>

            <div class="cart-div-content-holder-flex float-left"><?php
                //echo json_encode($_SESSION['CARTRECNOTRACKER']);
                //We only want to get the key so we can make a list of recno for the sql below.
                $thiscartrecno = array_keys($_SESSION['CARTRECNOTRACKER']);
                //echo json_encode($thiscartrecno);
                //now that $thiscartrecno is an array with just the recno, we need to convert it into a string separated by ","
                //implode
                $thiscartrecnostr = implode(",", $thiscartrecno);
                //
                //1 month from today only
                $sql = "SELECT p.*, c.name as cname FROM products p INNER JOIN category c ON p.foreign_cat_recno = c.recno WHERE p.recno IN ($thiscartrecnostr) ORDER BY p.name";
                //file_put_contents("./dodebug/debug.txt", 'Front sql event? '.$sql, FILE_APPEND);
                $result = $db -> PDOMiniquery($sql);
                if($db->PDORowcount($result) > 0)
                {
                    $i = 1;?>
                    <div class="cart-div-pro-container float-left"><?php
                        foreach($result as $rs)
                        {
                            $thisdir = "./images/others/products/".$rs['cname']."/".$rs['recno'];?>
                            <div class="align-right cart-div-content-holder-flex-data-container" style="display: inline-block;">  
                                <div class="float-left cart-img-data-container">
                                    <div class="float-left" ><img id="large_img_container" class="img-cart-review" src="<?php echo $thisdir ?>/mini/s_<?php echo substr($rs['attachment'],2) ?>" onerror="this.onerror=null;this.src='./images/others/default.png" /></div>
                                    <div><?php echo $rs['name'] ?></div>
                                    <div>Items: <?php echo $_SESSION['CARTRECNOTRACKER'][$rs['recno']] ?></div>
                                    <div class="font-weight-bold">$<?php echo number_format($rs['price'], 2) ?></div>
                                    <div class="float-left"><textarea class="cart-txtarea" rows="7" readonly><?php echo $rs['description'] ?></textarea></div>
                                </div>
                            </div><?php
                            $i++;
                        }?>
                    </div>
                    <div class="float-left align-left" style="width: 50%; min-width: 320px;"><?php
                        $sqlid = "SELECT square_application_id, square_ev_location_id FROM company_info";
                        $resultid = $db->PDOMiniquery($sqlid);
                        foreach($resultid as $rsid)
                        {
                            $thisappid = $rsid["square_application_id"];
                            $thislocationid = $rsid["square_ev_location_id"];
                        }?>
                        <div class="cart-div-container">
                            <script type="text/javascript">
                                $("body").data("square_application_id", "<?php echo $thisappid ?>");
                                $("body").data("square_ev_location_id", "<?php echo $thislocationid ?>");
                            </script>
                            <form name="payment-form" id="payment_form" method="post">
                                <div class="cart-div-headline-info align-center">ONE time payment only.  We do not keep any credit card information on file!</div>
                                <table class="tbl-cart float-left">
                                    <tr>
                                        <td class="tbl-cart-lbl">Email: <span class="asterisk"> * </span></td>
                                        <td><input type="text" class="cart-input email required" id="txt_email" name="txt_email" value="" onchange="validateEmail(this);" size="20" /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">First Name: <span class="asterisk"> * </span></td>
                                        <td><input type="text" class="cart-input firstname" id="txt_firstname" name="txt_firstname" value="" required /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">Last Name: <span class="asterisk"> * </span></td>
                                        <td><input type="text" class="cart-input lastname" id="txt_lastname" name="txt_lastname" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">Address 1: <span class="asterisk"> * </span></td>
                                        <td><input type="text" class="cart-input lastname" id="txt_address" name="txt_address" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">Last Name: <span class="asterisk"> * </span></td>
                                        <td><input type="text" class="cart-input lastname" id="txt_address2" name="txt_address2" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">City: </td>
                                        <td><input type="text" class="cart-input city " id="txt_city" name="txt_city" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">State: </td>
                                        <td><input type="text" class="state" id="txt_state" name="txt_state" size="2" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="tbl-cart-lbl">Zip-code: </td>
                                        <td><input type="text" class="zipcode" id="txt_zipcode" name="txt_zipcode" size="5" value="" /></td>
                                    </tr>
                                </table>
                                <div class="pay-now-div-card-holder" name="div_card_container" id="div_card_container">
                                </div>
                                <div class="align-center" style="width: 100%;">
                                    <button type="button" name="btn_card" id="btn_card">Pay</button>
                                </div>
                                <input type="hidden" id="token" name="token">
                                <input type="hidden" id="depofull" name="depofull">
                            </form>
                        </div>
                    </div><?php
                }?>
            </div>
            <div class="align-center main-div-footer float-left"><?php echo $pc->Load_Footer();?></div>
        </div>
        

    </div><?php
}