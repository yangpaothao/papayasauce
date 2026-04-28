<?php
require __DIR__ . '/common/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
require("./common/page.php");
require("./common/classes/PageloaderClass.php");
require("./common/pdocon.php");
require_once("./common/prompt.php");
$pt = new PROMPT();
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
        <script type="text/javascript">
            function validateForm(){
                if($("#txtsquare_application_id").val() == ""){
                    alert("Please enter your application I.D and try again.");
                    return(false);
                }
            }

            function showHint(){
                if($("#main_div_body_codefetch_right_container").hasClass("display-none")){
                    $("#main_div_body_codefetch_right_container").removeClass("display-none");
                }
                else{
                    $("#main_div_body_codefetch_right_container").addClass("display-none");
                }
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
function SquareUp()
{
    global $db, $load_headers, $pt;
    //WE ARE TO USE PKCE FLOW, NOT THE CODE FLOW
    //IMPORTANT!!!! MAKE SURE IN THE DASH BOARD UNDER APP DETAIL, THE APP VERSIONS ARE THE LATEST!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    ///https://developer.squareup.com/docs/oauth-api/walkthrough
    //this works
    $thisappid = "";
    $developerstatus = $pt->GetDeveloperstatus();
    $tempsandpropost = "";
    $thissession = true;
    if($_SESSION['isLive'] == true){
        $tempsandpropost = "_pro";
        $thissession = false;
    }
    $sql  = "SELECT square_application_id$tempsandpropost FROM users WHERE recno = ".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thisappid = $rs["square_application_id$tempsandpropost"];
    }
    $averylongstr = $load_headers->Hash_SHA256(date("YdmHs")); //202602172305
    $_SESSION['squarestate'] = $averylongstr;
    $_SESSION['code_challenge'] = $averylongstr;
    $thisscope = "ITEMS_READ+MERCHANT_PROFILE_READ+PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS+PAYMENTS_WRITE+PAYMENTS_READ";
    header("Location: $developerstatus?client_id=$thisappid&scope=$thisscope&session=$thissession&state=$averylongstr&code_challenge=$averylongstr");
    exit();
}
function DrawTable($thisappid, $thiscode)
{?>    
    <div class="div-body-container">
            <form name="frmregistration" id="frmfetchcode" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=SquareUp" onsubmit="return validateForm()">
                <table class="tbl-codetech">
                    <tr>
                        <td class="tbl-fetchcode-lbl align-center font-size-2em" colspan="2">Fetch Code</td>
                    </tr>
                    <tr>
                        <td class="tbl-fetchcode-lbl">Application I.D.: <span class="asterisk"> * </span></td>
                        <td class="fetchcodeinput"><input class="align-center" type="text" id="this_square_application_id" name="this_square_application_id" size="45" placeholder="Paste or Type your Application I.D here." value="<?php echo $thisappid ?>" /><img onclick="showHint();" title="Click to see hint on how to get it." src="./images/others/question.png"></td>
                    </tr>
                    <tr>
                        <td class="tbl-fetchcode-lbl">Code:</td>
                        <td class="fetchcodeinput"><input class="align-center" type="text" id="this_square_code" name="this_square_code" size="45"  value="<?php echo $thiscode ?>" readonly /></td>
                    </tr>
                    <tr class="tr-codefetch-btn-container">
                        <td class="tbl-fetchcode-lbl align-center" colspan="2">
                            <input type="submit" id="btnfrmcodefetch" name="btnfrmcodefetch" value="Submit">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    <div class="main-div-body-codefetch-hint-container display-none" id="main_div_body_codefetch_right_container"><img src="./images/others/getappid.png"/></div><?php
}
function Main()
{
    global $db, $load_headers;
    $thisappid = "";
    $thiscode = "";
    $thistable = "users";
    $thisdata = [];
    if(!empty($_POST))
    {
        $thiscode = $_GET['code'];//This is the code we sent to square with the request and square is sending it back, we have to compare
        $thisstate = $_GET['state'];
        
    }?>
    <div class="main-div-codefetch"><?php
        $load_headers::Load_Header_Logo();
        $tempsandpropost = "";
        if($_SESSION['isLive'] == true){
            $tempsandpropost = "_pro";
        }
        if($thiscode == "")
        {
            $sql = "SELECT square_application_id$tempsandpropost, square_code$tempsandpropost FROM $thistable WHERE recno = ".$_SESSION['user_recno'];
            $result = $db ->PDOMiniquery($sql);
            foreach($result as $rs)
            {
                $thisappid = $rs["square_application_id$tempsandpropost"];
                $thiscode = $rs["square_code$tempsandpropost"];
            }
            $_SESSION['appid'] = $thisappid;
        }
        else
        {
            if($_SESSION['squarestate'] == $thisstate)
            {
                //verify this code.
                $thisappid = $_SESSION['appid'];
                $thisdata = ["square_code" => $thiscode];
                $thiswhere = ['recno' => $_SESSION['user_recno']];
                $db->PDOUpdate($thistable, $thisdata, $thiswhere);
            }
        }
        DrawTable($thisappid, $thiscode);?>
    </div><?php
}