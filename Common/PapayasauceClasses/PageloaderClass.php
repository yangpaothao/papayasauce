<?php
namespace PapayasauceClasses;
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
class PageloaderClass {
    const DEV = 'localhost';
    const PROD = 'https://www.papayasauce.com';

    function __construct() {}
    
    function Load_Header($page)
    {
        ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta content=IE=edge, chrome="1" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Ka's Papaya Sauce</title>
        <link href="./Multiple-Dates-Picker-for-jQuery-UI/jquery-ui.multidatespicker.css" rel="stylesheet" />
        <link href="./jquery-ui-1.14.0/jquery-ui.theme.css" rel="stylesheet" />
        <link href="./jquery-ui-themes-1.13.2/themes/base/jquery-ui.css" rel="stylesheet" />
        <link href="./jquery-ui-themes-1.13.2/themes/base/jquery-ui.min.css" rel="stylesheet" />
        <link href="./jquery-ui-themes-1.13.2/themes/base/theme.css" rel="stylesheet" />
        <link href="./css/all.css" rel="stylesheet" type="text/css" />
        <link href="./select2-4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="./datatables/datatables.css">
        <script type="text/javascript" src="./jquery4/jquery.js"></script>
        <script type="text/javascript" src="./jquery-ui-1.14.0/jquery-ui.js"></script>
        <script type="text/javascript" src="./Multiple-Dates-Picker-for-jQuery-UI/jquery-ui.multidatespicker.js"></script>
        <script type="text/javascript" charset="utf8" src="./datatables/datatables.js"></script> 
        <script src="./select2-4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script type="text/javascript" src="./common/common.js"></script><?php
        if($page == 'addccard' || $page == 'paynow')
        {
            if(isset($_SESSION['isLive']) && $_SESSION['isLive'] == true)
            {?>
                <script src="https://web.squarecdn.com/v1/square.js"></script><?php
            }
            else
            {?>
                <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script><?php
            }
        }?>        
        <script type="text/javascript">
            function logout(){  
                fetchAjaxdata();
            }
            async function fetchAjaxdata(){
                try{
                    const result = await $.ajax({
                    url: '<?=$_SERVER['PHP_SELF']; ?>?cmd=Logout',
                    type: 'POST',
                    contentType: "application/json"
                    });
                    if(result == "Success"){
                        window.location.href = "./index.php";
                    }
                    else
                    {
                        alert(result);
                    }
                }
                catch(error){
                    alert("ERROR in index.php");
                    alert(result);
                }
            }
            function goTocart(){
                window.location.href = "cart.php";
            }
            function doProfile()
            {
                window.location.href = "profile.php";
            }
            function doLogin()
            {
                window.location.href = "login.php";
            }
            function doRegistration(){
               window.location.href = "registration.php"; 
            }
            function dashBoard(){
                window.location.href = "dashboard.php";
            }
            function goTohomepage(){
                window.location.href = "index.php";
            }
            function goTorefund(){
                window.location.href = "refund.php";
            }
            function goTocancelpayment(){
                window.location.href = "cancelpayment.php";
            }
            function doRegister(){
                window.location.href = "registerguest.php";
            }
            function showLoginmenu(){
                $("#responsive_ul_child").toggle();
            }
        </script><?php
    }
    function Logout()
    {
        session_unset();
        session_destroy();
        $_SESSION = [];
        echo "Success";
    }
    function LoadLogo($db)
    {
        //LOGO DETAIL BEST IN HEIGHT: 240, WIDTH: 1200
        $sql = "SELECT mainlogo FROM company_info";
        $result = $db->PDOminiquery($sql);
        if($db->PDOrowcount($result) > 0)
        {
            foreach($result as $rs)
            {?>
                <img class="float-left main-logo-img" src="../images/headers/<?php echo $rs['mainlogo']?>" onerror="this.onerror=null; this.src='./images/headers/defaultimage.png'"><?php 
            }
        }
        else
        {?>
            <img class="float-left main-logo-img" src="../images/headers/papayaheader.png" onerror="this.onerror=null; this.src='./images/headers/defaultimage.png'"><?php  
        }
        
    }
    function LoginPanel()
    {?>
        <div class='for-pc' style="min-width: 100px;">
            <nav class="navmenu float-left" id='navlist' style='width: 50%;'>
                <ul class="ul-parent">
                    <li class="li-menu"><?php
                        if(isset($_SESSION['fullname']))
                        {?>
                            <img onclick="goTohomepage();" class="cursor-pointer main-top-icon-menu swing img-loginpnl" title="<?php echo $_SESSION['fullname'] ?>" src="../../images/others/ripedpapaya.png"/><?php                        
                        }
                        else
                        {?>
                            <img onclick="goTohomepage();" class="cursor-pointer main-top-icon-menu swing" title="HOME!" src="../../images/others/greenpapaya.png"/><?php
                        }?>
                        <ul class="ul-child"><?php
                            if(isset($_SESSION['fullname']))
                            {?>                                    
                                <li onclick="doProfile();" class="li-menu-sub">Profile</li><?php
                                if($_SESSION['isAdmin'] == true)
                                {?>
                                    <li onclick="doRegistration();" class="li-menu-sub">Add An Employee</li><?php
                                }?>
                                <li onclick="dashBoard();" class="li-menu-sub">Dashboard</li>
                                <li onclick="logout();" class="li-menu-sub">Log Out</li><?php
                            }
                            else
                            {?>
                                <li onclick="doLogin();" class="li-menu-sub">Login</li> 
                            <?php
                            }?>
                        </ul>
                    </li>
                </ul>
            </nav>
            <div style="position: relative;">
                <div id="pc_cart_tracker" class="pc-cart-tracker"><?php echo !isset($_SESSION['TEMP_CART']) ? '0' : $_SESSION['TEMP_CART'] ?></div>
                <img id="pc_img_cart" onclick="goTocart();" class="cursor-pointer main-top-icon-menu float-right pc-img-cart" title="Go to cart!" src="../../images/others/cart.png"/>
            </div>
        </div>
        <?php
    }
    static function Load_Footer()
    {?>
        <div class="for-responsive align-center responsive-div-load-footer-container">
            <div class="responsive-div-load-footer-data-container">
                <nav class="responsive-navmenu float-left" id='navlist'>
                    <ul class="responsive-ul-parent">
                        <li class="responsive-li-menu"><?php
                            if(isset($_SESSION['fullname']))
                            {?>
                                <img onclick="showLoginmenu();" class="cursor-pointer responsive-main-top-icon-menu swing" title="<?php echo $_SESSION['fullname'] ?>" src="../../images/others/ripedpapaya.png"/><?php                        
                            }
                            else
                            {?>
                                <img onclick="showLoginmenu();" class="cursor-pointer responsive-main-top-icon-menu swing" title="HOME!" src="../../images/others/greenpapaya.png"/><?php
                            }?>
                            <ul class="responsive-ul-child" id="responsive_ul_child" onmouseout="showLoginmenu();"><?php
                                if(isset($_SESSION['fullname']))
                                {?>                                    
                                    <li onclick="doProfile();" class="li-menu-sub">Profile</li><?php
                                    if($_SESSION['isAdmin'] == true)
                                    {?>
                                        <li onclick="doRegistration();" class="li-menu-sub">Add An Employee</li><?php
                                    }?>
                                    <li onclick="dashBoard();" class="li-menu-sub">Dashboard</li>
                                    <li onclick="logout();" class="li-menu-sub">Log Out</li><?php
                                }
                                else
                                {?>
                                    <li onclick="doLogin();" class="responsive-li-menu-sub">Login</li> 
                                <?php
                                }?>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <div style="position: relative;">
                    <div id="responsive_cart_tracker" class="font-size-1p5em div-cart-tracker-item"><?php echo !isset($_SESSION['TEMP_CART']) ? '0' : $_SESSION['TEMP_CART'] ?></div>
                    <img id="responsive_img_cart" onclick="goTocart();" class="cursor-pointer float-right div-cart-img" title="Go to cart!" src="../../images/others/cart.png"/>
                </div>
            </div>
        </div>
        <div class="main-div-footer">
            <div class="for-responsive-float-left white-space-no-wrap main-div-footer-data">&copy;&nbsp;2024, YPT Web Development, LLC.</div>
        </div><?php
    }
    static function GET_THIS_SERVER()
    {
        $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); // will get 'localhost'
        return($temp_host);
    }
    static function Hash_Me_Password($temppassword = null)
    {
        if(is_null($temppassword))
        {
            //We need to add the vericode to add to the row and also add password
            $temppassword = md5(time()); 
            $temppw1 = substr($temppassword, 0, 3); //Get first 3 of the string
            $temppw2 = substr($temppassword, -3); //Get last 3 of the string
            $realpassword = $temppw2.((int)$temppw2+(int)$temppw1).$temppw1;  //realpasswod will be the last3 and then the sumb of the two and the first 3.
            return($realpassword);
        }
        else
        {
            return(sha1($temppassword));
            //return(hash('sha256', $temppassword));
        }
    }
    static function Hash_MD5($hashthis)
    {   
        return(md5($hashthis));
    }
    static function Hash_SHA256($hashthis)
    {   
        return(hash('sha256', $hashthis));
    }
    static function Base64url($hashthis)
    {   
        // Standard base64 encoding
        $base64 = base64_encode($hashthis);
        // Replace standard Base64 characters with URL-safe variants
        $base64Url = strtr($base64, '+/', '-_');
        // Remove padding '=' characters as they are optional for decoding
        return rtrim($base64Url, '=');
        //return rtrim(strtr(base64_encode($hashthis), '+/', '-_'), '=');
        //return(base64_encode($hashthis));
    }
    static function Hash_Me_Vericode()
    {   
        return(sha1(microtime()));
    }
    static function Hash_Me_Recno($recno)
    {   
        return( sha1($recno)); 
    }
    static function Hash_Me_Questionniare_Answers($tempanswer)
    {   
        $realanswer = sha1($tempanswer);  //This vericode will get sent to user and they will have to click on the link to verify.
        return( $realanswer); 
    }
    static function Check_Time_Conflict($time1, $time2)
    {
        //$time1 (arrival time) and $time2 (depature time) comes in format of 00:00 in 24 hours format.  EX: 01:01 or 23:01
        if(strtotime($time1) > strtotime($time2))
        {
            return("Failed");
        }
        else
        {
            return("Sucess");
        }
    }
}
