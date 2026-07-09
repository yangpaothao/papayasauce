<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
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
            $pc->Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function Main()
{
    global $pc, $db;?>
    <script type="text/javascript">
        window.location.href = "index.php";
    </script>
    <div class="main-div">
        <div class="paid-div-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);
                //We unset it so it will show zeroes on the cart, top right.
                unset($_SESSION['TEMPCART']);
                unset($_SESSION['CARTTOTALTRACKER']);?>
            </div>
            <div class="float-left div-loginpanel" style="width: 7%;"><?php echo $pc->LoginPanel();?></div>
            <div class="div-content-holder-flex align-center">
                <div class="align-center" style="color: black; width: 100%;">
                    <?php
                    $showonstatement = "SQ *KA'S PAPAYA SAUCE";
                    //BULIT A RECEIPT HERE!!!
                    echo "Thank you for your payment.  A receipt has been sent to your email.  Your statement will show \"$showonstatement\",\n Confirmation#: ".$_SESSION['THISCONFIRMATION'];
                    
                    //At this point, we are done with $_SESSION['THISCONFIRMATION']
                    unset($_SESSION['THISCONFIRMATION']);
                    unset($_SESSION['CARTRECNOTRACKER']);
                    unset($_SESSION['SELECTED_PRODUCT_RECNO']);
                    unset($_SESSION['PAYMENTERROR']);
                    unset($_SESSION['THISCONFIRMATION']);?>
                </div>
            </div>
            <div class="align-center main-div-footer"><?php echo $pc->Load_Footer();?></div>
        </div>
    </div><?php
}