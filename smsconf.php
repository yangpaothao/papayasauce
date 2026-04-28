<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/classes/PageloaderClass.php");
require("./common/pdocon.php");

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
    global $load_headers, $db;?>
    <div class="main-div">
        <br><br> <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="main-div-body">
            <div class="align-center" style="color: black;">
            <?php
                //If we are here, it is going to be a 100% NO.
                if(array_key_exists('recno', $_GET))
                {
                    $thistable = "users";
                    $thisdata["isOpt"] = false;
                    $thiswhere = $_GET['recno'];
                    $thisupdate = $db->PDOUpdate($thistable, $thisdataupdate, $thiswhere);

                    echo "TEXT HAS BEEN STOPPED.  Thank you!.";
                }
                else
                {
                    echo "Came from an invalid link.";
                }?>
            </div>
        </div>
    </div><?php
}?>