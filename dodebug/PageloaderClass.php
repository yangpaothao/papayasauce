<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
class PageloaderClass {
    const DEV = 'localhost';
    const PROD = 'https://www.yptdevelopment.com';

    function __construct() {}
    
    static function Load_Header($page)
    {
        ?>
        <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta content=IE=edge, chrome="1" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Moua Cuts</title>
        <link href="./Multiple-Dates-Picker-for-jQuery-UI/jquery-ui.multidatespicker.css" rel="stylesheet" />
        <link href="./jquery-ui-themes-1.13.2/themes/base/jquery-ui.css" rel="stylesheet" />
        <link href="./jquery-ui-themes-1.13.2/themes/base/jquery-ui.min.css" rel="stylesheet" />
        <link href="./jquery-ui-themes-1.13.2/themes/base/theme.css" rel="stylesheet" />
        <link href="./css/all.css" rel="stylesheet" type="text/css" />
        <link href="./select2-4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="./datatables/datatables.css">
        <script type="text/javascript" src="./jquery-3.7.1/jquery.js"></script>
        <script type="text/javascript" src="./jquery-ui-1.14.0/jquery-ui.js"></script>
        <script type="text/javascript" src="./Multiple-Dates-Picker-for-jQuery-UI/jquery-ui.multidatespicker.js"></script>
        <script type="text/javascript" charset="utf8" src="./datatables/datatables.js"></script> 
        <!--<script type="text/javascript" src="./common/Chartjs4.4.1.js"></script>https://www.w3schools.com/ai/ai_chartjs.asp-->
        <script type="text/javascript" src="./common/common.js"></script>
        <script type="text/javascript" src="./common/common.js"></script>
        <script src="./select2-4.1.0-rc.0/dist/js/select2.min.js"></script><?php
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
            function doProfile()
            {
                window.location.href = "profile.php";
            }
            function doAdmin()
            {
                window.location.href = "admin.php";
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
        </script><?php
    }
    function Logout()
    {
        session_unset();
        session_destroy();
        $_SESSION = [];
        echo "Success";
    }
    static function Load_Header_Logo($isheader = true)
    {?>
        <div><?php
            if(isset($_SESSION['user']))
            {?>
                <div class="float-left signboard" style="width: 10%;"><p class="person-name"><?php echo $_SESSION['fullname'] ?></p></div><?php
            }
            else
            {?>
                <div class="float-left" style="width: 10%;">&nbsp;</div><?php
            }?>
            <div class="float-left div-header-logo-container"><?php  
                $usethiscss = "main-nev-menu-noheader";
                $usethiscsswhereishomedivline = "where-is-home-div";
                $usethiscssmargintop = "margin-top-neg15px";
                if($isheader == true)
                {
                    $usethiscss = "main-nev-menu float-right";
                    if(isset($_SESSION['user']))
                    {?>
                        <a href="/index.php"><img class="main-logo center" src="../images/others/<?php echo $_SESSION['media_dir']?>/logo/<?php echo $_SESSION['main_logo'] ?>" onerror="this.onerror=null; this.src='../../images/headers/mainlogo.png'"></a><?php
                    }
                    else
                    {?>
                        <a href="/index.php"><img class="main-logo center" src="../images/headers/mouacutlogo.png"></a><?php
                    }
                }?>             
            </div>
            <div class="float-right" style="width: 10%; margin-top: -20px;">
                <div class="<?php echo $usethiscss ?>">
                    <?php
                    $displayname = "<div class='hamburger-menu'></div>
                                    <div class='hamburger-menu'></div>
                                    <div class='hamburger-menu'></div>";

                    if(isset($_SESSION['fullname']))
                    {
                        $displayname = $_SESSION['fullname'];
                        $usethiscsswhereishomedivline = "where-is-home-div-btline"; 
                    }?>
                    <div class="float-right <?php echo $usethiscsswhereishomedivline ?>">
                        <nav class="navmenu" id='navlist'>
                            <ul class="ul-parent">
                                <li class="li-menu">
                                    <div class="no-wrap font-size-1em float-left"><img onclick="goTohomepage();" class="float-left cursor-pointer main-top-icon-menu" title="HOME!" src="../../images/others/home.PNG"/></div>
                                    <ul class="ul-child">
                                        <?php
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
                        <?php
                        if(isset($_SESSION['user']))
                        {
                            if($_SESSION['isShowrefund'] == true)
                            {?>
                                <img onclick="goTorefund();" class="float-left image-where-is-refund cursor-pointer main-top-icon-menu" title="Refund!" src="../../images/others/refund.png"/><?php
                            }
                            if($_SESSION['isShowcancel'] == true)
                            {?>
                                <img onclick="goTocancelpayment();" class="float-left image-where-is-cancel cursor-pointer main-top-icon-menu" title="Cancel!" src="../../images/others/cancel.png"/><?php

                            }
                            if($_SESSION['isShowfb'] == true)
                            {   //Have not yet enable this or coded this.  We just need to update the headcounter field in the company_info table everytime
                                //a barber completed a payment for a cut.?>
                                <a href="https://www.facebook.com/search/top/?q=moua%20cuts", target="_blank"><img title="Face Book!" style="width: 30px; height: 30px;" src="./images/others/facebook_icon.png" class="float-right" title="Follow us on facebook!" /></a><?php
                            }
                        }?>
                    </div>
                </div> 
            </div>
        </div><?php
    }
    static function Load_Footer()
    {?>
        <div class="main-div-footer">
            &copy;&nbsp;2024, YPT Web Development, LLC.
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
