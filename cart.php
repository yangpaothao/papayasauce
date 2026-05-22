<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
use PapayasauceClasses\OrderClass;
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
$oc = new OrderClass();
if(count($_POST) > 0 && isset($_POST['cmd']))
{
    $_REQUEST['cmd']();
    exit();
}
if(count($_POST) > 0 && isset($_POST['token']))
{
    echo var_dump($_POST);
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
            //https://www.google.com/search?q=square+api%2C+PHP+Payments+SDK+example&sca_esv=c84071398b66a2e1&sxsrf=ANbL-n6P1yPIAXFi1NxOlVYEJ26w5yN90w%3A1773282628177&source=hp&ei=RCWyaeeyCaaT8L0P8NLf6AM&iflsig=AFdpzrgAAAAAabIzVE98Flap9obPmHMRlH2ZxviamJPD&ved=0ahUKEwjn5IzJqJmTAxWmCbwBHXDpFz0Q4dUDCCE&uact=5&oq=square+api%2C+PHP+Payments+SDK+example&gs_lp=Egdnd3Mtd2l6IiRzcXVhcmUgYXBpLCBQSFAgUGF5bWVudHMgU0RLIGV4YW1wbGUyBRAhGKABMgUQIRigAUiOP1AAWIY-cAh4AJABAZgBhwKgAYkoqgEHMS4xNi4xMrgBA8gBAPgBAfgBApgCJKAC3ybCAgQQIxgnwgILEAAYgAQYkQIYigXCAg4QLhiABBixAxjRAxjHAcICBRAuGIAEwgILEAAYgAQYsQMYgwHCAgUQABiABMICERAuGIAEGLEDGNEDGIMBGMcBwgIIEC4YgAQYsQPCAggQABiABBixA8ICChAAGIAEGBQYhwLCAgYQABgWGB7CAgUQABjvBcICCBAAGIAEGKIEwgILEAAYgAQYhgMYigXCAgUQIRirAsICBxAhGKABGAqYAwCSBwc4LjE2LjEyoAfozQGyBwcwLjE2LjEyuAfOJsIHBzExLjIxLjTIBzCACAA&sclient=gws-wiz
            async function tokenize(payment) {
                const result = await payment.tokenize();
                if (result.status === 'OK') {
                    //alert(result.token);
                    // Send the token to your backend PHP script
                    $("#token").val(result.token);
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
    global $db, $pt, $mc, $tc, $pc, $load_headers, $ne, $sc, $sms, $oc;
    if(!isset($_SESSION['companyname']))
    {
        //if session timed out, we send user to index, front page.
        header("Location: /index.php"); //Unless this is the main/front page, if user does not have a logged session, they will be forced to login first.
    }
    $tempsandpropost = "";
    $thisreturns = "";
    $thissquareid = NULL;
    $square_receiptno = "";
    $thissquareorderid = "";
    $newuserrecno = "";
    $insertpro = "";
    $returnpost = $pt->AnalyzePostsubmit();
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
    //$thisserver = $load_headers -> GET_THIS_SERVER();
    
    $sqlacc = "SELECT square_api_access_token$tempsandpropost FROM company_info";
    //file_put_contents("./dodebug/debug.txt", "sqlacc = $sqlacc \n", FILE_APPEND);
    $resultacc = $db->PDOMiniquery($sqlacc);
    foreach($resultacc as $rsacc)
    {
        $thisaccesstoken = $rsacc["square_api_access_token$tempsandpropost"];
    }
    //We got the total from the form.  However, we want to run the total from the array to make sure it matches before we OKayed the total to be paid for security reason.
    //$_SESSION['CARTRECNOTRACKER']
    $realtotal = $oc->CalculateTotalorders($db);
    //file_put_contents("./dodebug/debug.txt", "returndata: $returndata \n", FILE_APPEND);
    //file_put_contents("./dodebug/debug.txt", "total $total \n", FILE_APPEND);
    //$total has a leading '$' that we need to remove.  Ex, $120.00
    if(substr($total,1) == $realtotal)
    {
        //file_put_contents("./dodebug/debug.txt", "Is Good \n", FILE_APPEND);
        //If the total coming from the form and the total from the recalculation is the same, that means it is good.
        
        
        if($realtotal > 0)
        {
            if(isset($_SESSION['user_recno']))
            {
                $sql = "SELECT squareid from users WHERE squareid IS NOT NULL AND recno = ".$_SESSION['user_recno'];
                //file_put_contents("./dodebug/debug.txt", "sql = $sql \n", FILE_APPEND);
                $result = $db->PDOMiniquery($sql);
                if($db->PDORowcount($result) > 0)
                {
                    foreach($result as $rs)
                    {
                        $thissquareid = $rs['squareid'];
                    }
                }
            }
            $newuserrecno = $_SESSION['user_recno'];
        }
        if(is_null($thissquareid))
        {
            $thisnewcust = $pt->CreateSquareCustomer($thisuser_recno, $firstname, $lastname, $email, $phonenumber, $thisaccesstoken);
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
            $thisdata = ["email" => $email, 
                         "firstname" => $firstname,
                         "lastname" => $lastname, 
                         "phone_number" => $phonenumber,
                         "address" => $address1,
                         "address2" => $address2,
                         "city" => $city,
                         "state" => $state, 
                         "zipcode" => $zipcode,
                         "squareid$tempsandpropost" => $thissquarecustomerid];

            $newuserrecno = $db->PDOInsert($thistable, $thisdata);
            
            $thistable = "orders";
            $thisdata = ["foreign_user_recno" => $newuserrecno,
                         "products" => json_encode($_SESSION['CARTRECNOTRACKER']),
                         "total" => $realtotal];
            $db->PDOInsert($thistable, $thisdata);
        }
        else
        {
            $thissquarecustomerid = $thissquareid;
        }
        //We need to get the recno of the deposit or the single full payment.
        //We will send this payment into the portal

        $thishash = $load_headers->Hash_Me_Recno(time());

        //https://developer.squareup.com/reference/sdks/web/payments/card-payments

        $thisreturnsarray = $pt->MakeSquarepayment($thissquarecustomerid, $realtotal, $thishash, $thisaccesstoken, $thistoken);
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
                        $thissquareorderid = $value1;
                    }
                }                 
            }
        }
        if($squarestatus == "COMPLETED")
        {
            $thisdataupdatecomplete["source_id"] = $thistoken;
            $thisdataupdatecomplete['isPaid'] = true;
            $thisdataupdatecomplete['date'] = date('Y-m-d H:i:s');
            $thiswhere = ["recno" => $newuserrecno];
            
            $thistable = "orders";
            $thisupdate = $db->PDOUpdate($thistable, $thisdataupdatecomplete, $thiswhere);
            //file_put_contents("./dodebug/debug.txt", "$thisupdate \n", FILE_APPEND);
            if($thisupdate == "Success")
            {
                //We need to send a receipt to the user's email and phone if the user opt-in for text

                //Send email
                //$thisfirstname, $thislastname 
                //$thisservicerecno
                $thiscartrecno = array_keys($_SESSION['CARTRECNOTRACKER']);
                $thiscartrecnostr = implode(",", $thiscartrecno);
                $oc->SetReceipt($thiscartrecnostr, $square_receiptno, $thissquareorderid);
                $payment_receipt_body = $oc->ShowReceipt($db);

                $payment_receipt_subject = $ne->get_paymentreceipt_subject();
                $payment_receipt_body = $ne->get_paymentreceipt_body($thisfirstname, $thislastname, $realamount, $square_receiptno, $getservicetitle);
                $guestname = $rs['firstname']." ".$rs['lastname'];
                $sendto[] = array($thisemail => $guestname);
                $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $payment_receipt_subject, $payment_receipt_body, $attachment);
            }
            $_SESSION['THISCONFIRMATION'] = $square_receiptno;
            header("location: ./paid.php");

        }
        else
        {
            $thisreturns = "Cost is not valid.";
        }
    }
    else
    {
        $thisreturns = "Server is having a maintenance right now.  Your order didn't go through.  Please try again some other times.";
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
    global $db, $pc, $pt, $oc;
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
                    $oc->ShowOrderproducts($db, $thiscartrecnostr, $thistotal);?>
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