<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/classes/PageloaderClass.php");
require("./common/pdocon.php");
require("./common/prompt.php");
$load_headers = new PageloaderClass();
$db = new PDOCON();
$pt = new PROMPT();
if(count($_POST) > 0 && isset($_POST['cmd']))
{
    $_REQUEST['cmd']();
    exit();
}
if(count($_POST) > 0 && isset($_POST['token']))
{
    TokenizedAddcard();
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
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
               getsourceidAddcc(); 
            });
            async function tokenizeAddcc(payment) {
                const result = await payment.tokenize();
                if (result.status === 'OK') {
                    // Send the token to your backend PHP script
                    $("#token").val(result.token);
                    //alert(result.token);
                    $("#payment_form").submit();
                } else {
                    alert("Please enter your card information correctly and try again.");
                    return(false);
                    //alert(result.errors);
                    //alert('Tokenization failed. See console for details.');
                }
            }
            async function getsourceidAddcc() {
                //4111 1111 1111 1111
                const payments = Square.payments($("body").data("square_application_id"), $("body").data("square_ev_location_id"));
                const card = await payments.card();
                await card.attach('#div_card_container');
                $('#btn_card').on('click', async function(event){
                    await tokenizeAddcc(card);
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
function TokenizedAddcard()
{
    global $db, $pt, $load_headers;
    $thistoken = $_POST['token']; //We get this token after we tokenized.
    $tempsandpropost = "";
    $thissquareid = "";
    $firstname = "";
    $lastname = "";
    $email = "";
    $phonenumber = "";
    $address = "";
    $city = "";
    $state = ""; 
    $zipcode = "";
    $realdata = [];
    //file_put_contents("./dodebug/debug.txt", "realsandpro = ".$_SESSION['realsandpro']." \n", FILE_APPEND);
    if($_SESSION['realsandpro'] == "Production")
    {
        $tempsandpropost = "_pro";
    }
    $sqlacc = "SELECT square_api_access_token$tempsandpropost  FROM users WHERE recno = ".$_SESSION['barberrecno_addcart'];
    //file_put_contents("./dodebug/debug.txt", "sqlacc = $sqlacc \n", FILE_APPEND);
    $resultacc = $db->PDOMiniquery($sqlacc);
    foreach($resultacc as $rsacc)
    {
        $thisaccesstoken = trim($rsacc["square_api_access_token$tempsandpropost"]); //This will the the barber's accesstoken
    }
    //file_put_contents("./dodebug/debug.txt", "thisaccesstoken = $thisaccesstoken \n", FILE_APPEND);
    //Now we have to check if this user already has square id, if not we have to get it fro the user before we can save the card
    $sql = "SELECT squareid, firstname, lastname, address, city, state, zipcode, email, phone_number FROM users WHERE recno = ".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
   
    foreach($result as $rs)
    {
        $thissquareid = $rs['squareid'];
        $firstname = $rs['firstname'];
        $lastname = $rs['lastname'];
        $address = $rs['address'];
        $city = $rs['city'];
        $state = $rs['state']; 
        $zipcode = $rs['zipcode'];
        $email = $rs['email'];
        $phonenumber = $rs['phone_number'];
    }

    if(is_null($thissquareid))
    {
        //We have to now create the customer at this point to try to get the squareid
        $thisnewcust = $pt->CreateSquareCustomer($_SESSION['user_recno'], $firstname, $lastname, $email, $phonenumber, $thisaccesstoken);
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
                        $thissquareid = $sqlvalue2;
                    }
                }
            }
        }
        $thistable = "users";
        $thisdata = ["squareid$tempsandpropost" => $thissquareid];
        $thiswhere = ["recno" => $_SESSION['user_recno']];
        $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    }
    //idempotencyKey
    $idemkey = substr($load_headers -> Hash_SHA256(date('Y-m-d H:s')), 0, 25);
    
    file_put_contents('./dodebug/debug.txt', "ispro? ".$_SESSION['realsandpro']." \n", FILE_APPEND);
    file_put_contents('./dodebug/debug.txt', "idemkey $idemkey \n", FILE_APPEND);
    file_put_contents('./dodebug/debug.txt', "token $thistoken \n", FILE_APPEND);
    file_put_contents('./dodebug/debug.txt', "thisaccesstoken $thisaccesstoken \n", FILE_APPEND);
    file_put_contents('./dodebug/debug.txt', "thissquareid $thissquareid \n", FILE_APPEND);
    //$thisaccesstoken - Must be the Outh Access point
    $thisnewcardarray = $pt->SavedcCard($_SESSION['user_recno'], $firstname, $lastname, $email, $phonenumber, $phonenumber, $address, $city, $state, $zipcode, $idemkey, $thissquareid, $thistoken, $thisaccesstoken);
    file_put_contents('./dodebug/debug.txt', "the id is thisnewcardarray: $thisnewcardarray \n", FILE_APPEND);
    /*
    $searcharray = ['customer_id', 'merchant_id', 'fingerprint', 'id', 'last_4', 'exp_year', 'exp_month'];
    $temppage = "addccard";
    $tempfuncname = "TokenizedAddcard";
    $i = 1;
    $pt->AnalyzeArraysearch($temppage, $tempfuncname, $i, $searcharray, $thisnewcardarray, &$realdata);
    $realdata['$realdata'] = $_SESSION['barberrecno_addcart'];
    $realdata['foreign_users_recno'] = $_SESSION['user_recno'];
    $thistable = "payment_methods";
    $thisdata = $realdata;
    $db->PDOInsert($thistable, $thisdata);
    */
}
function Main()
{
    global $db, $load_headers;?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo();
        $_SESSION['realsandpro'] = "Sandbox";
        $tempsandpropost = "";
        $thisappid = "";
        $thislocationid = "";
        $sqllive = "SELECT isLive FROM users WHERE recno = ".$_SESSION['barberrecno_addcart']." AND isLive = true";
        $resultlive = $db->PDOMiniquery($sqllive);
        if($db->PDORowcount($resultlive) > 0)
        {
            $tempsandpropost = "_pro";
            $_SESSION['realsandpro'] = "Production";
        }
        $sql = "SELECT square_ev_location_id$tempsandpropost, square_application_id$tempsandpropost FROM users WHERE recno = ".$_SESSION['barberrecno_addcart'];
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thislocationid = $rs["square_ev_location_id$tempsandpropost"];
            $thisappid = $rs["square_application_id$tempsandpropost"];
        }?>
        <br>
        <div class="main-div-body">
            <script type="text/javascript">
                $("body").data("square_ev_location_id", "<?php echo $thislocationid ?>");
                $("body").data("square_application_id", "<?php echo $thisappid ?>");
            </script>
            <div class="add-a-new-card align-center">Add a new card</div>
            <div class="align-center" id="div_card_data_contaner" style="width: 480px; margin: 0px auto;">
                <form name="payment-form" id="payment_form" method="post">
                    <div class="pay-now-div-card-holder" name="div_card_container" id="div_card_container"></div>
                    <div class="align-center" style="width: 100%;">
                        <button type="button" name="btn_card" id="btn_card">Add</button>
                    </div>
                    <input type="hidden" id="token" name="token">
                </form>
            </div>
        </div>
    </div><?php
}