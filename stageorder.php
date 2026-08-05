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
            function printAddress(){
                $.ajax({
                    url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=PrintAddress",
                    type: "POST"
                }).then(function(result) {
                    var printWindow = window.open('', '_blank', 'height=600,width=800');
                    printWindow.document.write(result);
                    printWindow.document.close();
                    printWindow.focus();
                    // Small delay ensures external assets like images/barcodes completely load
                    setTimeout(function() {
                        printWindow.print();
                        printWindow.close();
                    }, 500);
                }).catch(function(error) {
                    alert(error);
                });
            }
            function printFedex(){
                window.open('./fedex.php', '_self');
            }
            function printUSPS(){
                
            }
            function printUPS(){
                
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
function PrintAddress()
{
    global $db, $oc;
    //$_SESSION['STAGEORDERRECNO']
    $oc->SetCustomer($db, $_SESSION['STAGEORDERRECNO']);
    $getcustomerorderarray = $oc->GetCustomerarray(); ?>
    <div id="printable-label" style="width: 6in; height: 4in; padding: 10px;">
        <div><strong>To:</strong><?php echo $getcustomerorderarray['firstname'] ?> <?php echo $getcustomerorderarray['lastname'] ?></div>
        <div class='align-left'><?php echo $getcustomerorderarray['address'] ?></div>
        <?php
        if(!is_null($getcustomerorderarray['address2']))
        {?>
            <div class='align-left'><?php echo $getcustomerorderarray['address2'] ?></div><?php
        }?>
        <div class='align-left'><?php echo $getcustomerorderarray['city'] ?>, <?php echo $getcustomerorderarray['state'] ?>, <?php echo $getcustomerorderarray['zipcode'] ?></div>
    </div><?php
}
function Main()
{
    global $pc, $db, $oc;
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
                    <div class="float-left">
                        <table id="tblservicedata" class="tbl-dashboard-company">
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Name: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_name" id="txt_name" value="<?php echo $getcustomerorderarray['firstname'] ?> <?php echo $getcustomerorderarray['lastname'] ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Address: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_address" id="txt_address" value="<?php echo $getcustomerorderarray['address'] ?>" readonly /></td>
                            </tr><?php
                            if(!is_null($getcustomerorderarray['address2']))
                            {?>
                               <tr>
                                    <td class="tbl-dashboard-company-lbl-company align-right">Address2: </td>
                                    <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_address2" id="txt_address2" value="<?php echo $getcustomerorderarray['address2'] ?>" readonly /></td>
                                </tr><?php
                            }?>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">City: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_city" id="txt_city" value="<?php echo $getcustomerorderarray['city'] ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">State: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_state" id="txt_state" value="<?php echo $getcustomerorderarray['state'] ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Zip-Code: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_zipcode" id="txt_zipcode" value="<?php echo $getcustomerorderarray['zipcode'] ?>" readonly /></td>
                            </tr>
                           <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Phone No.: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_phone_number" id="txt_phone_number" value="<?php echo $getcustomerorderarray['phonenumber'] ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Email: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_email" id="txt_email" value="<?php echo $getcustomerorderarray['email'] ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right" colspan="2"></td>                                
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Date: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_date" id="txt_date" value="<?php echo date('m/d/Y', strtotime($getcustomerorderarray['date'])) ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Confirmation: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_payment_confirmation" id="txt_payment_confirmation" value="<?php echo $getcustomerorderarray['confirmation'] ?>" readonly /></td>
                            </tr> 
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-right">Total: </td>
                                <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_total" id="txt_total" value="<?php echo number_format($getcustomerorderarray['total'],2) ?>" readonly /></td>
                            </tr>
                            <tr>
                                <td class="tbl-dashboard-company-lbl-company align-center" colspan="2">Products</td>                                
                            </tr><?php
                            $i = 0;
                            foreach($getproductarray as $key => $value)
                            {
                                $i++;?>
                                <tr>
                                    <td class="tbl-dashboard-company-lbl-company align-right"><?php echo $i ?>.</td>
                                    <td><input type="text" class="dashboard-company-input-company align-left float-left" name="txt_total" id="txt_total" value="<?php echo $key ?>(<?php echo $value ?>)" readonly /></td>
                                </tr><?php
                            }?>
                        </table>
                    </div>
                    <div class="align-center" style="width: 100%;">
                        <button class="float-left cursor-pointer" type="button" name="btn_card" id="btn_card" onclick="printAddress();">Print Address</button>
                        <button class="float-left cursor-pointer" type="button" name="btn_card" id="btn_card" onclick="printFedex();">Fed-Ex Label</button>
                        <button class="float-left cursor-pointer" type="button" name="btn_card" id="btn_card" onclick="printUSPS();">USPS Label</button>
                        <button class="float-left cursor-pointer" type="button" name="btn_card" id="btn_card" onclick="printUPS();">UPS Label</button>
                    </div>
                </div>
            </div>
            <div class="align-center main-div-footer"><?php echo $pc->Load_Footer();?></div>
        </div>
    </div><?php
}