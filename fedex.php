<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
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
$oc = new OrderClass();
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
        <script type="text/javascript">
            
        </script>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
/*


    $client = new FedExClient($apiKey, $apiSecret, 'https://apis-sandbox.fedex.com');
    $shipment = new FedExShipment($client, $accountNumber);

    $response = $shipment->create($shipper, $recipient, $packages);
    $shipment->saveLabel($response, '/path/to/label.pdf');
 */
function Main()
{
    global $pc, $db, $oc;
    //google search with this phrase PHP, create label with Fedex API
    //This is where we will query the order and send it into FEDEX
    //$_SESSION['STAGEORDERRECNO'];
    $oc->SetCustomer($db, $_SESSION['STAGEORDERRECNO']);
    $getcustomerorderarray = $oc->GetCustomerarray(); 
    $getproductarray = $oc->GetProductarray();?>
    <div class="main-div">
        <div class="paid-div-container">
            <div class="main-logo float-left">
                <?php echo $pc->LoadLogo($db);?>
            </div>
            <div class="float-left div-loginpanel" style="width: 7%;"><?php echo $pc->LoginPanel();?></div>
            <div class="div-content-holder-flex align-center">
                <div class="align-center" style="color: black; width: 100%;">
                    FEDEX
                </div>
            </div>
            <div class="align-center main-div-footer"><?php echo $pc->Load_Footer();?></div>
        </div>
    </div><?php
}