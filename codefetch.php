<?php
require __DIR__ . '/Common/vendor/autoload.php';
use PapayasauceClasses\PdoClass;
use PapayasauceClasses\PageloaderClass;
use PapayasauceClasses\PromptClass;
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
$pt = new PromptClass();
$load_headers = new PageloaderClass();
$db = new PdoClass();

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
            $load_headers->Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
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
            function showToken(obj){
                if($(obj).hasClass('codefetch-seeing-eye')){
                    $(obj).removeClass('codefetch-seeing-eye');
                    $(obj).addClass('codefetch-seeing-eye-not');
                    $.ajax({
                        url: "<?=$_SERVER['PHP_SELF']; ?>?cmd=ShowToken",
                        type: "POST"
                    }).then(function(result) {
                        // Code here will execute *after* the AJAX request is successful - sandbox-sq0idb--T0TYR72gfayXs3qWKPynA
                        //alert(result);
                        //We need to change the eye depending on what it is right now
                        $("#this_square_application_id").val(result);
                    }).catch(function(error) {
                        alert(error);
                    });
                }
                else{
                    $(obj).removeClass('codefetch-seeing-eye-not');
                    $(obj).addClass('codefetch-seeing-eye');
                    $("#this_square_application_id").val("*****************************************************");
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
function ShowToken()
{
    global $db;
    $thissandpro = "";
    $thisaccesstoken = "";
    if($_SESSION['thisfrom'] == "Production")
    {
        $thissandpro = "_pro";
    }
    $sql = "SELECT square_refresh_token$thissandpro FROM company_info";
    //file_put_contents("./dodebug/debug.txt", "ShowToken: ".$sql."\n", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thisaccesstoken = $rs["square_refresh_token$thissandpro"];
    }
    echo $thisaccesstoken;
}
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
    $thisscope = "";
    $thissession = true;
    if($_SESSION['thisfrom'] == "Production"){
        $tempsandpropost = "_pro";
        $thissession = false;
    }
    $sql  = "SELECT square_application_id$tempsandpropost FROM company_info";
    //file_put_contents("./dodebug/debug.txt", "sql: ".$sql."\n", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {
        $thisappid = $rs["square_application_id$tempsandpropost"];
    }
    //file_put_contents("./dodebug/debug.txt", "thisscope: ".$thisscope."\n", FILE_APPEND);
    $averylongstatestr = $load_headers->Hash_MD5(date('Y/m/d H:m:s'));
    $_SESSION['squarestate'] = $averylongstatestr;
    
    $averycodeverifier = $load_headers->Hash_MD5(date('Y/m/d H:m:s', strtotime("+1 week"))).$load_headers->Hash_MD5(date('Y/m/d H:m:s', strtotime("+1 week"))); //202602172305
    //file_put_contents("./dodebug/debug.txt", "averycodeverifier: ".$averycodeverifier."\n", FILE_APPEND);
    
    $hash = hash('sha256', $averycodeverifier, true);
    //https://www.google.com/search?q=what+is+Encoding.ASCII.GetBytes%28codeVerifier%29+in+php+example&sca_esv=6924ee9ad18dd21d&sxsrf=ANbL-n7QSAIpTciT91I4t6kMZcgozlbN5g%3A1771653851584&ei=20qZaaazI-6ymtkP-fbBiAY&biw=1920&bih=953&ved=0ahUKEwjmjPTz9OmSAxVumSYFHXl7EGEQ4dUDCBM&uact=5&oq=what+is+Encoding.ASCII.GetBytes%28codeVerifier%29+in+php+example&gs_lp=Egxnd3Mtd2l6LXNlcnAiPHdoYXQgaXMgRW5jb2RpbmcuQVNDSUkuR2V0Qnl0ZXMoY29kZVZlcmlmaWVyKSBpbiBwaHAgZXhhbXBsZTIHECEYoAEYCjIHECEYoAEYCjIHECEYoAEYCjIFECEYqwJIix5QzApYix1wAXgBkAEAmAFqoAHEC6oBBDEzLjO4AQPIAQD4AQGYAhGgAu8LwgIKEAAYsAMY1gQYR8ICBBAjGCfCAgUQABjvBZgDAIgGAZAGCJIHBDE0LjOgB85vsgcEMTMuM7gH7AvCBwQyLjE1yAcZgAgA&sclient=gws-wiz-serp
    
    //file_put_contents("./dodebug/debug.txt", "averycodeverifier specialized: ".$averycodeverifier."\n", FILE_APPEND);
    $_SESSION['code_verifier'] = $averycodeverifier;
    
    //$averycodechallenge = $load_headers->Base64url($load_headers->Hash_SHA256($averycodeverifier));
    
    $averycodechallenge = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($hash));
    
    //file_put_contents("./dodebug/debug.txt", "sha256: ".$load_headers->Hash_SHA256($averycodeverifier)."\n", FILE_APPEND);
    //file_put_contents("./dodebug/debug.txt", "averycodechallenge: $averycodechallenge", FILE_APPEND);
    $thisscope = "ITEMS_READ+ITEMS_WRITE+MERCHANT_PROFILE_READ+PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS+PAYMENTS_WRITE+PAYMENTS_READ+CUSTOMERS_WRITE+CUSTOMERS_READ+DISPUTES_WRITE+DISPUTES_READ";
    $thisscope .= "+INVENTORY_READ+INVENTORY_WRITE+MERCHANT_PROFILE_WRITE+MERCHANT_PROFILE_READ+ORDERS_WRITE+ORDERS_READ";
    
    //$thisscope = "PAYMENTS_READ+PAYMENTS_WRITE+CUSTOMERS_READ+CUSTOMERS_WRITE+DISPUTES_WRITE+DISPUTES_READ+MERCHANT_PROFILE_READ+MERCHANT_PROFILE_WRITE+PAYMENTS_WRITE+PAYMENTS_READ";
    //$thisscope .= "+PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS";
    //Permissions that we might need https://developer.squareup.com/docs/oauth-api/square-permissions
    header("Location: $developerstatus?client_id=$thisappid&session=$thissession&scope=$thisscope&state=$averylongstatestr&code_challenge=$averycodechallenge");
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
                        <td class="tbl-fetchcode-lbl white-space-no-wrap">Application I.D.: <span class="asterisk"> * </span></td>
                        <td class="fetchcodeinput"><input class="align-left" type="text" id="this_square_application_id" name="this_square_application_id" style="width: 85%;" size="35" placeholder="Paste or Type your Application I.D here." value="<?php echo $thisappid ?>" /><img onclick="showHint();" title="Click to see hint on how to get it." src="./images/others/question.png"></td>
                    </tr>
                    <tr>
                        <td class="tbl-fetchcode-lbl">Code:</td>
                        <td class="fetchcodeinput"><input class="align-left" type="text" id="this_square_code" name="this_square_code" size="45" style="width: 85%;"  value="<?php echo $thiscode ?>" readonly /></td>
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
function DrawTabletoken($thistoken, $thisexpiredate)
{
    //This table shows the refresh token (maybe the same) but should have a different expiration date.?>    
    <div style="margin: 0px auto;">
            <form name="frmregistration" id="frmfetchcode" method="post">
                <table class="tbl-codetech">
                    <tr>
                        <td class="tbl-fetchcode-lbl align-center font-size-2em" colspan="2">Refresh Token</td>
                    </tr>
                    <tr>
                        <td class="tbl-fetchcode-lbl">Refresh Token.: <span class="asterisk"> * </span></td>
                        <td class="fetchcodeinput">
                            <input class="align-left" type="text" id="this_square_application_id" name="this_square_application_id" style="width: 85%;" size="45" placeholder="Paste or Type your Application I.D here." style="height: 25px; min-width: 200px; width: 100%;" value="<?php echo $thistoken ?>" readonly />
                            <img id="codefetch_seeing_eye" class="codefetch-seeing-eye-container codefetch-seeing-eye cursor-pointer" onclick="showToken(this);"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="tbl-fetchcode-lbl">Expire Date:</td>
                        <td class="fetchcodeinput"><input class="align-left" type="text" id="this_square_access_token_expire_date" style="width: 85%;" name="this_square_access_token_expire_date" size="45"  style="height: 25px; min-width: 200px; width: 100%;"value="<?php echo date('m/d/Y', strtotime($thisexpiredate)) ?>" readonly /></td>
                    </tr>
                </table>
            </form>
        </div>
    <div class="main-div-body-codefetch-hint-container display-none" id="main_div_body_codefetch_right_container"><img src="./images/others/getappid.png"/></div><?php
}
function Main()
{
    global $db, $load_headers, $pt;
    //https://developer.squareup.com/docs/oauth-api/migrate-to-refresh-tokens
    //https://developer.squareup.com/reference/square/o-auth-api/obtain-token
    //https://developer.squareup.com/explorer/square_2026-01-22/o-auth-api/obtain-token
    //https://developer.squareup.com/docs/oauth-api/create-urls-for-square-authorization - explained how the url works
    //https://developer.squareup.com/docs/oauth-api/receive-and-manage-tokens - how to create a url receivr
    
    //Walk through example of how to go through the process of obtaining code, created code challenge and such
    //https://www.oauth.com/playground/authorization-code-with-pkce.html
    $thisrecno = "";
    $thisappid = "";
    $thiscode = "";
    $thissecret = "";
    $thistable = "company_info";
    $thisdata = [];
    $thisrefreshexpiredate = "";
    $thisrereshtoken = "";
    $thisrefreshtoken = "";
    if(!empty($_POST))
    {
        $thiscode = $_GET['code'];//This is the code we sent to square with the request and square is sending it back, we have to compare
        $thisstate = $_GET['state'];
        
    }?>
    <div class="main-div">
        <div class="index-div-container">
            <div class="main-logo float-left">
                <?php echo $load_headers->LoadLogo($db);?>
            </div>
            <div class="float-left div-loginpanel" style="width: 7%;"><?php echo $load_headers->LoginPanel();?></div>
            <div style="width: 100%; height: 100%; min-height: 520px; height: 870px;"><?php
                $tempsandpropost = "";
                if($_SESSION['thisfrom'] == "Production"){
                    $tempsandpropost = "_pro";
                }
                if($thiscode == "")
                {
                    $sql = "SELECT recno, square_application_id$tempsandpropost, square_code$tempsandpropost FROM $thistable";
                    $result = $db ->PDOMiniquery($sql);
                    foreach($result as $rs)
                    {
                        $thisrecno = $rs['recno'];
                        $thisappid = $rs["square_application_id$tempsandpropost"];
                        $thiscode = $rs["square_code$tempsandpropost"];
                    }
                    $_SESSION['appid'] = $thisappid;
                    DrawTable($thisappid, $thiscode);
                }
                else
                {
                    if($_SESSION['squarestate'] == $thisstate)
                    {
                        //verify this code.
                        $thisappid = $_SESSION['appid'];
                        $sql = "SELECT square_api_access_token$tempsandpropost, square_refresh_token$tempsandpropost, square_application_id$tempsandpropost, square_client_secret$tempsandpropost ";
                        $sql .= "FROM company_info";
                        $result = $db->PDOMiniquery($sql);
                        foreach($result as $rs)
                        {
                            $thistoken = $rs["square_api_access_token$tempsandpropost"];
                            $thisrereshtoken = $rs["square_refresh_token$tempsandpropost"];
                            $thisappid = $rs["square_application_id$tempsandpropost"];
                            $thissecret = $rs["square_client_secret$tempsandpropost"];
                        }
                        //We must now use this code to get an access token and a refresh token
                        $thisreturnsarray = $pt->GetAccesstoken($thistoken, $thisrereshtoken, $thisappid, $thissecret, $thiscode, $_SESSION['code_verifier']);
                        //file_put_contents("./dodebug/debug.txt", "thisreturnsarray? $thisreturnsarray\n", FILE_APPEND);

                        if(is_array($thisreturnsarray))
                        {
                            foreach($thisreturnsarray as $key1 => $value1)
                            {
                                if($key1 == "access_token")
                                {
                                    $thisdata["square_api_access_token$tempsandpropost"] = $value1;
                                    //file_put_contents("./dodebug/debug.txt", "access_token? $value1\n", FILE_APPEND);
                                }
                                if($key1 == "expires_at")
                                {
                                    $explodedate = explode("T", $value1);
                                    $thisexpiredate = $explodedate[0];
                                    $thisdata["square_access_token_expire_date$tempsandpropost"] = date('Y-m-d', strtotime($thisexpiredate));
                                    //file_put_contents("./dodebug/debug.txt", "expires_at? $value1\n", FILE_APPEND);
                                }
                                if($key1 == "refresh_token")
                                {
                                    $thisdata["square_refresh_token$tempsandpropost"] = $value1;
                                    $thisrefreshtoken = $value1;
                                    //file_put_contents("./dodebug/debug.txt", "refresh_token? $value1\n", FILE_APPEND);
                                }
                                if($key1 == "refresh_token_expires_at")
                                {
                                    $explodedate = explode("T", $value1);
                                    $thisexpiredate = $explodedate[0];
                                    $thisrefreshexpiredate = date("Y-m-d", strtotime($thisexpiredate));
                                    $thisdata["square_refresh_token_expire_date$tempsandpropost"] = $thisrefreshexpiredate;

                                    //file_put_contents("./dodebug/debug.txt", "refresh_token_expires_at? $value1\n", FILE_APPEND);
                                }
                            }   
                        }
                        else
                        {
                            //file_put_contents("./dodebug/debug.txt", "NOT supposed to be here.\n", FILE_APPEND);
                        }
                        $thisdata["square_code$tempsandpropost"] = $thiscode;
                        $thiswhere['recno'] = $thisrecno;   
                        $thisupdate = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
                        //file_put_contents("./dodebug/debug.txt", "sql. $thisupdate\n", FILE_APPEND);
                        if($thisupdate == "Success")
                        {
                            //file_put_contents("./dodebug/debug.txt", "NOT supposed to be here.\n", FILE_APPEND);
                            $thistoken = "*****************************************************";
                            //"2025-04-03T18:31:06Z",
                            DrawTabletoken($thistoken, $thisrefreshexpiredate);
                        }
                    }
                }?>
            </div>
            <div class="align-center main-div-footer"><?php echo $load_headers->Load_Footer();?></div>
        </div>
    </div><?php
}