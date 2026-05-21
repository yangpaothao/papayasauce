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
            async function tokenize(payment) {
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
                    await tokenize(card);
                });
            }
            function validateState(obj){
                thisArray = [{
                    "this_thisstate": $(obj).val()
                }];
                const thisData = JSON.stringify(thisArray);
                $.ajax({
                url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=ValidateState&thisarray="+thisData,
                type: "POST"
                }).then(function(result) {
                    // Code here will execute *after* the AJAX request is successful
                    if(result == "Bad State"){
                        alert(result);
                        $(obj).focus();
                        $(obj).select();
                    }

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
function ValidateState()
{
    global $db, $pt;
    $returnpost = $pt->AnalyzePosts();
    $returnpost['thisstate'];
    echo $pt->GetStates($returnpost['thisstate']);
    
}
function TokenizedPayment()
{
    global $db, $pt, $mc, $tc, $pc, $load_headers, $ne, $sc, $sms;
    if(!isset($_SESSION['companyname']))
    {
        //if session timed out, we send user to index, front page.
        header("Location: /index.php"); //Unless this is the main/front page, if user does not have a logged session, they will be forced to login first.
    }
    $returnpost = $pt->AnalyzePosts();
    $email = $returnpost['email'];
    $firstname = $returnpost['firstname'];
    $lastname = $returnpost['lastname'];
    $phonenumber = $returnpost['phonenumber'];
    $address1 = $returnpost['address'];
    $address2 = $returnpost['address2'];
    $city = $returnpost['city'];
    $state = $returnpost['state'];
    $zipcode = $returnpost['zipcode'];
    $total = $returnpost['total'];
    $thistoken = $_POST['token']; //We get this token after we tokenized.
    $thisstatus = "Full"; //$_POST['depofull'];
    //file_put_contents("./dodebug/debug.txt", "thisstatus = $thisstatus \n", FILE_APPEND);
    if(isset($_SESSION['isLive']) && $_SESSION['isLive'] == true){
        $tempsandpropost = "_pro";
    }
    $thisserver = $load_headers -> GET_THIS_SERVER();
    
    $sqlacc = "SELECT square_api_access_token$tempsandpropost FROM company_info";
    //file_put_contents("./dodebug/debug.txt", "sqlacc = $sqlacc \n", FILE_APPEND);
    $resultacc = $db->PDOMiniquery($sqlacc);
    foreach($resultacc as $rsacc)
    {
        $thisaccesstoken = $rsacc["square_api_access_token$tempsandpropost"];
    }
    //We got the total from the form.  However, we want to run the total from the array to make sure it matches before we OKayed the total to be paid for security reason.
    //$_SESSION['CARTRECNOTRACKER']
    
    
    
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
    global $db, $pc, $pt;
    $thistotal = 0;
    if(!isset($_SESSION['SELECTED_PRODUCT_RECNO']))
    {?>
        <script type="text/javascript">
            window.location.href = "index.php";
        </script><?php
        exit;
    }?>
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
                $thiscartrecnostr = implode(",", $thiscartrecno);?>
                
                <div class="cart-div-pro-container float-left"><?php
                    $pt->ShowReceipt($db, $thiscartrecnostr, $thistotal);?>
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
                                    <td class="tbl-cart-lbl align-right">Email: <span class="asterisk"> * </span></td>
                                    <td><input type="text" class="cart-input email required" id="txt_email" name="txt_email" value="" onchange="validateEmail(this);" size="20" placeholder="abc@email.com" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">First Name: <span class="asterisk"> * </span></td>
                                    <td><input type="text" class="cart-input" id="txt_firstname" name="txt_firstname" value="" required /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">Last Name: <span class="asterisk"> * </span></td>
                                    <td><input type="text" class="cart-input" id="txt_lastname" name="txt_lastname" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">Phone#: <span class="asterisk"> * </span></td>
                                    <td><input type="text" id="txt_phone_number" name="txt_phonenumber" size="10" value="" placeholder="9161234567" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">Address 1: <span class="asterisk"> * </span></td>
                                    <td><input type="text" class="cart-input" id="txt_address" name="txt_address" value="" placeholder="---Shipping address---" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">Address 2:</td>
                                    <td><input type="text" class="cart-input" id="txt_address2" name="txt_address2" value="" placeholder="" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">City: </td>
                                    <td><input type="text" class="cart-input" id="txt_city" name="txt_city" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">State: </td>
                                    <td><?php
                                        $pt->GetStates($db)->GetSelect("slt_state", '', true, false, false, true, false, true);?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right">Zip-Code: </td>
                                    <td><input type="text" id="txt_zipcode" name="txt_zipcode" size="5" value="" /></td>
                                </tr>
                                <tr>
                                    <td class="tbl-cart-lbl align-right"><b>Total:</b> </td>
                                    <td><input type="text" id="txt_total" name="txt_total" size="10" value="$<?php echo number_format($thistotal,2) ?>" /></td>
                                </tr>
                            </table>
                            <div class="pay-now-div-card-holder" name="div_card_container" id="div_card_container">
                            </div>
                            <div class="align-center" style="width: 100%;">
                                <button type="button" name="btn_card" id="btn_card">Pay</button>
                            </div>
                            <input type="hidden" id="token" name="token">
                        </form>
                    </div>
                </div>
            </div>
            <div class="align-center main-div-footer float-left"><?php echo $pc->Load_Footer();?></div>
        </div>
        

    </div><?php
}