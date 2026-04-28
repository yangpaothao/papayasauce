<?php
require("./common/page.php");
require("./common/pdocon.php");
require("./common/prompt.php");
require("./common/sendsms.php");
require("./common/classes/pageloaderclass.php");

$load_headers = new PageloaderClass();
$db = new PDOCON();
$pt = new PROMPT();

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
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.?>
        <script type="text/javascript">
             function submitRegisterguest(){
                if($("#txtfirstname").val() == ""){
                     alert("Employee First namne can not be empty.");
                     return(false);
                }
                if($("#txtlastname").val() == ""){
                     alert("Employee last name can not be empty.");
                     return(false);
                }
                if($("#txtphone_number").val() == ""){
                     alert("Phone can not be empty.");
                     return(false);
                }
                if(isPhonenumber === false){
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitRegisterguest&'+$("#frmregisterguest").serialize(), function(result){
                    if(result == "Insert OK"){
                        alert("Guest added successfully.");
                        //window.open.href = "localhost";
                        //window.open('','_self').close();
                        window.location.href = "index.php";
                    }
                    else{
                        alert(result);
                        return(false);
                    }
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
function SubmitRegisterguest()
{
    global $db, $ne, $pt, $load_headers; 
    $isFailed = false;
    $realemail = NULL;
    
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
 
    if($pt->CheckPhonenumber($_POST['txtphone_number']) == false)
    {
        $result = "Please enter a proper phone number.  Ex: 1234567890.";
        $isFailed = true;
    }
    else
    {
        $realphonenumber = $_POST['txtphone_number'];
        $realmessage = get_phonenumber_body($thisserver);
        sendsms($realphonenumber, $realmessage);
    }

    echo $result;
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo(false);?>
        <br>
        <div class="div-header-main-container">Guest Registration</div>
        <br>
        <div class="div-body-container">
            <form name="frmregisterguest" id="frmregisterguest" method="post">
                <table class="tbl-guest-register">
                    <tr>
                        <td class="tbl-register-guest-lbl">First Name: <span class="asterisk"> * </span></td>
                        <td class="registration-guest-input"><input type="text" class="firstname" id="txtfirstname" name="txtfirstname" size="30" value="" required /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-guest-lbl">Middle Name: </td>
                        <td class="registration-guest-input"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" size="30" value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-guest-lbl">Last Name: </td>
                        <td class="registration-guest-input"><input type="text" class="lastname" id="txtlastname" name="txtlastname" size="30" value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-guest-lbl">Phone#: <span class="asterisk"> * </span></td>
                        <td class="registration-guest-input"><input type="text" class="email required" id="txtphone_number" name="txtphone_number" size="30" value="" placeholder="1234567890" onchange="validateEmail(this);" size="20" /></td>
                    </tr>
                    <tr class="tr-register-btn-container">
                        <td class="tbl-register-guest-lbl align-center" colspan="2">
                            <button type="button" value="Submit" id="btnfrmregistration" onclick="submitRegisterguest();">Submit</button>
                        </td>
                    </tr>
                </table>
            </form>
            ?>
        </div>
        <?php $load_headers::Load_Footer();?>
    </div><?php
}