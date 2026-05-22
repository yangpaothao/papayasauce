<?php
require __DIR__ . '/Common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PdoClass;
require("./Common/sendmail.php");
if($temp_host != "localhost")
{
    require_once("/home1/gcwwkite/public_html/website_ad583fcd/Common/page.php");
}
else
{
    require_once("./Common/page.php");
}
$load_headers = new PageloaderClass();
$db = new PDOCON();

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
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
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
    global $load_headers;?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="main-div-body">
            <div class="align-center" style="color: black;">
                <?php
                //BULIT A RECEIPT HERE!!!
                echo "Thank you for your payment.  A receipt has been sent to your email.\n Confirmation#: ".$_SESSION['THISCONFIRMATION'];
            ?>
            </div>
        </div>
    </div><?php
}