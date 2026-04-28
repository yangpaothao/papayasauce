<?php
require("./common/page.php");
require("./common/classes/pageloaderclass.php");
require("./common/pdocon.php");
require("./common/sendmail.php");

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
    <div class="main-div">
        <br><br> <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="main-div-body">
            <?php
                $db = new PDOCON();
                $thistable = "users";
                $thisfields = array('vericode', 'recno');
                $thiswhere = array("vericode" => $_GET['vericode'], "isverified" => false);
                $rs = $db -> PDOQuery($thistable, $thisfields, $thiswhere); //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
                if(!is_null($rs))
                {
                    ////If there is a record, we know we need to verify 
                    foreach($rs as $row)
                    {
                        $recno = $row['recno'];
                    }
                    
                    //But we have to make sure the link is still valid and less than 24 hours.
                    
                    $sql = "SELECT recno from $thistable WHERE recno = $recno AND passwordtimer <= now() + INTERVAL 1 DAY";
                    $result = $db->PDOMiniquery($sql);
                    
                    if($db->PDORowcount($result) > 0)
                    {
                        $thisdata = array('vericode' => NULL, 'isverified' => true);
                        $thiswhere = array('recno' => $recno);
                        $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
                        if($rows != 'Success')
                        {?>
                            <div class="div-verifexpiredheader">Failed to verify account.  Contact your administrator.</div>;
                            <?php
                        }
                        else
                        {?>
                            <div class="div-verifexpiredheader">Your account is verified.</div>";
                            <?php
                        }
                    }
                    else
                    {
                        //Link is more than 24 hours.
                        ?>
                         
                        <div class="div-verifexpiredheader">This link has expired.</div><?php
                    }
                }
                else
                {
                    //If we do not have anything back, that means it's already verified.?> 
                    <div class="div-verifexpiredheader">This link is no longer valid.</div><?php
                }
            ?>
        </div>
    </div><?php
}