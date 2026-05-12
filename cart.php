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
            $(document).ready(function(){
               getSourceid();
            });
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
                    <div class="float-left" style="width: 50%;"><?php
                        foreach($result as $rs)
                        {
                            $thisdir = "./images/others/products/".$rs['cname']."/".$rs['recno'];?>
                            <div class="align-left cart-div-content-holder-flex-data-container" style="display: inline-block;">  
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
                    <div class="float-left" style="background-color: darkred;"><?php
                        
                        ?>
                        <div style="min-width: 742px; margin: 0px auto;">
                            <form name="payment-form" id="payment_form" method="post">
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